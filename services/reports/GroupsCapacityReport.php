<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\enum\StatusEnum;
use app\models\Group;
use app\models\Lesson;
use app\models\relations\EducationGroup;
use app\models\Pupil;
use Yii;

/**
 * Отчет о загрузке групп
 *
 * Показывает статистику по группам:
 * - количество учеников
 * - количество занятий
 * - активность
 */
class GroupsCapacityReport extends BaseReport
{
    public function getId(): string
    {
        return 'operations-groups';
    }

    public function getTitle(): string
    {
        return 'Загрузка групп';
    }

    public function getDescription(): string
    {
        return 'Статистика по группам';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_OPERATIONS;
    }

    public function getIcon(): string
    {
        return 'users';
    }

    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
            OrganizationRoles::ADMIN,
        ];
    }

    public function getAvailableFilters(): array
    {
        return ['date_range'];
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            // Получаем все активные группы (findWithDeleted т.к. используем alias и свой фильтр is_deleted)
            $groupsQuery = Group::findWithDeleted()
                ->alias('g')
                ->select([
                    'g.id',
                    'g.code',
                    'g.name',
                    'g.type',
                    's.name as subject_name',
                ])
                ->leftJoin('subject s', 's.id = g.subject_id')
                ->andWhere(['g.is_deleted' => 0])
                ->andWhere(['g.status' => StatusEnum::STATUS_ACTIVE]);

            $this->applyOrganizationFilter($groupsQuery, 'g.organization_id');

            $groups = $groupsQuery->asArray()->all();

            $result = [];
            foreach ($groups as $group) {
                // Количество активных учеников в группе (findWithDeleted т.к. используем alias)
                $pupilsCount = EducationGroup::findWithDeleted()
                    ->alias('eg')
                    ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
                    ->innerJoin('pupil p', 'p.id = pe.pupil_id')
                    ->where(['eg.group_id' => $group['id']])
                    ->andWhere(['eg.is_deleted' => 0])
                    ->andWhere(['pe.is_deleted' => 0])
                    ->andWhere(['p.status' => Pupil::STATUS_ACTIVE])
                    ->andWhere(['<=', 'pe.date_start', date('Y-m-d')])
                    ->andWhere(['>=', 'pe.date_end', date('Y-m-d')])
                    ->count();

                // Количество занятий за период
                $lessonsQuery = Lesson::find()
                    ->where(['group_id' => $group['id']])
                    ->andWhere(['is_deleted' => 0]);

                if ($filter->dateFrom) {
                    $lessonsQuery->andWhere(['>=', 'date', $filter->dateFrom]);
                }
                if ($filter->dateTo) {
                    $lessonsQuery->andWhere(['<=', 'date', $filter->dateTo]);
                }

                $lessonsCount = $lessonsQuery->count();
                $finishedLessons = (clone $lessonsQuery)
                    ->andWhere(['status' => Lesson::STATUS_FINISHED])
                    ->count();

                $result[] = [
                    'id' => $group['id'],
                    'code' => $group['code'],
                    'name' => $group['name'],
                    'full_name' => $group['code'] . ' - ' . $group['name'],
                    'subject' => $group['subject_name'] ?? '-',
                    'type' => $group['type'] == Group::TYPE_INDIVIDUAL ? 'Инд.' : 'Групп.',
                    'pupils_count' => (int)$pupilsCount,
                    'lessons_count' => (int)$lessonsCount,
                    'finished_lessons' => (int)$finishedLessons,
                ];
            }

            // Сортируем по количеству учеников (убывание)
            usort($result, function($a, $b) {
                return $b['pupils_count'] <=> $a['pupils_count'];
            });

            return $result;
        }, 120);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $data = $this->getData($filter);

            $totalGroups = count($data);
            $totalPupils = array_sum(array_column($data, 'pupils_count'));
            $totalLessons = array_sum(array_column($data, 'lessons_count'));
            $avgPupils = $totalGroups > 0 ? round($totalPupils / $totalGroups, 1) : 0;

            // Группы без учеников
            $emptyGroups = count(array_filter($data, function($row) {
                return $row['pupils_count'] == 0;
            }));

            return [
                'total_groups' => $totalGroups,
                'total_pupils' => $totalPupils,
                'total_lessons' => $totalLessons,
                'avg_pupils' => $avgPupils,
                'empty_groups' => $emptyGroups,
            ];
        }, 120);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total_groups',
                'label' => 'Групп',
                'icon' => 'folder',
                'color' => 'primary',
                'format' => 'number',
            ],
            [
                'key' => 'total_pupils',
                'label' => 'Учеников',
                'icon' => 'users',
                'color' => 'success',
                'format' => 'number',
            ],
            [
                'key' => 'avg_pupils',
                'label' => 'Среднее в группе',
                'icon' => 'chart',
                'color' => 'info',
                'format' => 'number',
            ],
            [
                'key' => 'empty_groups',
                'label' => 'Пустых групп',
                'icon' => 'exclamation',
                'color' => 'warning',
                'format' => 'number',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $data = $this->getData($filter);

            if (empty($data)) {
                return null;
            }

            // Топ-8 групп по количеству учеников
            $topData = array_slice($data, 0, 8);

            $chartData = [];
            foreach ($topData as $row) {
                $chartData[] = [
                    'label' => $row['code'],
                    'value' => $row['pupils_count'],
                ];
            }

            return $this->buildBarChart($chartData, 'Учеников', 'primary');
        }, 120);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'code', 'label' => 'Код'],
            ['field' => 'name', 'label' => 'Название'],
            ['field' => 'subject', 'label' => 'Предмет'],
            ['field' => 'type', 'label' => 'Тип'],
            ['field' => 'pupils_count', 'label' => 'Учеников', 'format' => 'number'],
            ['field' => 'lessons_count', 'label' => 'Занятий', 'format' => 'number'],
            ['field' => 'finished_lessons', 'label' => 'Проведено', 'format' => 'number'],
        ];
    }
}
