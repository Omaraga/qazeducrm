<?php

namespace app\models\services;

use app\helpers\DateHelper;
use app\helpers\RoleChecker;
use app\models\enum\StatusEnum;
use app\models\Group;
use app\models\Lesson;
use app\models\Lids;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\relations\TeacherGroup;
use Yii;

/**
 * DashboardService - сервис для получения данных дашборда
 */
class DashboardService
{
    private int $organizationId;
    private string $today;

    public function __construct(?int $organizationId = null)
    {
        $this->organizationId = $organizationId ?? Organizations::getCurrentOrganizationId();
        $this->today = DateHelper::today();
    }

    /**
     * Получить все данные для дашборда
     */
    public function getStatistics(): array
    {
        return [
            // Основные счётчики
            'pupils' => $this->getPupilsCount(),
            'groups' => $this->getActiveGroupsCount(),
            'revenue' => $this->getMonthlyRevenue(),
            'lessons_today' => $this->getTodayLessonsCount(),

            // Данные для графика платежей за неделю
            'week_payments' => $this->getWeekPaymentsData(),
            'week_labels' => $this->getWeekLabels(),

            // Списки
            'recent_payments' => $this->getRecentPayments(5),
            'today_lessons' => $this->getTodayLessons(5),

            // Лиды
            'new_lids' => $this->getNewLidsCount(),
        ];
    }

    /**
     * Количество активных учеников
     */
    public function getPupilsCount(): int
    {
        return (int) Pupil::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->count();
    }

    /**
     * Количество активных групп
     */
    public function getActiveGroupsCount(): int
    {
        return (int) Group::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['status' => StatusEnum::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Доход за текущий месяц
     */
    public function getMonthlyRevenue(): float
    {
        return (float) (Payment::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'date', DateHelper::startOfMonth()])
            ->andWhere(['type' => Payment::TYPE_PAY])
            ->sum('amount') ?? 0);
    }

