<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationPayment $model */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\OrganizationPayment;

$this->title = 'Платёж #' . $model->id;
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
    <a href="<?= Url::to(['manager-sales']) ?>" class="btn btn-outline-info ml-2">
        <i class="fas fa-chart-bar"></i> Продажи менеджеров
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация о платеже</span>
                <?php
                $badges = [
                    'pending' => 'badge-warning',
                    'completed' => 'badge-success',
                    'failed' => 'badge-danger',
                    'refunded' => 'badge-secondary',
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
                                <td class="text-muted">Подписка</td>
                                <td>
                                    <?php if ($model->subscription): ?>
                                        <?= Html::a('Подписка #' . $model->subscription_id, ['/superadmin/subscription/view', 'id' => $model->subscription_id]) ?>
                                        <br><small class="text-muted"><?= $model->subscription->saasPlan->name ?? '—' ?></small>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Сумма</td>
                                <td>
                                    <strong class="h4"><?= number_format($model->amount, 0, '', ' ') ?></strong>
                                    <span class="text-muted"><?= $model->currency ?></span>
                                    <?php if ($model->discount_amount > 0): ?>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-tag"></i>
                                            Скидка: <?= number_format($model->discount_amount, 0, '', ' ') ?> KZT
                                            <?php if ($model->discount_type): ?>
                                                (<?= OrganizationPayment::getDiscountTypeList()[$model->discount_type] ?? $model->discount_type ?>)
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Период</td>
                                <td>
                                    <?php if ($model->period_start): ?>
                                        <?= Yii::$app->formatter->asDate($model->period_start, 'php:d.m.Y') ?> —
                                        <?= Yii::$app->formatter->asDate($model->period_end, 'php:d.m.Y') ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($model->payment_method || $model->payment_reference): ?>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Способ оплаты</td>
                                <td><?= Html::encode($model->payment_method) ?: '—' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Референс</td>
                                <td><code><?= Html::encode($model->payment_reference) ?: '—' ?></code></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($model->notes): ?>
                <hr>
                <h6>Примечания</h6>
                <p class="mb-0"><?= Html::encode($model->notes) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Блок менеджера -->
        <?php if ($model->manager_id): ?>
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-tie"></i> Менеджер продаж</span>
                <?php
                $bonusBadges = [
                    OrganizationPayment::BONUS_PENDING => 'badge-warning',
                    OrganizationPayment::BONUS_PAID => 'badge-success',
                    OrganizationPayment::BONUS_CANCELLED => 'badge-secondary',
                ];
                $bonusClass = $bonusBadges[$model->manager_bonus_status] ?? 'badge-secondary';
                $bonusLabels = OrganizationPayment::getBonusStatusList();
                ?>
                <span class="badge <?= $bonusClass ?>">
                    <?= $bonusLabels[$model->manager_bonus_status] ?? $model->manager_bonus_status ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Менеджер</td>
                                <td>
                                    <strong><?= Html::encode($model->manager->name ?? 'Удалён') ?></strong>
                                    <?php if ($model->manager && $model->manager->email): ?>
                                        <br><small class="text-muted"><?= Html::encode($model->manager->email) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Бонус</td>
                                <td>
                                    <strong class="h5 text-primary">
                                        <?= number_format($model->manager_bonus_amount, 0, '', ' ') ?> KZT
                                    </strong>
                                    <br>
                                    <small class="text-muted"><?= $model->manager_bonus_percent ?>% от суммы</small>
                                </td>
                            </tr>
                            <?php if ($model->manager_bonus_paid_at): ?>
                            <tr>
                                <td class="text-muted">Выплачен</td>
                                <td><?= Yii::$app->formatter->asDatetime($model->manager_bonus_paid_at, 'php:d.m.Y H:i') ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if ($model->status === OrganizationPayment::STATUS_COMPLETED && $model->manager_bonus_amount > 0): ?>
                <hr>
                <div class="d-flex justify-content-end">
                    <?php if ($model->manager_bonus_status === OrganizationPayment::BONUS_PENDING): ?>
                        <?= Html::a('<i class="fas fa-money-bill-wave"></i> Выплатить бонус', ['pay-bonus', 'id' => $model->id], [
                            'class' => 'btn btn-success mr-2',
                            'data-method' => 'post',
                            'data-confirm' => 'Подтвердить выплату бонуса ' . number_format($model->manager_bonus_amount, 0, '', ' ') . ' KZT?',
                        ]) ?>
                        <?= Html::a('<i class="fas fa-ban"></i> Отменить бонус', ['cancel-bonus', 'id' => $model->id], [
                            'class' => 'btn btn-outline-secondary',
                            'data-method' => 'post',
                            'data-confirm' => 'Отменить бонус менеджера?',
                        ]) ?>
                    <?php elseif ($model->manager_bonus_status === OrganizationPayment::BONUS_PAID): ?>
                        <span class="text-success">
                            <i class="fas fa-check-circle"></i> Бонус выплачен
                        </span>
                    <?php elseif ($model->manager_bonus_status === OrganizationPayment::BONUS_CANCELLED): ?>
                        <span class="text-muted">
                            <i class="fas fa-ban"></i> Бонус отменён
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">Действия</div>
            <div class="card-body">
                <?php if ($model->status === 'pending'): ?>
                    <?= Html::a('<i class="fas fa-check"></i> Подтвердить оплату', ['complete', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Подтвердить оплату? Подписка будет продлена.' .
                            ($model->manager_id ? ' Бонус менеджера будет рассчитан.' : ''),
                    ]) ?>
                    <?= Html::a('<i class="fas fa-times"></i> Отклонить', ['fail', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Пометить платёж как неудавшийся?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status === 'completed'): ?>
                    <?= Html::a('<i class="fas fa-undo"></i> Сделать возврат', ['refund', 'id' => $model->id], [
                        'class' => 'btn btn-outline-warning btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Сделать возврат? Это не отменит подписку автоматически.',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status === 'pending' || $model->status === 'failed'): ?>
                    <?= Html::a('<i class="fas fa-trash"></i> Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => 'Удалить платёж?',
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
                        <td class="text-muted">Создан</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?></td>
                    </tr>
                    <?php if ($model->processed_at): ?>
                    <tr>
                        <td class="text-muted">Обработан</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->processed_at, 'php:d.m.Y H:i') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Обновлён</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') ?></td>
                    </tr>
                    <?php if ($model->original_amount && $model->original_amount != $model->amount): ?>
                    <tr>
                        <td class="text-muted">До скидки</td>
                        <td><?= number_format($model->original_amount, 0, '', ' ') ?> KZT</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
