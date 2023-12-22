<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="">

    <div class="row my-3">
        <div class="input-group col-3">
            <div class="input-group-text" id="btnGroupAddon2" style="background: lightgreen;"><b>На счету ученика:</b></div>
            <input type="text" disabled class="form-control" style="background: <?=$model->balance > 0 ? 'lightblue' : 'pink';?>"  aria-describedby="btnGroupAddon2" value="<?=$model->balance ? : 0;?> тг.">
        </div>
    </div>

    <?php $form = ActiveForm::begin(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'iin', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'first_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'last_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'middle_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'sex', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getGenders()) ?>
            <?= $form->field($model, 'birth_date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\yii\jui\DatePicker::className(), [
                'options' => ['class' => 'form-control'],
            ]) ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Контактные данные');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'email',  ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'phone', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'home_phone',['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'address',['options' =>['class' => 'col-12']])->textInput() ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основное место обучения');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'school_name',['options' =>['class' => 'col-6']])->textInput() ?>
            <?= $form->field($model, 'class_id',['options' =>['class' => 'col-6']])->dropDownList(\app\helpers\Lists::getGrades()) ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Сведения о родителях');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'parent_fio',['options' =>['class' => 'col-6']])->textInput() ?>
            <?= $form->field($model, 'parent_phone',['options' =>['class' => 'col-6']])->textInput() ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
