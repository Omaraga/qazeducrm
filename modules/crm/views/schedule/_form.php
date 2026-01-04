<?php

use app\helpers\OrganizationUrl;
use app\models\Group;
use app\models\LessonAttendance;
use app\models\Organizations;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lesson $model */

$groups = Group::find()->byOrganization()->all();
$teachers = Organizations::getOrganizationTeachersMap();

$teachersUrl = OrganizationUrl::to(['schedule/teachers']);
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const groupSelect = document.getElementById('lesson-group_id');
    const teacherSelect = document.getElementById('lesson-teacher_id');

    if (groupSelect && teacherSelect) {
        groupSelect.addEventListener('change', function() {
            const groupId = this.value;
            if (!groupId) return;

            fetch('{$teachersUrl}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: 'id=' + groupId
            })
            .then(response => response.json())
            .then(data => {
                teacherSelect.innerHTML = '<option value="">Выберите преподавателя</option>';
                data.forEach(teacher => {
                    const option = document.createElement('option');
                    option.value = teacher.id;
                    option.textContent = teacher.fio;
                    teacherSelect.appendChild(option);
                });
                teacherSelect.disabled = false;
            })
            .catch(err => console.error('Error loading teachers:', err));
        });
    }
});
JS;
$this->registerJs($js);
?>

<form action="" method="post" class="space-y-6">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Lesson Data -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Занятие') ?></h3>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="lesson-group_id">Группа <span class="text-danger-500">*</span></label>
                    <?= Html::activeDropDownList($model, 'group_id', ArrayHelper::map($groups, 'id', 'nameFull'), [
                        'class' => 'form-select',
                        'id' => 'lesson-group_id',
                        'prompt' => 'Выберите группу'
                    ]) ?>
                    <?php if ($model->hasErrors('group_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('group_id') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="lesson-teacher_id">Преподаватель</label>
                    <?= Html::activeDropDownList($model, 'teacher_id', $teachers, [
                        'class' => 'form-select',
                        'id' => 'lesson-teacher_id',
                        'prompt' => 'Выберите преподавателя',
                        'disabled' => !$model->group_id
                    ]) ?>
                    <?php if ($model->hasErrors('teacher_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('teacher_id') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="lesson-date">Дата <span class="text-danger-500">*</span></label>
                    <input type="date" name="Lesson[date]" id="lesson-date" class="form-input"
                           value="<?= $model->date ? date('Y-m-d', strtotime($model->date)) : '' ?>">
                    <?php if ($model->hasErrors('date')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="lesson-start_time">Время начала <span class="text-danger-500">*</span></label>
                    <input type="time" name="Lesson[start_time]" id="lesson-start_time" class="form-input"
                           value="<?= Html::encode($model->start_time) ?>">
                    <?php if ($model->hasErrors('start_time')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('start_time') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="lesson-end_time">Время окончания <span class="text-danger-500">*</span></label>
                    <input type="time" name="Lesson[end_time]" id="lesson-end_time" class="form-input"
                           value="<?= Html::encode($model->end_time) ?>">
                    <?php if ($model->hasErrors('end_time')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('end_time') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance (only for existing lessons) -->
    <?php if ($model->id && !empty($model->pupils)): ?>
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Посещаемость</h3>
            <a href="<?= OrganizationUrl::to(['attendance/lesson', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary">
                Редактировать посещения
            </a>
        </div>
        <div class="card-body">
            <div class="divide-y divide-gray-100">
                <?php foreach ($model->pupils as $index => $pupil): ?>
                <?php
                $attendance = LessonAttendance::find()
                    ->where(['pupil_id' => $pupil->id, 'lesson_id' => $model->id])
                    ->notDeleted()
                    ->one();
                ?>
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400 w-6"><?= $index + 1 ?></span>
                        <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]) ?>" class="text-sm text-primary-600 hover:text-primary-800">
                            <?= Html::encode($pupil->fio) ?>
                        </a>
                    </div>
                    <div>
                        <?php if ($attendance): ?>
                            <?php if ($attendance->status == LessonAttendance::STATUS_VISIT): ?>
                                <span class="badge badge-success"><?= $attendance->getStatusLabel() ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?= $attendance->getStatusLabel() ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-secondary">Не задано</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <?php if ($model->id): ?>
        <?= Html::a('Удалить занятие', OrganizationUrl::to(['schedule/delete', 'id' => $model->id]), [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить занятие?',
                'method' => 'post',
            ],
        ]) ?>
        <?php else: ?>
        <div></div>
        <?php endif; ?>
        <div class="flex items-center gap-3">
            <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-secondary">Отмена</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
            </button>
        </div>
    </div>
</form>
