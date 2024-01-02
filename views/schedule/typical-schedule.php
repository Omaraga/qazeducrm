<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var \app\models\forms\TypicalLessonForm $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
function formatMonth(month){
    month++;
    if (month < 10){
        return '0' + month;
    }
    return month;
}
function formatDate(date){
     if (date < 10){
        return '0' + date;
    }
    return date;
}
$('#prevWeek').click(function (e){
    let start = moment($('#date_start_week').val(), 'DD.MM.YYYY');
    let end = moment($('#date_end_week').val(), 'DD.MM.YYYY');
    let startStr = start.subtract('days', 7).format('DD.MM.YYYY');
    let endStr = end.subtract('days', 7).format('DD.MM.YYYY');
    $('#date_start_week').val(startStr)
    $('#date_end_week').val(endStr);
    $('#week-period').text(startStr + ' - ' + endStr)
})
$('#nextWeek').click(function (e){
    let start = moment($('#date_start_week').val(), 'DD.MM.YYYY');
    let end = moment($('#date_end_week').val(), 'DD.MM.YYYY');
    let startStr = start.add('days', 7).format('DD.MM.YYYY');
    let endStr = end.add('days', 7).format('DD.MM.YYYY');
    $('#date_start_week').val(startStr)
    $('#date_end_week').val(endStr);
    $('#week-period').text(startStr + ' - ' + endStr)
})
$('.is_copy_checkbox').change(function (e){
    if ($(this).is(':checked')){
        $(this).closest('.week-block').find('.week_dropdown').removeAttr('disabled');
    }else{
        $(this).closest('.week-block').find('.week_dropdown').attr({disabled : true});
    }
    
});

JS;
$this->registerJs($js);
$this->title = Yii::t('main', 'Заполнение расписания на основе типового расписания');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Расписание'), 'url' => \app\helpers\OrganizationUrl::to(['schedule/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="typical-schedule-form">
    <h3 class="my-2"><?= Html::encode($this->title) ?></h3>
    <p>
        <?= Html::button(Yii::t('main','Предыдущая неделя'),[
            'class' => 'btn btn-info',
            'id' => 'prevWeek'
        ]) ?>
        <?= Html::button(Yii::t('main','Следующая неделя'),[
            'class' => 'btn btn-info',
            'id' => 'nextWeek'
        ]) ?>
    </p>
    <?php $form = ActiveForm::begin(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?=$form->field($model, 'date_start')->hiddenInput(['id' => 'date_start_week'])->label(false);?>
                <?=$form->field($model, 'date_end')->hiddenInput(['id' => 'date_end_week'])->label(false);?>
                <div class="col-12">
                    <p id="week-period" class="alert alert-warning" style="font-weight: bold;"><?=date('d.m.Y', strtotime($model->date_start));?> - <?=date('d.m.Y', strtotime($model->date_end));?></p>
                </div>

            </div>
            <div class="row">
                <div class="col-8">
                    <table class="table table-light">
                        <?foreach ($model->weeks as $k => $week):?>
                            <tr class="week-block">
                                <td>
                                    <?=$form->field($model, "weeks[$k][is_copy]")->checkbox([
                                        'label' => \app\helpers\Lists::getWeekDays()[$k],
                                        'class' => 'is_copy_checkbox'
                                    ])->label(false);?>
                                </td>
                                <td>
                                    <?=$form->field($model, "weeks[$k][week]")->dropDownList(\app\helpers\Lists::getWeekDays(),[
                                        'class' => 'week_dropdown form-control'
                                    ])->label(false);?>
                                </td>
                            </tr>
                        <?endforeach;?>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="form-group d-flex" style="justify-content: space-between;">
        <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-primary']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
