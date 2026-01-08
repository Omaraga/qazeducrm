<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\models\enum\StatusEnum;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Group;
use Yii;

/**
 * Отчет о посещаемости учеников
 *
 * Показывает статистику посещаемости по группам,
 * выявляет проблемных учеников, тренды
 */
class PupilsAttendanceReport extends BaseReport
{
    public function getId(): string
    {
        return 'pupils-attendance';
    }

    public function getTitle(): string
    {
        return 'Посещаемость';
    }

    public function getDescription(): string
    {
        return 'Статистика посещаемости занятий';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_PUPILS;
    }

    public function getIcon(): string
    {
        return 'calendar';
    }

    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
            OrganizationRoles::ADMIN,
            OrganizationRoles::TEACHER,
        ];
    }

    /**
     * Проверка - является ли пользователь учителем
     */
    protected function isTeacher(): bool
    {
        return RoleChecker::isTeacherOnly();
    }

    /**
     * Получить ID групп учителя (для фильтрации)
     * Возвращает null если пользователь не учитель
     * Возвращает пустой массив если учитель без групп
     */
    protected function getTeacherGroupIds(): ?array
    {
        if (!$this->isTeacher()) {
            return null; // Не учитель - фильтр не нужен
        }

        $teacherId = RoleChecker::getCurrentTeacherId();
        if (!$teacherId) {
            return []; // Учитель без ID - пустой результат
        }

        return \app\models\relations\TeacherGroup::find()
            ->select('target_id')
            ->where(['related_id' => $teacherId])
            ->andWhere(['is_deleted' => 0])
            ->column();
    }

    public function getAvailableFilters(): array
    {
        return ['date_range', 'group'];
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            // Статистика по группам (findWithDeleted т.к. используем alias и свой фильтр is_deleted)
            $query = Group::findWithDeleted()
                ->alias('g')
                ->select([
                    'g.id',
                    'g.code',
                    'g.name',
                    // Количество занятий
                    'COUNT(DISTINCT l.id) as lessons_count',
                    // Общее количество посещений
                    'COUNT(la.id) as total_records',
                    // Посещения (статус = посещение)
                    'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END) as visits',
                    // Пропуски
                    'SUM(CASE WHEN la.status IN (' . LessonAttendance::STATUS_MISS_WITH_PAY . ', ' . LessonAttendance::STATUS_MISS_WITHOUT_PAY . ', ' . LessonAttendance::STATUS_MISS_VALID_REASON . ') THEN 1 ELSE 0 END) as misses',
                ])
                ->leftJoin('lesson l', 'l.group_id = g.id AND l.is_deleted = 0')
                ->leftJoin('lesson_attendance la', 'la.lesson_id = l.id')
                ->andWhere(['g.is_deleted' => 0])
                ->andWhere(['g.status' => StatusEnum::STATUS_ACTIVE])
                ->groupBy(['g.id', 'g.code', 'g.name'])
                ->orderBy(['g.name' => SORT_ASC]);

            $this->applyOrganizationFilter($query, 'g.organization_id');

            // Для учителя - только его группы
            $teacherGroupIds = $this->getTeacherGroupIds();
            if ($teacherGroupIds !== null) {
                // Это учитель
                if (empty($teacherGroupIds)) {
                    // У учителя нет групп - возвращаем пустой результат
                    return [];
                }
                $query->andWhere(['g.id' => $teacherGroupIds]);
            }

            if ($filter->dateFrom) {
                $query->andWhere(['>=', 'l.date', $filter->dateFrom]);
            }
            if ($filter->dateTo) {
                $query->andWhere(['<=', 'l.date', $filter->dateTo]);
            }
            if ($filter->groupId) {
                $query->andWhere(['g.id' => $filter->groupId]);
            }

            $results = $query->asArray()->all();

            // Рассчитываем процент посещаемости
            foreach ($results as &$row) {
                $row['attendance_rate'] = $row['total_records'] > 0
                    ? round($row['visits'] / $row['total_records'] * 100, 1)
                    : 0;
                $row['miss_rate'] = $row['total_records'] > 0
                    ? round($row['misses'] / $row['total_records'] * 100, 1)
                    : 0;
            }

            return $results;
        }, 60);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            // findWithDeleted т.к. используем alias и lesson_attendance не имеет is_deleted в БД
            $query = LessonAttendance::findWithDeleted()
                ->alias('la')
                ->leftJoin('lesson l', 'l.id = la.lesson_id')
                ->andWhere(['l.is_deleted' => 0]);

            $this->applyOrganizationFilter($query, 'l.organization_id');

            // Для учителя - только его группы
            $teacherGroupIds = $this->getTeacherGroupIds();
            if ($teacherGroupIds !== null) {
                if (empty($teacherGroupIds)) {
                    return [
                        'total_records' => 0,
                        'visits' => 0,
                        'misses' => 0,
                        'attendance_rate' => 0,
                        'lessons_count' => 0,
                    ];
                }
                $query->andWhere(['l.group_id' => $teacherGroupIds]);
            }

            if ($filter->dateFrom) {
                $query->andWhere(['>=', 'l.date', $filter->dateFrom]);
            }
            if ($filter->dateTo) {
                $query->andWhere(['<=', 'l.date', $filter->dateTo]);
            }
            if ($filter->groupId) {
                $query->andWhere(['l.group_id' => $filter->groupId]);
            }

            $total = (clone $query)->count();
            $visits = (clone $query)->andWhere(['la.status' => LessonAttendance::STATUS_VISIT])->count();
            $misses = (clone $query)->andWhere(['la.status' => [
                LessonAttendance::STATUS_MISS_WITH_PAY,
                LessonAttendance::STATUS_MISS_WITHOUT_PAY,
                LessonAttendance::STATUS_MISS_VALID_REASON,
            ]])->count();

            // Количество проведённых занятий
            $lessonsQuery = Lesson::find()
                ->andWhere(['is_deleted' => 0])
                ->andFilterWhere(['>=', 'date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'date', $filter->dateTo])
                ->andFilterWhere(['group_id' => $filter->groupId]);
            $this->applyOrganizationFilter($lessonsQuery);

            // Для учителя - только его группы (teacherGroupIds уже проверен выше)
            if ($teacherGroupIds !== null && !empty($teacherGroupIds)) {
                $lessonsQuery->andWhere(['group_id' => $teacherGroupIds]);
            }

            $lessonsCount = $lessonsQuery->count();

            $attendanceRate = $total > 0 ? round($visits / $total * 100, 1) : 0;

            return [
                'total_records' => (int)$total,
                'visits' => (int)$visits,
                'misses' => (int)$misses,
                'attendance_rate' => $attendanceRate,
                'lessons_count' => (int)$lessonsCount,
            ];
        }, 60);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'attendance_rate',
                'label' => 'Посещаемость',
                'icon' => 'check-circle',
                'color' => 'success',
                'format' => 'percent',
            ],
            [
                'key' => 'visits',
                'label' => 'Посещений',
                'icon' => 'user-check',
                'color' => 'primary',
                'format' => 'number',
            ],
            [
                'key' => 'misses',
                'label' => 'Пропусков',
                'icon' => 'user-x',
                'color' => 'danger',
                'format' => 'number',
            ],
            [
                'key' => 'lessons_count',
                'label' => 'Занятий',
                'icon' => 'calendar',
                'color' => 'info',
                'format' => 'number',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            // Посещаемость по дням (findWithDeleted т.к. lesson_attendance не имеет is_deleted в БД)
            $query = LessonAttendance::findWithDeleted()
                ->alias('la')
                ->select([
                    'DATE(l.date) as date',
                    'COUNT(*) as total',
                    'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END) as visits',
                ])
                ->leftJoin('lesson l', 'l.id = la.lesson_id')
                ->andWhere(['l.is_deleted' => 0])
                ->andFilterWhere(['>=', 'l.date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'l.date', $filter->dateTo])
                ->andFilterWhere(['l.group_id' => $filter->groupId]);

            $this->applyOrganizationFilter($query, 'l.organization_id');

            // Для учителя - только его группы
            $teacherGroupIds = $this->getTeacherGroupIds();
            if ($teacherGroupIds !== null) {
                if (empty($teacherGroupIds)) {
                    return null; // У учителя нет групп
                }
                $query->andWhere(['l.group_id' => $teacherGroupIds]);
            }

            $data = $query
                ->groupBy(['DATE(l.date)'])
                ->orderBy(['date' => SORT_ASC])
                ->asArray()
                ->all();

            if (empty($data)) {
                return null;
            }

            $chartData = [];
            foreach ($data as $row) {
                $rate = $row['total'] > 0 ? round($row['visits'] / $row['total'] * 100, 1) : 0;
                $chartData[] = [
                    'date' => Yii::$app->formatter->asDate($row['date'], 'short'),
                    'value' => $rate,
                ];
            }

            return $this->buildLineChart($chartData, 'Посещаемость %', 'success');
        }, 60);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'code', 'label' => 'Код'],
            ['field' => 'name', 'label' => 'Группа'],
            ['field' => 'lessons_count', 'label' => 'Занятий', 'format' => 'number'],
            ['field' => 'visits', 'label' => 'Посещений', 'format' => 'number'],
            ['field' => 'misses', 'label' => 'Пропусков', 'format' => 'number'],
            ['field' => 'attendance_rate', 'label' => 'Посещаемость', 'format' => 'percent'],
        ];
    }
}
