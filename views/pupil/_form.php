<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#birth_date_input').mask('99.99.9999');
    $('#pupil-phone').mask("+7(999)9999999");
    $('#pupil-home_phone').mask("+7(999)9999999");
JS;
$this->registerJs($js);
?>

<div class="">

    <?=$this->render('balance', [
        'model' => $model
    ]);?>

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
            <?= $form->field($model, 'birth_date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'birth_date_input']
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
