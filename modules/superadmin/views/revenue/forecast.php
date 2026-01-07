<?php

/** @var yii\web\View $this */
/** @var array $dashboard */
/** @var array $mrrForecast */
/** @var array $mrrAtRisk */
/** @var array $expiringTrials */
/** @var array $guaranteedRevenue */
/** @var array $scenarioAnalysis */
/** @var array $healthIndicators */
/** @var array $chartData */
/** @var int $months */
/** @var int $riskDays */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Прогнозирование выручки';
$this->params['breadcrumbs'][] = ['label' => 'Аналитика выручки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$formatMoney = function($value) {
    return number_format($value, 0, '', ' ') . ' KZT';
};

$statusColors = [
    'excellent' => 'success',
    'good' => 'primary',
    'warning' => 'warning',
    'critical' => 'danger',
];

$statusLabels = [
    'excellent' => 'Отлично',
    'good' => 'Хорошо',
    'warning' => 'Внимание',
    'critical' => 'Критично',
];
?>

<div class="mb-4">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-chart-line"></i> Дашборд
    </a>
    <a href="<?= Url::to(['reports']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-file-alt"></i> Отчёты
    </a>
    <a href="<?= Url::to(['metrics']) ?>" class="btn btn-outline-primary">
        <i class="fas fa-tachometer-alt"></i> Метрики
    </a>

    <!-- Фильтры -->
    <div class="float-right">
        <form class="form-inline" method="get">
            <label class="mr-2">Горизонт:</label>
            <select name="months" class="form-control form-control-sm mr-3" onchange="this.form.submit()">
                <option value="3" <?= $months == 3 ? 'selected' : '' ?>>3 мес</option>
                <option value="6" <?= $months == 6 ? 'selected' : '' ?>>6 мес</option>
                <option value="12" <?= $months == 12 ? 'selected' : '' ?>>12 мес</option>
            </select>
            <label class="mr-2">Риск:</label>
            <select name="risk_days" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="14" <?= $riskDays == 14 ? 'selected' : '' ?>>14 дней</option>
                <option value="30" <?= $riskDays == 30 ? 'selected' : '' ?>>30 дней</option>
                <option value="60" <?= $riskDays == 60 ? 'selected' : '' ?>>60 дней</option>
            </select>
        </form>
    </div>
</div>

<!-- Основные показатели -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Текущий MRR</h6>
                        <h3 class="mb-0"><?= $formatMoney($mrrForecast['current_mrr']) ?></h3>
                    </div>
                    <i class="fas fa-coins fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Прогноз MRR (<?= $months ?> мес)</h6>
                        <h3 class="mb-0"><?= $formatMoney($mrrForecast['summary']['final_mrr']) ?></h3>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
                <small>
                    <?= $mrrForecast['summary']['total_growth_percent'] >= 0 ? '+' : '' ?><?= $mrrForecast['summary']['total_growth_percent'] ?>%
                    рост
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-danger text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">MRR под риском</h6>
                        <h3 class="mb-0"><?= $formatMoney($mrrAtRisk['total_mrr_at_risk']) ?></h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
                <small><?= $mrrAtRisk['subscriptions_count'] ?> подписок истекают за <?= $riskDays ?> дн.</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card card-custom bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Ожидаемый MRR от триалов</h6>
                        <h3 class="mb-0"><?= $formatMoney($dashboard['trial_funnel']['expected_mrr']) ?></h3>
                    </div>
                    <i class="fas fa-user-plus fa-2x opacity-50"></i>
                </div>
                <small><?= $dashboard['trial_funnel']['active_trials'] ?> триалов, <?= $dashboard['trial_funnel']['conversion_rate'] ?>% конверсия</small>
            </div>
        </div>
    </div>
</div>

<!-- Индикаторы здоровья -->
<div class="card card-custom mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-heartbeat"></i> Здоровье бизнеса</span>
        <span class="badge badge-<?= $statusColors[$healthIndicators['overall_status']] ?? 'secondary' ?> p-2">
            <?= $statusLabels[$healthIndicators['overall_status']] ?? 'N/A' ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($healthIndicators['indicators'] as $indicator): ?>
                <?php
                $color = $statusColors[$indicator['status']] ?? 'secondary';
                ?>
                <div class="col-md-3 text-center mb-3">
                    <div class="h4 mb-1">
                        <span class="badge badge-<?= $color ?> p-2"><?= $indicator['value'] ?></span>
                    </div>
                    <strong><?= Html::encode($indicator['name']) ?></strong>
                    <br>
                    <small class="text-muted">
                        Цель: <?= $indicator['target'] ?>
                        <br>
                        <?= Html::encode($indicator['description']) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-center text-muted mt-3 mb-0">
            <i class="fas fa-info-circle"></i> <?= Html::encode($healthIndicators['summary']) ?>
        </p>
    </div>
