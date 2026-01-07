<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationSubscriptionRequest $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->title = 'Запрос #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Подписки', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Запросы', 'url' => ['requests']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <?= Html::encode($this->title) ?>
        <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= $model->getStatusLabel() ?></span>
    </h1>
    <a href="<?= Url::to(['requests']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку запросов
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Информация о запросе
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-bordered'],
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'organization_id',
                            'format' => 'raw',
                            'value' => $model->organization
                                ? Html::a(Html::encode($model->organization->name), ['/superadmin/organization/view', 'id' => $model->organization_id])
                                : '-',
                        ],
                        [
                            'attribute' => 'request_type',
                            'value' => $model->getTypeLabel(),
                        ],
                        [
                            'attribute' => 'current_plan_id',
                            'label' => 'Текущий план',
                            'value' => $model->currentPlan ? $model->currentPlan->name : '-',
                        ],
                        [
                            'attribute' => 'requested_plan_id',
                            'label' => 'Запрашиваемый план',
                            'format' => 'raw',
                            'value' => $model->requestedPlan
                                ? '<strong>' . Html::encode($model->requestedPlan->name) . '</strong> (' .
                                  number_format($model->requestedPlan->price_monthly, 0, '', ' ') . ' KZT/мес)'
                                : '-',
                        ],
                        [
                            'attribute' => 'billing_period',
                            'value' => $model->billing_period === 'yearly' ? 'Годовой' : 'Месячный',
                        ],
                        [
                            'attribute' => 'contact_name',
                            'value' => $model->contact_name ?: '-',
                        ],
                        [
                            'attribute' => 'contact_phone',
                            'format' => 'raw',
                            'value' => $model->contact_phone
                                ? Html::a($model->contact_phone, 'tel:' . $model->contact_phone)
                                : '-',
                        ],
                        [
                            'attribute' => 'comment',
                            'format' => 'ntext',
                            'value' => $model->comment ?: '-',
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => ['datetime', 'php:d.m.Y H:i:s'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

        <?php if ($model->processed_at): ?>
        <div class="card card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-user-check"></i> Обработка
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-bordered'],
                    'attributes' => [
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>',
                        ],
                        [
                            'attribute' => 'processed_by',
                            'value' => $model->processedByUser ? $model->processedByUser->username : '-',
                        ],
                        [
                            'attribute' => 'processed_at',
                            'format' => ['datetime', 'php:d.m.Y H:i:s'],
                        ],
                        [
                            'attribute' => 'admin_comment',
                            'format' => 'ntext',
                            'value' => $model->admin_comment ?: '-',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <?php if ($model->isPending()): ?>
        <div class="card card-custom mb-4 border-warning">
            <div class="card-header bg-warning text-white">
                <i class="fas fa-tasks"></i> Действия
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div class="form-group">
                        <label for="admin_comment">Комментарий администратора</label>
                        <textarea name="admin_comment" id="admin_comment" class="form-control" rows="3"
                                  placeholder="Опционально для одобрения, обязательно для отклонения"></textarea>
                    </div>

                    <div class="btn-group-vertical w-100">
                        <button type="submit" formaction="<?= Url::to(['approve-request', 'id' => $model->id]) ?>"
                                class="btn btn-success mb-2">
                            <i class="fas fa-check"></i> Одобрить
                        </button>
                        <button type="submit" formaction="<?= Url::to(['reject-request', 'id' => $model->id]) ?>"
                                class="btn btn-danger mb-2">
                            <i class="fas fa-times"></i> Отклонить
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif ($model->status === 'approved'): ?>
        <div class="card card-custom mb-4 border-info">
            <div class="card-header bg-info text-white">
                <i class="fas fa-tasks"></i> Следующий шаг: Оплата
            </div>
            <div class="card-body">
                <?php
                // Рассчитываем сумму платежа
                $paymentAmount = 0;
                if ($model->requestedPlan) {
                    $paymentAmount = $model->billing_period === 'yearly'
                        ? $model->requestedPlan->price_yearly
                        : $model->requestedPlan->price_monthly;
                }
                ?>

                <div class="alert alert-info mb-3">
                    <strong>Сумма к оплате:</strong><br>
                    <?= number_format($paymentAmount, 0, '', ' ') ?> KZT
                    (<?= $model->billing_period === 'yearly' ? 'годовой' : 'месячный' ?> период)
                </div>

                <a href="<?= Url::to(['/superadmin/payment/create',
                    'organization_id' => $model->organization_id,
                    'request_id' => $model->id,
                    'amount' => $paymentAmount,
                ]) ?>" class="btn btn-warning btn-block mb-3">
                    <i class="fas fa-money-bill"></i> Создать платёж
                </a>

                <hr>
                <p class="text-muted small mb-2">После получения оплаты отметьте заявку как выполненную:</p>

                <form method="post" action="<?= Url::to(['complete-request', 'id' => $model->id]) ?>">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <input type="hidden" name="admin_comment" value="">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-check-double"></i> Оплата получена - Выполнить
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Быстрые действия -->
        <div class="card card-custom">
            <div class="card-header">
                <i class="fas fa-bolt"></i> Быстрые действия
            </div>
            <div class="card-body">
                <?php if ($model->organization): ?>
                    <a href="<?= Url::to(['/superadmin/organization/view', 'id' => $model->organization_id]) ?>"
                       class="btn btn-outline-primary btn-block mb-2">
                        <i class="fas fa-building"></i> Организация
                    </a>
                <?php endif; ?>

                <?php if ($model->organization): ?>
                    <?php
                    $subscription = $model->organization->getActiveSubscription();
                    if ($subscription):
                    ?>
                        <a href="<?= Url::to(['view', 'id' => $subscription->id]) ?>"
                           class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-credit-card"></i> Текущая подписка
                        </a>
                        <a href="<?= Url::to(['extend', 'id' => $subscription->id]) ?>"
                           class="btn btn-outline-success btn-block mb-2">
                            <i class="fas fa-calendar-plus"></i> Продлить подписку
                        </a>
                    <?php else: ?>
                        <a href="<?= Url::to(['create', 'organization_id' => $model->organization_id]) ?>"
                           class="btn btn-success btn-block mb-2">
                            <i class="fas fa-plus"></i> Создать подписку
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="<?= Url::to(['/superadmin/payment/create', 'organization_id' => $model->organization_id]) ?>"
                   class="btn btn-outline-warning btn-block">
                    <i class="fas fa-money-bill"></i> Создать платёж
                </a>
            </div>
        </div>
    </div>
</div>
