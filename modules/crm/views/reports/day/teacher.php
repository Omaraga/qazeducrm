<?php

use app\helpers\OrganizationUrl;
use app\models\Lesson;
use yii\helpers\Html;

/** @var \app\models\User[] $teachers */
/** @var array $teacherLessons */
/** @var array $lessonPupilSalary */
?>

<div class="space-y-4">
    <?php foreach ($teachers as $teacher): ?>
    <?php $teacherSalary = 0; ?>
    <div class="card border-l-4 border-l-success-500">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Html::encode($teacher['fio']) ?></h3>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-gray-200">
                <?php foreach ($teacherLessons[$teacher['id']] as $lesson): ?>
                <?php if (!$lesson->group) continue; ?>
                <?php $lessonSalary = 0; ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    <!-- Lesson Info -->
                    <div class="p-4 space-y-2">
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
                                <dt class="w-24 text-gray-500">Группа:</dt>
                                <dd class="text-gray-900"><?= Html::encode($lesson->group->getNameFull()) ?></dd>
                            </div>
                            <div class="flex">
                                <dt class="w-24 text-gray-500">Дата:</dt>
                                <dd class="text-gray-900"><?= Html::encode($lesson->getDateTime()) ?></dd>
                            </div>
                            <div class="flex">
                                <dt class="w-24 text-gray-500">Предмет:</dt>
                                <dd class="text-gray-900"><?= Html::encode($lesson->group->subject->name ?? '—') ?></dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Pupils Salary -->
                    <div class="p-4">
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($lesson->getPupils() as $i => $pupil): ?>
                            <?php
                            $pupilSalary = $lessonPupilSalary[$lesson['id']][$pupil['id']] ?? 0;
                            $teacherSalary += $pupilSalary;
                            $lessonSalary += $pupilSalary;
                            ?>
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-400 w-6"><?= $i + 1 ?></span>
                                    <span class="text-sm text-gray-900"><?= Html::encode($pupil->fio) ?></span>
                                </div>
                                <span class="text-sm font-medium text-gray-700"><?= number_format($pupilSalary, 0, '.', ' ') ?> ₸</span>
                            </div>
                            <?php endforeach; ?>
                            <div class="py-2 flex items-center justify-between bg-gray-50 -mx-4 px-4">
                                <span class="text-sm font-semibold text-gray-900">Итого за урок:</span>
                                <span class="text-sm font-bold text-gray-900"><?= number_format($lessonSalary, 0, '.', ' ') ?> ₸</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-footer bg-success-50 border-t border-success-200">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-success-800">Итого за день</span>
                <span class="text-lg font-bold text-success-700"><?= number_format($teacherSalary, 0, '.', ' ') ?> ₸</span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
