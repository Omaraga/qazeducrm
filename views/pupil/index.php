<?php

use app\models\Pupil;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\PupilSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ученики';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pupil-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить ученика', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<!--    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'iin',
            'fio',
            [
                'attribute' => 'class_id',
                'value' => function($model){
                    return \app\helpers\Lists::getGrades()[$model->class_id];
                }
            ],
            [
                'attribute' => 'contacts',
                'value' => function($model){
                    $phone = '';
                    if ($model->phone){
                        $phone .= $model->phone.'<br>';
                    }
                    if ($model->home_phone){
                        $phone .= $model->home_phone;
                    }
                    return $phone;
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'parent_contacts',
                'value' => function($model){
                    return $model->parent_fio.'<br>'.$model->parent_phone;
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'balance',
                'value' => function($model){
                    if ($model->balance > 0){
                        return '<span style="font-weight: bold; color: darkblue;">'.number_format($model->balance, 0, '.', ' ').'</span>';
                    }else{
                        return '<span style="font-weight: bold; color: red;">'.number_format($model->balance, 0, '.', ' ').'</span>';
                    }

                },
                'format' => 'raw'
            ],
            [
                'format' => 'raw',
                'value' => function($model){
                    return \yii\bootstrap4\Html::a('Посмотреть', \app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $model->id]), ['class' => 'btn btn-secondary']);
                }
            ]

        ],
    ]); ?>


</div>
