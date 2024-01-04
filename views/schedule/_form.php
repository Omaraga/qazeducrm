<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Lesson $model */
/** @var yii\widgets\ActiveForm $form */

$url = \app\helpers\OrganizationUrl::to(['schedule/teachers']);
$js = <<<JS
    that = $('.modal-content');
    $(that).find('#select_group_id').change(function (){
        let id = $(this).find('option:selected').val();
        $.ajax({
            url : '$url',
            type: 'post',
            data: {id : id},
            success: function (data){
                $('#lesson-teacher_id').find('option').each(function (e){
                    $(this).remove();
                });
                data = JSON.parse(data);
                console.log('data ', data)
                $(data).each(function (){
                   let html = '<option value="' + this.id + '">' + this.fio + '</option>';
                    $('#lesson-teacher_id').append(html);
                    $('#lesson-teacher_id').removeAttr('disabled');
                });
            }
        })
    });

$('#lesson-start_time').mask('99:99');
$('#lesson-end_time').mask('99:99');
$('#schedule_date_input').mask('99.99.9999');
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
                <?= $form->field($model, 'date', ['options' =>['class' => 'col-6 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => \kartik\date\DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'schedule_date_input']
                ]) ?>
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

    <?if($model->id):?>
        <div class="card border-info mb-3 mt-3" style="width: 100%;">
            <div class="card-header">
                <b>Посещаемость</b>
                <a href="<?=\app\helpers\OrganizationUrl::to(['attendance/lesson', 'id' => $model->id]);?>" class="badge badge-light" style="cursor: pointer;">Редактировать посещения</a>
            </div>
            <div class="card-body" style="padding: 0 1.25rem;">
                <table>
                    <tbody>
                    <?foreach ($model->pupils as $pupil):?>
                        <tr class="py-2">
                            <td style="padding-right: 25px;" class="py-2">
                                <span class="npp">1</span><a href="<?=\app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]);?>"><?=$pupil->fio;?> </a>
                            </td>
                            <td>
                                <?
                                $attendance = \app\models\LessonAttendance::find()->where(['pupil_id' => $pupil->id, 'lesson_id' => $model->id])->notDeleted()->one();
                                ?>
                                <?if($attendance):?>
                                    <?if ($attendance->status == \app\models\LessonAttendance::STATUS_VISIT):?>
                                        <span style="color: green; font-weight: bold;"><?=$attendance->getStatusLabel();?></span>
                                    <?else:?>
                                        <span style="color: red; font-weight: bold;"><?=$attendance->getStatusLabel();?></span>
                                    <?endif;?>
                                <?else:?>
                                    <span style="color: black; font-weight: bold;"><?=Yii::t('main', 'Не задано');?></span>
                                <?endif;?>

                            </td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    <?endif;?>
    <div class="form-group d-flex" style="justify-content: space-between;">
        <?if($model->id):?>
            <?= Html::a(Yii::t('main', 'Удалить занятие'), \app\helpers\OrganizationUrl::to(['schedule/delete', 'id' => $model->id]), [
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
