<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\User;
use app\models\TeacherGroup;
use app\models\PupilEducation;
use app\models\Tariff;
use app\models\Organizations;
use app\components\ActiveRecord;
use Yii;

/**
 * Отчет о зарплатах учителей
 *
 * Рассчитывает зарплату учителей на основе:
 * - проведённых занятий
 * - посещаемости учеников
 * - ставок (фикс/процент)
 */
class TeacherSalaryReport extends BaseReport
{
    public function getId(): string
    {
        return 'teachers-salary';
    }

    public function getTitle(): string
    {
        return 'Зарплаты учителей';
    }

    public function getDescription(): string
    {
        return 'Расчёт зарплаты за период';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_TEACHERS;
    }

    public function getIcon(): string
    {
        return 'wallet';
    }

    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
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
            // Получаем всех учителей организации
            $teachers = User::find()
                ->innerJoinWith(['currentUserOrganizations' => function($q) {
                    $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED])
                      ->andWhere(['user_organization.role' => OrganizationRoles::TEACHER]);
                }])
                ->all();

            if (empty($teachers)) {
                return [];
            }

            // Рассчитываем зарплату для каждого учителя
            $result = [];
            foreach ($teachers as $teacher) {
                $salaryData = $this->calculateTeacherSalary($teacher->id, $filter);
                $result[] = [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->fio,
                    'lessons_count' => $salaryData['lessons_count'],
                    'pupils_count' => $salaryData['pupils_count'],
                    'salary' => $salaryData['salary'],
                ];
            }

            // Сортируем по зарплате (убывание)
            usort($result, function($a, $b) {
                return $b['salary'] <=> $a['salary'];
            });

            return $result;
        }, 120);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $data = $this->getData($filter);

            $totalSalary = array_sum(array_column($data, 'salary'));
            $totalLessons = array_sum(array_column($data, 'lessons_count'));
            $teachersCount = count($data);
            $avgSalary = $teachersCount > 0 ? round($totalSalary / $teachersCount) : 0;

            return [
                'total_salary' => $totalSalary,
                'teachers_count' => $teachersCount,
                'total_lessons' => $totalLessons,
                'avg_salary' => $avgSalary,
            ];
        }, 120);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total_salary',
                'label' => 'Общий ФОТ',
                'icon' => 'wallet',
                'color' => 'primary',
                'format' => 'currency',
            ],
            [
                'key' => 'teachers_count',
                'label' => 'Учителей',
                'icon' => 'user-group',
                'color' => 'info',
                'format' => 'number',
            ],
            [
                'key' => 'total_lessons',
                'label' => 'Занятий',
                'icon' => 'calendar',
                'color' => 'success',
                'format' => 'number',
            ],
            [
                'key' => 'avg_salary',
                'label' => 'Средняя ЗП',
                'icon' => 'chart',
                'color' => 'warning',
                'format' => 'currency',
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

            // Топ-5 учителей по зарплате
            $topData = array_slice($data, 0, 5);

            $chartData = [];
            foreach ($topData as $row) {
                $chartData[] = [
                    'label' => $row['teacher_name'],
                    'value' => $row['salary'],
                ];
            }

            return $this->buildBarChart($chartData, 'Зарплата', 'primary');
        }, 120);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'teacher_name', 'label' => 'Учитель'],
            ['field' => 'lessons_count', 'label' => 'Занятий', 'format' => 'number'],
            ['field' => 'pupils_count', 'label' => 'Учеников', 'format' => 'number'],
            ['field' => 'salary', 'label' => 'Зарплата', 'format' => 'currency'],
        ];
    }

    /**
     * Расчёт зарплаты учителя за период
     */
    private function calculateTeacherSalary(int $teacherId, ReportFilterDTO $filter): array
    {
        $dateFrom = $filter->dateFrom ?? date('Y-m-01');
        $dateTo = $filter->dateTo ?? date('Y-m-t');

        // Получаем посещения с оплатой учителю (findWithDeleted т.к. lesson_attendance не имеет is_deleted в БД)
        $attendances = LessonAttendance::findWithDeleted()
            ->alias('la')
            ->innerJoinWith(['lesson' => function($q) {
                $q->andWhere(['<>', 'lesson.is_deleted', 1]);
            }])
            ->where(['lesson.teacher_id' => $teacherId])
            ->andWhere(['lesson.status' => Lesson::STATUS_FINISHED])
            ->andWhere(['>=', 'lesson.date', $dateFrom])
            ->andWhere(['<=', 'lesson.date', $dateTo])
            ->andWhere(['la.status' => [
                LessonAttendance::STATUS_VISIT,
                LessonAttendance::STATUS_MISS_WITH_PAY,
            ]])
            ->all();

        $totalSalary = 0;
        $lessonsIds = [];
        $pupilsIds = [];

        // Кэш для образований учеников
        $pupilEducationsCache = [];

        foreach ($attendances as $attendance) {
            $lesson = $attendance->lesson;
            $lessonsIds[$lesson->id] = true;
            $pupilsIds[$attendance->pupil_id] = true;

            // Получаем ставку учителя в группе
            $teacherGroup = TeacherGroup::find()
                ->where([
                    'related_id' => $teacherId,
                    'target_id' => $lesson->group_id,
                ])
                ->notDeleted()
                ->asArray()
                ->one();

            if (!$teacherGroup) {
                continue;
            }

            // Получаем образование ученика (кэшируем)
            $pupilId = $attendance->pupil_id;
            if (!isset($pupilEducationsCache[$pupilId])) {
                $eduQuery = PupilEducation::find()
                    ->innerJoinWith(['groups' => function($q) use ($lesson) {
                        $q->andWhere(['<>', 'education_group.is_deleted', 1])
                          ->andWhere(['education_group.group_id' => $lesson->group_id]);
                    }])
                    ->where(['pupil_education.pupil_id' => $pupilId])
                    ->andWhere(['<=', 'pupil_education.date_start', $lesson->date])
                    ->andWhere(['>=', 'pupil_education.date_end', $lesson->date])
                    ->notDeleted(PupilEducation::tableName());

                $this->applyOrganizationFilter($eduQuery, 'pupil_education.organization_id');

                $pupilEducationsCache[$pupilId] = $eduQuery->asArray()->one();
            }

            $education = $pupilEducationsCache[$pupilId];
            if (!$education) {
                continue;
            }

            // Расчёт суммы
            if ($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_FIX) {
                // Фиксированная ставка за ученика
                $sum = (float)$teacherGroup['price'];
                $sale = (float)($education['sale'] ?? 0);
                if ($sale > 0) {
                    $sum = $sum * (100 - $sale) / 100;
                }
                $totalSalary += $sum;
            } elseif ($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_PERCENT) {
                // Процент от стоимости
                $tariff = Tariff::findOne($education['tariff_id']);
                if ($tariff) {
                    $lessonCount = 0;
                    foreach ($tariff->subjectsRelation as $subject) {
                        $lessonCount += $subject->lesson_amount;
                    }
                    if ($lessonCount > 0) {
                        $sum = (($education['total_price'] / ($lessonCount * 4.33)) * 50) / 100;
                        $totalSalary += intval($sum);
                    }
                }
            }
        }

        return [
            'salary' => (int)$totalSalary,
            'lessons_count' => count($lessonsIds),
            'pupils_count' => count($pupilsIds),
        ];
    }
}
