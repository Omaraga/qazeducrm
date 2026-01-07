<?php

/** @var yii\web\View $this */
/** @var array $topByUsage */
/** @var array $usageByMonth */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Статистика промокодов';
$this->params['breadcrumbs'][] = ['label' => 'Промокоды', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Статистика';
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<div class="row">
    <!-- Топ промокодов -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-trophy text-warning"></i> Топ промокодов по использованию
            </div>
            <div class="card-body p-0">
                <?php if (empty($topByUsage)): ?>
                    <p class="text-muted text-center py-4">Нет данных</p>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Код</th>
                                <th>Название</th>
                                <th class="text-right">Использований</th>
                                <th class="text-right">Сумма скидок</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topByUsage as $i => $promo): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?= Html::a('<code>' . Html::encode($promo['code']) . '</code>', ['view', 'id' => $promo['id']]) ?>
                                </td>
                                <td><?= Html::encode($promo['name']) ?></td>
                                <td class="text-right">
                                    <strong><?= $promo['usage_count'] ?></strong>
                                </td>
                                <td class="text-right text-success">
                                    <?= number_format($promo['total_discount'] ?? 0, 0, '', ' ') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Использование по месяцам -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Использование по месяцам
            </div>
            <div class="card-body p-0">
                <?php if (empty($usageByMonth)): ?>
                    <p class="text-muted text-center py-4">Нет данных</p>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Месяц</th>
                                <th class="text-right">Использований</th>
                                <th class="text-right">Сумма скидок</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalCount = 0;
                            $totalDiscount = 0;
                            foreach ($usageByMonth as $row):
                                $totalCount += $row['count'];
                                $totalDiscount += $row['total_discount'];
                            ?>
                            <tr>
                                <td><?= $row['month'] ?></td>
                                <td class="text-right"><?= $row['count'] ?></td>
                                <td class="text-right text-success">
                                    <?= number_format($row['total_discount'], 0, '', ' ') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Итого</th>
                                <th class="text-right"><?= $totalCount ?></th>
                                <th class="text-right text-success"><?= number_format($totalDiscount, 0, '', ' ') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- График (если есть данные) -->
<?php if (!empty($usageByMonth)): ?>
<div class="card card-custom">
    <div class="card-header">
        <i class="fas fa-chart-line"></i> Динамика использования
    </div>
    <div class="card-body">
        <canvas id="usage-chart" height="100"></canvas>
    </div>
</div>

<?php
$labels = array_column($usageByMonth, 'month');
$counts = array_column($usageByMonth, 'count');
$discounts = array_column($usageByMonth, 'total_discount');

$this->registerJs("
var ctx = document.getElementById('usage-chart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: " . json_encode($labels) . ",
        datasets: [{
            label: 'Использований',
            data: " . json_encode($counts) . ",
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            yAxisID: 'y'
        }, {
            label: 'Сумма скидок (KZT)',
            data: " . json_encode($discounts) . ",
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
                    text: 'Использований'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Сумма скидок (KZT)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});
");
?>
<?php endif; ?>
