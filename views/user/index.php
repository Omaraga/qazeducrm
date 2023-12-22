<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main','Преподаватели');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('main','Добавить преподавателя'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'username',
            'fio',
            [
                'attribute' => 'contacts',
                'value' => function($model){
                    $phone = '';
                    if ($model->phone){
                        $phone .= $model->phone.'<br>';
                    }
                    if ($model->home_phone){
                        $phone .= $model->home_phone.'<br>';
                    }
                    if ($model->email){
                        $phone .= $model->email;
                    }
                    return $phone;
                },
                'format' => 'raw'
            ],
            'statusLabel',
            [
                'format' => 'raw',
                'value' => function($model){
                    return \yii\bootstrap4\Html::a('Посмотреть', \app\helpers\OrganizationUrl::to(['user/view', 'id' => $model->id]), ['class' => 'btn btn-secondary']);
                }
            ]
        ],
    ]); ?>


</div>
