<?php

/** @var yii\web\View $this */
/** @var array $dashboard */
/** @var array $metrics */
/** @var array $healthSummary */
/** @var array $chartData */
/** @var array $dailyChartData */
/** @var array $distributionByPlan */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Аналитика выручки';
$this->params['breadcrumbs'][] = $this->title;

// Форматирование чисел
$formatMoney = function($value) {
    return number_format($value, 0, '', ' ') . ' KZT';
};
?>

<div class="mb-4">
    <a href="<?= Url::to(['forecast']) ?>" class="btn btn-primary">
        <i class="fas fa-crystal-ball"></i> Прогнозирование
    </a>
    <a href="<?= Url::to(['reports']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-file-alt"></i> Детальные отчёты
    </a>
    <a href="<?= Url::to(['metrics']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-chart-line"></i> KPI Метрики
    </a>
    <a href="<?= Url::to(['cohorts']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-users"></i> Когортный анализ
    </a>
    <a href="<?= Url::to(['monthly']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-calendar-alt"></i> По месяцам
    </a>
    <a href="<?= Url::to(['recalculate-all']) ?>" class="btn btn-outline-secondary float-right"
       onclick="return confirm('Пересчитать все данные?')">
        <i class="fas fa-sync-alt"></i> Пересчитать
    </a>
</div>

<!-- Основные метрики -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">MRR</h6>
                        <h3 class="mb-0"><?= $formatMoney($metrics['mrr']) ?></h3>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
                <?php if ($metrics['mrr_growth_percent'] !== null): ?>
                    <small class="<?= $metrics['mrr_growth_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $metrics['mrr_growth_percent'] >= 0 ? '+' : '' ?><?= $metrics['mrr_growth_percent'] ?>%
                        vs прошлый месяц
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">ARR</h6>
                        <h3 class="mb-0"><?= $formatMoney($metrics['arr']) ?></h3>
                    </div>
                    <i class="fas fa-calendar fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Активных подписок</h6>
                        <h3 class="mb-0"><?= $metrics['total_subscriptions'] ?></h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
                <small>Новых (MTD): +<?= $metrics['new_subscriptions_mtd'] ?></small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-warning text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Churn Rate</h6>
                        <h3 class="mb-0"><?= $metrics['churn_rate'] ?>%</h3>
                    </div>
                    <i class="fas fa-user-minus fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Вторая строка метрик -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <h6 class="text-muted mb-1">ARPU</h6>
                <h4 class="mb-0"><?= $formatMoney($metrics['arpu']) ?></h4>
                <small class="text-muted">Средняя выручка на организацию</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <h6 class="text-muted mb-1">LTV</h6>
                <h4 class="mb-0"><?= $formatMoney($metrics['ltv']) ?></h4>
                <small class="text-muted">Пожизненная ценность клиента</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <h6 class="text-muted mb-1">LTV/CAC</h6>
                <h4 class="mb-0"><?= $metrics['ltv_cac_ratio'] ?>x</h4>
                <small class="text-muted <?= $metrics['ltv_cac_ratio'] >= 3 ? 'text-success' : 'text-warning' ?>">
                    <?= $metrics['ltv_cac_ratio'] >= 3 ? 'Отлично' : ($metrics['ltv_cac_ratio'] >= 1 ? 'Нормально' : 'Нужна оптимизация') ?>
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <h6 class="text-muted mb-1">Trial → Paid</h6>
                <h4 class="mb-0"><?= $metrics['trial_to_paid_rate'] ?>%</h4>
                <small class="text-muted">Конверсия в платные</small>
            </div>
        </div>
    </div>
</div>

