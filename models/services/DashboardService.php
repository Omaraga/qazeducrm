<?php

namespace app\models\services;

use app\models\enum\StatusEnum;
use app\models\Group;
use app\models\Lesson;
use app\models\Lids;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
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
        $this->today = date('Y-m-d');
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
            ->andWhere(['>=', 'date', date('Y-m-01')])
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
     */
    public function getWeekPaymentsData(): array
    {
        $weekStart = strtotime('monday this week');
        $weekEnd = strtotime('sunday this week');

        $data = [];
        for ($i = $weekStart; $i <= $weekEnd; $i += 86400) {
            $date = date('Y-m-d', $i);
            $amount = Payment::find()
                ->andWhere(['organization_id' => $this->organizationId])
                ->andWhere(['date' => $date])
                ->andWhere(['type' => Payment::TYPE_PAY])
                ->sum('amount') ?? 0;
            $data[] = (float) $amount;
        }

        return $data;
    }

    /**
     * Лейблы дней недели (для графика)
     */
    public function getWeekLabels(): array
    {
        $weekStart = strtotime('monday this week');
        $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

        $labels = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('d.m', $weekStart + ($i * 86400));
            $labels[] = $days[$i] . ' ' . $date;
        }

        return $labels;
    }
}
