<?php

namespace app\modules\superadmin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\services\RevenueReportService;
use app\services\SaasMetricsService;
use app\services\RevenueAggregationService;
use app\services\RevenueForecastService;
use app\models\SaasRevenueMonthly;
use app\models\SaasRevenueDaily;
use app\models\OrganizationPayment;

/**
 * Контроллер отчётов и аналитики выручки
 */
class RevenueController extends Controller
{
    /**
     * @var RevenueReportService
     */
    private RevenueReportService $reportService;

    /**
     * @var SaasMetricsService
     */
    private SaasMetricsService $metricsService;

    /**
     * @var RevenueForecastService
     */
    private RevenueForecastService $forecastService;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->reportService = new RevenueReportService();
        $this->metricsService = new SaasMetricsService();
        $this->forecastService = new RevenueForecastService();
    }

    /**
     * Главный дашборд аналитики
     */
    public function actionIndex()
    {
        $dashboard = $this->reportService->getDashboard();
        $metrics = $this->metricsService->getAllMetrics();
        $healthSummary = $this->metricsService->getHealthSummary();

        // Данные для графиков
        $chartData = $this->reportService->getRevenueChartData(12);
        $dailyChartData = $this->reportService->getDailyChartData(30);

        // Распределение по планам
        $distributionByPlan = $this->metricsService->getDistributionByPlan();

        return $this->render('index', [
            'dashboard' => $dashboard,
            'metrics' => $metrics,
            'healthSummary' => $healthSummary,
            'chartData' => $chartData,
            'dailyChartData' => $dailyChartData,
            'distributionByPlan' => $distributionByPlan,
        ]);
    }

    /**
     * Детальные отчёты по выручке
     */
    public function actionReports()
    {
        $period = Yii::$app->request->get('period', 'month');
        $from = Yii::$app->request->get('from');
        $to = Yii::$app->request->get('to');

        // Определяем период
        if (!$from || !$to) {
            switch ($period) {
                case 'week':
                    $from = date('Y-m-d', strtotime('-7 days'));
                    $to = date('Y-m-d');
                    break;
                case 'quarter':
                    $from = date('Y-m-01', strtotime('-3 months'));
                    $to = date('Y-m-d');
                    break;
                case 'year':
                    $from = date('Y-01-01');
                    $to = date('Y-m-d');
                    break;
                case 'month':
                default:
                    $from = date('Y-m-01');
                    $to = date('Y-m-d');
                    break;
            }
        }

        $fromDateTime = $from . ' 00:00:00';
        $toDateTime = $to . ' 23:59:59';

        // Выручка по периодам
        $groupBy = $period === 'year' ? 'month' : 'day';
        $revenueByPeriod = $this->reportService->getRevenueByPeriod($fromDateTime, $toDateTime, $groupBy);

        // Выручка по планам
        $revenueByPlan = $this->reportService->getRevenueByPlan($fromDateTime, $toDateTime);

        // Выручка по менеджерам
        $revenueByManager = $this->reportService->getRevenueByManager($fromDateTime, $toDateTime);

        // Анализ скидок
        $discountAnalysis = $this->reportService->getDiscountAnalysis($fromDateTime, $toDateTime);

        // Топ организаций
        $topOrganizations = $this->reportService->getTopOrganizations($fromDateTime, $toDateTime, 10);

        // Статистика по методам оплаты
        $paymentMethodStats = $this->reportService->getPaymentMethodStats($fromDateTime, $toDateTime);

        return $this->render('reports', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'revenueByPeriod' => $revenueByPeriod,
            'revenueByPlan' => $revenueByPlan,
            'revenueByManager' => $revenueByManager,
            'discountAnalysis' => $discountAnalysis,
            'topOrganizations' => $topOrganizations,
            'paymentMethodStats' => $paymentMethodStats,
        ]);
    }

    /**
     * KPI метрики
     */
    public function actionMetrics()
    {
        $metrics = $this->metricsService->getAllMetrics();
        $healthSummary = $this->metricsService->getHealthSummary();

        // Тренды
        $mrrTrend = $this->metricsService->getMetricTrend('mrr', 6);
        $churnTrend = $this->metricsService->getMetricTrend('churn', 6);
        $nrrTrend = $this->metricsService->getMetricTrend('nrr', 6);

        // Распределения
        $distributionByPlan = $this->metricsService->getDistributionByPlan();
        $distributionByBilling = $this->metricsService->getDistributionByBillingPeriod();

        return $this->render('metrics', [
            'metrics' => $metrics,
            'healthSummary' => $healthSummary,
            'mrrTrend' => $mrrTrend,
            'churnTrend' => $churnTrend,
            'nrrTrend' => $nrrTrend,
            'distributionByPlan' => $distributionByPlan,
            'distributionByBilling' => $distributionByBilling,
        ]);
    }

    /**
     * Когортный анализ
     */
    public function actionCohorts()
    {
        $months = (int) Yii::$app->request->get('months', 12);
        $cohorts = $this->reportService->getCohortAnalysis($months);

        return $this->render('cohorts', [
            'cohorts' => $cohorts,
            'months' => $months,
        ]);
    }

    /**
     * История месячных данных
     */
    public function actionMonthly()
    {
        $data = SaasRevenueMonthly::find()
            ->orderBy(['year_month' => SORT_DESC])
            ->limit(24)
            ->all();

        return $this->render('monthly', [
            'data' => $data,
        ]);
    }

    /**
     * Пересчитать данные за месяц
     */
    public function actionRecalculateMonth(string $month)
    {
        $service = new RevenueAggregationService();
        $result = $service->aggregateMonth($month);

        Yii::$app->session->setFlash('success', "Данные за {$month} пересчитаны");

        return $this->redirect(['monthly']);
    }

    /**
     * Пересчитать все данные
     */
    public function actionRecalculateAll()
    {
        $service = new RevenueAggregationService();
        $service->reAggregateAll();

        Yii::$app->session->setFlash('success', 'Все данные пересчитаны');

        return $this->redirect(['index']);
    }

    // ==================== FORECAST ====================

    /**
     * Прогнозирование выручки
     */
    public function actionForecast()
    {
        $months = (int) Yii::$app->request->get('months', 6);
        $riskDays = (int) Yii::$app->request->get('risk_days', 30);

        $dashboard = $this->forecastService->getForecastDashboard();
        $mrrForecast = $this->forecastService->forecastMRR($months);
        $mrrAtRisk = $this->forecastService->getMRRAtRisk($riskDays);
        $expiringTrials = $this->forecastService->getExpiringTrials(14);
        $guaranteedRevenue = $this->forecastService->getGuaranteedRevenue(3);
        $scenarioAnalysis = $this->forecastService->getScenarioAnalysis($months);
        $healthIndicators = $this->forecastService->getHealthIndicators();
        $chartData = $this->forecastService->getForecastChartData($months);

        return $this->render('forecast', [
            'dashboard' => $dashboard,
            'mrrForecast' => $mrrForecast,
            'mrrAtRisk' => $mrrAtRisk,
            'expiringTrials' => $expiringTrials,
            'guaranteedRevenue' => $guaranteedRevenue,
            'scenarioAnalysis' => $scenarioAnalysis,
            'healthIndicators' => $healthIndicators,
            'chartData' => $chartData,
            'months' => $months,
            'riskDays' => $riskDays,
        ]);
    }

    /**
     * API: Данные для графика прогноза
     */
    public function actionForecastChartData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $months = (int) Yii::$app->request->get('months', 6);

        return $this->forecastService->getForecastChartData($months);
    }

    /**
     * API: MRR под риском
     */
    public function actionMrrAtRisk()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $days = (int) Yii::$app->request->get('days', 30);

        return $this->forecastService->getMRRAtRisk($days);
    }

    /**
     * API: Получить данные для графика
     */
    public function actionChartData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $type = Yii::$app->request->get('type', 'monthly');
        $months = (int) Yii::$app->request->get('months', 12);
        $days = (int) Yii::$app->request->get('days', 30);

        if ($type === 'daily') {
            return $this->reportService->getDailyChartData($days);
        }

        return $this->reportService->getRevenueChartData($months);
    }

    /**
     * API: Сравнение периодов
     */
    public function actionCompare()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $period1From = Yii::$app->request->get('period1_from', date('Y-m-01'));
        $period1To = Yii::$app->request->get('period1_to', date('Y-m-d'));
        $period2From = Yii::$app->request->get('period2_from', date('Y-m-01', strtotime('-1 month')));
        $period2To = Yii::$app->request->get('period2_to', date('Y-m-t', strtotime('-1 month')));

        return $this->reportService->comparePeriods(
            $period1From . ' 00:00:00',
            $period1To . ' 23:59:59',
            $period2From . ' 00:00:00',
            $period2To . ' 23:59:59'
        );
    }

    /**
     * Экспорт отчёта
     */
    public function actionExport()
    {
        $period = Yii::$app->request->get('period', 'month');
        $format = Yii::$app->request->get('format', 'csv');

        // Определяем период
        switch ($period) {
            case 'week':
                $from = date('Y-m-d', strtotime('-7 days'));
                $to = date('Y-m-d');
                break;
            case 'quarter':
                $from = date('Y-m-01', strtotime('-3 months'));
                $to = date('Y-m-d');
                break;
            case 'year':
                $from = date('Y-01-01');
                $to = date('Y-m-d');
                break;
            default:
                $from = date('Y-m-01');
                $to = date('Y-m-d');
        }

        $fromDateTime = $from . ' 00:00:00';
        $toDateTime = $to . ' 23:59:59';

        // Получаем данные
        $payments = OrganizationPayment::find()
            ->with(['organization', 'manager'])
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $fromDateTime])
            ->andWhere(['<=', 'processed_at', $toDateTime])
            ->orderBy(['processed_at' => SORT_DESC])
            ->all();

        if ($format === 'csv') {
            return $this->exportToCsv($payments, $from, $to);
        }

        return $this->redirect(['reports', 'period' => $period]);
    }

    /**
     * Экспорт в CSV
     */
    private function exportToCsv(array $payments, string $from, string $to): Response
    {
        $filename = "revenue_report_{$from}_{$to}.csv";

        $headers = [
            'Дата', 'Организация', 'Сумма', 'Скидка', 'Тип скидки', 'Менеджер', 'Метод оплаты', 'Номер транзакции',
        ];

        $rows = [];
        foreach ($payments as $payment) {
            $rows[] = [
                date('d.m.Y H:i', strtotime($payment->processed_at)),
                $payment->organization ? $payment->organization->name : 'N/A',
                number_format($payment->amount, 0, '.', ''),
                number_format($payment->discount_amount, 0, '.', ''),
                $payment->discount_type ?? '',
                $payment->manager ? $payment->manager->name : '',
                $payment->payment_method ?? '',
                $payment->payment_reference ?? '',
            ];
        }

        // Формируем CSV
        $output = fopen('php://temp', 'w');
        // BOM для UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->data = $csv;

        return $response;
    }
}
