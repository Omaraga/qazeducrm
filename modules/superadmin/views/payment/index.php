<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Платежи';
$currentStatus = Yii::$app->request->get('status');
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold">Платежи организаций</span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?></span>
        </div>
        <?= Html::a('<i class="fas fa-plus"></i> Создать платёж', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="btn-group mb-3">
            <?= Html::a('Все', ['index'], [
                'class' => 'btn btn-outline-secondary' . (empty($currentStatus) ? ' active' : '')
            ]) ?>
            <?= Html::a('Ожидают', ['index', 'status' => 'pending'], [
                'class' => 'btn btn-outline-warning' . ($currentStatus === 'pending' ? ' active' : '')
            ]) ?>
            <?= Html::a('Оплачено', ['index', 'status' => 'completed'], [
                'class' => 'btn btn-outline-success' . ($currentStatus === 'completed' ? ' active' : '')
            ]) ?>
            <?= Html::a('Неудачные', ['index', 'status' => 'failed'], [
                'class' => 'btn btn-outline-danger' . ($currentStatus === 'failed' ? ' active' : '')
            ]) ?>
            <?= Html::a('Возвраты', ['index', 'status' => 'refunded'], [
                'class' => 'btn btn-outline-secondary' . ($currentStatus === 'refunded' ? ' active' : '')
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
                    'attribute' => 'amount',
                    'label' => 'Сумма',
                    'value' => function ($model) {
                        return number_format($model->amount, 0, '', ' ') . ' ' . $model->currency;
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $badges = [
                            'pending' => 'badge-warning',
                            'completed' => 'badge-success',
                            'failed' => 'badge-danger',
                            'refunded' => 'badge-secondary',
                        ];
                        $class = $badges[$model->status] ?? 'badge-secondary';
                        return '<span class="badge ' . $class . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'attribute' => 'period_start',
                    'label' => 'Период',
                    'value' => function ($model) {
                        if (!$model->period_start) return '—';
                        return Yii::$app->formatter->asDate($model->period_start) . ' — ' . Yii::$app->formatter->asDate($model->period_end);
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Создан',
                    'value' => function ($model) {
                        return Yii::$app->formatter->asDate($model->created_at);
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
