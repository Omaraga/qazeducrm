<?php

use app\helpers\Lists;
use app\models\forms\TeacherForm;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\bootstrap4\ActiveForm;

/** @var yii\web\View $this */
/** @var TeacherForm $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#birth_date_input').mask('99.99.9999');
    $('#teacherform-phone').mask("+7(999)9999999");
    $('#teacherform-home_phone').mask("+7(999)9999999");
JS;
$this->registerJs($js);
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'username', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'iin', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'first_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'last_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'middle_name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'sex', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(Lists::getGenders()) ?>
                <?= $form->field($model, 'birth_date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.m.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'birth_date_input']
                ]) ?>
            </div>


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
            <?=Yii::t('main', 'Системные сведения');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'status',  ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\User::getStatusList()) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
