<?php

use app\helpers\OrganizationUrl;
use app\models\LessonAttendance;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\forms\AttendancesForm $model */
/** @var \app\models\Lesson $lesson */

$this->title = Yii::t('main', 'Посещаемость');
$this->params['breadcrumbs'][] = ['label' => 'Расписание', 'url' => OrganizationUrl::to(['schedule/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Занятие #' . $lesson->id, 'url' => OrganizationUrl::to(['schedule/view', 'id' => $lesson->id])];
$this->params['breadcrumbs'][] = $this->title;

$statuses = LessonAttendance::getStatusList();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= Html::encode($lesson->group->color ?? '#3b82f6') ?>20">
                <svg class="w-6 h-6" style="color: <?= Html::encode($lesson->group->color ?? '#3b82f6') ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Отметка посещаемости занятия</p>
            </div>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['schedule/view', 'id' => $lesson->id]) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к занятию
            </a>
        </div>
    </div>

    <!-- Lesson Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Информация о занятии</h3>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Группа</dt>
                    <dd class="mt-1">
                        <a href="<?= OrganizationUrl::to(['group/view', 'id' => $lesson->group_id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                            <?= Html::encode($lesson->group->getNameFull()) ?>
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Дата и время</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lesson->getDateTime()) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Преподаватель</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lesson->teacher->fio ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Предмет</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lesson->group->subject->name ?? '—') ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Attendance Form -->
    <form action="<?= OrganizationUrl::to(['attendance/lesson', 'id' => $lesson->id]) ?>" method="post" class="space-y-6">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Ученики</h3>
                <span class="text-sm text-gray-500"><?= count($model->pupils) ?> учеников</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($model->pupils)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ученик</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус посещаемости</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($model->pupils as $k => $pupil): ?>
                            <?php
                            $currentStatus = $model->statuses[$pupil->id]['status'] ?? null;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm font-medium text-gray-500"><?= $k + 1 ?></td>
                                <td class="px-4 py-4">
                                    <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]) ?>" target="_blank" class="text-primary-600 hover:text-primary-800 font-medium">
                                        <?= Html::encode($pupil->fio) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-3">
                                        <?php foreach ($statuses as $statusId => $statusLabel): ?>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio"
                                                   name="AttendancesForm[statuses][<?= $pupil->id ?>][status]"
                                                   value="<?= $statusId ?>"
                                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                   <?= $currentStatus == $statusId ? 'checked' : '' ?>>
                                            <span class="ml-2 text-sm <?= $statusId == LessonAttendance::STATUS_VISIT ? 'text-success-700' : ($statusId == LessonAttendance::STATUS_MISS_WITHOUT_PAY ? 'text-danger-700' : 'text-gray-700') ?>">
                                                <?= Html::encode($statusLabel) ?>
                                            </span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500">В группе нет учеников</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Быстрые действия</h3>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setAllStatus(<?= LessonAttendance::STATUS_VISIT ?>)" class="btn btn-sm btn-success">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Все присутствовали
                    </button>
                    <button type="button" onclick="setAllStatus(<?= LessonAttendance::STATUS_MISS_WITHOUT_PAY ?>)" class="btn btn-sm btn-danger">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Все отсутствовали
                    </button>
                </div>
            </div>
        </div>

        <!-- Help -->
        <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-warning-800">
                    <p class="font-semibold mb-2">Что такое редактирование посещения?</p>
                    <p class="mb-2">Каждый раз когда ученики посещают уроки, необходимо отмечать их посещаемость в системе:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><strong>Посещение</strong> — ученик был на уроке, преподаватель получит оплату</li>
                        <li><strong>Пропуск (с оплатой)</strong> — ученика не было, но преподаватель получит оплату (индивидуальные занятия)</li>
                        <li><strong>Пропуск (без оплаты)</strong> — ученика не было, преподаватель не получит оплату (групповые занятия)</li>
                        <li><strong>Уваж. причина</strong> — пропуск по уважительной причине, урок переносится</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="<?= OrganizationUrl::to(['schedule/view', 'id' => $lesson->id]) ?>" class="btn btn-secondary">Отмена</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить посещаемость
            </button>
        </div>
    </form>
</div>

<script>
function setAllStatus(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(radio) {
        radio.checked = true;
    });
}
</script>