    /**
     * Количество занятий сегодня
     */
    public function getTodayLessonsCount(): int
    {
        return (int) Lesson::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['date' => $this->today])
            ->count();
    }

    /**
     * Количество новых лидов
     */
    public function getNewLidsCount(): int
    {
        return (int) Lids::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['status' => Lids::STATUS_NEW])
            ->count();
    }

    /**
     * Последние платежи
     */
    public function getRecentPayments(int $limit = 5): array
    {
        $payments = Payment::find()
            ->joinWith(['pupil', 'method'])
            ->andWhere(['payment.organization_id' => $this->organizationId])
            ->andWhere(['payment.type' => Payment::TYPE_PAY])
            ->orderBy(['payment.date' => SORT_DESC])
            ->limit($limit)
            ->all();

        $result = [];
        foreach ($payments as $payment) {
            $result[] = [
                'id' => $payment->id,
                'date' => $payment->date,
                'pupil' => $payment->pupil ? $payment->pupil->fio : 'Неизвестно',
                'pupil_id' => $payment->pupil_id,
                'amount' => $payment->amount,
                'method' => $payment->method ? $payment->method->name : 'Наличные',
            ];
        }

        return $result;
    }

    /**
     * Занятия сегодня
     */
    public function getTodayLessons(int $limit = 5): array
    {
        $lessons = Lesson::find()
            ->joinWith(['group', 'teacher'])
            ->andWhere(['lesson.organization_id' => $this->organizationId])
            ->andWhere(['lesson.date' => $this->today])
            ->orderBy(['lesson.start_time' => SORT_ASC])
            ->limit($limit)
            ->all();

        $result = [];
        foreach ($lessons as $lesson) {
            $result[] = [
                'id' => $lesson->id,
                'time' => $lesson->start_time ? Yii::$app->formatter->asTime($lesson->start_time, 'short') : '--:--',
                'end_time' => $lesson->end_time ? Yii::$app->formatter->asTime($lesson->end_time, 'short') : '--:--',
                'group' => $lesson->group ? $lesson->group->name : 'Группа',
                'group_id' => $lesson->group_id,
                'teacher' => $lesson->teacher ? ($lesson->teacher->fio ?? $lesson->teacher->username) : 'Преподаватель',
                'status' => $lesson->status,
            ];
        }

        return $result;
    }

    /**
     * Данные платежей за неделю (для графика)
     * Оптимизировано: один запрос вместо 7
     */
    public function getWeekPaymentsData(): array
    {
        $startOfWeek = DateHelper::startOfWeek();
        $endOfWeek = DateHelper::endOfWeek();
        $weekDates = DateHelper::range($startOfWeek, $endOfWeek);

        // Один запрос с группировкой по дате
        $payments = Payment::find()
            ->select(['DATE(date) as payment_date', 'SUM(amount) as total'])
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['type' => Payment::TYPE_PAY])
            ->andWhere(['between', 'DATE(date)', $startOfWeek, $endOfWeek])
            ->groupBy(['DATE(date)'])
            ->asArray()
            ->all();

        // Индексируем результаты по дате
        $paymentsByDate = [];
        foreach ($payments as $payment) {
            $paymentsByDate[$payment['payment_date']] = (float)$payment['total'];
        }

        // Формируем массив данных для графика
        $data = [];
        foreach ($weekDates as $date) {
            $data[] = $paymentsByDate[$date] ?? 0.0;
        }

        return $data;
    }

    /**
     * Лейблы дней недели (для графика)
     */
    public function getWeekLabels(): array
    {
        $weekDates = DateHelper::range(DateHelper::startOfWeek(), DateHelper::endOfWeek());
        $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

        $labels = [];
        foreach ($weekDates as $i => $date) {
            $labels[] = $days[$i] . ' ' . DateHelper::format($date, 'd.m');
        }

        return $labels;
    }

    /**
     * Получить статистику в зависимости от роли пользователя
     * - Директор: полная статистика
     * - Админ: без финансовых данных
     * - Учитель: только свои данные
     */
    public function getStatisticsForRole(): array
    {
        if (RoleChecker::isTeacherOnly()) {
            return $this->getTeacherDashboard();
        }

        if (!RoleChecker::hasFinanceAccess()) {
            return $this->getAdminDashboard();
        }

        return $this->getStatistics();
    }

    /**
     * Дашборд для учителя (только свои данные)
     */
    public function getTeacherDashboard(): array
    {
        $teacherId = RoleChecker::getCurrentTeacherId();

        return [
            // Счетчики учителя
            'my_groups' => $this->getTeacherGroupsCount($teacherId),
            'my_lessons_today' => $this->getTeacherTodayLessonsCount($teacherId),
            'my_students' => $this->getTeacherStudentsCount($teacherId),

            // Занятия учителя на сегодня
            'today_lessons' => $this->getTeacherTodayLessons($teacherId, 10),

            // Группы учителя
            'my_groups_list' => $this->getTeacherGroupsList($teacherId, 5),

            // Флаг - это дашборд учителя
            'is_teacher_dashboard' => true,
        ];
    }

    /**
     * Дашборд для админа (без финансов)
     */
    public function getAdminDashboard(): array
    {
        return [
            // Основные счётчики (без revenue)
            'pupils' => $this->getPupilsCount(),
            'groups' => $this->getActiveGroupsCount(),
            'lessons_today' => $this->getTodayLessonsCount(),
            'new_lids' => $this->getNewLidsCount(),

            // Списки (без платежей)
            'today_lessons' => $this->getTodayLessons(5),

            // Флаг - это дашборд админа
            'is_admin_dashboard' => true,
        ];
    }

    /**
     * Количество групп учителя
     */
    public function getTeacherGroupsCount(int $teacherId): int
    {
        return (int) TeacherGroup::find()
            ->andWhere(['related_id' => $teacherId])
            ->joinWith('group')
            ->andWhere(['group.organization_id' => $this->organizationId])
            ->andWhere(['group.status' => StatusEnum::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Количество занятий учителя сегодня
     */
    public function getTeacherTodayLessonsCount(int $teacherId): int
    {
        return (int) Lesson::find()
            ->andWhere(['organization_id' => $this->organizationId])
            ->andWhere(['teacher_id' => $teacherId])
            ->andWhere(['date' => $this->today])
            ->count();
    }

    /**
     * Количество учеников в группах учителя
     */
    public function getTeacherStudentsCount(int $teacherId): int
    {
        // Получаем ID групп учителя
        $groupIds = TeacherGroup::find()
            ->select('target_id')
            ->andWhere(['related_id' => $teacherId])
            ->column();

        if (empty($groupIds)) {
            return 0;
        }

        // Считаем уникальных учеников в этих группах через EducationGroup
        return (int) \app\models\relations\EducationGroup::find()
            ->select('pupil_id')
            ->distinct()
            ->andWhere(['group_id' => $groupIds])
            ->count();
    }

    /**
     * Занятия учителя сегодня
     */
    public function getTeacherTodayLessons(int $teacherId, int $limit = 5): array
    {
        $lessons = Lesson::find()
            ->joinWith(['group'])
            ->andWhere(['lesson.organization_id' => $this->organizationId])
            ->andWhere(['lesson.teacher_id' => $teacherId])
            ->andWhere(['lesson.date' => $this->today])
            ->orderBy(['lesson.start_time' => SORT_ASC])
            ->limit($limit)
            ->all();

        $result = [];
        foreach ($lessons as $lesson) {
            $result[] = [
                'id' => $lesson->id,
                'time' => $lesson->start_time ? Yii::$app->formatter->asTime($lesson->start_time, 'short') : '--:--',
                'end_time' => $lesson->end_time ? Yii::$app->formatter->asTime($lesson->end_time, 'short') : '--:--',
                'group' => $lesson->group ? $lesson->group->name : 'Группа',
                'group_id' => $lesson->group_id,
                'status' => $lesson->status,
            ];
        }

        return $result;
    }

    /**
     * Список групп учителя
     */
    public function getTeacherGroupsList(int $teacherId, int $limit = 5): array
    {
        $teacherGroups = TeacherGroup::find()
            ->joinWith('group')
            ->andWhere(['teacher_group.related_id' => $teacherId])
            ->andWhere(['group.organization_id' => $this->organizationId])
            ->andWhere(['group.status' => StatusEnum::STATUS_ACTIVE])
            ->limit($limit)
            ->all();

        $result = [];
        foreach ($teacherGroups as $tg) {
            if ($tg->group) {
                $result[] = [
                    'id' => $tg->group->id,
                    'name' => $tg->group->name,
                    'students_count' => $tg->group->getPupilsCount(),
                ];
            }
        }

        return $result;
    }
}
