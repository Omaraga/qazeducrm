<?php

use app\helpers\OrganizationUrl;
use app\models\LessonAttendance;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lesson $model */

$this->title = 'Занятие #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Расписание', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= Html::encode($model->group->color ?? '#3b82f6') ?>20">
                <svg class="w-6 h-6" style="color: <?= Html::encode($model->group->color ?? '#3b82f6') ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1"><?= $model->date ? date('d.m.Y', strtotime($model->date)) : '' ?> <?= $model->start_time ?> - <?= $model->end_time ?></p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['schedule/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['schedule/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить это занятие?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Lesson Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Информация о занятии</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Группа</dt>
                            <dd class="mt-1">
                                <?php if ($model->group): ?>
                                    <a href="<?= OrganizationUrl::to(['group/view', 'id' => $model->group_id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                        <?= Html::encode($model->group->code . ' - ' . $model->group->name) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Преподаватель</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->teacher->fio ?? '—') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= $model->date ? date('d.m.Y', strtotime($model->date)) : '—' ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Время</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= $model->start_time ?> - <?= $model->end_time ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Attendance -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Посещаемость</h3>
                    <a href="<?= OrganizationUrl::to(['attendance/lesson', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Редактировать
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->pupils)): ?>
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
                    <?php else: ?>
                    <p class="text-gray-500 text-sm">В группе нет учеников</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Back Button -->
            <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к расписанию
            </a>
        </div>
    </div>
</div>
