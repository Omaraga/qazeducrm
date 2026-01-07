<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\services\RevenueAggregationService;
use app\services\RevenueReportService;
use app\services\SaasMetricsService;

/**
 * Console команды для работы с выручкой
 *
 * Cron:
 * - 0 3 * * *   php yii revenue/daily        # Ежедневно в 3:00
 * - 0 2 1 * *   php yii revenue/aggregate    # 1-го числа в 2:00
 * - 0 10 * * 1  php yii revenue/weekly-report # Понедельник в 10:00
 */
class RevenueController extends Controller
{
    /**
     * @var RevenueAggregationService
     */
    private RevenueAggregationService $aggregationService;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->aggregationService = new RevenueAggregationService();
    }

    /**
     * Агрегация данных за вчера
     *
     * Запуск: php yii revenue/daily
     * Cron: 0 3 * * * (каждый день в 3:00)
     */
    public function actionDaily(): int
    {
        $date = date('Y-m-d', strtotime('-1 day'));
        $this->stdout("Агрегация данных за {$date}...\n", Console::FG_CYAN);

        try {
            $result = $this->aggregationService->aggregateDay($date);

            $this->stdout("Результаты:\n", Console::FG_GREEN);
            $this->stdout("  Выручка: " . number_format($result->revenue, 0, '', ' ') . " KZT\n");
            $this->stdout("  Платежей: {$result->payments_count}\n");
            $this->stdout("  Новых триалов: {$result->new_trials}\n");
            $this->stdout("  Конверсий: {$result->conversions}\n");

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Агрегация данных за прошлый месяц
     *
     * Запуск: php yii revenue/aggregate
     * Запуск с параметром: php yii revenue/aggregate 2025-01
     * Cron: 0 2 1 * * (первого числа в 2:00)
     */
    public function actionAggregate(?string $month = null): int
    {
        $month = $month ?? date('Y-m', strtotime('-1 month'));
        $this->stdout("Агрегация данных за {$month}...\n", Console::FG_CYAN);

        try {
            $result = $this->aggregationService->aggregateMonth($month);

            $this->stdout("\nРезультаты:\n", Console::FG_GREEN);
            $this->stdout("  Выручка: " . number_format($result->total_revenue, 0, '', ' ') . " KZT\n");
            $this->stdout("  Скидки: " . number_format($result->total_discounts, 0, '', ' ') . " KZT\n");
            $this->stdout("  MRR (начало): " . number_format($result->mrr_start, 0, '', ' ') . " KZT\n");
            $this->stdout("  MRR (конец): " . number_format($result->mrr_end, 0, '', ' ') . " KZT\n");
            $this->stdout("  Новых подписок: {$result->new_subscriptions}\n");
            $this->stdout("  Продлений: {$result->renewals}\n");
            $this->stdout("  Отмен: {$result->cancellations}\n");
            $this->stdout("  Активных организаций: {$result->active_organizations}\n");

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Агрегация за последние N дней
     *
     * Запуск: php yii revenue/last-days 30
     */
    public function actionLastDays(int $days = 30): int
    {
        $this->stdout("Агрегация данных за последние {$days} дней...\n", Console::FG_CYAN);

        try {
            $this->aggregationService->aggregateLastDays($days);
            $this->stdout("Готово!\n", Console::FG_GREEN);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Агрегация за последние N месяцев
     *
     * Запуск: php yii revenue/last-months 12
     */
    public function actionLastMonths(int $months = 12): int
    {
        $this->stdout("Агрегация данных за последние {$months} месяцев...\n", Console::FG_CYAN);

        try {
            $this->aggregationService->aggregateLastMonths($months);
            $this->stdout("Готово!\n", Console::FG_GREEN);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Полная переагрегация всех данных
     *
     * Запуск: php yii revenue/re-aggregate
     */
    public function actionReAggregate(): int
    {
        $this->stdout("Полная переагрегация всех данных...\n", Console::FG_YELLOW);
        $this->stdout("Это может занять некоторое время.\n\n");

        if (!$this->confirm('Продолжить?')) {
            return ExitCode::OK;
        }

        try {
            $this->aggregationService->reAggregateAll();
            $this->stdout("Готово!\n", Console::FG_GREEN);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Показать текущие метрики
     *
     * Запуск: php yii revenue/metrics
     */
    public function actionMetrics(): int
    {
        $this->stdout("\n=== SaaS Метрики ===\n\n", Console::FG_CYAN, Console::BOLD);

        try {
            $service = new SaasMetricsService();
            $metrics = $service->getAllMetrics();

            // Выручка
            $this->stdout("ВЫРУЧКА:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  MRR: " . number_format($metrics['mrr'], 0, '', ' ') . " KZT\n");
            $this->stdout("  ARR: " . number_format($metrics['arr'], 0, '', ' ') . " KZT\n");
            if ($metrics['mrr_growth_percent'] !== null) {
                $color = $metrics['mrr_growth_percent'] >= 0 ? Console::FG_GREEN : Console::FG_RED;
                $sign = $metrics['mrr_growth_percent'] >= 0 ? '+' : '';
                $this->stdout("  Рост MRR: {$sign}{$metrics['mrr_growth_percent']}%\n", $color);
            }

            // Подписки
            $this->stdout("\nПОДПИСКИ:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  Активных: {$metrics['total_subscriptions']}\n");
            $this->stdout("  Новых (MTD): {$metrics['new_subscriptions_mtd']}\n");
            $this->stdout("  Churn Rate: {$metrics['churn_rate']}%\n");

            // Unit Economics
            $this->stdout("\nUNIT ECONOMICS:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  ARPU: " . number_format($metrics['arpu'], 0, '', ' ') . " KZT\n");
            $this->stdout("  ARPPU: " . number_format($metrics['arppu'], 0, '', ' ') . " KZT\n");
            $this->stdout("  LTV: " . number_format($metrics['ltv'], 0, '', ' ') . " KZT\n");
            $this->stdout("  CAC: " . number_format($metrics['cac'], 0, '', ' ') . " KZT\n");
            $this->stdout("  LTV/CAC: {$metrics['ltv_cac_ratio']}x\n");

            // Health
            $this->stdout("\nЗДОРОВЬЕ БИЗНЕСА:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  NRR: {$metrics['nrr']}%\n");
            $this->stdout("  Quick Ratio: {$metrics['quick_ratio']}\n");
            $this->stdout("  Trial → Paid: {$metrics['trial_to_paid_rate']}%\n");

            $this->stdout("\n");

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Показать дашборд выручки
     *
     * Запуск: php yii revenue/dashboard
     */
    public function actionDashboard(): int
    {
        $this->stdout("\n=== Дашборд выручки ===\n\n", Console::FG_CYAN, Console::BOLD);

        try {
            $service = new RevenueReportService();
            $dashboard = $service->getDashboard();

            $this->stdout("MRR: " . number_format($dashboard['current_mrr'], 0, '', ' ') . " KZT\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("ARR: " . number_format($dashboard['current_arr'], 0, '', ' ') . " KZT\n");

            if ($dashboard['mrr_growth'] !== null) {
                $color = $dashboard['mrr_growth'] >= 0 ? Console::FG_GREEN : Console::FG_RED;
                $sign = $dashboard['mrr_growth'] >= 0 ? '+' : '';
                $this->stdout("Рост MRR: {$sign}{$dashboard['mrr_growth']}%\n", $color);
            }

            $this->stdout("\n");
            $this->stdout("Выручка этот месяц: " . number_format($dashboard['revenue_this_month'], 0, '', ' ') . " KZT\n");
            $this->stdout("Выручка прошлый месяц: " . number_format($dashboard['revenue_last_month'], 0, '', ' ') . " KZT\n");

            $this->stdout("\n");
            $this->stdout("Активных подписок: {$dashboard['active_subscriptions']}\n");
            $this->stdout("На триале: {$dashboard['trial_subscriptions']}\n");
            $this->stdout("Платящих: {$dashboard['paying_organizations']}\n");

            $this->stdout("\n");
            $this->stdout("Churn Rate: {$dashboard['churn_rate']}%\n");
            $this->stdout("ARPU: " . number_format($dashboard['avg_revenue_per_org'], 0, '', ' ') . " KZT\n");
            $this->stdout("Trial Conversion: {$dashboard['trial_conversion_rate']}%\n");

            $this->stdout("\n");

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Еженедельный отчёт
     *
     * Запуск: php yii revenue/weekly-report
     * Cron: 0 10 * * 1 (понедельник в 10:00)
     */
    public function actionWeeklyReport(): int
    {
        $this->stdout("\n=== Еженедельный отчёт ===\n\n", Console::FG_CYAN, Console::BOLD);

        try {
            $service = new RevenueReportService();

            // Период - последняя неделя
            $from = date('Y-m-d', strtotime('-7 days'));
            $to = date('Y-m-d', strtotime('-1 day'));

            $this->stdout("Период: {$from} - {$to}\n\n");

            // Выручка за неделю
            $revenueData = $service->getRevenueByPeriod($from, $to, 'day');
            $totalRevenue = array_sum(array_column($revenueData, 'revenue'));
            $totalPayments = array_sum(array_column($revenueData, 'payments_count'));

            $this->stdout("ВЫРУЧКА ЗА НЕДЕЛЮ:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  Общая: " . number_format($totalRevenue, 0, '', ' ') . " KZT\n");
            $this->stdout("  Платежей: {$totalPayments}\n");

            // По дням
            $this->stdout("\n  По дням:\n");
            foreach ($revenueData as $day) {
                $this->stdout("    {$day['period']}: " . number_format($day['revenue'], 0, '', ' ') . " KZT ({$day['payments_count']} платежей)\n");
            }

            // По менеджерам
            $byManager = $service->getRevenueByManager($from . ' 00:00:00', $to . ' 23:59:59');
            if (!empty($byManager)) {
                $this->stdout("\n\nПО МЕНЕДЖЕРАМ:\n", Console::FG_GREEN, Console::BOLD);
                foreach ($byManager as $manager) {
                    $this->stdout("  {$manager['manager_name']}: " . number_format($manager['revenue'], 0, '', ' ') . " KZT ({$manager['payments_count']} платежей)\n");
                }
            }

            // Текущие метрики
            $metrics = new SaasMetricsService();
            $this->stdout("\n\nТЕКУЩИЕ МЕТРИКИ:\n", Console::FG_GREEN, Console::BOLD);
            $this->stdout("  MRR: " . number_format($metrics->getMRR(), 0, '', ' ') . " KZT\n");
            $this->stdout("  Активных подписок: {$metrics->getTotalActiveSubscriptions()}\n");
            $this->stdout("  Churn Rate: {$metrics->getChurnRate()}%\n");

            $this->stdout("\n");

            // TODO: Отправить отчёт на email супер-админа

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Показать топ организаций
     *
     * Запуск: php yii revenue/top-organizations
     */
    public function actionTopOrganizations(int $limit = 10): int
    {
        $this->stdout("\n=== Топ {$limit} организаций по выручке ===\n\n", Console::FG_CYAN, Console::BOLD);

        try {
            $service = new RevenueReportService();

            // За последние 30 дней
            $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
            $to = date('Y-m-d 23:59:59');

            $topOrgs = $service->getTopOrganizations($from, $to, $limit);

            $this->stdout("Период: последние 30 дней\n\n");

            $i = 1;
            foreach ($topOrgs as $org) {
                $this->stdout("{$i}. {$org['organization_name']}\n");
                $this->stdout("   Выручка: " . number_format($org['total_revenue'], 0, '', ' ') . " KZT\n");
                $this->stdout("   Платежей: {$org['payments_count']}\n\n");
                $i++;
            }

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
