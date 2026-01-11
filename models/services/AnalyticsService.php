<?php

namespace app\models\services;

use app\helpers\DateHelper;
use app\models\Group;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Lids;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\Room;
use Yii;
use yii\db\Expression;

/**
 * AnalyticsService - сервис расширенной аналитики
 *
 * Метрики:
 * - LTV (Lifetime Value) - пожизненная ценность клиента
 * - Конверсия лидов по источникам
 * - Конверсия по менеджерам
 * - Средняя посещаемость по группам
 * - Churn rate (отток учеников)
 * - Загруженность кабинетов
 * - Доход по группам
 * - Общая задолженность
 */
class AnalyticsService
{
    private int $organizationId;

    public function __construct(?int $organizationId = null)
    {
        $this->organizationId = $organizationId ?? Organizations::getCurrentOrganizationId();
    }

    /**
     * Получить все аналитические метрики
     */
    public function getAllMetrics(): array
    {
        return [
            'ltv' => $this->calculateLTV(),
            'lead_conversion' => $this->getLeadConversionRate(),
            'lead_conversion_by_source' => $this->getLeadConversionBySource(),
            'lead_conversion_by_manager' => $this->getLeadConversionByManager(),
            'attendance_rate' => $this->getOverallAttendanceRate(),
            'attendance_by_group' => $this->getAttendanceByGroup(),
            'churn_rate' => $this->getChurnRate(),
            'room_utilization' => $this->getRoomUtilization(),
            'revenue_by_group' => $this->getRevenueByGroup(),
            'total_debt' => $this->getTotalDebt(),
            'monthly_comparison' => $this->getMonthlyComparison(),
        ];
    }

    // ==================== LTV ====================

    /**
     * Рассчитать средний LTV ученика
     * LTV = Сумма всех платежей ученика
     */
    public function calculateLTV(): array
    {
        // Средний LTV по всем ученикам
        // Используем (new \yii\db\Query()) вместо Payment::find() для внешнего запроса,
        // чтобы избежать автоматического добавления условий ActiveRecord
        $subQuery = Payment::find()
            ->select(['pupil_id', 'SUM(amount) as pupil_total'])
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['type' => Payment::TYPE_PAY])
            ->andWhere(['is_deleted' => 0])
            ->groupBy('pupil_id');

        $avgLTV = (float) (new \yii\db\Query())
            ->select([new Expression('AVG(pupil_total) as avg_ltv')])
            ->from(['sub' => $subQuery])
            ->scalar();

        // Топ-10 учеников по LTV
        $topPupils = Payment::find()
            ->select(['pupil_id', 'SUM(amount) as total_paid'])
            ->joinWith('pupil')
            ->where(['payment.organization_id' => $this->organizationId])
            ->andWhere(['payment.type' => Payment::TYPE_PAY])
            ->andWhere(['payment.is_deleted' => 0])
            ->groupBy('pupil_id')
            ->orderBy(['total_paid' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        return [
            'average' => round($avgLTV, 0),
            'top_pupils' => $topPupils,
        ];
    }

    // ==================== LEAD CONVERSION ====================

    /**
     * Общий коэффициент конверсии лидов
     */
    public function getLeadConversionRate(): array
    {
        $totalLids = (int) Lids::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['is_deleted' => 0])
            ->count();

        $convertedLids = (int) Lids::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['status' => Lids::STATUS_IN_TRAINING])
            ->count();

        $rate = $totalLids > 0 ? round($convertedLids / $totalLids * 100, 1) : 0;

