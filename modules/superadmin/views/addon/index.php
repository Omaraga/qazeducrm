<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $organizations */
/** @var array $features */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Аддоны организаций';
$currentStatus = Yii::$app->request->get('status');
$expiring = Yii::$app->request->get('expiring');
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold">Аддоны организаций</span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?></span>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-chart-bar"></i> Статистика', ['stats'], ['class' => 'btn btn-outline-info mr-2']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Добавить аддон', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
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
            <?= Html::a('Отменённые', ['index', 'status' => 'cancelled'], [
                'class' => 'btn btn-outline-secondary' . ($currentStatus === 'cancelled' ? ' active' : '')
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
                        return '-';
                    },
                ],
                [
                    'attribute' => 'feature_id',
                    'label' => 'Аддон',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $name = $model->feature->name ?? 'ID ' . $model->feature_id;
                        if ($model->quantity > 1) {
                            $name .= ' <span class="badge badge-secondary">x' . $model->quantity . '</span>';
                        }
                        return $name;
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $badges = [
                            'trial' => 'badge-warning',
                            'active' => 'badge-success',
                            'expired' => 'badge-danger',
                            'cancelled' => 'badge-secondary',
                        ];
                        $class = $badges[$model->status] ?? 'badge-secondary';
                        return '<span class="badge ' . $class . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'attribute' => 'price',
                    'label' => 'Цена',
                    'value' => function ($model) {
                        return $model->getFormattedPrice();
                    },
                ],
                [
                    'attribute' => 'expires_at',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (!$model->expires_at) return '-';

                        $date = Yii::$app->formatter->asDate($model->expires_at, 'php:d.m.Y');
                        $days = $model->getDaysRemaining();

                        if ($model->isExpiringSoon()) {
                            return $date . ' <span class="badge badge-warning">' . $days . ' дн.</span>';
                        }
                        if ($model->isExpired()) {
                            return '<span class="text-danger">' . $date . '</span>';
                        }
                        return $date;
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'title' => 'Просмотр',
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Редактировать',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
