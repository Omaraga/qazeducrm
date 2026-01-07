<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationPayment;
use app\models\OrganizationSubscription;
use app\models\SaasPlan;
use app\models\SaasRevenueMonthly;
use app\models\SaasRevenueDaily;

/**
 * Сервис агрегации данных о выручке
 *
 * Использование:
 * - Ежедневно: php yii revenue/daily
 * - Ежемесячно: php yii revenue/aggregate
 */
class RevenueAggregationService
{
    /**
     * Агрегировать данные за день
     */
    public function aggregateDay(string $date): SaasRevenueDaily
    {
        $daily = SaasRevenueDaily::getOrCreate($date);

        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        // Выручка за день
        $paymentStats = OrganizationPayment::find()
            ->select([
                'SUM(amount) as revenue',
                'SUM(discount_amount) as discounts',
                'COUNT(*) as payments_count',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateStart])
            ->andWhere(['<=', 'processed_at', $dateEnd])
            ->asArray()
            ->one();

        $daily->revenue = (float) ($paymentStats['revenue'] ?? 0);
        $daily->discounts = (float) ($paymentStats['discounts'] ?? 0);
        $daily->net_revenue = $daily->revenue - $daily->discounts;
        $daily->payments_count = (int) ($paymentStats['payments_count'] ?? 0);

        // Новые триалы
        $daily->new_trials = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
            ->andWhere(['>=', 'created_at', $dateStart])
            ->andWhere(['<=', 'created_at', $dateEnd])
            ->count();

        // Конверсии триал -> платный
        $daily->conversions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['IS NOT', 'trial_ends_at', null])
            ->andWhere(['>=', 'started_at', $dateStart])
            ->andWhere(['<=', 'started_at', $dateEnd])
            ->count();

