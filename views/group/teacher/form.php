<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var \app\models\relations\TeacherGroup $model */


if ($model->id){
    $this->title = 'Редактировать преподавателя: ' . $model->teacher->fio.'-'.$model->group->name;
}else{
    $this->title = 'Добавить преподавателя';
}
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['group/teachers', 'id' => $model->target_id])];


?>

<div class="group-form">

    <?php $form = ActiveForm::begin(['action' => \app\helpers\OrganizationUrl::to(['group/create-teacher', 'group_id' => $model->target_id]), 'method' => 'POST']); ?>

    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'related_id', ['options' =>['class' => 'col-12 col-sm-6']])->widget(\kartik\select2\Select2::classname(), [
                    'data' => \app\models\Organizations::getOrganizationTeachersMap(),
                    'options' => ['placeholder' => 'Выберите преподователя'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]); ?>
                <?= $form->field($model, 'type', ['options' =>['class' => 'col-12 col-sm-6']])->dropDownList(\app\models\relations\TeacherGroup::getPriceTypeList()) ?>
                <?= $form->field($model, 'price', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
            </div>
        </div>
    </div>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
