<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationPayment;
use app\models\OrganizationSubscription;
use app\models\SaasPlan;
use app\models\SaasRevenueMonthly;

/**
 * Сервис для расчёта SaaS метрик и KPI
 */
class SaasMetricsService
{
    private ?RevenueReportService $revenueService = null;

    /**
     * Получить все ключевые метрики для дашборда
     */
    public function getAllMetrics(): array
    {
        return [
            // Выручка
            'mrr' => $this->getMRR(),
            'arr' => $this->getMRR() * 12,
            'mrr_growth_percent' => $this->getMRRGrowthPercent(),

            // Подписки
            'total_subscriptions' => $this->getTotalActiveSubscriptions(),
            'new_subscriptions_mtd' => $this->getNewSubscriptionsMTD(),
            'churn_rate' => $this->getChurnRate(),
            'net_mrr_churn' => $this->getNetMRRChurn(),

            // Unit Economics
            'arpu' => $this->getARPU(),
            'arppu' => $this->getARPPU(),
            'ltv' => $this->getLTV(),
            'cac' => $this->getCAC(),
            'ltv_cac_ratio' => $this->getLTVCACRatio(),

            // Конверсии
            'trial_to_paid_rate' => $this->getTrialToPaidRate(),
            'lead_to_trial_rate' => $this->getLeadToTrialRate(),

            // Expansion
            'expansion_mrr' => $this->getExpansionMRR(),
            'upgrade_rate' => $this->getUpgradeRate(),
            'addon_attach_rate' => $this->getAddonAttachRate(),

            // Health metrics
            'nrr' => $this->getNRR(),
            'quick_ratio' => $this->getQuickRatio(),
        ];
    }

    /**
     * Получить RevenueReportService
     */
    private function getRevenueService(): RevenueReportService
    {
        if ($this->revenueService === null) {
            $this->revenueService = new RevenueReportService();
        }
        return $this->revenueService;
    }

    // ==================== REVENUE METRICS ====================

    /**
     * MRR (Monthly Recurring Revenue)
     */
    public function getMRR(): float
    {
        return $this->getRevenueService()->getCurrentMRR();
    }

    /**
     * ARR (Annual Recurring Revenue)
     */
    public function getARR(): float
    {
        return $this->getMRR() * 12;
    }

    /**
     * Рост MRR в процентах за месяц
     */
    public function getMRRGrowthPercent(): ?float
    {
        return $this->getRevenueService()->getMRRGrowth();
    }