        return [
            'total' => $totalLids,
            'converted' => $convertedLids,
            'rate' => $rate,
        ];
    }

    /**
     * Конверсия лидов по источникам
     */
    public function getLeadConversionBySource(): array
    {
        $sources = Lids::find()
            ->select([
                'source',
                'COUNT(*) as total',
                'SUM(CASE WHEN status = ' . Lids::STATUS_IN_TRAINING . ' THEN 1 ELSE 0 END) as converted'
            ])
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['is_deleted' => 0])
            ->groupBy('source')
            ->orderBy(['total' => SORT_DESC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($sources as $source) {
            $rate = $source['total'] > 0 ? round($source['converted'] / $source['total'] * 100, 1) : 0;
            $result[] = [
                'source' => $source['source'] ?: 'unknown',
                'source_label' => $this->getSourceLabel($source['source']),
                'total' => (int) $source['total'],
                'converted' => (int) $source['converted'],
                'rate' => $rate,
            ];
        }

        return $result;
    }

    /**
     * Конверсия лидов по менеджерам
     */
    public function getLeadConversionByManager(): array
    {
        $managers = Lids::find()
            ->select([
                'manager_id',
                'COUNT(*) as total',
                'SUM(CASE WHEN lids.status = ' . Lids::STATUS_IN_TRAINING . ' THEN 1 ELSE 0 END) as converted'
            ])
            ->joinWith('manager')
            ->where(['lids.organization_id' => $this->organizationId])
            ->andWhere(['lids.is_deleted' => 0])
            ->andWhere(['IS NOT', 'manager_id', null])
            ->groupBy('manager_id')
            ->orderBy(['converted' => SORT_DESC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($managers as $manager) {
            $rate = $manager['total'] > 0 ? round($manager['converted'] / $manager['total'] * 100, 1) : 0;
            $result[] = [
                'manager_id' => (int) $manager['manager_id'],
                'total' => (int) $manager['total'],
                'converted' => (int) $manager['converted'],
                'rate' => $rate,
            ];
        }

        return $result;
    }

    // ==================== ATTENDANCE ====================

    /**
     * Общая посещаемость
     */
    public function getOverallAttendanceRate(): array
    {
        $monthStart = date('Y-m-01');

        // Используем Query вместо ActiveRecord, т.к. lesson_attendance не имеет is_deleted
        $stats = (new \yii\db\Query())
            ->from('lesson_attendance la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['l.organization_id' => $this->organizationId])
            ->andWhere(['>=', 'l.date', $monthStart])
            ->select([
                'total' => 'COUNT(*)',
                'visited' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END)',
            ])
            ->one();

        $total = (int) ($stats['total'] ?? 0);
        $visited = (int) ($stats['visited'] ?? 0);
        $rate = $total > 0 ? round($visited / $total * 100, 1) : 0;

        return [
            'total' => $total,
            'visited' => $visited,
            'rate' => $rate,
        ];
    }

    /**
     * Посещаемость по группам
     */
    public function getAttendanceByGroup(): array
    {
        $monthStart = date('Y-m-01');

        // Используем Query вместо ActiveRecord, т.к. lesson_attendance не имеет is_deleted
        $groups = (new \yii\db\Query())
            ->from('lesson_attendance la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->innerJoin('`group` g', 'g.id = l.group_id')
            ->where(['l.organization_id' => $this->organizationId])
            ->andWhere(['>=', 'l.date', $monthStart])
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'COUNT(*) as total',
                'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END) as visited',
            ])
            ->groupBy(['g.id', 'g.name'])
            ->orderBy(['total' => SORT_DESC])
            ->all();

        $result = [];
        foreach ($groups as $group) {
            $total = (int) $group['total'];
            $visited = (int) $group['visited'];
            $rate = $total > 0 ? round($visited / $total * 100, 1) : 0;

            $result[] = [
                'group_id' => (int) $group['group_id'],
                'group_name' => $group['group_name'],
                'total' => $total,
                'visited' => $visited,
                'rate' => $rate,
            ];
        }

        return $result;
    }

    // ==================== CHURN RATE ====================

    /**
     * Отток учеников (Churn Rate)
     * Ученики, которые не посещали занятия 30+ дней
     */
    public function getChurnRate(): array
    {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        // Активные ученики с обучениями
        // Используем Query чтобы избежать конфликта с автоматическим is_deleted условием
        $activePupils = (new \yii\db\Query())
            ->from('pupil p')
            ->innerJoin('pupil_education pe', 'pe.pupil_id = p.id')
            ->where(['p.organization_id' => $this->organizationId])
            ->andWhere(['p.is_deleted' => 0])
            ->andWhere(['p.status' => Pupil::STATUS_ACTIVE])
            ->andWhere(['pe.is_deleted' => 0])
            ->select('p.id')
            ->distinct()
            ->column();

        if (empty($activePupils)) {
            return [
                'total_active' => 0,
                'churned' => 0,
                'rate' => 0,
            ];
        }

        // Ученики, которые посещали занятия за последние 30 дней
        // Используем Query вместо ActiveRecord, т.к. lesson_attendance не имеет is_deleted
        $recentlyActive = (new \yii\db\Query())
            ->from('lesson_attendance la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['la.pupil_id' => $activePupils])
            ->andWhere(['>=', 'l.date', $thirtyDaysAgo])
            ->andWhere(['la.status' => LessonAttendance::STATUS_VISIT])
            ->select('la.pupil_id')
            ->distinct()
            ->column();

        $totalActive = count($activePupils);
        $churned = $totalActive - count($recentlyActive);
        $rate = $totalActive > 0 ? round($churned / $totalActive * 100, 1) : 0;

        return [
            'total_active' => $totalActive,
            'churned' => $churned,
            'rate' => $rate,
        ];
    }

    // ==================== ROOM UTILIZATION ====================

    /**
     * Загруженность кабинетов
     */
    public function getRoomUtilization(): array
    {
        // Получаем все кабинеты
        $rooms = Room::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['is_deleted' => 0])
            ->all();

        if (empty($rooms)) {
            return [];
        }

        $roomIds = array_map(fn($r) => $r->id, $rooms);

        // Считаем часы занятий за текущий месяц по кабинетам
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $lessonHours = Lesson::find()
            ->select([
                'room_id',
                'SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) / 60 as total_hours'
            ])
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['room_id' => $roomIds])
            ->andWhere(['>=', 'date', $monthStart])
            ->andWhere(['<=', 'date', $monthEnd])
            ->andWhere(['is_deleted' => 0])
            ->groupBy('room_id')
            ->indexBy('room_id')
            ->asArray()
            ->all();

        // Рабочие часы в день (примерно 10 часов) * рабочие дни в месяце (примерно 22)
        $maxHoursPerMonth = 10 * 22; // 220 часов

        $result = [];
        foreach ($rooms as $room) {
            $hours = isset($lessonHours[$room->id]) ? (float) $lessonHours[$room->id]['total_hours'] : 0;
            $utilization = round($hours / $maxHoursPerMonth * 100, 1);

            $result[] = [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'hours_used' => round($hours, 1),
                'utilization' => min($utilization, 100), // Не больше 100%
            ];
        }

        // Сортируем по загруженности
        usort($result, fn($a, $b) => $b['utilization'] <=> $a['utilization']);

        return $result;
    }

    // ==================== REVENUE BY GROUP ====================

    /**
     * Доход по группам
     */
    public function getRevenueByGroup(): array
    {
        // Получаем суммы обучений по группам
        // Используем Query чтобы избежать конфликта с автоматическим is_deleted условием
        $revenues = (new \yii\db\Query())
            ->from('pupil_education pe')
            ->innerJoin('education_group eg', 'eg.education_id = pe.id')
            ->innerJoin('`group` g', 'g.id = eg.group_id')
            ->where(['pe.organization_id' => $this->organizationId])
            ->andWhere(['pe.is_deleted' => 0])
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'SUM(pe.total_price) as total_revenue',
                'COUNT(DISTINCT pe.pupil_id) as students_count'
            ])
            ->groupBy(['g.id', 'g.name'])
            ->orderBy(['total_revenue' => SORT_DESC])
            ->limit(15)
            ->all();

        $result = [];
        foreach ($revenues as $rev) {
            $result[] = [
                'group_id' => (int) $rev['group_id'],
                'group_name' => $rev['group_name'],
                'revenue' => (float) $rev['total_revenue'],
                'students' => (int) $rev['students_count'],
            ];
        }

        return $result;
    }

    // ==================== DEBT ====================

    /**
     * Общая задолженность (отрицательные балансы)
     */
    public function getTotalDebt(): array
    {
        // Ученики с отрицательным балансом
        $debtors = Pupil::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['<', 'balance', 0])
            ->orderBy(['balance' => SORT_ASC])
            ->all();

        $totalDebt = 0;
        $debtorsList = [];

        foreach ($debtors as $pupil) {
            $totalDebt += abs($pupil->balance);
            $debtorsList[] = [
                'pupil_id' => $pupil->id,
                'pupil_name' => $pupil->fio,
                'balance' => $pupil->balance,
            ];
        }

        return [
            'total' => round($totalDebt, 0),
            'count' => count($debtors),
            'debtors' => array_slice($debtorsList, 0, 10), // Топ-10 должников
        ];
    }

    // ==================== MONTHLY COMPARISON ====================

    /**
     * Сравнение с прошлым месяцем
     */
    public function getMonthlyComparison(): array
    {
        $thisMonthStart = date('Y-m-01');
        $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

        // Доход этого месяца
        $thisMonthRevenue = (float) (Payment::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'date', $thisMonthStart])
            ->andWhere(['type' => Payment::TYPE_PAY])
            ->andWhere(['is_deleted' => 0])
            ->sum('amount') ?? 0);

        // Доход прошлого месяца
        $lastMonthRevenue = (float) (Payment::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'date', $lastMonthStart])
            ->andWhere(['<=', 'date', $lastMonthEnd])
            ->andWhere(['type' => Payment::TYPE_PAY])
            ->andWhere(['is_deleted' => 0])
            ->sum('amount') ?? 0);

        // Новые ученики этого месяца
        $thisMonthPupils = (int) Pupil::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $thisMonthStart])
            ->andWhere(['is_deleted' => 0])
            ->count();

        // Новые ученики прошлого месяца
        $lastMonthPupils = (int) Pupil::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $lastMonthStart])
            ->andWhere(['<=', 'FROM_UNIXTIME(created_at)', $lastMonthEnd])
            ->andWhere(['is_deleted' => 0])
            ->count();

        // Новые лиды этого месяца
        $thisMonthLids = (int) Lids::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $thisMonthStart])
            ->andWhere(['is_deleted' => 0])
            ->count();

        // Новые лиды прошлого месяца
        $lastMonthLids = (int) Lids::find()
            ->where(['organization_id' => $this->organizationId])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $lastMonthStart])
            ->andWhere(['<=', 'FROM_UNIXTIME(created_at)', $lastMonthEnd])
            ->andWhere(['is_deleted' => 0])
            ->count();

        return [
            'revenue' => [
                'this_month' => $thisMonthRevenue,
                'last_month' => $lastMonthRevenue,
                'change' => $lastMonthRevenue > 0
                    ? round(($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 1)
                    : 0,
            ],
            'pupils' => [
                'this_month' => $thisMonthPupils,
                'last_month' => $lastMonthPupils,
                'change' => $lastMonthPupils > 0
                    ? round(($thisMonthPupils - $lastMonthPupils) / $lastMonthPupils * 100, 1)
                    : 0,
            ],
            'lids' => [
                'this_month' => $thisMonthLids,
                'last_month' => $lastMonthLids,
                'change' => $lastMonthLids > 0
                    ? round(($thisMonthLids - $lastMonthLids) / $lastMonthLids * 100, 1)
                    : 0,
            ],
        ];
    }

    // ==================== HELPERS ====================

    /**
     * Получить человекочитаемое название источника лида
     */
    private function getSourceLabel(?string $source): string
    {
        $labels = [
            'instagram' => 'Instagram',
            'whatsapp' => 'WhatsApp',
            '2gis' => '2GIS',
            'website' => Yii::t('app', 'Сайт'),
            'referral' => Yii::t('app', 'Рекомендация'),
            'walk_in' => Yii::t('app', 'Пришёл сам'),
            'phone' => Yii::t('app', 'Телефон'),
            'other' => Yii::t('app', 'Другое'),
        ];

        return $labels[$source] ?? ($source ?: Yii::t('app', 'Неизвестно'));
    }
}
