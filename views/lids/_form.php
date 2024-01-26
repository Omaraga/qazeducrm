<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\Lids $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#lid_date_input').mask('99.99.9999');
    $('#pupil-phone').mask("+7(999)9999999");
    $('#pupil-home_phone').mask("+7(999)9999999");
JS;
$this->registerJs($js);
?>

<div class="">
    <?php $form = ActiveForm::begin(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'fio', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'lid_date_input']
            ]) ?>
            <?= $form->field($model, 'phone', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            <?= $form->field($model, 'sale', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
            <?= $form->field($model, 'total_sum', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
            <?= $form->field($model, 'total_point', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основное место обучения');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'school',['options' =>['class' => 'col-6']])->textInput() ?>
            <?= $form->field($model, 'class_id',['options' =>['class' => 'col-6']])->dropDownList(\app\helpers\Lists::getGrades()) ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Сведения о родителях');?>
        </div>
        <div class="card-body row">

        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
