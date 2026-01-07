<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationPayment;
use app\models\OrganizationSubscription;
use app\models\SaasPlan;
use app\models\SaasPromoCodeUsage;
use app\models\SaasRevenueMonthly;
use app\models\SaasRevenueDaily;
use app\models\User;

/**
 * Сервис отчётов по выручке
 */
class RevenueReportService
{
    /**
     * Обзорная панель для супер-админа
     */
    public function getDashboard(): array
    {
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        return [
            'current_mrr' => $this->getCurrentMRR(),
            'current_arr' => $this->getCurrentMRR() * 12,
            'mrr_growth' => $this->getMRRGrowth(),
            'active_subscriptions' => $this->getActiveSubscriptionsCount(),
            'trial_subscriptions' => $this->getTrialSubscriptionsCount(),
            'paying_organizations' => $this->getPayingOrganizationsCount(),
            'churn_rate' => $this->getChurnRate(),
            'avg_revenue_per_org' => $this->getARPU(),
            'trial_conversion_rate' => $this->getTrialConversionRate(),
            'revenue_this_month' => $this->getRevenueForMonth($currentMonth),
            'revenue_last_month' => $this->getRevenueForMonth($lastMonth),
        ];
    }

    /**
     * Текущий MRR (Monthly Recurring Revenue)
     */
    public function getCurrentMRR(): float
    {
        $total = 0;

        $subscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->with('saasPlan')
            ->all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->saasPlan) {
                if ($subscription->billing_period === OrganizationSubscription::PERIOD_YEARLY) {
                    // Годовая подписка - делим на 12
                    $total += $subscription->saasPlan->price_yearly / 12;
                } else {
                    $total += $subscription->saasPlan->price_monthly;
                }
            }
        }

        return round($total, 2);
    }

    /**
     * Рост MRR в процентах за последний месяц
     */
    public function getMRRGrowth(): ?float
    {
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $current = SaasRevenueMonthly::findOne(['year_month' => $currentMonth]);
        $last = SaasRevenueMonthly::findOne(['year_month' => $lastMonth]);

        if (!$last || $last->mrr_end <= 0) {
            return null;
        }

        $currentMrr = $current ? $current->mrr_end : $this->getCurrentMRR();
        return round((($currentMrr - $last->mrr_end) / $last->mrr_end) * 100, 2);
    }

    /**
     * Количество активных подписок
     */
    public function getActiveSubscriptionsCount(): int
    {
        return OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Количество пробных подписок
     */
    public function getTrialSubscriptionsCount(): int
    {
        return OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
            ->count();
    }

    /**
     * Количество платящих организаций
     */
    public function getPayingOrganizationsCount(): int
    {
        return OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['!=', 'saas_plan_id', 1]) // Исключаем FREE план
            ->count('DISTINCT organization_id');
    }

    /**
     * Выручка за месяц
     */
    public function getRevenueForMonth(string $yearMonth): float
    {
        $from = $yearMonth . '-01 00:00:00';
        $to = date('Y-m-t 23:59:59', strtotime($from));

        return (float) OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $from])
            ->andWhere(['<=', 'processed_at', $to])
            ->sum('amount');
    }

    /**
     * Выручка за период
     */
    public function getRevenueByPeriod(string $from, string $to, string $groupBy = 'month'): array
    {
        $format = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'quarter' => '%Y-Q%q',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $results = OrganizationPayment::find()
            ->select([
                "DATE_FORMAT(processed_at, '{$format}') as period",
                'SUM(amount) as revenue',
                'SUM(discount_amount) as discounts',
                'COUNT(*) as payments_count',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $from])
            ->andWhere(['<=', 'processed_at', $to])
            ->groupBy('period')
            ->orderBy('period')
            ->asArray()
            ->all();

        return $results;
    }

    /**
     * Выручка по тарифам
     */
    public function getRevenueByPlan(string $from, string $to): array
    {
        $results = OrganizationPayment::find()
            ->alias('p')
            ->select([
                'plan.code as plan_code',
                'plan.name as plan_name',
                'SUM(p.amount) as revenue',
                'SUM(p.discount_amount) as discounts',
                'COUNT(DISTINCT p.organization_id) as organizations',
                'COUNT(*) as payments_count',
            ])
            ->innerJoin('organization_subscription sub', 'p.subscription_id = sub.id')
            ->innerJoin('saas_plan plan', 'sub.saas_plan_id = plan.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $from])
            ->andWhere(['<=', 'p.processed_at', $to])
            ->groupBy('plan.id')
            ->orderBy(['revenue' => SORT_DESC])
            ->asArray()
            ->all();

        return $results;
    }

    /**
     * Выручка по менеджерам
     */
    public function getRevenueByManager(string $from, string $to): array
    {
        $results = OrganizationPayment::find()
            ->alias('p')
            ->select([
                'u.id as manager_id',
                'COALESCE(u.fio, u.username) as manager_name',
                'SUM(p.amount) as revenue',
                'SUM(p.manager_bonus_amount) as total_bonus',
                "SUM(CASE WHEN p.manager_bonus_status = 'pending' THEN p.manager_bonus_amount ELSE 0 END) as pending_bonus",
                "SUM(CASE WHEN p.manager_bonus_status = 'paid' THEN p.manager_bonus_amount ELSE 0 END) as paid_bonus",
                'COUNT(*) as payments_count',
                'COUNT(DISTINCT p.organization_id) as organizations',
            ])
            ->innerJoin('user u', 'p.manager_id = u.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $from])
            ->andWhere(['<=', 'p.processed_at', $to])
            ->groupBy('p.manager_id')
            ->orderBy(['revenue' => SORT_DESC])
            ->asArray()
            ->all();

        return $results;
    }

    /**
     * Анализ скидок
     */
    public function getDiscountAnalysis(string $from, string $to): array
    {
        $totalDiscounts = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $from])
            ->andWhere(['<=', 'processed_at', $to])
            ->sum('discount_amount');

        $byType = OrganizationPayment::find()
            ->select([
                'discount_type',
                'SUM(discount_amount) as amount',
                'COUNT(*) as count',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $from])
            ->andWhere(['<=', 'processed_at', $to])
            ->andWhere(['IS NOT', 'discount_type', null])
            ->groupBy('discount_type')
            ->asArray()
            ->all();

        $topPromoCodes = SaasPromoCodeUsage::find()
            ->alias('u')
            ->select([
                'p.code',
                'p.name',
                'SUM(u.discount_amount) as total_discount',
                'COUNT(*) as usage_count',
            ])
            ->innerJoin('saas_promo_code p', 'u.promo_code_id = p.id')
            ->andWhere(['>=', 'u.used_at', $from])
            ->andWhere(['<=', 'u.used_at', $to])
            ->groupBy('u.promo_code_id')
            ->orderBy(['total_discount' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        return [
            'total_discounts' => (float) $totalDiscounts,
            'by_type' => $byType,
            'top_promo_codes' => $topPromoCodes,
        ];
    }

    /**
     * Churn Rate - процент оттока
     */
    public function getChurnRate(string $month = null): float
    {
        $month = $month ?? date('Y-m', strtotime('-1 month'));
        $monthlyData = SaasRevenueMonthly::findOne(['year_month' => $month]);

        if ($monthlyData && $monthlyData->mrr_start > 0) {
            return round(($monthlyData->mrr_churn / $monthlyData->mrr_start) * 100, 2);
        }

        // Рассчитываем на лету
        $startOfMonth = $month . '-01';
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        $cancelledCount = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_CANCELLED])
            ->andWhere(['>=', 'cancelled_at', $startOfMonth])
            ->andWhere(['<=', 'cancelled_at', $endOfMonth])
            ->count();

        $totalActive = OrganizationSubscription::find()
            ->where(['in', 'status', [
                OrganizationSubscription::STATUS_ACTIVE,
                OrganizationSubscription::STATUS_CANCELLED,
            ]])
            ->andWhere(['<=', 'started_at', $startOfMonth])
            ->count();

        if ($totalActive <= 0) {
            return 0;
        }

        return round(($cancelledCount / $totalActive) * 100, 2);
    }

    /**
     * ARPU - Average Revenue Per User
     */
    public function getARPU(): float
    {
        $activeOrgs = $this->getPayingOrganizationsCount();
        if ($activeOrgs <= 0) {
            return 0;
        }

        return round($this->getCurrentMRR() / $activeOrgs, 2);
    }

    /**
     * Trial Conversion Rate - процент конверсии пробных в платные
     */
    public function getTrialConversionRate(int $days = 30): float
    {
        $fromDate = date('Y-m-d', strtotime("-{$days} days"));

        // Триалы которые закончились
        $expiredTrials = OrganizationSubscription::find()
            ->where(['<=', 'trial_ends_at', date('Y-m-d H:i:s')])
            ->andWhere(['>=', 'trial_ends_at', $fromDate])
            ->count();

        if ($expiredTrials <= 0) {
            return 0;
        }

        // Из них стали платными
        $converted = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['IS NOT', 'trial_ends_at', null])
            ->andWhere(['>=', 'trial_ends_at', $fromDate])
            ->andWhere(['<=', 'trial_ends_at', date('Y-m-d H:i:s')])
            ->count();

        return round(($converted / $expiredTrials) * 100, 2);
    }

    /**
     * Когортный анализ
     */
    public function getCohortAnalysis(int $months = 12): array
    {
        $cohorts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $cohortMonth = date('Y-m', strtotime("-{$i} months"));
            $cohortStart = $cohortMonth . '-01';
            $cohortEnd = date('Y-m-t', strtotime($cohortStart));

            // Организации, сделавшие первый платёж в этом месяце
            $cohortOrgs = OrganizationPayment::find()
                ->select(['organization_id'])
                ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
                ->andWhere(['>=', 'processed_at', $cohortStart])
                ->andWhere(['<=', 'processed_at', $cohortEnd . ' 23:59:59'])
                ->andFilterWhere([
                    'organization_id' => OrganizationPayment::find()
                        ->select(['organization_id'])
                        ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
                        ->andWhere(['<', 'processed_at', $cohortStart])
                        ->column(),
                ])
                ->distinct()
                ->column();

            // Исключаем тех, кто платил раньше
            $paidBefore = OrganizationPayment::find()
                ->select(['organization_id'])
                ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
                ->andWhere(['<', 'processed_at', $cohortStart])
                ->column();

            $cohortOrgs = array_diff($cohortOrgs, $paidBefore);

            if (empty($cohortOrgs)) {
                continue;
            }

            $cohorts[$cohortMonth] = [
                'size' => count($cohortOrgs),
                'retention' => [],
            ];

            // Retention для каждого последующего месяца
            for ($j = 0; $j <= $i; $j++) {
                $checkMonth = date('Y-m', strtotime("-" . ($i - $j) . " months"));
                $checkStart = $checkMonth . '-01';
                $checkEnd = date('Y-m-t', strtotime($checkStart)) . ' 23:59:59';

                $retained = OrganizationSubscription::find()
                    ->where(['in', 'organization_id', $cohortOrgs])
                    ->andWhere(['in', 'status', [
                        OrganizationSubscription::STATUS_ACTIVE,
                        OrganizationSubscription::STATUS_TRIAL,
                    ]])
                    ->andWhere(['OR',
                        ['>=', 'expires_at', $checkStart],
                        ['expires_at' => null],
                    ])
                    ->count();

                $cohorts[$cohortMonth]['retention'][$checkMonth] = round(($retained / count($cohortOrgs)) * 100, 1);
            }
        }

        return $cohorts;
    }

    /**
     * Топ организаций по выручке
     */
    public function getTopOrganizations(string $from, string $to, int $limit = 10): array
    {
        return OrganizationPayment::find()
            ->alias('p')
            ->select([
                'o.id as organization_id',
                'o.name as organization_name',
                'SUM(p.amount) as total_revenue',
                'COUNT(*) as payments_count',
            ])
            ->innerJoin('organization o', 'p.organization_id = o.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'p.processed_at', $from])
            ->andWhere(['<=', 'p.processed_at', $to])
            ->groupBy('p.organization_id')
            ->orderBy(['total_revenue' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
    }

    /**
     * Сравнение периодов
     */
    public function comparePeriods(string $period1From, string $period1To, string $period2From, string $period2To): array
    {
        $revenue1 = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $period1From])
            ->andWhere(['<=', 'processed_at', $period1To])
            ->sum('amount') ?? 0;

        $revenue2 = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $period2From])
            ->andWhere(['<=', 'processed_at', $period2To])
            ->sum('amount') ?? 0;

        $payments1 = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $period1From])
            ->andWhere(['<=', 'processed_at', $period1To])
            ->count();

        $payments2 = OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $period2From])
            ->andWhere(['<=', 'processed_at', $period2To])
            ->count();

        return [
            'period1' => [
                'from' => $period1From,
                'to' => $period1To,
                'revenue' => (float) $revenue1,
                'payments' => $payments1,
            ],
            'period2' => [
                'from' => $period2From,
                'to' => $period2To,
                'revenue' => (float) $revenue2,
                'payments' => $payments2,
            ],
            'revenue_change' => $revenue1 - $revenue2,
            'revenue_change_percent' => $revenue2 > 0
                ? round((($revenue1 - $revenue2) / $revenue2) * 100, 1)
                : null,
            'payments_change' => $payments1 - $payments2,
        ];
    }

    /**
     * Данные для графика выручки
     */
    public function getRevenueChartData(int $months = 12): array
    {
        $data = SaasRevenueMonthly::getLastMonths($months);
        $data = array_reverse($data);

        $labels = [];
        $revenues = [];
        $mrr = [];
        $discounts = [];

        foreach ($data as $item) {
            $labels[] = $item->getFormattedMonth();
            $revenues[] = (float) $item->total_revenue;
            $mrr[] = (float) $item->mrr_end;
            $discounts[] = (float) $item->total_discounts;
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'mrr' => $mrr,
            'discounts' => $discounts,
        ];
    }

    /**
     * Данные для графика по дням
     */
    public function getDailyChartData(int $days = 30): array
    {
        return SaasRevenueDaily::getChartData($days);
    }

    /**
     * Статистика по методам оплаты
     */
    public function getPaymentMethodStats(string $from, string $to): array
    {
        return OrganizationPayment::find()
            ->select([
                'payment_method',
                'SUM(amount) as revenue',
                'COUNT(*) as payments_count',
            ])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $from])
            ->andWhere(['<=', 'processed_at', $to])
            ->groupBy('payment_method')
            ->orderBy(['revenue' => SORT_DESC])
            ->asArray()
            ->all();
    }
}