        // Новые подписки
        $daily->new_subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['>=', 'started_at', $dateStart])
            ->andWhere(['<=', 'started_at', $dateEnd])
            ->count();

        // Продления (платежи по существующим подпискам)
        $daily->renewals = OrganizationPayment::find()
            ->alias('p')
            ->innerJoin('organization_subscription s', 'p.subscription_id = s.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $dateStart])
            ->andWhere(['<=', 'p.processed_at', $dateEnd])
            ->andWhere(['<', 's.started_at', $dateStart]) // Подписка началась раньше
            ->count();

        // Отмены
        $daily->cancellations = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_CANCELLED])
            ->andWhere(['>=', 'cancelled_at', $dateStart])
            ->andWhere(['<=', 'cancelled_at', $dateEnd])
            ->count();

        // По типам
        $revenueByType = OrganizationPayment::find()
            ->alias('p')
            ->select([
                "SUM(CASE WHEN s.id IS NOT NULL THEN p.amount ELSE 0 END) as subscription_revenue",
                "SUM(CASE WHEN s.id IS NULL THEN p.amount ELSE 0 END) as addon_revenue",
            ])
            ->leftJoin('organization_subscription s', 'p.subscription_id = s.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $dateStart])
            ->andWhere(['<=', 'p.processed_at', $dateEnd])
            ->asArray()
            ->one();

        $daily->subscription_revenue = (float) ($revenueByType['subscription_revenue'] ?? 0);
        $daily->addon_revenue = (float) ($revenueByType['addon_revenue'] ?? 0);

        $daily->calculated_at = date('Y-m-d H:i:s');
        $daily->save();

        return $daily;
    }

    /**
     * Агрегировать данные за месяц
     */
    public function aggregateMonth(string $yearMonth): SaasRevenueMonthly
    {
        $monthly = SaasRevenueMonthly::getOrCreate($yearMonth);

        $monthStart = $yearMonth . '-01 00:00:00';
        $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));
        $prevMonth = date('Y-m', strtotime($yearMonth . '-01 -1 month'));

        // ==================== ВЫРУЧКА ====================

        $paymentStats = OrganizationPayment::find()
            ->select([
                'SUM(amount) as total_revenue',
                'SUM(discount_amount) as total_discounts',
                "SUM(CASE WHEN discount_type = 'promo' THEN discount_amount ELSE 0 END) as promo_discounts",
                "SUM(CASE WHEN discount_type = 'volume' THEN discount_amount ELSE 0 END) as volume_discounts",
                "SUM(CASE WHEN discount_type = 'yearly' THEN discount_amount ELSE 0 END) as yearly_discounts",
                "SUM(CASE WHEN discount_type = 'individual' THEN discount_amount ELSE 0 END) as individual_discounts",
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $monthStart])
            ->andWhere(['<=', 'processed_at', $monthEnd])
            ->asArray()
            ->one();

        $monthly->total_revenue = (float) ($paymentStats['total_revenue'] ?? 0);
        $monthly->total_discounts = (float) ($paymentStats['total_discounts'] ?? 0);
        $monthly->promo_discounts = (float) ($paymentStats['promo_discounts'] ?? 0);
        $monthly->volume_discounts = (float) ($paymentStats['volume_discounts'] ?? 0);
        $monthly->yearly_discounts = (float) ($paymentStats['yearly_discounts'] ?? 0);
        $monthly->individual_discounts = (float) ($paymentStats['individual_discounts'] ?? 0);

        // Выручка по типам
        $revenueByType = OrganizationPayment::find()
            ->alias('p')
            ->select([
                "SUM(CASE WHEN s.id IS NOT NULL THEN p.amount ELSE 0 END) as subscription_revenue",
                "SUM(CASE WHEN s.id IS NULL THEN p.amount ELSE 0 END) as addon_revenue",
            ])
            ->leftJoin('organization_subscription s', 'p.subscription_id = s.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $monthStart])
            ->andWhere(['<=', 'p.processed_at', $monthEnd])
            ->asArray()
            ->one();

        $monthly->subscription_revenue = (float) ($revenueByType['subscription_revenue'] ?? 0);
        $monthly->addon_revenue = (float) ($revenueByType['addon_revenue'] ?? 0);

        // ==================== ОПЕРАЦИИ ====================

        // Новые подписки
        $monthly->new_subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['>=', 'started_at', $monthStart])
            ->andWhere(['<=', 'started_at', $monthEnd])
            ->andWhere(['OR',
                ['trial_ends_at' => null],
                ['<', 'trial_ends_at', 'started_at'],
            ])
            ->count();

        // Продления
        $monthly->renewals = OrganizationPayment::find()
            ->alias('p')
            ->innerJoin('organization_subscription s', 'p.subscription_id = s.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $monthStart])
            ->andWhere(['<=', 'p.processed_at', $monthEnd])
            ->andWhere(['<', 's.started_at', $monthStart])
            ->count();

        // Отмены
        $monthly->cancellations = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_CANCELLED])
            ->andWhere(['>=', 'cancelled_at', $monthStart])
            ->andWhere(['<=', 'cancelled_at', $monthEnd])
            ->count();

        // TODO: Апгрейды и даунгрейды требуют отслеживания изменений плана
        $monthly->upgrades = 0;
        $monthly->downgrades = 0;

        // ==================== MRR МЕТРИКИ ====================

        // MRR на начало месяца
        $monthly->mrr_start = $this->calculateMRRAtDate(date('Y-m-d', strtotime($monthStart . ' -1 day')));

        // MRR на конец месяца
        $monthly->mrr_end = $this->calculateMRRAtDate(date('Y-m-t', strtotime($monthStart)));

        // MRR от новых подписок
        $monthly->mrr_new = $this->calculateNewMRR($monthStart, $monthEnd);

        // MRR Churn (от отменённых подписок)
        $monthly->mrr_churn = $this->calculateChurnedMRR($monthStart, $monthEnd);
        $monthly->churned_mrr = $monthly->mrr_churn;

        // MRR Expansion и Contraction (требуют отслеживания изменений)
        $monthly->mrr_expansion = 0;
        $monthly->mrr_contraction = 0;

        // ==================== СРЕДНИЕ ЧЕКИ ====================

        $avgStats = OrganizationPayment::find()
            ->select([
                'AVG(amount) as avg_value',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $monthStart])
            ->andWhere(['<=', 'processed_at', $monthEnd])
            ->asArray()
            ->one();

        $monthly->avg_subscription_value = (float) ($avgStats['avg_value'] ?? 0);
        $monthly->avg_addon_value = 0; // TODO

        // ==================== ОРГАНИЗАЦИИ ====================

        $monthly->active_organizations = OrganizationSubscription::find()
            ->where(['in', 'status', [
                OrganizationSubscription::STATUS_ACTIVE,
                OrganizationSubscription::STATUS_TRIAL,
            ]])
            ->andWhere(['<=', 'started_at', $monthEnd])
            ->count('DISTINCT organization_id');

        $monthly->trial_organizations = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
            ->andWhere(['<=', 'started_at', $monthEnd])
            ->count('DISTINCT organization_id');

        $monthly->paying_organizations = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['!=', 'saas_plan_id', 1]) // Исключаем FREE
            ->andWhere(['<=', 'started_at', $monthEnd])
            ->count('DISTINCT organization_id');

        // ==================== ПО ПЛАНАМ ====================

        $revenueByPlan = OrganizationPayment::find()
            ->alias('p')
            ->select([
                'plan.code',
                'SUM(p.amount) as revenue',
            ])
            ->innerJoin('organization_subscription s', 'p.subscription_id = s.id')
            ->innerJoin('saas_plan plan', 's.saas_plan_id = plan.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $monthStart])
            ->andWhere(['<=', 'p.processed_at', $monthEnd])
            ->groupBy('plan.id')
            ->asArray()
            ->all();

        $monthly->revenue_by_plan = array_column($revenueByPlan, 'revenue', 'code');

        $orgsByPlan = OrganizationSubscription::find()
            ->alias('s')
            ->select([
                'plan.code',
                'COUNT(DISTINCT s.organization_id) as count',
            ])
            ->innerJoin('saas_plan plan', 's.saas_plan_id = plan.id')
            ->where(['in', 's.status', [
                OrganizationSubscription::STATUS_ACTIVE,
                OrganizationSubscription::STATUS_TRIAL,
            ]])
            ->andWhere(['<=', 's.started_at', $monthEnd])
            ->groupBy('plan.id')
            ->asArray()
            ->all();

        $monthly->organizations_by_plan = array_column($orgsByPlan, 'count', 'code');

        // ==================== ПО МЕНЕДЖЕРАМ ====================

        $revenueByManager = OrganizationPayment::find()
            ->select([
                'manager_id',
                'SUM(amount) as revenue',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $monthStart])
            ->andWhere(['<=', 'processed_at', $monthEnd])
            ->andWhere(['IS NOT', 'manager_id', null])
            ->groupBy('manager_id')
            ->asArray()
            ->all();

        $monthly->revenue_by_manager = array_column($revenueByManager, 'revenue', 'manager_id');

        $bonusesByManager = OrganizationPayment::find()
            ->select([
                'manager_id',
                'SUM(manager_bonus_amount) as bonus',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $monthStart])
            ->andWhere(['<=', 'processed_at', $monthEnd])
            ->andWhere(['IS NOT', 'manager_id', null])
            ->groupBy('manager_id')
            ->asArray()
            ->all();

        $monthly->bonuses_by_manager = array_column($bonusesByManager, 'bonus', 'manager_id');

        // ==================== КОНВЕРСИИ ====================

        // Триалы которые закончились в этом месяце
        $expiredTrials = OrganizationSubscription::find()
            ->where(['>=', 'trial_ends_at', $monthStart])
            ->andWhere(['<=', 'trial_ends_at', $monthEnd])
            ->count();

        // Из них стали платными
        $converted = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['>=', 'trial_ends_at', $monthStart])
            ->andWhere(['<=', 'trial_ends_at', $monthEnd])
            ->count();

        $monthly->trial_to_paid_count = $converted;
        $monthly->trial_to_paid_rate = $expiredTrials > 0
            ? round(($converted / $expiredTrials) * 100, 2)
            : 0;

        $monthly->calculated_at = date('Y-m-d H:i:s');
        $monthly->save();

        return $monthly;
    }

    /**
     * Рассчитать MRR на определённую дату
     */
    private function calculateMRRAtDate(string $date): float
    {
        $mrr = 0;

        $subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['<=', 'started_at', $date . ' 23:59:59'])
            ->andWhere(['OR',
                ['>=', 'expires_at', $date],
                ['expires_at' => null],
            ])
            ->with('saasPlan')
            ->all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->saasPlan) {
                if ($subscription->billing_period === OrganizationSubscription::PERIOD_YEARLY) {
                    $mrr += $subscription->saasPlan->price_yearly / 12;
                } else {
                    $mrr += $subscription->saasPlan->price_monthly;
                }
            }
        }

        return round($mrr, 2);
    }

    /**
     * Рассчитать MRR от новых подписок за период
     */
    private function calculateNewMRR(string $from, string $to): float
    {
        $mrr = 0;

        $subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['>=', 'started_at', $from])
            ->andWhere(['<=', 'started_at', $to])
            ->with('saasPlan')
            ->all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->saasPlan) {
                if ($subscription->billing_period === OrganizationSubscription::PERIOD_YEARLY) {
                    $mrr += $subscription->saasPlan->price_yearly / 12;
                } else {
                    $mrr += $subscription->saasPlan->price_monthly;
                }
            }
        }

        return round($mrr, 2);
    }

    /**
     * Рассчитать потерянный MRR от отменённых подписок
     */
    private function calculateChurnedMRR(string $from, string $to): float
    {
        $mrr = 0;

        $subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_CANCELLED])
            ->andWhere(['>=', 'cancelled_at', $from])
            ->andWhere(['<=', 'cancelled_at', $to])
            ->with('saasPlan')
            ->all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->saasPlan) {
                if ($subscription->billing_period === OrganizationSubscription::PERIOD_YEARLY) {
                    $mrr += $subscription->saasPlan->price_yearly / 12;
                } else {
                    $mrr += $subscription->saasPlan->price_monthly;
                }
            }
        }

        return round($mrr, 2);
    }

    /**
     * Агрегировать данные за последние N дней
     */
    public function aggregateLastDays(int $days = 30): void
    {
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $this->aggregateDay($date);
        }
    }

    /**
     * Агрегировать данные за последние N месяцев
     */
    public function aggregateLastMonths(int $months = 12): void
    {
        for ($i = $months - 1; $i >= 0; $i--) {
            $yearMonth = date('Y-m', strtotime("-{$i} months"));
            $this->aggregateMonth($yearMonth);
        }
    }

    /**
     * Полная переагрегация всех данных
     */
    public function reAggregateAll(): void
    {
        // Найти самый ранний платёж
        $firstPayment = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->orderBy(['processed_at' => SORT_ASC])
            ->one();

        if (!$firstPayment) {
            return;
        }

        $startDate = date('Y-m-01', strtotime($firstPayment->processed_at));
        $endDate = date('Y-m-t');

        // Агрегируем месяцы
        $current = $startDate;
        while ($current <= $endDate) {
            $this->aggregateMonth(date('Y-m', strtotime($current)));
            $current = date('Y-m-d', strtotime($current . ' +1 month'));
        }

        // Агрегируем последние 30 дней по дням
        $this->aggregateLastDays(30);
    }
}
