<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

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
            <a class="nav-item nav-link active" id="nav-profile-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/edu', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="false"><?=Yii::t('main', 'Обучение');?></a>
            <a class="nav-item nav-link" id="nav-contact-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $model->id]);?>" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Оплата');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <p class="my-2">
                <?= Html::a(Yii::t('main', 'Добавить обучение'), \app\helpers\OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]), ['class' => 'btn btn-success']) ?>
            </p>
            <div class="row">
                <div id="divTuitionList" class="col-12">
                    <?foreach ($model->educations as $education):?>
                        <?=$this->render('_card', [
                            'model' => $education,
                        ]);?>
                    <?endforeach;?>
                </div>
            </div>
        </div>
    </div>



</div>
