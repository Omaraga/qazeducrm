<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationAddon $model */

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\OrganizationAddon;

$this->title = 'Аддон #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold"><?= Html::encode($model->getFullName()) ?></span>
                <div>
                    <?php if ($model->status === OrganizationAddon::STATUS_ACTIVE): ?>
                        <?= Html::a('<i class="fas fa-sync"></i> Продлить', ['renew', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-ban"></i> Отменить', ['cancel', 'id' => $model->id], [
                            'class' => 'btn btn-warning btn-sm',
                            'data' => ['confirm' => 'Отменить этот аддон?', 'method' => 'post'],
                        ]) ?>
                    <?php elseif ($model->status === OrganizationAddon::STATUS_TRIAL): ?>
                        <?= Html::beginForm(['activate', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                            <?= Html::hiddenInput('period', 'monthly') ?>
                            <?= Html::submitButton('<i class="fas fa-check"></i> Активировать', ['class' => 'btn btn-success btn-sm']) ?>
                        <?= Html::endForm() ?>
                    <?php elseif ($model->status === OrganizationAddon::STATUS_EXPIRED): ?>
                        <?= Html::a('<i class="fas fa-redo"></i> Продлить', ['renew', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    <?= Html::a('<i class="fas fa-trash"></i> Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger btn-sm',
                        'data' => ['confirm' => 'Удалить этот аддон?', 'method' => 'post'],
                    ]) ?>
                </div>
            </div>
            <div class="card-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-bordered detail-view'],
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'organization_id',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->organization) {
                                    return Html::a(
                                        Html::encode($model->organization->name),
                                        ['/superadmin/organization/view', 'id' => $model->organization_id]
                                    );
                                }
                                return '-';
                            },
                        ],
                        [
                            'attribute' => 'feature_id',
                            'label' => 'Функция/Аддон',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->feature) {
                                    return Html::encode($model->feature->name) .
                                        ' <span class="badge badge-secondary">' . $model->feature->code . '</span>';
                                }
                                return 'ID ' . $model->feature_id;
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return '<span class="badge ' . $model->getStatusBadgeClass() . '">' .
                                    $model->getStatusLabel() . '</span>';
                            },
                        ],
                        'quantity',
                        [
                            'attribute' => 'billing_period',
                            'value' => function ($model) {
                                return OrganizationAddon::getBillingPeriodList()[$model->billing_period] ?? $model->billing_period;
                            },
                        ],
                        [
                            'attribute' => 'price',
                            'value' => function ($model) {
                                return $model->getFormattedPrice();
                            },
                        ],
                        [
                            'attribute' => 'value',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $value = $model->getValueArray();
                                if (empty($value)) {
                                    return '-';
                                }
                                return '<pre class="mb-0">' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                            },
                        ],
                        [
                            'attribute' => 'started_at',
                            'format' => 'datetime',
                        ],
                        [
                            'attribute' => 'expires_at',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if (!$model->expires_at) return '-';

                                $date = Yii::$app->formatter->asDatetime($model->expires_at);
                                $days = $model->getDaysRemaining();

                                if ($days !== null) {
                                    if ($days === 0) {
                                        return $date . ' <span class="badge badge-danger">Сегодня</span>';
                                    } elseif ($days <= 7) {
                                        return $date . ' <span class="badge badge-warning">' . $days . ' дн.</span>';
                                    } else {
                                        return $date . ' <span class="badge badge-success">' . $days . ' дн.</span>';
                                    }
                                }
                                return $date;
                            },
                        ],
                        [
                            'attribute' => 'trial_ends_at',
                            'format' => 'datetime',
                            'visible' => $model->status === OrganizationAddon::STATUS_TRIAL || $model->trial_ends_at,
                        ],
                        [
                            'attribute' => 'cancelled_at',
                            'format' => 'datetime',
                            'visible' => $model->cancelled_at !== null,
                        ],
                        [
                            'attribute' => 'created_by',
                            'value' => function ($model) {
                                return $model->createdByUser ? $model->createdByUser->name : '-';
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'datetime',
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php if ($model->feature): ?>
        <div class="card card-custom">
            <div class="card-header">
                <span class="font-weight-bold">Информация об аддоне</span>
            </div>
            <div class="card-body">
                <h5><?= Html::encode($model->feature->name) ?></h5>
                <p class="text-muted"><?= Html::encode($model->feature->description) ?></p>

                <div class="mb-2">
                    <strong>Категория:</strong>
                    <?= $model->feature->getCategoryLabel() ?>
                </div>
                <div class="mb-2">
                    <strong>Тип:</strong>
                    <?= $model->feature->getTypeLabel() ?>
                </div>
                <?php if ($model->feature->addon_price_monthly): ?>
                <div class="mb-2">
                    <strong>Цена:</strong>
                    <?= $model->feature->getFormattedAddonPrice() ?>
                </div>
                <?php endif; ?>
                <?php if ($model->feature->trial_available): ?>
                <div class="mb-2">
                    <strong>Trial:</strong>
                    <?= $model->feature->trial_days ?> дней
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card card-custom mt-3">
            <div class="card-header">
                <span class="font-weight-bold">Быстрые действия</span>
            </div>
            <div class="card-body">
                <?= Html::a('<i class="fas fa-building"></i> Все аддоны организации',
                    ['organization', 'id' => $model->organization_id],
                    ['class' => 'btn btn-outline-secondary btn-block mb-2']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> К списку аддонов',
                    ['index'],
                    ['class' => 'btn btn-outline-secondary btn-block']) ?>
            </div>
        </div>
    </div>
</div>
