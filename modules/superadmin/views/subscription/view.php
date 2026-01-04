<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationSubscription $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Подписка #' . $model->id;
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация о подписке</span>
                <?php
                $badges = [
                    'trial' => 'badge-trial',
                    'active' => 'badge-active',
                    'expired' => 'badge-expired',
                    'suspended' => 'badge-suspended',
                    'cancelled' => 'badge-secondary',
                ];
                $class = $badges[$model->status] ?? 'badge-secondary';
                ?>
                <span class="badge <?= $class ?>"><?= $model->getStatusLabel() ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Организация</td>
                                <td>
                                    <?php if ($model->organization): ?>
                                        <?= Html::a(Html::encode($model->organization->name), ['/superadmin/organization/view', 'id' => $model->organization_id]) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Тарифный план</td>
                                <td>
                                    <?php if ($model->saasPlan): ?>
                                        <?= Html::a(Html::encode($model->saasPlan->name), ['/superadmin/plan/view', 'id' => $model->saas_plan_id]) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Период оплаты</td>
                                <td>
                                    <?= $model->billing_period === 'yearly' ? 'Годовая' : 'Месячная' ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Дата начала</td>
                                <td><?= Yii::$app->formatter->asDatetime($model->started_at) ?></td>
                            </tr>
                            <?php if ($model->trial_ends_at): ?>
                            <tr>
                                <td class="text-muted">Trial до</td>
                                <td><?= Yii::$app->formatter->asDatetime($model->trial_ends_at) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="text-muted">Истекает</td>
                                <td>
                                    <?php if ($model->expires_at): ?>
                                        <?php if ($model->isExpired()): ?>
                                            <span class="text-danger"><?= Yii::$app->formatter->asDatetime($model->expires_at) ?></span>
                                        <?php elseif ($model->isExpiringSoon()): ?>
                                            <?= Yii::$app->formatter->asDatetime($model->expires_at) ?>
                                            <span class="badge badge-warning">Скоро</span>
                                        <?php else: ?>
                                            <?= Yii::$app->formatter->asDatetime($model->expires_at) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($model->custom_limits): ?>
                <hr>
                <h6>Кастомные лимиты</h6>
                <pre class="bg-light p-3 rounded"><?= Html::encode(json_encode(json_decode($model->custom_limits), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                <?php endif; ?>
            </div>
        </div>

        <!-- Лимиты тарифа -->
        <?php if ($model->saasPlan): ?>
        <div class="card card-custom mb-4">
            <div class="card-header">Лимиты тарифа</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <div class="h3"><?= $model->saasPlan->max_pupils ?: '∞' ?></div>
                        <small class="text-muted">Учеников</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->saasPlan->max_teachers ?: '∞' ?></div>
                        <small class="text-muted">Учителей</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->saasPlan->max_groups ?: '∞' ?></div>
                        <small class="text-muted">Групп</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->saasPlan->max_admins ?: '∞' ?></div>
                        <small class="text-muted">Админов</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->saasPlan->max_branches ?: '∞' ?></div>
                        <small class="text-muted">Филиалов</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- История платежей -->
        <div class="card card-custom">
            <div class="card-header">Платежи</div>
            <div class="card-body">
                <?php
                $payments = $model->getPayments()->orderBy(['created_at' => SORT_DESC])->limit(10)->all();
                if ($payments):
                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Сумма</th>
                            <th>Период</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDate($payment->created_at) ?></td>
                            <td><?= number_format($payment->amount, 0, '', ' ') ?> <?= $payment->currency ?></td>
                            <td>
                                <?= Yii::$app->formatter->asDate($payment->period_start) ?> —
                                <?= Yii::$app->formatter->asDate($payment->period_end) ?>
                            </td>
                            <td>
                                <?php
                                $statusBadges = [
                                    'pending' => 'badge-warning',
                                    'completed' => 'badge-success',
                                    'failed' => 'badge-danger',
                                    'refunded' => 'badge-secondary',
                                ];
                                $badgeClass = $statusBadges[$payment->status] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $payment->getStatusLabel() ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted mb-0">Платежей пока нет</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">Действия</div>
            <div class="card-body">
                <?php if ($model->status === 'trial' || $model->status === 'expired'): ?>
                    <?= Html::a('<i class="fas fa-check"></i> Активировать', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Активировать подписку?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status === 'active' || $model->status === 'trial'): ?>
                    <?= Html::a('<i class="fas fa-calendar-plus"></i> Продлить', ['extend', 'id' => $model->id], [
                        'class' => 'btn btn-primary btn-block mb-2',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status === 'active' || $model->status === 'trial'): ?>
                    <?= Html::a('<i class="fas fa-pause"></i> Приостановить', ['suspend', 'id' => $model->id], [
                        'class' => 'btn btn-warning btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Приостановить подписку?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status === 'suspended'): ?>
                    <?= Html::a('<i class="fas fa-play"></i> Возобновить', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Возобновить подписку?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status !== 'cancelled'): ?>
                    <?= Html::a('<i class="fas fa-times"></i> Отменить', ['cancel', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => 'Вы уверены? Подписка будет отменена.',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header">Информация</div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">ID</td>
                        <td><?= $model->id ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Создано</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Обновлено</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
