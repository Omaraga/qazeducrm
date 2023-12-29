<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$this->title = $model->code.'-'.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="group-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-home-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/view', 'id' => $model->id]);?>" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('main', 'Основные данные');?></a>
            <a class="nav-item nav-link" id="nav-profile-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/teachers', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="false"><?=Yii::t('main', 'Преподаватели');?></a>
            <a class="nav-item nav-link" id="nav-contact-tab" href="<?=\app\helpers\OrganizationUrl::to(['group/pupils', 'id' => $model->id]);?>" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Ученики');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <p class="my-2">
                <?= Html::a('<span>Редактировать <i class="fa fa-pencil" aria-hidden="true"></i></span>', \app\helpers\OrganizationUrl::to(['update', 'id' => $model->id]), ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<span>Удалить <i class="fa fa-trash" aria-hidden="true"></i></span>', \app\helpers\OrganizationUrl::to(['delete', 'id' => $model->id]), [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы действительно хотите удалить ученика?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'subjectLabel',
                    'code',
                    'name',
                    'categoryLabel',
                    'type',
                    [
                        'attribute' => 'color',
                        'value' => function($model){
                            return '<div style="height: 30px; width: 50px; background:'.$model->color.'"></div>';
                        },
                        'format' => 'raw',
                    ],
                    'statusLabel'
                ],
            ]) ?>
        </div>
    </div>




</div>
