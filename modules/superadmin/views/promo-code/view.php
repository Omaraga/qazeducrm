<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPromoCode $model */
/** @var yii\data\ActiveDataProvider $usageDataProvider */
/** @var array $stats */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Промокод: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Промокоды', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->code;
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
    <?= Html::a('<i class="fas fa-edit"></i> Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary ml-2']) ?>
    <?= Html::a($model->is_active ? '<i class="fas fa-toggle-off"></i> Деактивировать' : '<i class="fas fa-toggle-on"></i> Активировать',
        ['toggle', 'id' => $model->id],
        [
            'class' => $model->is_active ? 'btn btn-outline-warning ml-2' : 'btn btn-success ml-2',
            'data-method' => 'post',
        ]
    ) ?>
    <?php if ($model->getUsageCount() === 0): ?>
        <?= Html::a('<i class="fas fa-trash"></i> Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger ml-2',
            'data-method' => 'post',
            'data-confirm' => 'Удалить промокод?',
        ]) ?>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Основная информация -->
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация о промокоде</span>
                <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= $model->getStatusLabel() ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="text-center p-4 bg-light rounded">
                            <code class="h1"><?= Html::encode($model->code) ?></code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5><?= Html::encode($model->name) ?></h5>
                        <?php if ($model->description): ?>
                            <p class="text-muted"><?= Html::encode($model->description) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <table class="table table-bordered">
                    <tr>
                        <th width="200">Скидка</th>
                        <td>
                            <strong class="h4 text-primary"><?= $model->getFormattedDiscount() ?></strong>
                            <?php if ($model->max_discount): ?>
                                <br><small class="text-muted">Макс: <?= number_format($model->max_discount, 0, '', ' ') ?> KZT</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Применяется к</th>
                        <td><?= $model->getAppliesToLabel() ?></td>
                    </tr>
                    <?php if ($model->min_amount > 0): ?>
                    <tr>
                        <th>Мин. сумма заказа</th>
                        <td><?= number_format($model->min_amount, 0, '', ' ') ?> KZT</td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Период действия</th>
                        <td>
                            <?php if ($model->valid_from || $model->valid_until): ?>
                                <?= $model->valid_from ? Yii::$app->formatter->asDatetime($model->valid_from, 'php:d.m.Y H:i') : 'Начало не ограничено' ?>
                                —
                                <?= $model->valid_until ? Yii::$app->formatter->asDatetime($model->valid_until, 'php:d.m.Y H:i') : 'Бессрочно' ?>
                            <?php else: ?>
                                <span class="text-muted">Без ограничений</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Лимит использований</th>
                        <td>
                            <strong><?= $stats['total_usages'] ?></strong> / <?= $model->usage_limit ?: '∞' ?>
                            <?php if ($model->getRemainingUsage() !== null && $model->getRemainingUsage() <= 5): ?>
                                <span class="badge badge-warning">Осталось: <?= $model->getRemainingUsage() ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Лимит на организацию</th>
                        <td><?= $model->usage_per_org ?></td>
                    </tr>
                    <tr>
                        <th>Ограничения</th>
                        <td>
                            <?php if ($model->first_payment_only): ?>
                                <span class="badge badge-info">Только первый платёж</span>
                            <?php endif; ?>
                            <?php if ($model->new_customers_only): ?>
                                <span class="badge badge-info">Только новые клиенты</span>
                            <?php endif; ?>
                            <?php if (!$model->first_payment_only && !$model->new_customers_only): ?>
                                <span class="text-muted">Нет</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- История использования -->
        <div class="card card-custom">
            <div class="card-header">
                <i class="fas fa-history"></i> История использования
                <span class="badge badge-primary ml-2"><?= $stats['total_usages'] ?></span>
            </div>
            <div class="card-body p-0">
                <?= GridView::widget([
                    'dataProvider' => $usageDataProvider,
                    'layout' => "{items}\n{pager}",
                    'tableOptions' => ['class' => 'table table-hover mb-0'],
                    'emptyText' => '<p class="text-muted text-center py-4">Промокод ещё не использовался</p>',
                    'columns' => [
                        [
                            'attribute' => 'used_at',
                            'format' => ['datetime', 'php:d.m.Y H:i'],
                        ],
                        [
                            'attribute' => 'organization_id',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->organization) {
                                    return Html::a(Html::encode($model->organization->name), ['/superadmin/organization/view', 'id' => $model->organization_id]);
                                }
                                return 'Организация #' . $model->organization_id;
                            },
                        ],
                        [
                            'attribute' => 'payment_id',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->payment_id) {
                                    return Html::a('Платёж #' . $model->payment_id, ['/superadmin/payment/view', 'id' => $model->payment_id]);
                                }
                                return '-';
                            },
                        ],
                        [
                            'attribute' => 'discount_amount',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return '<strong class="text-success">' . number_format($model->discount_amount, 0, '', ' ') . ' KZT</strong>';
                            },
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Статистика -->
        <div class="card card-custom mb-4">
            <div class="card-header">Статистика</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h3 mb-0"><?= $stats['total_usages'] ?></div>
                        <small class="text-muted">Использований</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h3 mb-0"><?= $stats['unique_organizations'] ?></div>
                        <small class="text-muted">Организаций</small>
                    </div>
                    <div class="col-12">
                        <div class="h3 mb-0 text-success"><?= number_format($stats['total_discount'], 0, '', ' ') ?></div>
                        <small class="text-muted">Общая сумма скидок (KZT)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Метаданные -->
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
                    <?php if ($model->creator): ?>
                    <tr>
                        <td class="text-muted">Автор</td>
                        <td><?= Html::encode($model->creator->name) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Обновлён</td>
                        <td><?= Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
