<?php

use app\models\TeacherRate;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\search\TeacherRateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ставки учителей';
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="teacher-rate-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> К зарплатам
            </a>
            <a href="<?= Url::to(['create-rate']) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить ставку
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
                        'label' => 'Область применения',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getScopeLabel();
                        },
                    ],
                    [
                        'attribute' => 'rate_type',
                        'label' => 'Тип',
                        'value' => function ($model) {
                            return $model->getRateTypeLabel();
                        },
                        'filter' => Html::activeDropDownList($searchModel, 'rate_type', TeacherRate::getRateTypeList(), ['class' => 'form-control', 'prompt' => 'Все']),
                    ],
                    [
                        'attribute' => 'rate_value',
                        'label' => 'Ставка',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-end fw-bold'],
                        'headerOptions' => ['class' => 'text-end'],
                        'value' => function ($model) {
                            return $model->getFormattedRate();
                        },
                    ],
                    [
                        'attribute' => 'is_active',
                        'label' => 'Активна',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                        'value' => function ($model) {
                            return $model->is_active
                                ? '<span class="badge bg-success">Да</span>'
                                : '<span class="badge bg-secondary">Нет</span>';
                        },
                        'filter' => Html::activeDropDownList($searchModel, 'is_active', [1 => 'Да', 0 => 'Нет'], ['class' => 'form-control', 'prompt' => 'Все']),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', ['update-rate', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'Редактировать',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fas fa-trash"></i>', ['delete-rate', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-danger ms-1',
                                    'title' => 'Удалить',
                                    'data' => [
                                        'confirm' => 'Удалить эту ставку?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Справка по ставкам</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6><i class="fas fa-user text-primary"></i> За ученика</h6>
                    <p class="text-muted small">
                        Фиксированная сумма за каждого ученика, посетившего урок (или с пропуском с оплатой).
                        <br><strong>Пример:</strong> 500 ₸ × 10 учеников = 5 000 ₸
                    </p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-chalkboard text-success"></i> За урок</h6>
                    <p class="text-muted small">
                        Фиксированная сумма за проведённый урок, независимо от количества учеников.
                        <br><strong>Пример:</strong> 3 000 ₸ за урок
                    </p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-percent text-warning"></i> Процент</h6>
                    <p class="text-muted small">
                        Процент от оплаты ученика. Рассчитывается на основе тарифа группы.
                        <br><strong>Пример:</strong> 30% от 10 000 ₸ = 3 000 ₸
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