    /**
     * Net MRR Churn (Чистый отток MRR)
     */
    public function getNetMRRChurn(): float
    {
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $lastMonth]);

        if (!$data) {
            return 0;
        }

        return $data->mrr_churn - $data->mrr_expansion;
    }

    /**
     * Expansion MRR (MRR от апгрейдов и аддонов)
     */
    public function getExpansionMRR(): float
    {
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $lastMonth]);

        return $data ? (float) $data->mrr_expansion : 0;
    }

    // ==================== SUBSCRIPTION METRICS ====================

    /**
     * Общее количество активных подписок
     */
    public function getTotalActiveSubscriptions(): int
    {
        return OrganizationSubscription::find()
            ->where(['in', 'status', [
                OrganizationSubscription::STATUS_ACTIVE,
                OrganizationSubscription::STATUS_TRIAL,
            ]])
            ->count();
    }

    /**
     * Новые подписки за текущий месяц
     */
    public function getNewSubscriptionsMTD(): int
    {
        $startOfMonth = date('Y-m-01');

        return OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['>=', 'started_at', $startOfMonth])
            ->count();
    }

    /**
     * Churn Rate (процент оттока)
     */
    public function getChurnRate(): float
    {
        return $this->getRevenueService()->getChurnRate();
    }

    /**
     * Upgrade Rate (процент апгрейдов)
     */
    public function getUpgradeRate(): float
    {
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $lastMonth]);

        if (!$data || $data->active_organizations <= 0) {
            return 0;
        }

        return round(($data->upgrades / $data->active_organizations) * 100, 2);
    }

    /**
     * Addon Attach Rate (процент организаций с аддонами)
     */
    public function getAddonAttachRate(): float
    {
        $totalActive = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->count();

        if ($totalActive <= 0) {
            return 0;
        }

        // TODO: когда будет таблица organization_addon
        // $withAddons = OrganizationAddon::find()
        //     ->where(['status' => 'active'])
        //     ->count('DISTINCT organization_id');

        return 0; // Пока вернём 0
    }

    // ==================== UNIT ECONOMICS ====================

    /**
     * ARPU (Average Revenue Per User) - средняя выручка на организацию
     */
    public function getARPU(): float
    {
        return $this->getRevenueService()->getARPU();
    }

    /**
     * ARPPU (Average Revenue Per Paying User) - средняя выручка на платящую организацию
     */
    public function getARPPU(): float
    {
        $payingOrgs = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['!=', 'saas_plan_id', 1]) // Исключаем FREE
            ->count();

        if ($payingOrgs <= 0) {
            return 0;
        }

        return round($this->getMRR() / $payingOrgs, 2);
    }

    /**
     * LTV (Lifetime Value) = ARPU / Churn Rate
     */
    public function getLTV(): float
    {
        $arpu = $this->getARPU();
        $churnRate = $this->getChurnRate() / 100; // Переводим в доли

        if ($churnRate <= 0) {
            // Если нет оттока - предполагаем 3 года
            return $arpu * 36;
        }

        return round($arpu / $churnRate, 2);
    }

    /**
     * CAC (Customer Acquisition Cost) - стоимость привлечения клиента
     * Пока заглушка, можно интегрировать с расходами на маркетинг
     */
    public function getCAC(): float
    {
        // TODO: интегрировать с данными о маркетинговых расходах
        // Пока возвращаем примерную оценку
        return 15000; // 15,000 KZT
    }

    /**
     * LTV/CAC Ratio
     * > 3 = отлично, 1-3 = нормально, < 1 = убыток
     */
    public function getLTVCACRatio(): float
    {
        $cac = $this->getCAC();
        if ($cac <= 0) {
            return 0;
        }

        return round($this->getLTV() / $cac, 2);
    }

    // ==================== CONVERSION METRICS ====================

    /**
     * Trial to Paid Rate (конверсия триал → платный)
     */
    public function getTrialToPaidRate(): float
    {
        return $this->getRevenueService()->getTrialConversionRate();
    }

    /**
     * Lead to Trial Rate (конверсия лид → триал)
     * Заглушка - требует интеграции с лидами superadmin
     */
    public function getLeadToTrialRate(): float
    {
        // TODO: интегрировать с лидами суперадмина
        return 0;
    }

    // ==================== HEALTH METRICS ====================

    /**
     * NRR (Net Revenue Retention)
     * (MRR_start + Expansion - Contraction - Churn) / MRR_start * 100
     * > 100% = рост от существующих клиентов
     */
    public function getNRR(string $month = null): float
    {
        $month = $month ?? date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $month]);

        if (!$data || $data->mrr_start <= 0) {
            return 100;
        }

        $nrr = ($data->mrr_start + $data->mrr_expansion - $data->mrr_contraction - $data->mrr_churn)
            / $data->mrr_start * 100;

        return round($nrr, 1);
    }

    /**
     * Quick Ratio = (New MRR + Expansion MRR) / (Churned MRR + Contraction MRR)
     * > 4 = отлично, > 2 = хорошо, < 1 = проблема
     */
    public function getQuickRatio(string $month = null): float
    {
        $month = $month ?? date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $month]);

        if (!$data) {
            return 0;
        }

        $growth = $data->mrr_new + $data->mrr_expansion;
        $loss = $data->mrr_churn + $data->mrr_contraction;

        if ($loss <= 0) {
            return 999; // Условный "бесконечный" рост
        }

        return round($growth / $loss, 2);
    }

    /**
     * Payback Period (время окупаемости CAC) в месяцах
     */
    public function getPaybackPeriod(): float
    {
        $arpu = $this->getARPU();
        if ($arpu <= 0) {
            return 0;
        }

        $cac = $this->getCAC();
        // Предполагаем gross margin 80%
        $grossMargin = 0.8;

        return round($cac / ($arpu * $grossMargin), 1);
    }

    /**
     * Magic Number (эффективность продаж)
     * Net New ARR / Sales & Marketing Spend
     * > 0.75 = отлично, 0.5-0.75 = хорошо, < 0.5 = нужно оптимизировать
     */
    public function getMagicNumber(): float
    {
        // TODO: интегрировать с данными о расходах на S&M
        return 0;
    }

    // ==================== SEGMENTATION ====================

    /**
     * Распределение по планам
     */
    public function getDistributionByPlan(): array
    {
        return OrganizationSubscription::find()
            ->alias('s')
            ->select([
                'p.code as plan_code',
                'p.name as plan_name',
                'COUNT(*) as count',
            ])
            ->innerJoin('saas_plan p', 's.saas_plan_id = p.id')
            ->where(['in', 's.status', [
                OrganizationSubscription::STATUS_ACTIVE,
                OrganizationSubscription::STATUS_TRIAL,
            ]])
            ->groupBy('s.saas_plan_id')
            ->orderBy(['count' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * Распределение по периодам биллинга
     */
    public function getDistributionByBillingPeriod(): array
    {
        return OrganizationSubscription::find()
            ->select([
                'billing_period',
                'COUNT(*) as count',
            ])
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->groupBy('billing_period')
            ->asArray()
            ->all();
    }

    // ==================== TRENDS ====================

    /**
     * Тренд метрики за последние N месяцев
     */
    public function getMetricTrend(string $metric, int $months = 6): array
    {
        $data = SaasRevenueMonthly::getLastMonths($months);
        $data = array_reverse($data);

        $result = [];
        foreach ($data as $item) {
            $value = match ($metric) {
                'mrr' => $item->mrr_end,
                'revenue' => $item->total_revenue,
                'subscriptions' => $item->active_organizations,
                'churn' => $item->getChurnRate(),
                'nrr' => $item->getNRR(),
                default => 0,
            };

            $result[] = [
                'month' => $item->year_month,
                'value' => $value,
            ];
        }

        return $result;
    }

    /**
     * Сводка здоровья бизнеса
     */
    public function getHealthSummary(): array
    {
        $nrr = $this->getNRR();
        $quickRatio = $this->getQuickRatio();
        $ltvCac = $this->getLTVCACRatio();
        $churnRate = $this->getChurnRate();

        return [
            'nrr' => [
                'value' => $nrr,
                'status' => $nrr >= 100 ? 'good' : ($nrr >= 90 ? 'warning' : 'bad'),
                'label' => 'Net Revenue Retention',
            ],
            'quick_ratio' => [
                'value' => $quickRatio,
                'status' => $quickRatio >= 4 ? 'good' : ($quickRatio >= 2 ? 'warning' : 'bad'),
                'label' => 'Quick Ratio',
            ],
            'ltv_cac' => [
                'value' => $ltvCac,
                'status' => $ltvCac >= 3 ? 'good' : ($ltvCac >= 1 ? 'warning' : 'bad'),
                'label' => 'LTV/CAC Ratio',
            ],
            'churn_rate' => [
                'value' => $churnRate,
                'status' => $churnRate <= 3 ? 'good' : ($churnRate <= 7 ? 'warning' : 'bad'),
                'label' => 'Churn Rate',
            ],
        ];
    }
}
