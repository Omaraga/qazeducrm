<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $model */
/** @var app\models\Organizations $parent */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Organizations;

$this->title = 'Новый филиал';
?>

<div class="mb-3">
    <a href="<?= Url::to(['view', 'id' => $parent->id]) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К организации
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Создание филиала для: <strong><?= Html::encode($parent->name) ?></strong>
</div>

<div class="card card-custom">
    <div class="card-header">
        Создание филиала
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'parent_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Название филиала']) ?>
            </div>
            <div class="col-md-6">
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

        <hr>

        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Создать филиал', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена', ['view', 'id' => $parent->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
