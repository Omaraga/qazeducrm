<?php

/** @var yii\web\View $this */
/** @var string $period */
/** @var string $from */
/** @var string $to */
/** @var array $revenueByPeriod */
/** @var array $revenueByPlan */
/** @var array $revenueByManager */
/** @var array $discountAnalysis */
/** @var array $topOrganizations */
/** @var array $paymentMethodStats */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\OrganizationPayment;

$this->title = 'Отчёты по выручке';
$this->params['breadcrumbs'][] = ['label' => 'Аналитика', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Отчёты';

$formatMoney = function($value) {
    return number_format($value, 0, '', ' ');
};

$totalRevenue = array_sum(array_column($revenueByPeriod, 'revenue'));
$totalDiscounts = array_sum(array_column($revenueByPeriod, 'discounts'));
$totalPayments = array_sum(array_column($revenueByPeriod, 'payments_count'));
?>

<div class="mb-4">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К дашборду
    </a>
    <a href="<?= Url::to(['export', 'period' => $period, 'format' => 'csv']) ?>" class="btn btn-outline-success">
        <i class="fas fa-download"></i> Экспорт CSV
    </a>
</div>

<!-- Фильтр периода -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <form method="get" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Период:</label>
                <select name="period" class="form-control" onchange="this.form.submit()">
                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>Неделя</option>
                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Месяц</option>
                    <option value="quarter" <?= $period === 'quarter' ? 'selected' : '' ?>>Квартал</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Год</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">С:</label>
                <input type="date" name="from" value="<?= $from ?>" class="form-control">
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">По:</label>
                <input type="date" name="to" value="<?= $to ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Применить</button>
        </form>
    </div>
</div>

<!-- Сводка -->
<div class="row">
    <div class="col-md-4">
        <div class="card card-custom bg-success text-white mb-4">
            <div class="card-body text-center">
                <h6 class="text-white-50">Общая выручка</h6>
                <h2><?= $formatMoney($totalRevenue) ?> KZT</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom bg-warning text-white mb-4">
            <div class="card-body text-center">
                <h6 class="text-white-50">Скидки</h6>
                <h2><?= $formatMoney($totalDiscounts) ?> KZT</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom bg-info text-white mb-4">
            <div class="card-body text-center">
                <h6 class="text-white-50">Платежей</h6>
                <h2><?= $totalPayments ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- График по периодам -->
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-area"></i> Динамика выручки
            </div>
            <div class="card-body">
                <canvas id="period-chart" height="100"></canvas>
            </div>
        </div>

        <!-- По менеджерам -->
        <?php if (!empty($revenueByManager)): ?>
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-user-tie"></i> Выручка по менеджерам
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Менеджер</th>
                            <th class="text-right">Выручка</th>
                            <th class="text-right">Платежей</th>
                            <th class="text-right">Организаций</th>
                            <th class="text-right">Бонус</th>
                            <th class="text-right">К выплате</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenueByManager as $row): ?>
                        <tr>
                            <td><?= Html::encode($row['manager_name']) ?></td>
                            <td class="text-right text-success"><strong><?= $formatMoney($row['revenue']) ?></strong></td>
                            <td class="text-right"><?= $row['payments_count'] ?></td>
                            <td class="text-right"><?= $row['organizations'] ?></td>
                            <td class="text-right"><?= $formatMoney($row['total_bonus']) ?></td>
                            <td class="text-right text-warning"><?= $formatMoney($row['pending_bonus']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Топ организаций -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-trophy"></i> Топ организаций
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Организация</th>
                            <th class="text-right">Выручка</th>
                            <th class="text-right">Платежей</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topOrganizations as $i => $org): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?= Html::a(Html::encode($org['organization_name']), ['/superadmin/organization/view', 'id' => $org['organization_id']]) ?>
                            </td>
                            <td class="text-right text-success"><strong><?= $formatMoney($org['total_revenue']) ?></strong></td>
                            <td class="text-right"><?= $org['payments_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- По тарифам -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-layer-group"></i> По тарифам
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Тариф</th>
                            <th class="text-right">Выручка</th>
                            <th class="text-right">Орг.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenueByPlan as $plan): ?>
                        <tr>
                            <td><?= Html::encode($plan['plan_name']) ?></td>
                            <td class="text-right text-success"><?= $formatMoney($plan['revenue']) ?></td>
                            <td class="text-right"><?= $plan['organizations'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- По методам оплаты -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-credit-card"></i> По методам оплаты
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Метод</th>
                            <th class="text-right">Выручка</th>
                            <th class="text-right">Кол-во</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentMethodStats as $method): ?>
                        <tr>
                            <td><?= OrganizationPayment::getPaymentMethodList()[$method['payment_method']] ?? $method['payment_method'] ?></td>
                            <td class="text-right text-success"><?= $formatMoney($method['revenue']) ?></td>
                            <td class="text-right"><?= $method['payments_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Анализ скидок -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-percent"></i> Анализ скидок
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-warning"><?= $formatMoney($discountAnalysis['total_discounts']) ?> KZT</h4>
                    <small class="text-muted">Общая сумма скидок</small>
                </div>

                <?php if (!empty($discountAnalysis['by_type'])): ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th class="text-right">Сумма</th>
                            <th class="text-right">Кол-во</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discountAnalysis['by_type'] as $type): ?>
                        <tr>
                            <td><?= OrganizationPayment::getDiscountTypeList()[$type['discount_type']] ?? $type['discount_type'] ?></td>
                            <td class="text-right"><?= $formatMoney($type['amount']) ?></td>
                            <td class="text-right"><?= $type['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <?php if (!empty($discountAnalysis['top_promo_codes'])): ?>
                <hr>
                <h6>Топ промокодов</h6>
                <table class="table table-sm">
                    <?php foreach ($discountAnalysis['top_promo_codes'] as $promo): ?>
                    <tr>
                        <td><code><?= Html::encode($promo['code']) ?></code></td>
                        <td class="text-right"><?= $formatMoney($promo['total_discount']) ?></td>
                        <td class="text-right text-muted"><?= $promo['usage_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$periodLabels = json_encode(array_column($revenueByPeriod, 'period'));
$periodRevenues = json_encode(array_column($revenueByPeriod, 'revenue'));
$periodDiscounts = json_encode(array_column($revenueByPeriod, 'discounts'));

$this->registerJs(<<<JS
var periodCtx = document.getElementById('period-chart').getContext('2d');
new Chart(periodCtx, {
    type: 'line',
    data: {
        labels: {$periodLabels},
        datasets: [{
            label: 'Выручка (KZT)',
            data: {$periodRevenues},
            fill: true,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1
        }, {
            label: 'Скидки (KZT)',
            data: {$periodDiscounts},
            fill: true,
            backgroundColor: 'rgba(255, 206, 86, 0.2)',
            borderColor: 'rgba(255, 206, 86, 1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
JS
);
?>
