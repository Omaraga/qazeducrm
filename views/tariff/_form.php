<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#tariff-duration').change(function (e){
        let val = $(this).find('option:selected').val();
        if (parseInt(val) === 3){
            $('#lesson_amount_block').removeClass('d-none');
        }else{
            $('#lesson_amount_block').addClass('d-none');
        }
    });
JS;
$this->registerJs($js);
?>

<div class="tariff-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?= $form->field($model, 'name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
        <?= $form->field($model, 'duration', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getTariffDurations()) ?>
        <?= $form->field($model, 'lesson_amount', ['options' =>['class' => 'col-12 col-sm-4 d-none', 'id' => 'lesson_amount_block']])->textInput(['type' => 'number']) ?>
    </div>
    <div class="row">
        <?= $form->field($model, 'type', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getTariffTypes()) ?>
        <?= $form->field($model, 'price', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
        <?= $form->field($model, 'status', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\Tariff::getStatusList(), []) ?>
    </div>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton( Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
