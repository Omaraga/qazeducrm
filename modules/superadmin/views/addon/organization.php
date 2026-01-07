<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $organization */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Аддоны: ' . $organization->name;
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $organization->name;
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold"><?= Html::encode($organization->name) ?></span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?> аддонов</span>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-building"></i> Организация',
                ['/superadmin/organization/view', 'id' => $organization->id],
                ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Добавить аддон',
                ['create', 'organization_id' => $organization->id],
                ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'layout' => "{items}\n{pager}",
            'columns' => [
                'id',
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
                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' .
                            $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'attribute' => 'price',
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
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary',
                            ]);
                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
