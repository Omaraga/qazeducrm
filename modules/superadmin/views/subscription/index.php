<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Подписки';
$currentStatus = Yii::$app->request->get('status');
$expiring = Yii::$app->request->get('expiring');
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold">Подписки организаций</span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?></span>
        </div>
        <?= Html::a('<i class="fas fa-plus"></i> Создать подписку', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="btn-group mb-3">
            <?= Html::a('Все', ['index'], [
                'class' => 'btn btn-outline-secondary' . (empty($currentStatus) && !$expiring ? ' active' : '')
            ]) ?>
            <?= Html::a('Trial', ['index', 'status' => 'trial'], [
                'class' => 'btn btn-outline-warning' . ($currentStatus === 'trial' ? ' active' : '')
            ]) ?>
            <?= Html::a('Активные', ['index', 'status' => 'active'], [
                'class' => 'btn btn-outline-success' . ($currentStatus === 'active' ? ' active' : '')
            ]) ?>
            <?= Html::a('Истекшие', ['index', 'status' => 'expired'], [
                'class' => 'btn btn-outline-danger' . ($currentStatus === 'expired' ? ' active' : '')
            ]) ?>
            <?= Html::a('Приостановлены', ['index', 'status' => 'suspended'], [
                'class' => 'btn btn-outline-secondary' . ($currentStatus === 'suspended' ? ' active' : '')
            ]) ?>
            <?= Html::a('<i class="fas fa-clock"></i> Истекают скоро', ['index', 'expiring' => 1], [
                'class' => 'btn btn-outline-warning' . ($expiring ? ' active' : '')
            ]) ?>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'layout' => "{items}\n{pager}",
            'columns' => [
                'id',
                [
                    'attribute' => 'organization_id',
                    'label' => 'Организация',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->organization) {
                            return Html::a(Html::encode($model->organization->name), ['/superadmin/organization/view', 'id' => $model->organization_id]);
                        }
                        return '—';
                    },
                ],
                [
                    'attribute' => 'saas_plan_id',
                    'label' => 'План',
                    'value' => function ($model) {
                        return $model->saasPlan->name ?? '—';
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $badges = [
                            'trial' => 'badge-trial',
                            'active' => 'badge-active',
                            'expired' => 'badge-expired',
                            'suspended' => 'badge-suspended',
                            'cancelled' => 'badge-secondary',
                        ];
                        $class = $badges[$model->status] ?? 'badge-secondary';
                        return '<span class="badge ' . $class . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'attribute' => 'expires_at',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (!$model->expires_at) return '—';

                        $date = Yii::$app->formatter->asDate($model->expires_at, 'php:d.m.Y');
                        if ($model->isExpiringSoon()) {
                            return $date . ' <span class="badge badge-warning">Скоро</span>';
                        }
                        if ($model->isExpired()) {
                            return '<span class="text-danger">' . $date . '</span>';
                        }
                        return $date;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-secondary',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
