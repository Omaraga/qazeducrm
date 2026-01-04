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

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-home-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $model->id]);?>" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('main', 'Основные данные');?></a>
            <a class="nav-item nav-link" id="nav-profile-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/edu', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="false"><?=Yii::t('main', 'Обучение');?></a>
            <a class="nav-item nav-link" id="nav-contact-tab" href="<?=\app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $model->id]);?>" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Оплата');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <p class="my-2">
                <?= Html::a('<span>Редактировать <i class="fa fa-pencil" aria-hidden="true"></i></span>', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<span>Удалить <i class="fa fa-trash" aria-hidden="true"></i></span>', ['delete', 'id' => $model->id], [
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
                    'iin',
                    'email:email',
                    'phone',
                    'home_phone',
                    'address',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'parent_fio',
                    'parent_phone',
                    'genderLabel',
                    'birth_date',
                    'school_name',
                    'class_id',
                ],
            ]) ?>
        </div>
    </div>



</div>
