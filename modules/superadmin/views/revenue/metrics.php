<?php

/** @var yii\web\View $this */
/** @var array $metrics */
/** @var array $healthSummary */
/** @var array $mrrTrend */
/** @var array $churnTrend */
/** @var array $nrrTrend */
/** @var array $distributionByPlan */
/** @var array $distributionByBilling */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'KPI Метрики';
$this->params['breadcrumbs'][] = ['label' => 'Аналитика', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'KPI Метрики';

$formatMoney = function($value) {
    return number_format($value, 0, '', ' ');
};
?>

<div class="mb-4">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К дашборду
    </a>
</div>

<!-- Здоровье бизнеса -->
<div class="card card-custom mb-4">
    <div class="card-header">
        <i class="fas fa-heartbeat"></i> Здоровье бизнеса
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($healthSummary as $key => $item): ?>
                <div class="col-md-3">
                    <div class="card mb-3 <?= $item['status'] === 'good' ? 'border-success' : ($item['status'] === 'warning' ? 'border-warning' : 'border-danger') ?>">
                        <div class="card-body text-center">
                            <?php
                            $badgeClass = match($item['status']) {
                                'good' => 'badge-success',
                                'warning' => 'badge-warning',
                                'bad' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                            $icon = match($key) {
                                'nrr' => 'fas fa-sync-alt',
                                'quick_ratio' => 'fas fa-tachometer-alt',
                                'ltv_cac' => 'fas fa-balance-scale',
                                'churn_rate' => 'fas fa-user-minus',
                                default => 'fas fa-chart-line',
                            };
                            ?>
                            <h3>
                                <span class="badge <?= $badgeClass ?> p-2">
                                    <?= $item['value'] ?><?= in_array($key, ['nrr', 'churn_rate']) ? '%' : '' ?>
                                </span>
                            </h3>
                            <p class="mb-0"><i class="<?= $icon ?> mr-1"></i> <?= $item['label'] ?></p>
                            <small class="text-muted">
                                <?php
                                echo match($item['status']) {
                                    'good' => 'Отлично',
                                    'warning' => 'Требует внимания',
                                    'bad' => 'Критично',
                                    default => '',
                                };
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Выручка -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-dollar-sign"></i> Выручка
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>MRR (Monthly Recurring Revenue)</td>
                        <td class="text-right"><strong class="text-primary"><?= $formatMoney($metrics['mrr']) ?> KZT</strong></td>
                    </tr>
                    <tr>
                        <td>ARR (Annual Recurring Revenue)</td>
                        <td class="text-right"><strong><?= $formatMoney($metrics['arr']) ?> KZT</strong></td>
                    </tr>
                    <tr>
                        <td>Рост MRR (vs прошлый месяц)</td>
                        <td class="text-right">
                            <?php if ($metrics['mrr_growth_percent'] !== null): ?>
                                <span class="<?= $metrics['mrr_growth_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $metrics['mrr_growth_percent'] >= 0 ? '+' : '' ?><?= $metrics['mrr_growth_percent'] ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Net MRR Churn</td>
                        <td class="text-right">
                            <span class="<?= $metrics['net_mrr_churn'] <= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $formatMoney($metrics['net_mrr_churn']) ?> KZT
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Expansion MRR (от апгрейдов)</td>
                        <td class="text-right text-success"><?= $formatMoney($metrics['expansion_mrr']) ?> KZT</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Подписки -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-users"></i> Подписки
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>Активных подписок</td>
                        <td class="text-right"><strong class="text-info"><?= $metrics['total_subscriptions'] ?></strong></td>
                    </tr>
                    <tr>
                        <td>Новых (MTD)</td>
                        <td class="text-right text-success">+<?= $metrics['new_subscriptions_mtd'] ?></td>
                    </tr>
                    <tr>
                        <td>Churn Rate</td>
                        <td class="text-right">
                            <span class="<?= $metrics['churn_rate'] <= 5 ? 'text-success' : 'text-danger' ?>">
                                <?= $metrics['churn_rate'] ?>%
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Trial → Paid</td>
                        <td class="text-right"><?= $metrics['trial_to_paid_rate'] ?>%</td>
                    </tr>
                    <tr>
                        <td>Upgrade Rate</td>
                        <td class="text-right"><?= $metrics['upgrade_rate'] ?>%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Unit Economics -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-calculator"></i> Unit Economics
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>
                            ARPU
                            <small class="text-muted d-block">Average Revenue Per User</small>
                        </td>
                        <td class="text-right"><strong><?= $formatMoney($metrics['arpu']) ?> KZT</strong></td>
                    </tr>
                    <tr>
                        <td>
                            ARPPU
                            <small class="text-muted d-block">Average Revenue Per Paying User</small>
                        </td>
                        <td class="text-right"><strong><?= $formatMoney($metrics['arppu']) ?> KZT</strong></td>
                    </tr>
                    <tr>
                        <td>
                            LTV
                            <small class="text-muted d-block">Lifetime Value</small>
                        </td>
                        <td class="text-right"><strong class="text-success"><?= $formatMoney($metrics['ltv']) ?> KZT</strong></td>
                    </tr>
                    <tr>
                        <td>
                            CAC
                            <small class="text-muted d-block">Customer Acquisition Cost</small>
                        </td>
                        <td class="text-right"><?= $formatMoney($metrics['cac']) ?> KZT</td>
                    </tr>
                    <tr>
                        <td>
                            LTV/CAC Ratio
                            <small class="text-muted d-block">&gt; 3x = отлично</small>
                        </td>
                        <td class="text-right">
                            <span class="<?= $metrics['ltv_cac_ratio'] >= 3 ? 'text-success' : 'text-warning' ?>">
                                <strong><?= $metrics['ltv_cac_ratio'] ?>x</strong>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Retention -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header bg-warning text-white">
                <i class="fas fa-sync-alt"></i> Retention
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>
                            NRR (Net Revenue Retention)
                            <small class="text-muted d-block">&gt; 100% = рост от существующих</small>
                        </td>
                        <td class="text-right">
                            <span class="<?= $metrics['nrr'] >= 100 ? 'text-success' : 'text-warning' ?>">
                                <strong><?= $metrics['nrr'] ?>%</strong>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Quick Ratio
                            <small class="text-muted d-block">&gt; 4 = отлично, &gt; 2 = хорошо</small>
                        </td>
                        <td class="text-right">
                            <span class="<?= $metrics['quick_ratio'] >= 4 ? 'text-success' : ($metrics['quick_ratio'] >= 2 ? 'text-warning' : 'text-danger') ?>">
                                <strong><?= $metrics['quick_ratio'] ?></strong>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Тренды -->
<div class="row">
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> MRR Тренд
            </div>
            <div class="card-body">
                <canvas id="mrr-trend-chart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Churn Тренд
            </div>
            <div class="card-body">
                <canvas id="churn-trend-chart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> NRR Тренд
            </div>
            <div class="card-body">
                <canvas id="nrr-trend-chart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Распределения -->
<div class="row">
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-pie-chart"></i> По тарифам
            </div>
            <div class="card-body">
                <canvas id="plan-chart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-pie-chart"></i> По периодам биллинга
            </div>
            <div class="card-body">
                <canvas id="billing-chart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$mrrTrendLabels = json_encode(array_column($mrrTrend, 'month'));
$mrrTrendValues = json_encode(array_column($mrrTrend, 'value'));
$churnTrendLabels = json_encode(array_column($churnTrend, 'month'));
$churnTrendValues = json_encode(array_column($churnTrend, 'value'));
$nrrTrendLabels = json_encode(array_column($nrrTrend, 'month'));
$nrrTrendValues = json_encode(array_column($nrrTrend, 'value'));

$planLabels = json_encode(array_column($distributionByPlan, 'plan_name'));
$planCounts = json_encode(array_column($distributionByPlan, 'count'));
$billingLabels = json_encode(array_map(fn($b) => $b['billing_period'] === 'monthly' ? 'Ежемесячно' : 'Ежегодно', $distributionByBilling));
$billingCounts = json_encode(array_column($distributionByBilling, 'count'));

$this->registerJs(<<<JS
// Функция безопасного создания графика
function createChart(canvasId, config) {
    var canvas = document.getElementById(canvasId);
    if (canvas && config.data && config.data.labels && config.data.labels.length > 0) {
        return new Chart(canvas.getContext('2d'), config);
    }
    return null;
}

// MRR Trend
createChart('mrr-trend-chart', {
    type: 'line',
    data: {
        labels: {$mrrTrendLabels},
        datasets: [{
            label: 'MRR',
            data: {$mrrTrendValues},
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: false } }
    }
});

// Churn Trend
createChart('churn-trend-chart', {
    type: 'line',
    data: {
        labels: {$churnTrendLabels},
        datasets: [{
            label: 'Churn %',
            data: {$churnTrendValues},
            borderColor: 'rgba(255, 99, 132, 1)',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// NRR Trend
createChart('nrr-trend-chart', {
    type: 'line',
    data: {
        labels: {$nrrTrendLabels},
        datasets: [{
            label: 'NRR %',
            data: {$nrrTrendValues},
            borderColor: 'rgba(255, 206, 86, 1)',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: false } }
    }
});

// Plan Distribution
createChart('plan-chart', {
    type: 'doughnut',
    data: {
        labels: {$planLabels},
        datasets: [{
            data: {$planCounts},
            backgroundColor: ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Billing Distribution
createChart('billing-chart', {
    type: 'doughnut',
    data: {
        labels: {$billingLabels},
        datasets: [{
            data: {$billingCounts},
            backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
JS
);
?>
