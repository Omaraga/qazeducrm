<?php

/** @var yii\web\View $this */
/** @var array $topManagers */
/** @var array $pendingBonuses */
/** @var float $totalPending */
/** @var array $monthlyStats */
/** @var array|null $managerStats */
/** @var array $managerPayments */
/** @var array $managers */
/** @var int $year */
/** @var int $month */
/** @var int|null $managerId */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Продажи менеджеров';
$this->params['breadcrumbs'][] = ['label' => 'Платежи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$months = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
];
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К платежам
    </a>
    <a href="<?= Url::to(['pending-bonuses']) ?>" class="btn btn-outline-warning ml-2">
        <i class="fas fa-clock"></i> Ожидающие бонусы
        <?php if ($totalPending > 0): ?>
            <span class="badge badge-light"><?= number_format($totalPending, 0, '', ' ') ?> KZT</span>
        <?php endif; ?>
    </a>
</div>

<!-- Фильтр периода -->
<div class="card card-custom mb-4">
    <div class="card-body py-3">
        <form method="get" class="form-inline">
            <label class="mr-2">Период:</label>
            <select name="month" class="form-control form-control-sm mr-2">
                <?php foreach ($months as $m => $name): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-control form-control-sm mr-2">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <label class="ml-4 mr-2">Менеджер:</label>
            <select name="manager_id" class="form-control form-control-sm mr-2">
                <option value="">Все менеджеры</option>
                <?php foreach ($managers as $id => $name): ?>
                    <option value="<?= $id ?>" <?= $id == $managerId ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Применить
            </button>
        </form>
    </div>
</div>

<!-- Статистика за месяц -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-custom bg-primary text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($monthlyStats['total'], 0, '', ' ') ?></div>
                <small>Общий бонус за <?= $months[(int)$month] ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-warning text-dark">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($monthlyStats['pending'], 0, '', ' ') ?></div>
                <small>Ожидает выплаты</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-success text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($monthlyStats['paid'], 0, '', ' ') ?></div>
                <small>Выплачено</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-info text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($totalPending, 0, '', ' ') ?></div>
                <small>Всего к выплате</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Топ менеджеров -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-trophy text-warning"></i>
                Топ менеджеров за <?= $months[(int)$month] ?> <?= $year ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topManagers)): ?>
                    <p class="text-muted text-center py-4">Нет данных за этот период</p>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Менеджер</th>
                                <th class="text-right">Продажи</th>
                                <th class="text-right">Бонус</th>
                                <th class="text-right">Ожидает</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topManagers as $i => $manager): ?>
                            <tr>
                                <td>
                                    <?php if ($i === 0): ?>
                                        <span class="text-warning"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($i === 1): ?>
                                        <span class="text-secondary"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($i === 2): ?>
                                        <span class="text-danger"><i class="fas fa-medal"></i></span>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= Html::a(Html::encode($manager['manager_name']), ['manager-sales', 'manager_id' => $manager['manager_id'], 'year' => $year, 'month' => $month]) ?>
                                    <br><small class="text-muted"><?= $manager['payments_count'] ?> платежей</small>
                                </td>
                                <td class="text-right">
                                    <strong><?= number_format($manager['total_sales'], 0, '', ' ') ?></strong>
                                </td>
                                <td class="text-right text-primary">
                                    <?= number_format($manager['total_bonus'], 0, '', ' ') ?>
                                </td>
                                <td class="text-right text-warning">
                                    <?= number_format($manager['pending_bonus'], 0, '', ' ') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ожидающие бонусы по менеджерам -->
    <div class="col-lg-6">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock text-warning"></i> Ожидающие выплаты</span>
                <span class="badge badge-warning"><?= number_format($totalPending, 0, '', ' ') ?> KZT</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingBonuses)): ?>
                    <p class="text-muted text-center py-4">Нет ожидающих бонусов</p>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Менеджер</th>
                                <th class="text-right">Платежей</th>
                                <th class="text-right">К выплате</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingBonuses as $bonus): ?>
                            <tr>
                                <td><?= Html::encode($bonus['manager_name']) ?></td>
                                <td class="text-right"><?= $bonus['payments_count'] ?></td>
                                <td class="text-right">
                                    <strong class="text-warning"><?= number_format($bonus['pending_amount'], 0, '', ' ') ?></strong>
                                </td>
                                <td class="text-right">
                                    <?= Html::a('<i class="fas fa-money-bill-wave"></i> Выплатить', ['pay-all-bonuses', 'manager_id' => $bonus['manager_id']], [
                                        'class' => 'btn btn-sm btn-success',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Выплатить все бонусы менеджеру ' . $bonus['manager_name'] . '?',
                                    ]) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Детальная статистика выбранного менеджера -->
<?php if ($managerStats): ?>
<div class="card card-custom mb-4">
    <div class="card-header">
        <i class="fas fa-user-tie"></i>
        Детализация: <?= Html::encode($managers[$managerId] ?? 'Менеджер #' . $managerId) ?>
        <span class="text-muted ml-2"><?= $months[(int)$month] ?> <?= $year ?></span>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0"><?= number_format($managerStats['total_sales'], 0, '', ' ') ?></div>
                    <small class="text-muted">Продажи (KZT)</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0"><?= $managerStats['total_payments'] ?></div>
                    <small class="text-muted">Платежей</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0"><?= number_format($managerStats['avg_check'], 0, '', ' ') ?></div>
                    <small class="text-muted">Средний чек</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0 text-primary"><?= number_format($managerStats['total_bonus'], 0, '', ' ') ?></div>
                    <small class="text-muted">Общий бонус</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0 text-warning"><?= number_format($managerStats['pending_bonus'], 0, '', ' ') ?></div>
                    <small class="text-muted">Ожидает</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <div class="h3 mb-0 text-success"><?= number_format($managerStats['paid_bonus'], 0, '', ' ') ?></div>
                    <small class="text-muted">Выплачено</small>
                </div>
            </div>
        </div>

        <?php if (!empty($managerPayments)): ?>
        <h6 class="mb-3">Платежи за период</h6>
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата</th>
                    <th>Организация</th>
                    <th class="text-right">Сумма</th>
                    <th class="text-right">Бонус</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($managerPayments as $payment): ?>
                <tr>
                    <td>
                        <?= Html::a('#' . $payment->id, ['view', 'id' => $payment->id]) ?>
                    </td>
                    <td><?= Yii::$app->formatter->asDate($payment->processed_at, 'php:d.m.Y') ?></td>
                    <td><?= Html::encode($payment->organization->name ?? '-') ?></td>
                    <td class="text-right"><?= number_format($payment->amount, 0, '', ' ') ?></td>
                    <td class="text-right text-primary"><?= number_format($payment->manager_bonus_amount, 0, '', ' ') ?></td>
                    <td>
                        <?php
                        $bonusBadges = [
                            'pending' => 'badge-warning',
                            'paid' => 'badge-success',
                            'cancelled' => 'badge-secondary',
                        ];
                        $bonusLabels = [
                            'pending' => 'Ожидает',
                            'paid' => 'Выплачен',
                            'cancelled' => 'Отменён',
                        ];
                        ?>
                        <span class="badge <?= $bonusBadges[$payment->manager_bonus_status] ?? 'badge-light' ?>">
                            <?= $bonusLabels[$payment->manager_bonus_status] ?? $payment->manager_bonus_status ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
