<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var array $weekDays */
/** @var string $weekStart */
/** @var string $weekEnd */

use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Расписание на неделю');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('bars-3', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Неделя') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/schedule/index', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('calendar', 'sm') ?>
            <?= Yii::t('app', 'Календарь') ?>
        </a>
    </div>

    <!-- Week Range -->
    <div class="card-glass-solid p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                <?= Icon::show('calendar-days', 'sm', 'text-indigo-600') ?>
            </div>
            <span class="font-semibold text-gray-900">
                <?= Yii::$app->formatter->asDate($weekStart, 'd MMMM') ?> - <?= Yii::$app->formatter->asDate($weekEnd, 'd MMMM') ?>
            </span>
        </div>
    </div>

    <!-- Desktop Week Grid (hidden on mobile) -->
    <div class="hidden md:block card-glass-solid overflow-hidden">
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <!-- Days Header -->
                <div class="grid grid-cols-7 border-b border-gray-100">
                    <?php foreach ($weekDays as $day): ?>
                        <?php $isToday = $day['date'] == date('Y-m-d'); ?>
                        <div class="text-center py-4 px-2 <?= $isToday ? 'bg-indigo-50' : '' ?>">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide"><?= Html::encode($day['dayName']) ?></div>
                            <div class="mt-1">
                                <?php if ($isToday): ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-sm">
                                        <?= Yii::$app->formatter->asDate($day['date'], 'd') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-semibold text-gray-900">
                                        <?= Yii::$app->formatter->asDate($day['date'], 'd') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Lessons Grid -->
                <div class="grid grid-cols-7">
                    <?php foreach ($weekDays as $day): ?>
                        <?php $isToday = $day['date'] == date('Y-m-d'); ?>
                        <div class="min-h-[200px] p-3 border-r border-gray-100 last:border-r-0 <?= $isToday ? 'bg-indigo-50/30' : '' ?>">
                            <?php if (empty($day['lessons'])): ?>
                                <div class="h-full flex items-center justify-center">
                                    <p class="text-xs text-gray-400"><?= Yii::t('app', 'Нет занятий') ?></p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($day['lessons'] as $lesson): ?>
                                        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-semibold text-indigo-600">
                                                    <?= substr($lesson->start_time, 0, 5) ?>
                                                </span>
                                            </div>
                                            <div class="font-medium text-gray-900 text-sm"><?= Html::encode($lesson->group->name ?? '') ?></div>
                                            <?php if ($lesson->room): ?>
                                                <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                                    <?= Icon::show('map-pin', 'xs', 'text-gray-400') ?>
                                                    <?= Html::encode($lesson->room->name) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="md:hidden space-y-3">
        <?php foreach ($weekDays as $day): ?>
            <?php $isToday = $day['date'] == date('Y-m-d'); ?>
            <div class="card-glass-solid overflow-hidden <?= $isToday ? 'ring-2 ring-indigo-500 ring-offset-2' : '' ?>">
                <!-- Day Header -->
                <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3 <?= $isToday ? 'bg-indigo-50' : 'bg-gray-50' ?>">
                    <?php if ($isToday): ?>
                        <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center text-sm">
                            <?= Yii::$app->formatter->asDate($day['date'], 'd') ?>
                        </span>
                    <?php else: ?>
                        <span class="w-10 h-10 rounded-xl bg-gray-200 text-gray-700 font-bold flex items-center justify-center text-sm">
                            <?= Yii::$app->formatter->asDate($day['date'], 'd') ?>
                        </span>
                    <?php endif; ?>
                    <div>
                        <div class="font-semibold <?= $isToday ? 'text-indigo-600' : 'text-gray-900' ?>">
                            <?= Html::encode($day['dayName']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= Yii::$app->formatter->asDate($day['date'], 'd MMMM') ?>
                        </div>
                    </div>
                    <?php if ($isToday): ?>
                        <span class="ml-auto badge-ios bg-indigo-100 text-indigo-700"><?= Yii::t('app', 'Сегодня') ?></span>
                    <?php endif; ?>
                </div>

                <!-- Lessons -->
                <div class="p-4">
                    <?php if (empty($day['lessons'])): ?>
                        <div class="text-center py-4">
                            <?= Icon::show('calendar', 'lg', 'text-gray-300') ?>
                            <p class="text-sm text-gray-400 mt-2"><?= Yii::t('app', 'Нет занятий') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($day['lessons'] as $lesson): ?>
                                <div class="flex items-start gap-4">
                                    <!-- Time -->
                                    <div class="flex-shrink-0 w-16 text-center">
                                        <div class="text-sm font-bold text-indigo-600"><?= substr($lesson->start_time, 0, 5) ?></div>
                                        <div class="text-xs text-gray-400"><?= substr($lesson->end_time, 0, 5) ?></div>
                                    </div>

                                    <!-- Lesson Card -->
                                    <div class="flex-1 bg-gray-50 rounded-xl p-3 border-l-4 border-indigo-500">
                                        <div class="font-semibold text-gray-900"><?= Html::encode($lesson->group->name ?? '') ?></div>
                                        <?php if ($lesson->room): ?>
                                            <div class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                                                <?= Icon::show('map-pin', 'xs', 'text-gray-400') ?>
                                                <?= Html::encode($lesson->room->name) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
