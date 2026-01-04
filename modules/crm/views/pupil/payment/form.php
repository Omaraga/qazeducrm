<?php

use app\helpers\Lists;
use app\models\forms\TeacherForm;
use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\datetime\DateTimePicker;
use yii\bootstrap4\ActiveForm;

/** @var yii\web\View $this */
/** @var \app\models\forms\PaymentForm $model */
/** @var \app\models\Pupil $pupil */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#order_date_input').mask('99.99.9999 99:99');
JS;
$this->registerJs($js);
if ($model->type == \app\models\Payment::TYPE_PAY){
    $this->title = 'Оплату ученику '.$pupil->fio;
}else{
    $this->title = 'Возврат ученику '.$pupil->fio;
}

$this->params['breadcrumbs'][] = ['label' => 'Оплата', 'url' => \app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="payment-form">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php $form = ActiveForm::begin(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <?if($model->type == \app\models\Payment::TYPE_PAY):?>
                <?=Yii::t('main', 'Оплата');?>
            <?else:?>
                <?=Yii::t('main', 'Возврат');?>
            <?endif;?>
        </div>
        <div class="card-body">
            <?if($model->type == \app\models\Payment::TYPE_PAY):?>
                <div class="row">
                    <?= $form->field($model, 'purpose_id', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\Payment::getPurposeList()) ?>
                    <?= $form->field($model, 'method_id', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\PayMethod::find()->byOrganization()->all(), 'id', 'name')) ?>
                </div>
            <?endif;?>
            <div class="row">
                <?if($model->type == \app\models\Payment::TYPE_PAY):?>
                    <?= $form->field($model, 'number', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?endif;?>
                <?= $form->field($model, 'amount', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>

                <?=$form->field($model, 'date', ['options' =>['class' => 'col-12 col-sm-4']])->widget(DateTimePicker::classname(), [
                    'options' => ['autocomplete' => 'off', 'id' => 'order_date_input'],
                    'type' => \kartik\datetime\DateTimePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy hh:ii'
                    ]
                ]);?>
                <?= $form->field($model, 'comment', ['options' => ['class' => 'col-12']])->textarea();?>
            </div>


        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
