<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $pendingCount */

use app\models\OrganizationSubscriptionRequest;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Запросы на подписку';
$this->params['breadcrumbs'][] = ['label' => 'Подписки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <?= Html::encode($this->title) ?>
        <?php if ($pendingCount > 0): ?>
            <span class="badge badge-warning"><?= $pendingCount ?> ожидают</span>
        <?php endif; ?>
    </h1>
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К подпискам
    </a>
</div>

<!-- Фильтры -->
<div class="card card-custom mb-4">
    <div class="card-body">
        <div class="btn-group">
            <a href="<?= Url::to(['requests']) ?>" class="btn btn-<?= !Yii::$app->request->get('status') ? 'primary' : 'outline-primary' ?>">
                Все
            </a>
            <a href="<?= Url::to(['requests', 'status' => 'pending']) ?>" class="btn btn-<?= Yii::$app->request->get('status') === 'pending' ? 'warning' : 'outline-warning' ?>">
                Ожидают
            </a>
            <a href="<?= Url::to(['requests', 'status' => 'approved']) ?>" class="btn btn-<?= Yii::$app->request->get('status') === 'approved' ? 'info' : 'outline-info' ?>">
                Одобрены
            </a>
            <a href="<?= Url::to(['requests', 'status' => 'completed']) ?>" class="btn btn-<?= Yii::$app->request->get('status') === 'completed' ? 'success' : 'outline-success' ?>">
                Выполнены
            </a>
            <a href="<?= Url::to(['requests', 'status' => 'rejected']) ?>" class="btn btn-<?= Yii::$app->request->get('status') === 'rejected' ? 'danger' : 'outline-danger' ?>">
                Отклонены
            </a>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['style' => 'width: 60px'],
                ],
                [
                    'attribute' => 'organization_id',
                    'label' => 'Организация',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->organization
                            ? Html::a(Html::encode($model->organization->name), ['/superadmin/organization/view', 'id' => $model->organization_id])
                            : '-';
                    },
                ],
                [
                    'attribute' => 'request_type',
                    'label' => 'Тип',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $icons = [
                            'renewal' => '<i class="fas fa-sync text-primary"></i>',
                            'upgrade' => '<i class="fas fa-arrow-up text-success"></i>',
                            'downgrade' => '<i class="fas fa-arrow-down text-warning"></i>',
                            'trial_convert' => '<i class="fas fa-exchange-alt text-info"></i>',
                            'addon' => '<i class="fas fa-puzzle-piece text-purple"></i>',
                        ];
                        return ($icons[$model->request_type] ?? '') . ' ' . $model->getTypeLabel();
                    },
                ],
                [
                    'label' => 'План',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $current = $model->currentPlan ? $model->currentPlan->name : '-';
                        $requested = $model->requestedPlan ? $model->requestedPlan->name : '-';

                        if ($model->request_type === 'upgrade' || $model->request_type === 'downgrade') {
                            return "{$current} → <strong>{$requested}</strong>";
                        }
                        return $requested;
                    },
                ],
                [
                    'attribute' => 'billing_period',
                    'label' => 'Период',
                    'value' => function ($model) {
                        return $model->billing_period === 'yearly' ? 'Годовой' : 'Месячный';
                    },
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Статус',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Дата',
                    'format' => ['datetime', 'php:d.m.Y H:i'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', ['view-request', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Просмотр',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
