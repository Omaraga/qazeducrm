<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $model */
/** @var yii\widgets\ActiveForm $form */
$js = <<<JS
    $('#order_date_start_input').mask('99.99.9999');
    $('#order_date_end_input').mask('99.99.9999');
JS;
$this->registerJs($js);
?>

<div class="payment-search">

    <?php $form = ActiveForm::begin([
        'action' => \app\helpers\OrganizationUrl::to(['payment/index']),
        'method' => 'get',
    ]); ?>
    <div class="row my-3">
        <?= $form->field($model, 'date_start', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy'
            ],
            'options' => ['autocomplete' => 'off', 'id' => 'order_date_start_input']
        ]) ?>
        <?= $form->field($model, 'date_end', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy'
            ],
            'options' => ['autocomplete' => 'off', 'id' => 'order_date_end_input']
        ]) ?>
        <?= $form->field($model, 'type', ['options' =>['class' => 'col-12 col-sm-8']])->dropDownList(\app\models\Payment::getTypeList(), [
                'prompt' => Yii::t('main', 'Все')
        ]);?>
    </div>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('main', 'Поиск'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('main', 'Сбросить'), \app\helpers\OrganizationUrl::to(['payment/index']), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
