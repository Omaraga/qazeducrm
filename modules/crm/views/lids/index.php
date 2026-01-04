<?php

use app\models\Pupil;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\PupilSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Пробное тестирование';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pupil-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить пробное тестирование', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<!--    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}{items}',
        'columns' => [
            'id',
            'fio',
            'date',
            [
                'attribute' => 'class_id',
                'value' => function($model){
                    return \app\helpers\Lists::getGrades()[$model->class_id];
                }
            ],
            'phone',
            'total_point',
            'sale',
            'total_sum',

            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, PayMethod $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],

        ],
    ]); ?>
    <?= \yii\bootstrap4\LinkPager::widget([
        'pagination' => $dataProvider->pagination
    ]) ?>


</div>
