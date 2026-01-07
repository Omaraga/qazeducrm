<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationAddon $model */

use yii\helpers\Html;
use app\models\OrganizationAddon;

$this->title = 'Продлить аддон';
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Аддон #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header">
                <span class="font-weight-bold">Продление аддона</span>
            </div>
            <div class="card-body">
                <h5><?= Html::encode($model->getFullName()) ?></h5>
                <p class="text-muted">
                    Организация: <?= Html::encode($model->organization->name ?? '-') ?>
                </p>

                <table class="table table-bordered">
                    <tr>
                        <th>Текущий статус</th>
                        <td>
                            <span class="badge <?= $model->getStatusBadgeClass() ?>">
                                <?= $model->getStatusLabel() ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Текущий период</th>
                        <td><?= OrganizationAddon::getBillingPeriodList()[$model->billing_period] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <th>Текущая дата окончания</th>
                        <td>
                            <?php if ($model->expires_at): ?>
                                <?= Yii::$app->formatter->asDate($model->expires_at, 'php:d.m.Y H:i') ?>
                                <?php $days = $model->getDaysRemaining(); ?>
                                <?php if ($days !== null): ?>
                                    <span class="badge <?= $days <= 0 ? 'badge-danger' : 'badge-info' ?>">
                                        <?= $days > 0 ? $days . ' дн.' : 'Истёк' ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Цена</th>
                        <td><?= $model->getFormattedPrice() ?></td>
                    </tr>
                </table>

                <hr>

                <h6>Выберите период продления:</h6>

                <?= Html::beginForm(['renew', 'id' => $model->id], 'post') ?>

                <div class="btn-group btn-group-lg d-flex mb-3">
                    <button type="submit" name="period" value="monthly" class="btn btn-outline-primary w-50">
                        <strong>Месяц</strong><br>
                        <small>+1 месяц</small>
                    </button>
                    <button type="submit" name="period" value="yearly" class="btn btn-outline-success w-50">
                        <strong>Год</strong><br>
                        <small>+1 год (экономия 2 мес.)</small>
                    </button>
                </div>

                <?= Html::endForm() ?>

                <p class="text-muted small">
                    Аддон будет продлён от текущей даты окончания (или от сегодня, если уже истёк).
                </p>

                <?= Html::a('Отмена', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <?php if ($model->feature): ?>
        <div class="card card-custom">
            <div class="card-header">
                <span class="font-weight-bold">Информация об аддоне</span>
            </div>
            <div class="card-body">
                <h5><?= Html::encode($model->feature->name) ?></h5>
                <p class="text-muted"><?= Html::encode($model->feature->description) ?></p>

                <?php if ($model->feature->addon_price_monthly): ?>
                <div class="mb-2">
                    <strong>Цена/мес:</strong>
                    <?= number_format($model->feature->addon_price_monthly, 0, '.', ' ') ?> KZT
                </div>
                <?php endif; ?>
                <?php if ($model->feature->addon_price_yearly): ?>
                <div class="mb-2">
                    <strong>Цена/год:</strong>
                    <?= number_format($model->feature->addon_price_yearly, 0, '.', ' ') ?> KZT
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
