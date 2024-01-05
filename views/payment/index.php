<?php

use app\models\Payment;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Бухгалтерия');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'layout' => '{summary}{items}',
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'dd.MM.yyyy',
            'datetimeFormat' => 'php: d.m.Y H:i',
            'locale' => 'ru'
        ],
        'showFooter'=>true,

        'columns' => [
            [
                'attribute' => 'date',
                'format' => 'datetime',
                'footer' => '<span style="font-weight: bold;">'.Yii::t('main', 'Итого').'</span>',
            ],
            [
                'attribute' => 'type',
                'value' => function($data){
                    return $data->typeLabel;
                }
            ],
            [
                'attribute' => 'amount',
                'value' => function($model){
                    if ($model->type == Payment::TYPE_PAY){
                        return number_format($model->amount, 0,'.', ' ').' тг.';
                    }
                    return '-'.number_format($model->amount, 0,'.', ' ').' тг.';
                },
                'footer' => '<span style="font-weight: bold;">'.number_format($searchModel->sum, 0,'.', ' ').' тг.</span>',
            ],
            [
                'attribute' => 'purpose_id',
                'value' => function($model){
                    if ($model->type == Payment::TYPE_PAY){
                        return $model->purposeLabel;
                    }
                    return '';
                }
            ],
            [
                'attribute' => 'method_id',
                'value' => function($model){
                    if ($model->type == Payment::TYPE_PAY){
                        return $model->method->name;
                    }
                    return '';

                }
            ],
            [
                'attribute' => 'pupil_id',
                'value' => function($data){
                    if ($data->pupil_id){
                        return $data->pupil->fio;
                    }
                    return '';

                }
            ],
            [
                'attribute' => 'number',
                'value' => function($model){
                    return $model->number? :'';
                }
            ],
            'comment',
            [
                'format' => 'raw',
                'value' => function($model){
                    if ($model->pupil_id){
                        return \yii\bootstrap4\Html::a('Посмотреть', \app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]), ['class' => 'btn btn-secondary']);
                    }
                    return '';
                }
            ]
        ],
    ]); ?>
    <table class="table table-striped table-bordered">
        <tbody>
        <tr data-key="5">
            <td><?=Yii::t('main', 'Итого');?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>


</div>