<!-- Здоровье бизнеса -->
<div class="card card-custom mb-4">
    <div class="card-header">
        <i class="fas fa-heartbeat"></i> Здоровье бизнеса
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($healthSummary as $key => $item): ?>
                <div class="col-md-3 text-center">
                    <div class="mb-2">
                        <?php
                        $badgeClass = match($item['status']) {
                            'good' => 'badge-success',
                            'warning' => 'badge-warning',
                            'bad' => 'badge-danger',
                            default => 'badge-secondary',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?> p-2">
                            <?= $item['value'] ?><?= $key === 'churn_rate' ? '%' : ($key === 'nrr' ? '%' : '') ?>
                        </span>
                    </div>
                    <small class="text-muted"><?= $item['label'] ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- График выручки -->
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar"></i> Выручка и MRR</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" data-chart="monthly">Месяцы</button>
                    <button class="btn btn-outline-secondary" data-chart="daily">Дни</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenue-chart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Распределение по планам -->
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-pie-chart"></i> По тарифам
            </div>
            <div class="card-body">
                <canvas id="plan-chart" height="200"></canvas>
                <table class="table table-sm mt-3">
                    <?php foreach ($distributionByPlan as $plan): ?>
                        <tr>
                            <td><?= Html::encode($plan['plan_name']) ?></td>
                            <td class="text-right"><strong><?= $plan['count'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Выручка этот/прошлый месяц -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-check"></i> Выручка этот месяц
            </div>
            <div class="card-body text-center">
                <h2 class="text-success"><?= $formatMoney($dashboard['revenue_this_month']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-calendar"></i> Выручка прошлый месяц
            </div>
            <div class="card-body text-center">
                <h2><?= $formatMoney($dashboard['revenue_last_month']) ?></h2>
                <?php
                $change = $dashboard['revenue_this_month'] - $dashboard['revenue_last_month'];
                $changePercent = $dashboard['revenue_last_month'] > 0
                    ? round(($change / $dashboard['revenue_last_month']) * 100, 1)
                    : 0;
                ?>
                <?php if ($changePercent !== 0): ?>
                    <small class="<?= $changePercent >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $changePercent >= 0 ? '+' : '' ?><?= $changePercent ?>% к прошлому месяцу
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$monthlyData = json_encode($chartData);
$dailyData = json_encode($dailyChartData);
$planLabels = json_encode(array_column($distributionByPlan, 'plan_name'));
$planCounts = json_encode(array_column($distributionByPlan, 'count'));

$this->registerJs(<<<JS
var monthlyData = {$monthlyData};
var dailyData = {$dailyData};

// График выручки
var revenueCanvas = document.getElementById('revenue-chart');
var revenueChart = null;
if (revenueCanvas && monthlyData && monthlyData.labels) {
    var revenueCtx = revenueCanvas.getContext('2d');
    revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Выручка (KZT)',
            data: monthlyData.revenues,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            yAxisID: 'y'
        }, {
            label: 'MRR (KZT)',
            data: monthlyData.mrr,
            type: 'line',
            fill: false,
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Выручка'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'MRR'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});
}

// Переключение графика
$('[data-chart]').on('click', function() {
    if (!revenueChart) return;

    $('[data-chart]').removeClass('active');
    $(this).addClass('active');

    var type = $(this).data('chart');
    if (type === 'daily' && dailyData) {
        revenueChart.data.labels = dailyData.labels;
        revenueChart.data.datasets[0].data = dailyData.revenues;
        revenueChart.data.datasets[1].data = dailyData.payments;
        revenueChart.data.datasets[1].label = 'Платежей';
    } else if (monthlyData) {
        revenueChart.data.labels = monthlyData.labels;
        revenueChart.data.datasets[0].data = monthlyData.revenues;
        revenueChart.data.datasets[1].data = monthlyData.mrr;
        revenueChart.data.datasets[1].label = 'MRR (KZT)';
    }
    revenueChart.update();
});

// График по планам
var planCanvas = document.getElementById('plan-chart');
if (planCanvas) {
    var planLabels = {$planLabels};
    var planCounts = {$planCounts};
    if (planLabels && planLabels.length > 0) {
        var planCtx = planCanvas.getContext('2d');
        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: planLabels,
                datasets: [{
                    data: planCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}
JS
);
?>
