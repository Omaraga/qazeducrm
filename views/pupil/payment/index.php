<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Payment;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Ученики', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pupil-view">

    <h1><?= Html::encode($this->title) ?></h1>



    <?=$this->render('../balance', [
            'model' => $model
    ]);?>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link" id="nav-home-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $model->id]);?>" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('main', 'Основные данные');?></a>
            <a class="nav-item nav-link" id="nav-profile-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/edu', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="false"><?=Yii::t('main', 'Обучение');?></a>
            <a class="nav-item nav-link active" id="nav-contact-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $model->id]);?>" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Оплата');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <p class="my-2">
                <?= Html::a(Yii::t('main', 'Добавить оплату'), \app\helpers\OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_PAY]), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('main', 'Добавить возврат'), \app\helpers\OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_REFUND]), ['class' => 'btn btn-warning']) ?>
            </p>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'date',
                            'value' => function($model){
                                return date('d.m.Y H:i', strtotime($model->date));
                            }
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return Html::a($model->typeLabel, \app\helpers\OrganizationUrl::to(['pupil/create-payment', 'id' => $model->id, 'pupil_id' => $model->pupil_id, 'type' => $model->type]));
                            },
                            'format' => 'raw'
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
                            'attribute' => 'amount',
                            'value' => function($model){
                                if ($model->type == Payment::TYPE_PAY){
                                    return number_format($model->amount, 0,'.', ' ').' тг.';
                                }
                                return '-'.number_format($model->amount, 0,'.', ' ').' тг.';
                            }
                        ],
                        [
                            'attribute' => 'number',
                            'value' => function($model){
                                return $model->number? :'';
                            }
                        ],
                        'comment',

                    ],
                ]); ?>
        </div>
        <?= \yii\bootstrap4\LinkPager::widget([
            'pagination' => $dataProvider->pagination
        ]) ?>
    </div>



</div>
