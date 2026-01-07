<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationAddon;
use app\models\SaasRevenueMonthly;
use app\models\SaasPlan;

/**
 * Сервис прогнозирования выручки
 *
 * Использование:
 * ```php
 * $forecastService = new RevenueForecastService();
 *
 * // Прогноз MRR на 6 месяцев
 * $forecast = $forecastService->forecastMRR(6);
 *
 * // MRR под риском (истекающие подписки)
 * $atRisk = $forecastService->getMRRAtRisk(30);
 *
 * // Полный дашборд прогнозов
 * $dashboard = $forecastService->getForecastDashboard();
 * ```
 */
class RevenueForecastService
{
    /**
     * @var RevenueReportService
     */
    private RevenueReportService $reportService;

    public function __construct()
    {
        $this->reportService = new RevenueReportService();
    }

    // ==================== MAIN FORECAST ====================

    /**
     * Полный дашборд прогнозов
     */
    public function getForecastDashboard(): array
    {
        $currentMRR = $this->reportService->getCurrentMRR();
        $mrrForecast = $this->forecastMRR(6);
        $atRisk = $this->getMRRAtRisk(30);
        $guaranteed = $this->getGuaranteedRevenue(3);
        $trialFunnel = $this->getTrialFunnelForecast();
        $scenarioAnalysis = $this->getScenarioAnalysis();

        return [
            'current_mrr' => $currentMRR,
            'current_arr' => $currentMRR * 12,
            'mrr_forecast' => $mrrForecast,
            'mrr_at_risk' => $atRisk,
            'guaranteed_revenue' => $guaranteed,
            'trial_funnel' => $trialFunnel,
            'scenario_analysis' => $scenarioAnalysis,
            'health_indicators' => $this->getHealthIndicators(),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Прогноз MRR на N месяцев вперёд
     */
    public function forecastMRR(int $months = 6): array
    {
        $currentMRR = $this->reportService->getCurrentMRR();
        $growthRate = $this->calculateAverageGrowthRate(3);
        $churnRate = $this->calculateAverageChurnRate(3);

        $forecast = [];
        $mrr = $currentMRR;

        for ($i = 1; $i <= $months; $i++) {
            $month = date('Y-m', strtotime("+{$i} months"));

            // Прогнозируемый рост от новых клиентов
            $newMRR = $mrr * $growthRate;

            // Прогнозируемый отток
            $churnedMRR = $mrr * $churnRate;

            // Итоговый MRR
            $mrr = $mrr + $newMRR - $churnedMRR;

            $forecast[] = [
                'month' => $month,
                'month_label' => $this->formatMonth($month),
                'mrr' => round($mrr, 2),
                'arr' => round($mrr * 12, 2),
                'new_mrr' => round($newMRR, 2),
                'churned_mrr' => round($churnedMRR, 2),
                'net_change' => round($newMRR - $churnedMRR, 2),
                'growth_rate' => round($growthRate * 100, 2),
                'churn_rate' => round($churnRate * 100, 2),
            ];
        }

        return [
            'current_mrr' => $currentMRR,
            'forecast' => $forecast,
            'assumptions' => [
                'growth_rate' => round($growthRate * 100, 2) . '%',
                'churn_rate' => round($churnRate * 100, 2) . '%',
                'based_on_months' => 3,
            ],
            'summary' => [
                'final_mrr' => $forecast[count($forecast) - 1]['mrr'] ?? $currentMRR,
                'total_growth' => round(($forecast[count($forecast) - 1]['mrr'] ?? $currentMRR) - $currentMRR, 2),
                'total_growth_percent' => $currentMRR > 0
                    ? round(((($forecast[count($forecast) - 1]['mrr'] ?? $currentMRR) - $currentMRR) / $currentMRR) * 100, 1)
                    : 0,
            ],
        ];
    }

    // ==================== MRR AT RISK ====================

    /**
     * MRR под риском (истекающие подписки)
     */
    public function getMRRAtRisk(int $days = 30): array
    {
        $expiringSubscriptions = OrganizationSubscription::find()
            ->alias('s')
            ->select([
                's.*',
                'o.name as organization_name',
                'p.name as plan_name',
                'p.price_monthly',
                'p.price_yearly',
            ])
            ->innerJoin('organization o', 's.organization_id = o.id')
            ->innerJoin('saas_plan p', 's.saas_plan_id = p.id')
            ->where(['s.status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['between', 's.expires_at', date('Y-m-d'), date('Y-m-d', strtotime("+{$days} days"))])
            ->orderBy(['s.expires_at' => SORT_ASC])
            ->asArray()
            ->all();

        $totalMRRAtRisk = 0;
        $byWeek = [];
        $byPlan = [];

        foreach ($expiringSubscriptions as &$sub) {
            // Рассчитываем MRR для подписки
            $mrr = $sub['billing_period'] === OrganizationSubscription::PERIOD_YEARLY
                ? $sub['price_yearly'] / 12
                : $sub['price_monthly'];

            $sub['mrr'] = round($mrr, 2);
            $sub['days_remaining'] = max(0, floor((strtotime($sub['expires_at']) - time()) / 86400));

            $totalMRRAtRisk += $mrr;

            // Группировка по неделям
            $weekNumber = (int) ceil($sub['days_remaining'] / 7);
            $weekKey = "week_{$weekNumber}";
            if (!isset($byWeek[$weekKey])) {
                $byWeek[$weekKey] = [
                    'label' => $weekNumber === 1 ? 'Эта неделя' : "Неделя {$weekNumber}",
                    'count' => 0,
                    'mrr' => 0,
                ];
            }
            $byWeek[$weekKey]['count']++;
            $byWeek[$weekKey]['mrr'] += $mrr;

            // Группировка по планам
            $planName = $sub['plan_name'];
            if (!isset($byPlan[$planName])) {
                $byPlan[$planName] = [
                    'count' => 0,
                    'mrr' => 0,
                ];
            }
            $byPlan[$planName]['count']++;
            $byPlan[$planName]['mrr'] += $mrr;
        }

        return [
            'total_mrr_at_risk' => round($totalMRRAtRisk, 2),
            'total_arr_at_risk' => round($totalMRRAtRisk * 12, 2),
            'subscriptions_count' => count($expiringSubscriptions),
            'subscriptions' => $expiringSubscriptions,
            'by_week' => array_values($byWeek),
            'by_plan' => $byPlan,
            'period_days' => $days,
            'risk_level' => $this->calculateRiskLevel($totalMRRAtRisk),
        ];
    }

    /**
     * Истекающие триалы (потенциальные конверсии)
     */
    public function getExpiringTrials(int $days = 14): array
    {
        $expiringTrials = OrganizationSubscription::find()
            ->alias('s')
            ->select([
                's.*',
                'o.name as organization_name',
                'p.name as plan_name',
                'p.price_monthly',
            ])
            ->innerJoin('organization o', 's.organization_id = o.id')
            ->innerJoin('saas_plan p', 's.saas_plan_id = p.id')
            ->where(['s.status' => OrganizationSubscription::STATUS_TRIAL])
            ->andWhere(['between', 's.trial_ends_at', date('Y-m-d'), date('Y-m-d', strtotime("+{$days} days"))])
            ->orderBy(['s.trial_ends_at' => SORT_ASC])
            ->asArray()
            ->all();

        $potentialMRR = 0;
        $conversionRate = $this->reportService->getTrialConversionRate(60);

        foreach ($expiringTrials as &$trial) {
            $trial['days_remaining'] = max(0, floor((strtotime($trial['trial_ends_at']) - time()) / 86400));
            $trial['potential_mrr'] = (float) $trial['price_monthly'];
            $potentialMRR += $trial['price_monthly'];
        }

        $expectedMRR = $potentialMRR * ($conversionRate / 100);

        return [
            'total_trials' => count($expiringTrials),
            'potential_mrr' => round($potentialMRR, 2),
            'expected_mrr' => round($expectedMRR, 2),
            'conversion_rate' => $conversionRate,
            'trials' => $expiringTrials,
            'period_days' => $days,
        ];
    }

    // ==================== GUARANTEED REVENUE ====================

    /**
     * Гарантированная выручка (от текущих активных подписок)
     */
    public function getGuaranteedRevenue(int $months = 3): array
    {
        $activeSubscriptions = OrganizationSubscription::find()
            ->alias('s')
            ->innerJoin('saas_plan p', 's.saas_plan_id = p.id')
            ->where(['s.status' => OrganizationSubscription::STATUS_ACTIVE])
            ->select(['s.*', 'p.price_monthly', 'p.price_yearly'])
            ->asArray()
            ->all();

        $forecast = [];

        for ($i = 0; $i < $months; $i++) {
            $monthStart = date('Y-m-01', strtotime("+{$i} months"));
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            $monthLabel = $this->formatMonth(date('Y-m', strtotime($monthStart)));

            $revenue = 0;
            $subscriptionCount = 0;

            foreach ($activeSubscriptions as $sub) {
                // Проверяем, будет ли подписка активна в этом месяце
                if ($sub['expires_at'] && $sub['expires_at'] < $monthStart) {
                    continue;
                }

                $mrr = $sub['billing_period'] === OrganizationSubscription::PERIOD_YEARLY
                    ? $sub['price_yearly'] / 12
                    : $sub['price_monthly'];

                $revenue += $mrr;
                $subscriptionCount++;
            }

            $forecast[] = [
                'month' => date('Y-m', strtotime($monthStart)),
                'month_label' => $monthLabel,
                'guaranteed_revenue' => round($revenue, 2),
                'subscription_count' => $subscriptionCount,
            ];
        }

        return [
            'months' => $months,
            'forecast' => $forecast,
            'total_guaranteed' => round(array_sum(array_column($forecast, 'guaranteed_revenue')), 2),
        ];
    }

    // ==================== FUNNEL FORECAST ====================

    /**
     * Прогноз по воронке триалов
     */
    public function getTrialFunnelForecast(): array
    {
        // Текущие триалы
        $activeTrials = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
            ->count();

        // Историческая конверсия
        $conversionRate = $this->reportService->getTrialConversionRate(90);

        // Средний чек новой подписки
        $avgMRR = $this->calculateAverageNewSubscriptionMRR();

        // Прогнозируемые конверсии
        $expectedConversions = round($activeTrials * ($conversionRate / 100));
        $expectedMRR = $expectedConversions * $avgMRR;

        return [
            'active_trials' => $activeTrials,
            'conversion_rate' => $conversionRate,
            'expected_conversions' => $expectedConversions,
            'avg_mrr' => round($avgMRR, 2),
            'expected_mrr' => round($expectedMRR, 2),
            'expected_arr' => round($expectedMRR * 12, 2),
        ];
    }

    // ==================== SCENARIO ANALYSIS ====================

    /**
     * Анализ сценариев (оптимистичный, базовый, пессимистичный)
     */
    public function getScenarioAnalysis(int $months = 6): array
    {
        $currentMRR = $this->reportService->getCurrentMRR();
        $baseGrowth = $this->calculateAverageGrowthRate(3);
        $baseChurn = $this->calculateAverageChurnRate(3);

        $scenarios = [
            'optimistic' => [
                'label' => 'Оптимистичный',
                'growth_rate' => $baseGrowth * 1.5,
                'churn_rate' => $baseChurn * 0.5,
                'color' => '#10B981', // green
            ],
            'base' => [
                'label' => 'Базовый',
                'growth_rate' => $baseGrowth,
                'churn_rate' => $baseChurn,
                'color' => '#3B82F6', // blue
            ],
            'pessimistic' => [
                'label' => 'Пессимистичный',
                'growth_rate' => $baseGrowth * 0.5,
                'churn_rate' => $baseChurn * 1.5,
                'color' => '#EF4444', // red
            ],
        ];

        foreach ($scenarios as $key => &$scenario) {
            $mrr = $currentMRR;
            $forecast = [];

            for ($i = 1; $i <= $months; $i++) {
                $month = date('Y-m', strtotime("+{$i} months"));
                $newMRR = $mrr * $scenario['growth_rate'];
                $churnedMRR = $mrr * $scenario['churn_rate'];
                $mrr = $mrr + $newMRR - $churnedMRR;

                $forecast[] = [
                    'month' => $month,
                    'mrr' => round($mrr, 2),
                ];
            }

            $scenario['forecast'] = $forecast;
            $scenario['final_mrr'] = $forecast[count($forecast) - 1]['mrr'];
            $scenario['growth_percent'] = round((($scenario['final_mrr'] - $currentMRR) / $currentMRR) * 100, 1);
        }

        return [
            'current_mrr' => $currentMRR,
            'months' => $months,
            'scenarios' => $scenarios,
        ];
    }

    // ==================== HEALTH INDICATORS ====================

    /**
     * Индикаторы здоровья бизнеса
     */
    public function getHealthIndicators(): array
    {
        $growthRate = $this->calculateAverageGrowthRate(3);
        $churnRate = $this->calculateAverageChurnRate(3);
        $currentMRR = $this->reportService->getCurrentMRR();
        $atRisk = $this->getMRRAtRisk(30);

        // Quick Ratio = Growth / Churn (> 4 = отлично, > 2 = хорошо, < 1 = проблема)
        $quickRatio = $churnRate > 0 ? $growthRate / $churnRate : 10;

        // MRR at Risk Ratio
        $riskRatio = $currentMRR > 0 ? ($atRisk['total_mrr_at_risk'] / $currentMRR) * 100 : 0;

        $indicators = [
            [
                'name' => 'Quick Ratio',
                'value' => round($quickRatio, 2),
                'target' => '> 4',
                'status' => $quickRatio >= 4 ? 'excellent' : ($quickRatio >= 2 ? 'good' : ($quickRatio >= 1 ? 'warning' : 'critical')),
                'description' => 'Соотношение роста к оттоку',
            ],
            [
                'name' => 'Churn Rate',
                'value' => round($churnRate * 100, 2) . '%',
                'target' => '< 3%',
                'status' => $churnRate <= 0.03 ? 'excellent' : ($churnRate <= 0.05 ? 'good' : ($churnRate <= 0.1 ? 'warning' : 'critical')),
                'description' => 'Ежемесячный отток',
            ],
            [
                'name' => 'Growth Rate',
                'value' => round($growthRate * 100, 2) . '%',
                'target' => '> 5%',
                'status' => $growthRate >= 0.1 ? 'excellent' : ($growthRate >= 0.05 ? 'good' : ($growthRate >= 0.02 ? 'warning' : 'critical')),
                'description' => 'Ежемесячный рост',
            ],
            [
                'name' => 'MRR at Risk',
                'value' => round($riskRatio, 1) . '%',
                'target' => '< 20%',
                'status' => $riskRatio <= 10 ? 'excellent' : ($riskRatio <= 20 ? 'good' : ($riskRatio <= 30 ? 'warning' : 'critical')),
                'description' => 'MRR под риском на 30 дней',
            ],
        ];

        // Общий статус
        $statuses = array_column($indicators, 'status');
        $criticalCount = count(array_filter($statuses, fn($s) => $s === 'critical'));
        $warningCount = count(array_filter($statuses, fn($s) => $s === 'warning'));

        $overallStatus = 'excellent';
        if ($criticalCount > 0) {
            $overallStatus = 'critical';
        } elseif ($warningCount >= 2) {
            $overallStatus = 'warning';
        } elseif ($warningCount === 1) {
            $overallStatus = 'good';
        }

        return [
            'indicators' => $indicators,
            'overall_status' => $overallStatus,
            'summary' => $this->getHealthSummaryText($overallStatus),
        ];
    }

    // ==================== CHART DATA ====================

    /**
     * Данные для графика прогноза
     */
    public function getForecastChartData(int $months = 6): array
    {
        $forecast = $this->forecastMRR($months);
        $scenarios = $this->getScenarioAnalysis($months);

        $labels = [];
        $currentData = [];
        $forecastData = [];
        $optimisticData = [];
        $pessimisticData = [];

        // Добавляем текущий месяц
        $currentMonth = date('Y-m');
        $labels[] = $this->formatMonth($currentMonth);
        $currentData[] = $forecast['current_mrr'];
        $forecastData[] = $forecast['current_mrr'];
        $optimisticData[] = $forecast['current_mrr'];
        $pessimisticData[] = $forecast['current_mrr'];

        // Добавляем прогнозные месяцы
        foreach ($forecast['forecast'] as $i => $item) {
            $labels[] = $item['month_label'];
            $currentData[] = null;
            $forecastData[] = $item['mrr'];
            $optimisticData[] = $scenarios['scenarios']['optimistic']['forecast'][$i]['mrr'];
            $pessimisticData[] = $scenarios['scenarios']['pessimistic']['forecast'][$i]['mrr'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Текущий MRR',
                    'data' => $currentData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'pointRadius' => 6,
                    'fill' => false,
                ],
                [
                    'label' => 'Базовый прогноз',
                    'data' => $forecastData,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                ],
                [
                    'label' => 'Оптимистичный',
                    'data' => $optimisticData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.05)',
                    'borderDash' => [2, 2],
                    'fill' => false,
                ],
                [
                    'label' => 'Пессимистичный',
                    'data' => $pessimisticData,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)',
                    'borderDash' => [2, 2],
                    'fill' => false,
                ],
            ],
        ];
    }

    /**
     * Данные для графика MRR at Risk
     */
    public function getRiskChartData(): array
    {
        $atRisk = $this->getMRRAtRisk(30);

        return [
            'labels' => array_column($atRisk['by_week'], 'label'),
            'data' => array_column($atRisk['by_week'], 'mrr'),
            'counts' => array_column($atRisk['by_week'], 'count'),
        ];
    }

    // ==================== HELPERS ====================

    /**
     * Рассчитать средний рост за последние N месяцев
     */
    private function calculateAverageGrowthRate(int $months = 3): float
    {
        $data = SaasRevenueMonthly::find()
            ->orderBy(['year_month' => SORT_DESC])
            ->limit($months)
            ->all();

        if (count($data) < 2) {
            return 0.05; // Default 5% growth
        }

        $rates = [];
        for ($i = 0; $i < count($data) - 1; $i++) {
            $current = $data[$i];
            $prev = $data[$i + 1];

            if ($prev->mrr_end > 0) {
                $growth = ($current->mrr_new + $current->mrr_expansion) / $prev->mrr_end;
                $rates[] = $growth;
            }
        }

        return !empty($rates) ? array_sum($rates) / count($rates) : 0.05;
    }

    /**
     * Рассчитать средний отток за последние N месяцев
     */
    private function calculateAverageChurnRate(int $months = 3): float
    {
        $data = SaasRevenueMonthly::find()
            ->orderBy(['year_month' => SORT_DESC])
            ->limit($months)
            ->all();

        if (empty($data)) {
            return 0.03; // Default 3% churn
        }

        $rates = [];
        foreach ($data as $row) {
            if ($row->mrr_start > 0) {
                $rates[] = ($row->mrr_churn + $row->mrr_contraction) / $row->mrr_start;
            }
        }

        return !empty($rates) ? array_sum($rates) / count($rates) : 0.03;
    }

    /**
     * Средний MRR новой подписки
     */
    private function calculateAverageNewSubscriptionMRR(): float
    {
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $data = SaasRevenueMonthly::findOne(['year_month' => $lastMonth]);

        if ($data && $data->new_subscriptions > 0) {
            return $data->mrr_new / $data->new_subscriptions;
        }

        // Fallback: средняя цена активных подписок
        $avgPrice = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->andWhere(['!=', 'code', 'free'])
            ->average('price_monthly');

        return (float) ($avgPrice ?? 15000);
    }

    /**
     * Определить уровень риска
     */
    private function calculateRiskLevel(float $mrrAtRisk): string
    {
        $currentMRR = $this->reportService->getCurrentMRR();
        if ($currentMRR <= 0) {
            return 'unknown';
        }

        $riskPercent = ($mrrAtRisk / $currentMRR) * 100;

        if ($riskPercent <= 10) return 'low';
        if ($riskPercent <= 20) return 'medium';
        if ($riskPercent <= 30) return 'high';
        return 'critical';
    }

    /**
     * Форматирование месяца
     */
    private function formatMonth(string $yearMonth): string
    {
        $months = [
            '01' => 'Янв', '02' => 'Фев', '03' => 'Мар',
            '04' => 'Апр', '05' => 'Май', '06' => 'Июн',
            '07' => 'Июл', '08' => 'Авг', '09' => 'Сен',
            '10' => 'Окт', '11' => 'Ноя', '12' => 'Дек',
        ];

        $parts = explode('-', $yearMonth);
        if (count($parts) !== 2) {
            return $yearMonth;
        }

        return ($months[$parts[1]] ?? $parts[1]) . ' ' . substr($parts[0], 2);
    }

    /**
     * Текст статуса здоровья
     */
    private function getHealthSummaryText(string $status): string
    {
        return match ($status) {
            'excellent' => 'Бизнес-метрики в отличном состоянии',
            'good' => 'Бизнес-метрики в хорошем состоянии',
            'warning' => 'Некоторые метрики требуют внимания',
            'critical' => 'Критические показатели требуют немедленных действий',
            default => 'Недостаточно данных для оценки',
        };
    }
}
