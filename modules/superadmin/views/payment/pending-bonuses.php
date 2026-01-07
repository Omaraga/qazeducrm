<?php

/** @var yii\web\View $this */
/** @var array $pendingBonuses */
/** @var float $totalPending */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ожидающие бонусы';
$this->params['breadcrumbs'][] = ['label' => 'Платежи', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Продажи менеджеров', 'url' => ['manager-sales']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mb-3">
    <a href="<?= Url::to(['manager-sales']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К отчёту
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-custom bg-warning text-dark">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($totalPending, 0, '', ' ') ?> KZT</div>
                <small>Всего к выплате</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom bg-info text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= count($pendingBonuses) ?></div>
                <small>Платежей с ожидающими бонусами</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-clock text-warning"></i> Список ожидающих бонусов</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pendingBonuses)): ?>
            <p class="text-muted text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                Нет ожидающих бонусов
            </p>
        <?php else: ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Платёж</th>
                        <th>Дата</th>
                        <th>Организация</th>
                        <th>Менеджер</th>
                        <th class="text-right">Сумма платежа</th>
                        <th class="text-right">Бонус</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingBonuses as $bonus): ?>
                    <tr>
                        <td>
                            <?= Html::a('#' . $bonus['id'], ['view', 'id' => $bonus['id']]) ?>
                        </td>
                        <td><?= Yii::$app->formatter->asDate($bonus['processed_at'], 'php:d.m.Y') ?></td>
                        <td><?= Html::encode($bonus['organization_name']) ?></td>
                        <td>
                            <strong><?= Html::encode($bonus['manager_name']) ?></strong>
                        </td>
                        <td class="text-right">
                            <?= number_format($bonus['amount'], 0, '', ' ') ?> KZT
                        </td>
                        <td class="text-right">
                            <strong class="text-warning">
                                <?= number_format($bonus['manager_bonus_amount'], 0, '', ' ') ?> KZT
                            </strong>
                            <br>
                            <small class="text-muted"><?= $bonus['manager_bonus_percent'] ?>%</small>
                        </td>
                        <td class="text-right">
                            <?= Html::a('<i class="fas fa-money-bill-wave"></i> Выплатить', ['pay-bonus', 'id' => $bonus['id']], [
                                'class' => 'btn btn-sm btn-success',
                                'data-method' => 'post',
                                'data-confirm' => 'Выплатить бонус ' . number_format($bonus['manager_bonus_amount'], 0, '', ' ') . ' KZT?',
                            ]) ?>
                            <?= Html::a('<i class="fas fa-ban"></i>', ['cancel-bonus', 'id' => $bonus['id']], [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'data-method' => 'post',
                                'data-confirm' => 'Отменить бонус?',
                                'title' => 'Отменить бонус',
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-warning">
                        <td colspan="5" class="text-right"><strong>Итого:</strong></td>
                        <td class="text-right">
                            <strong><?= number_format($totalPending, 0, '', ' ') ?> KZT</strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</div>
