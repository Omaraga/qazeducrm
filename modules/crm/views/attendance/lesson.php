<?php
/** @var yii\web\View $this */
/** @var \app\models\forms\AttendancesForm $model */
/** @var \app\models\Lesson $lesson */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
$this->title = Yii::t('main', 'Посещаемость');
?>
<h1><?=$this->title;?></h1>
<div class="card mb-3">
    <div class="card-header">
        Урок
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <p><span class="font-weight-bold">Группа: </span><?=$lesson->group->getNameFull();?></p>
                <p><span class="font-weight-bold">Дата: </span><?=$lesson->getDateTime();?></p>
                <p><span class="font-weight-bold">Преподаватель: </span><?=$lesson->teacher->fio;?></p>
                <p><span class="font-weight-bold">Предмет: </span><?=$lesson->group->subject->name;?></p>
            </div>
        </div>
    </div>
</div>
<?php $form = ActiveForm::begin(['action' => \app\helpers\OrganizationUrl::to(['attendance/lesson', 'id' => $lesson->id]), 'method' => 'POST']); ?>
<div class="form-group">
    <?= Html::submitButton(Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
</div>
<div class="card">
    <div class="card-header">
        Ученики
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-light" id="attendance-table">
                    <?foreach ($model->pupils as $k => $pupil):?>
                        <tr>
                            <td class="font-weight-bold"><?=$k+1;?></td>
                            <td class="font-weight-bold"><a href="<?=\app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]);?>" target="_blank"><?=$pupil->fio;?></a></td>
                            <td><?=$form->field($model, 'statuses['.$pupil->id.'][status]')->radioList(\app\models\LessonAttendance::getStatusList(), array('labelOptions' => array('style' => 'display:inline'),
                                    'separator' => ' ',
                                ))->inline(true)->label(false);?></td>
                        </tr>
                    <?endforeach;?>
                </table>

            </div>
        </div>
    </div>
</div>
<div class="alert alert-warning mt-3">
    <p class="font-weight-bold">Что такое редактирование посещения?</p>
    <p>Каждый раз когда ученики посещают уроки, необходимо отмечать их посещаемость в системе. Всего существует 4 вида посещаемости:</p>
    <p>1. <span class="font-weight-bold">Посещение</span> - ученик был на уроке, преподаватель получит оплату за ученика;</p>
    <p>2. <span class="font-weight-bold">Пропуск (с оплатой)</span> - ученика не было на уроке, преподаватель получит оплату за ученика. Используется когда преподаватель явился ради ученика. В основном на индивиульных занятиях;</p>
    <p>3. <span class="font-weight-bold">Пропуск (без оплаты)</span> - ученик не был на уроке, преподаватель не получит оплату за ученика. Используется в обычных групповых занятиях;</p>
    <p>4. <span class="font-weight-bold">Уваж. причина</span> - пропуск урока по уважительной причине. Ученик не был на уроке, преподаватель не получит оплату за ученика. Урок ученика переносится (оплата не сгорает).</p>
</div>

<?php ActiveForm::end(); ?>