</div>

<div class="row">
    <!-- График прогноза -->
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-area"></i> Прогноз MRR
            </div>
            <div class="card-body">
                <canvas id="forecast-chart" height="100"></canvas>
            </div>
            <div class="card-footer text-muted small">
                <strong>Предположения:</strong>
                Рост: <?= $mrrForecast['assumptions']['growth_rate'] ?>,
                Отток: <?= $mrrForecast['assumptions']['churn_rate'] ?>
                (на основе <?= $mrrForecast['assumptions']['based_on_months'] ?> мес.)
            </div>
        </div>
    </div>

    <!-- Сценарии -->
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-balance-scale"></i> Анализ сценариев (<?= $months ?> мес)
            </div>
            <div class="card-body">
                <?php foreach ($scenarioAnalysis['scenarios'] as $key => $scenario): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span style="color: <?= $scenario['color'] ?>">
                                <i class="fas fa-circle"></i>
                            </span>
                            <?= Html::encode($scenario['label']) ?>
                        </div>
                        <div class="text-right">
                            <strong><?= $formatMoney($scenario['final_mrr']) ?></strong>
                            <br>
                            <small class="<?= $scenario['growth_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $scenario['growth_percent'] >= 0 ? '+' : '' ?><?= $scenario['growth_percent'] ?>%
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Гарантированная выручка -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i> Гарантированная выручка
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <?php foreach ($guaranteedRevenue['forecast'] as $month): ?>
                        <tr>
                            <td><?= Html::encode($month['month_label']) ?></td>
                            <td class="text-right">
                                <strong><?= $formatMoney($month['guaranteed_revenue']) ?></strong>
                            </td>
                            <td class="text-right text-muted">
                                <?= $month['subscription_count'] ?> подп.
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <hr>
                <div class="text-right">
                    <strong>Итого: <?= $formatMoney($guaranteedRevenue['total_guaranteed']) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MRR под риском -->
