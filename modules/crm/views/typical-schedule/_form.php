<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\TypicalSchedule $model */
/** @var yii\widgets\ActiveForm $form */

$url = \app\helpers\OrganizationUrl::to(['typical-schedule/teachers']);
$js = <<<JS
    that = $('.modal-content');
    $(that).find('#select_group_id').change(function (){
        let id = $(this).find('option:selected').val();
        $.ajax({
            url : '$url',
            type: 'post',
            data: {id : id},
            success: function (data){
                $('#typicalschedule-teacher_id').find('option').each(function (e){
                    $(this).remove();
                });
                data = JSON.parse(data);
                console.log('data ', data)
                $(data).each(function (){
                   let html = '<option value="' + this.id + '">' + this.fio + '</option>';
                    $('#typicalschedule-teacher_id').append(html);
                    $('#typicalschedule-teacher_id').removeAttr('disabled');
                });
            }
        })
    });

$('#typicalschedule-start_time').mask('99:99');
$('#typicalschedule-end_time').mask('99:99');
JS;
$this->registerJs($js);
?>

<div class="typical-schedule-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Занятие');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'group_id', ['options' =>['class' => 'col-12']])->widget(\kartik\select2\Select2::classname(), [
                    'data' => ArrayHelper::map(\app\models\Group::find()->byOrganization()->all(), 'id', 'nameFull'),
                    'options' => [
                        'placeholder' => 'Выберите группу',
                        'id' => 'select_group_id'
                    ],
                    'pluginOptions' => [
                        'allowClear' => false
                    ],
                ]) ?>
                <?= $form->field($model, 'teacher_id', ['options' =>['class' => 'col-12']])->dropDownList(\app\models\Organizations::getOrganizationTeachersMap(), [
                    'disabled' => true,
                    'prompt' => Yii::t('main', 'Выберите преподавателя'),
                ]) ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'week', ['options' =>['class' => 'col-6 col-sm-4']])->dropDownList(\app\helpers\Lists::getWeekDays(), []) ?>
                <?= $form->field($model, 'start_time', ['options' =>['class' => 'col-6 col-sm-4']])->widget(\kartik\time\TimePicker::classname(), [
                    'pluginOptions' => [
                        'showMeridian' => false
                    ]
                ]) ?>
                <?= $form->field($model, 'end_time', ['options' =>['class' => 'col-6 col-sm-4']])->widget(\kartik\time\TimePicker::classname(), [
                    'pluginOptions' => [
                        'showMeridian' => false
                    ]
                ]) ?>
            </div>



        </div>
    </div>

    <div class="form-group d-flex" style="justify-content: space-between;">
        <?if($model->id):?>
            <?= Html::a(Yii::t('main', 'Удалить занятие'), \app\helpers\OrganizationUrl::to(['typical-schedule/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('main', 'Вы действительно хотите удалить занятие?'),
                    'method' => 'post',
                ],
            ]) ?>
        <?endif;?>
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-primary']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
