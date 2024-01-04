<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $model */
/** @var yii\widgets\ActiveForm $form */
$js = <<<JS
    $('#search-date-input').mask('99.99.9999');
    $('#search-date-input').change(function (e){
        $('#date-search-form').submit();
    });
JS;
$this->registerJs($js);
?>

<div class="payment-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'id' => 'date-search-form'
    ]); ?>
    <div class="row my-3">
        <?= $form->field($model, 'date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy'
            ],
            'options' => ['autocomplete' => 'off', 'id' => 'search-date-input']
        ]) ?>
        <div class="col-12">
            <span style="color: gray; font-size: 0.75em;">Отчет отображается за выбранный месяц. Дата не учитывается.</span>
        </div>

    </div>


    <div class="form-group d-none">
        <?= Html::submitButton(Yii::t('main', 'Поиск'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('main', 'Сбросить'), \app\helpers\OrganizationUrl::to(['payment/index']), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