<div class="card card-custom mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-exclamation-triangle text-danger"></i>
            MRR под риском — <?= $formatMoney($mrrAtRisk['total_mrr_at_risk']) ?>
        </span>
        <span class="badge badge-<?= $mrrAtRisk['risk_level'] === 'low' ? 'success' : ($mrrAtRisk['risk_level'] === 'medium' ? 'warning' : 'danger') ?>">
            Уровень риска: <?= ucfirst($mrrAtRisk['risk_level']) ?>
        </span>
    </div>
    <div class="card-body">
        <?php if (empty($mrrAtRisk['subscriptions'])): ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p>Нет подписок, истекающих в ближайшие <?= $riskDays ?> дней</p>
            </div>
        <?php else: ?>
            <div class="row mb-4">
                <!-- По неделям -->
                <div class="col-md-6">
                    <h6>По неделям</h6>
                    <canvas id="risk-by-week-chart" height="150"></canvas>
                </div>
                <!-- По планам -->
                <div class="col-md-6">
                    <h6>По тарифам</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Тариф</th>
                                <th class="text-right">Подписок</th>
                                <th class="text-right">MRR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mrrAtRisk['by_plan'] as $planName => $planData): ?>
                                <tr>
                                    <td><?= Html::encode($planName) ?></td>
                                    <td class="text-right"><?= $planData['count'] ?></td>
                                    <td class="text-right"><?= $formatMoney($planData['mrr']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Таблица подписок -->
            <h6>Истекающие подписки</h6>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Организация</th>
                            <th>Тариф</th>
                            <th class="text-center">Дней</th>
                            <th class="text-right">MRR</th>
                            <th>Истекает</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mrrAtRisk['subscriptions'] as $sub): ?>
                            <?php
                            $urgencyClass = $sub['days_remaining'] <= 3 ? 'table-danger' :
                                           ($sub['days_remaining'] <= 7 ? 'table-warning' : '');
                            ?>
                            <tr class="<?= $urgencyClass ?>">
                                <td>
                                    <strong><?= Html::encode($sub['organization_name']) ?></strong>
                                </td>
                                <td><?= Html::encode($sub['plan_name']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $sub['days_remaining'] <= 3 ? 'danger' : ($sub['days_remaining'] <= 7 ? 'warning' : 'secondary') ?>">
                                        <?= $sub['days_remaining'] ?> дн.
                                    </span>
                                </td>
                                <td class="text-right"><?= $formatMoney($sub['mrr']) ?></td>
                                <td><?= date('d.m.Y', strtotime($sub['expires_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Истекающие триалы -->
<?php if (!empty($expiringTrials['trials'])): ?>
<div class="card card-custom mb-4">
    <div class="card-header">
        <i class="fas fa-hourglass-half text-info"></i>
        Истекающие триалы — потенциальный MRR: <?= $formatMoney($expiringTrials['potential_mrr']) ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong><?= $expiringTrials['total_trials'] ?></strong> триалов истекают в ближайшие <?= $expiringTrials['period_days'] ?> дней.
            При конверсии <?= $expiringTrials['conversion_rate'] ?>% ожидаемый MRR:
            <strong><?= $formatMoney($expiringTrials['expected_mrr']) ?></strong>
        </div>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Организация</th>
                    <th>Тариф</th>
                    <th class="text-center">Дней</th>
                    <th class="text-right">Потенц. MRR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiringTrials['trials'] as $trial): ?>
                    <tr>
                        <td><?= Html::encode($trial['organization_name']) ?></td>
                        <td><?= Html::encode($trial['plan_name']) ?></td>
                        <td class="text-center">
                            <span class="badge badge-info"><?= $trial['days_remaining'] ?> дн.</span>
                        </td>
                        <td class="text-right"><?= $formatMoney($trial['potential_mrr']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Прогноз по месяцам -->
<div class="card card-custom mb-4">
    <div class="card-header">
        <i class="fas fa-calendar-alt"></i> Детальный прогноз по месяцам
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Месяц</th>
                        <th class="text-right">MRR</th>
                        <th class="text-right">ARR</th>
                        <th class="text-right">Новый MRR</th>
                        <th class="text-right">Отток</th>
                        <th class="text-right">Чистый рост</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-primary">
                        <td><strong>Текущий</strong></td>
                        <td class="text-right"><strong><?= $formatMoney($mrrForecast['current_mrr']) ?></strong></td>
                        <td class="text-right"><?= $formatMoney($mrrForecast['current_mrr'] * 12) ?></td>
                        <td colspan="3" class="text-center text-muted">—</td>
                    </tr>
                    <?php foreach ($mrrForecast['forecast'] as $month): ?>
                        <tr>
                            <td><?= Html::encode($month['month_label']) ?></td>
                            <td class="text-right"><strong><?= $formatMoney($month['mrr']) ?></strong></td>
                            <td class="text-right"><?= $formatMoney($month['arr']) ?></td>
                            <td class="text-right text-success">+<?= $formatMoney($month['new_mrr']) ?></td>
                            <td class="text-right text-danger">-<?= $formatMoney($month['churned_mrr']) ?></td>
                            <td class="text-right <?= $month['net_change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $month['net_change'] >= 0 ? '+' : '' ?><?= $formatMoney($month['net_change']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th>Итого рост</th>
                        <th class="text-right"><?= $formatMoney($mrrForecast['summary']['final_mrr']) ?></th>
                        <th class="text-right"><?= $formatMoney($mrrForecast['summary']['final_mrr'] * 12) ?></th>
                        <th colspan="2" class="text-center">—</th>
                        <th class="text-right <?= $mrrForecast['summary']['total_growth'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= $mrrForecast['summary']['total_growth'] >= 0 ? '+' : '' ?><?= $formatMoney($mrrForecast['summary']['total_growth']) ?>
                            (<?= $mrrForecast['summary']['total_growth_percent'] ?>%)
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
$chartDataJson = json_encode($chartData);
$riskByWeekLabels = json_encode(array_column($mrrAtRisk['by_week'], 'label'));
$riskByWeekData = json_encode(array_column($mrrAtRisk['by_week'], 'mrr'));
$riskByWeekCounts = json_encode(array_column($mrrAtRisk['by_week'], 'count'));

$this->registerJs(<<<JS
// График прогноза MRR
var chartData = {$chartDataJson};
var forecastCtx = document.getElementById('forecast-chart').getContext('2d');
new Chart(forecastCtx, {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: chartData.datasets.map(function(ds) {
            return {
                label: ds.label,
                data: ds.data,
                borderColor: ds.borderColor,
                backgroundColor: ds.backgroundColor,
                borderDash: ds.borderDash || [],
                fill: ds.fill || false,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6
            };
        })
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' +
                            new Intl.NumberFormat('ru-RU').format(context.raw) + ' KZT';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('ru-RU', {
                            notation: 'compact',
                            compactDisplay: 'short'
                        }).format(value);
                    }
                }
            }
        }
    }
});

// График риска по неделям
var riskLabels = {$riskByWeekLabels};
var riskData = {$riskByWeekData};
var riskCounts = {$riskByWeekCounts};

if (riskLabels.length > 0) {
    var riskCtx = document.getElementById('risk-by-week-chart');
    if (riskCtx) {
        new Chart(riskCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: riskLabels,
                datasets: [{
                    label: 'MRR под риском',
                    data: riskData,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(253, 224, 71, 0.6)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                return 'Подписок: ' + riskCounts[context.dataIndex];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('ru-RU', {
                                    notation: 'compact'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
}
JS
);
?>
