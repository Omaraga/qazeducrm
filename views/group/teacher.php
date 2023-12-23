<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Group $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->code.'-'.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link" id="nav-home-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/view', 'id' => $model->id]);?>" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('main', 'Основные данные');?></a>
            <a class="nav-item nav-link active" id="nav-profile-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/teachers', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="true"><?=Yii::t('main', 'Преподаватели');?></a>
            <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Ученики');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <h4><?= Html::encode('Преподаватели группы: '.$this->title) ?></h4>

            <p>
                <?= Html::button(Yii::t('main','Добавить преподавателя'),[
                    'value' => \app\helpers\OrganizationUrl::to(['group/create-teacher', 'group_id' => $model->id]),
                    'class' => 'btn btn-success modal-form',
                    'id' => 'modalButton'
                ]) ?>
            </p>


            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => '{summary}{items}',
                'columns' => [
                    'id',
                    [
                        'attribute' => 'related_id',
                        'value' => function($model){
                            return $model->teacher->fio;
                        }
                    ],

                    'typeLabel',
                    'price',
                    [
                        'format' => 'raw',
                        'value' => function($model){
                            return Html::a('<span>Удалить <i class="fa fa-trash" aria-hidden="true"></i></span>', \app\helpers\OrganizationUrl::to(['group/delete-teacher', 'id' => $model->id]), [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Вы действительно хотите удалить преподователя?',
                                    'method' => 'post',
                                ],
                            ]);
                        }
                    ]
                ],
            ]); ?>

            <?= \yii\bootstrap4\LinkPager::widget([
                'pagination' => $dataProvider->pagination
            ]) ?>
        </div>
    </div>
    <? \yii\bootstrap4\Modal::begin([
        'title' => Yii::t('main', 'Добавить преподавателя'),
        'id' => 'modal-form',
        'size' => 'modal-lg'

    ]); ?>
    <div id="modalContent"></div>
    <? \yii\bootstrap4\Modal::end();?>

</div>


