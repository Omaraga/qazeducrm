<?php

use app\models\Group;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\GroupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Группы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('main','Создать группу'), \app\helpers\OrganizationUrl::to(['create']), ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}{items}',
        'columns' => [
            [
                'attribute' => 'color',
                'value' => function($model){
                    return '<div style="height: 30px; width: 50px; background:'.$model->color.'"></div>';
                },
                'format' => 'raw',
                'filter' => false
            ],
            [
                'attribute' => 'subject_id',
                'value' => function($model){
                    return $model->subject->name;
                },
                'filter' => Html::activeDropDownList($searchModel, 'subject_id', \yii\helpers\ArrayHelper::map(\app\models\Subject::find()->all(), 'id', 'name'),['class'=>'form-control', 'prompt' => 'Выберите предмет']),
            ],
            'code',
            'name',
            [
                'attribute' => 'category_id',
                'value' => function($model){
                    return \app\helpers\Lists::getGroupCategories()[$model->category_id];
                },
                'filter' => Html::activeDropDownList($searchModel, 'category_id', \app\helpers\Lists::getGroupCategories(),['class'=>'form-control','prompt' => 'Выберите категорию']),
            ],
            [
                'format' => 'raw',
                'value' => function($model){
                    return \yii\bootstrap4\Html::a('Посмотреть', \app\helpers\OrganizationUrl::to(['group/view', 'id' => $model->id]), ['class' => 'btn btn-secondary']);
                }
            ]
        ],
    ]); ?>

    <?= \yii\bootstrap4\LinkPager::widget([
        'pagination' => $dataProvider->pagination
    ]) ?>


</div>
