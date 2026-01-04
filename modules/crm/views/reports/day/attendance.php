<?php

use app\helpers\OrganizationUrl;
use app\models\Lesson;
use app\models\LessonAttendance;
use yii\helpers\Html;

/** @var \app\models\Lesson[] $lessons */
/** @var array $attendances */
?>

<div class="space-y-4">
    <?php foreach ($lessons as $lesson): ?>
    <div class="card overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
            <!-- Lesson Info -->
            <div class="p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-white text-sm font-medium" style="background-color: <?= Html::encode($lesson->group->color) ?>">
                        <?php if ($lesson->status == Lesson::STATUS_FINISHED): ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php else: ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php endif; ?>
                        <?= Html::encode($lesson->group->getNameFull()) ?>
                    </span>
                </div>
                <dl class="space-y-1 text-sm">
                    <div class="flex">
                        <dt class="w-32 text-gray-500">Группа:</dt>
                        <dd class="text-gray-900"><?= Html::encode($lesson->group->getNameFull()) ?></dd>
                    </div>
                    <div class="flex">
                        <dt class="w-32 text-gray-500">Дата:</dt>
                        <dd class="text-gray-900"><?= Html::encode($lesson->getDateTime()) ?></dd>
                    </div>
                    <div class="flex">
                        <dt class="w-32 text-gray-500">Преподаватель:</dt>
                        <dd class="text-gray-900"><?= Html::encode($lesson->teacher->fio ?? '—') ?></dd>
                    </div>
                    <div class="flex">
                        <dt class="w-32 text-gray-500">Предмет:</dt>
                        <dd class="text-gray-900"><?= Html::encode($lesson->group->subject->name ?? '—') ?></dd>
                    </div>
                </dl>
                <a href="<?= OrganizationUrl::to(['attendance/lesson', 'id' => $lesson->id]) ?>" target="_blank" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Редактировать посещения
                </a>
            </div>

            <!-- Pupils Attendance -->
            <div class="p-4">
                <?php $pupils = $lesson->getPupils(); ?>
                <?php if (!empty($pupils)): ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($pupils as $k => $pupil): ?>
                    <div class="py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400 w-6"><?= $k + 1 ?></span>
                            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]) ?>" target="_blank" class="text-sm text-primary-600 hover:text-primary-800">
                                <?= Html::encode($pupil->fio) ?>
                            </a>
                        </div>
                        <div>
                            <?php if (array_key_exists($lesson->id, $attendances) && array_key_exists($pupil->id, $attendances[$lesson->id])): ?>
                                <?php $status = $attendances[$lesson->id][$pupil->id]; ?>
                                <?php if ($status == LessonAttendance::STATUS_VISIT): ?>
                                    <span class="badge badge-success"><?= LessonAttendance::getStatusList()[$status] ?></span>
                                <?php elseif ($status == LessonAttendance::STATUS_MISS_WITHOUT_PAY): ?>
                                    <span class="badge badge-danger"><?= LessonAttendance::getStatusList()[$status] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= LessonAttendance::getStatusList()[$status] ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-secondary">Не выставлено</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-sm text-gray-500">Нет учеников в группе</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
