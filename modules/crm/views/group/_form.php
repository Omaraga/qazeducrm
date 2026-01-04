<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Group $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#group-subject_id').change(function (e){
        let name = $(this).find('option:selected').text();
        $('#group-name').val(name);
    });
JS;
$this->registerJs($js);
?>

<div class="group-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'subject_id', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\Subject::find()->all(), 'id', 'name')) ?>
                <?= $form->field($model, 'code', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'category_id', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getGroupCategories()) ?>
                <?= $form->field($model, 'type', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\Group::getTypeList()) ?>
            </div>
            <div class="row">
                <?=$form->field($model, 'color', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\color\ColorInput::classname(), [
                    'options' => [],
                ]);?>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Системные сведения');?>
        </div>
        <div class="card-body row">
            <?= $form->field($model, 'status',  ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\enum\StatusEnum::getStatusList()) ?>
        </div>
    </div>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
