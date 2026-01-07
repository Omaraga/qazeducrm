<?php

/** @var yii\web\View $this */
/** @var app\models\SaasRevenueMonthly[] $data */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Данные по месяцам';
$this->params['breadcrumbs'][] = ['label' => 'Аналитика', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'По месяцам';

$formatMoney = function($value) {
    return number_format($value, 0, '', ' ');
};
?>

<div class="mb-4">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К дашборду
    </a>
</div>

<div class="card card-custom">
    <div class="card-header">
        <i class="fas fa-calendar-alt"></i> История по месяцам
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Месяц</th>
                        <th class="text-right">Выручка</th>
                        <th class="text-right">Скидки</th>
                        <th class="text-right">MRR</th>
                        <th class="text-right">Рост MRR</th>
                        <th class="text-right">Новые</th>
                        <th class="text-right">Отмены</th>
                        <th class="text-right">Churn</th>
                        <th class="text-right">NRR</th>
                        <th class="text-right">Орг.</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <?php $comparison = $row->getComparisonWithPrevious(); ?>
                        <tr>
                            <td>
                                <strong><?= $row->getFormattedMonth() ?></strong>
                                <?php if ($row->calculated_at): ?>
                                    <br>
                                    <small class="text-muted">
                                        Обновлено: <?= date('d.m.Y H:i', strtotime($row->calculated_at)) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <strong class="text-success"><?= $formatMoney($row->total_revenue) ?></strong>
                                <?php if (!empty($comparison['revenue_change_percent'])): ?>
                                    <br>
                                    <small class="<?= $comparison['revenue_change_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $comparison['revenue_change_percent'] >= 0 ? '+' : '' ?><?= $comparison['revenue_change_percent'] ?>%
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right text-warning"><?= $formatMoney($row->total_discounts) ?></td>
                            <td class="text-right text-primary">
                                <strong><?= $formatMoney($row->mrr_end) ?></strong>
                            </td>
                            <td class="text-right">
                                <?php $mrrGrowth = $row->getMrrGrowthPercent(); ?>
                                <?php if ($mrrGrowth !== null): ?>
                                    <span class="<?= $mrrGrowth >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $mrrGrowth >= 0 ? '+' : '' ?><?= $mrrGrowth ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right text-success">+<?= $row->new_subscriptions ?></td>
                            <td class="text-right text-danger">-<?= $row->cancellations ?></td>
                            <td class="text-right">
                                <?php $churn = $row->getChurnRate(); ?>
                                <?php if ($churn !== null): ?>
                                    <span class="<?= $churn <= 5 ? 'text-success' : 'text-danger' ?>">
                                        <?= $churn ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php $nrr = $row->getNRR(); ?>
                                <?php if ($nrr !== null): ?>
                                    <span class="<?= $nrr >= 100 ? 'text-success' : 'text-warning' ?>">
                                        <?= $nrr ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= $row->active_organizations ?></td>
                            <td class="text-center">
                                <a href="<?= Url::to(['recalculate-month', 'month' => $row->year_month]) ?>"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Пересчитать"
                                   onclick="return confirm('Пересчитать данные за <?= $row->year_month ?>?')">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($data)): ?>
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> MRR по месяцам
            </div>
            <div class="card-body">
                <canvas id="mrr-history-chart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Выручка по месяцам
            </div>
            <div class="card-body">
                <canvas id="revenue-history-chart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$reversedData = array_reverse($data);
$months = json_encode(array_map(fn($d) => $d->year_month, $reversedData));
$mrrValues = json_encode(array_map(fn($d) => (float)$d->mrr_end, $reversedData));
$revenueValues = json_encode(array_map(fn($d) => (float)$d->total_revenue, $reversedData));

$this->registerJs(<<<JS
new Chart(document.getElementById('mrr-history-chart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {$months},
        datasets: [{
            label: 'MRR (KZT)',
            data: {$mrrValues},
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: false } }
    }
});

new Chart(document.getElementById('revenue-history-chart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {$months},
        datasets: [{
            label: 'Выручка (KZT)',
            data: {$revenueValues},
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
JS
);
?>
<?php endif; ?>
