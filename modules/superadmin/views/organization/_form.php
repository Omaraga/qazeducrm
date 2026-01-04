<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $model */
/** @var yii\widgets\ActiveForm $form */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Organizations;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'legal_name')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'bin')->textInput(['maxlength' => 12]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'type' => 'email']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'status')->dropDownList(Organizations::getStatusList()) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'timezone')->dropDownList([
            'Asia/Almaty' => 'Asia/Almaty (UTC+5)',
            'Asia/Aqtobe' => 'Asia/Aqtobe (UTC+5)',
            'Asia/Atyrau' => 'Asia/Atyrau (UTC+5)',
            'Asia/Qostanay' => 'Asia/Qostanay (UTC+6)',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'locale')->dropDownList([
            'ru' => 'Русский',
            'kk' => 'Қазақша',
        ]) ?>
    </div>
</div>

<hr>

<div class="form-group">
    <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
