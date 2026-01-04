<?php

use app\models\TeacherSalary;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;

/** @var yii\web\View $this */
/** @var app\models\search\TeacherSalarySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Зарплаты учителей';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="teacher-salary-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <a href="<?= Url::to(['rates']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Ставки
            </a>
            <a href="<?= Url::to(['calculate']) ?>" class="btn btn-primary">
                <i class="fas fa-calculator"></i> Рассчитать
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    [
                        'attribute' => 'teacher_id',
                        'label' => 'Преподаватель',
                        'value' => function ($model) {
                            return $model->teacher ? $model->teacher->fio : 'Не указан';
                        },
                        'filter' => Html::activeTextInput($searchModel, 'teacher_name', ['class' => 'form-control', 'placeholder' => 'Поиск...']),
                    ],
                    [
                        'attribute' => 'period_start',
                        'label' => 'Период',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getPeriodLabel();
                        },
                    ],
                    [
                        'attribute' => 'lessons_count',
                        'label' => 'Уроков',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'students_count',
                        'label' => 'Учеников',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'total_amount',
                        'label' => 'Сумма',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-end fw-bold'],
                        'headerOptions' => ['class' => 'text-end'],
                        'value' => function ($model) {
                            return $model->getFormattedTotal();
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'Статус',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                        'value' => function ($model) {
                            return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                        },
                        'filter' => Html::activeDropDownList($searchModel, 'status', TeacherSalary::getStatusList(), ['class' => 'form-control', 'prompt' => 'Все']),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
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
</div>
