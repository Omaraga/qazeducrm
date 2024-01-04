<?php

use app\models\Tariff;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Тарифы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariff-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?if(Yii::$app->user->can(\app\helpers\OrganizationRoles::GENERAL_DIRECTOR) || Yii::$app->user->can(\app\helpers\SystemRoles::SUPER)):?>
        <p>
            <?= Html::a('Создать тариф', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    <?endif;?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            [
                'attribute' => 'duration',
                'value' => 'durationLabel',
            ],
            [
                'attribute' => 'price',
                'value' => function($model){
                    return number_format($model->price, 0, '.', ' ');
                }
            ],
            'subjectsLabel',
            'description:ntext',
            [
                'attribute' => 'type',
                'value' => 'typeLabel',
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Tariff $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 },
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function($name, $model, $key){
                        return Html::a('<i class="fa fa-eye"></i>', \app\helpers\OrganizationUrl::to(['tariff/view', 'id' => $model->id]));
                    },
                    'update' => function($name, $model, $key){

                        return Html::a('<i class="fa fa-edit"></i>', \app\helpers\OrganizationUrl::to(['tariff/update', 'id' => $model->id]));
                    },
                    'delete' => function($name, $model, $key){
                        if(Yii::$app->user->can(\app\helpers\OrganizationRoles::GENERAL_DIRECTOR)){
                            return Html::a('<i class="fa fa-trash"></i>', \app\helpers\OrganizationUrl::to(['tariff/delete', 'id' => $model->id]), [
                                'data' => [
                                    'confirm' => 'Вы действительно хотите удалить?',
                                    'method' => 'post',
                                ],
                            ]);
                        }else{
                            return '';
                        }

                    }
                ]
            ],
        ],
    ]); ?>


</div>
