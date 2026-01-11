<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var array $monthlyStats */
/** @var array $allTimeStats */

use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Статистика посещений');
$orgId = Module::getOrganizationId();

$total = $allTimeStats['total'] ?? 0;
$visited = $allTimeStats['visited'] ?? 0;
$missed = ($allTimeStats['missed_with_pay'] ?? 0) + ($allTimeStats['missed_without_pay'] ?? 0);
$valid = $allTimeStats['missed_valid'] ?? 0;
$percent = $total > 0 ? round($visited / $total * 100) : 0;
$percentColor = $percent >= 80 ? 'text-green-600' : ($percent >= 60 ? 'text-amber-500' : 'text-red-500');
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('chart-bar', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Статистика') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/attendance/index', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('arrow-left', 'sm') ?>
            <?= Yii::t('app', 'Назад') ?>
        </a>
    </div>

    <!-- Overall Stats Card -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios">
            <?= Yii::t('app', 'Общая статистика') ?>
        </div>

        <div class="p-4">
            <!-- Main Percentage -->
            <div class="text-center mb-5">
                <div class="relative inline-flex items-center justify-center">
                    <svg class="w-32 h-32 transform -rotate-90">
                        <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="12" fill="none"/>
                        <circle cx="64" cy="64" r="56"
                                stroke="<?= $percent >= 80 ? '#16a34a' : ($percent >= 60 ? '#f59e0b' : '#dc2626') ?>"
                                stroke-width="12"
                                fill="none"
                                stroke-linecap="round"
                                stroke-dasharray="<?= 2 * 3.14159 * 56 ?>"
                                stroke-dashoffset="<?= 2 * 3.14159 * 56 * (1 - $percent / 100) ?>"/>
                    </svg>
                    <span class="absolute text-3xl font-bold <?= $percentColor ?>"><?= $percent ?>%</span>
                </div>
                <p class="text-gray-500 mt-2"><?= Yii::t('app', 'Посещаемость за всё время') ?></p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-indigo-50 rounded-2xl p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('calendar', 'sm', 'text-indigo-600') ?>
                    </div>
                    <p class="text-2xl font-bold text-indigo-600"><?= $total ?></p>
                    <p class="text-xs text-gray-500"><?= Yii::t('app', 'Всего занятий') ?></p>
                </div>
                <div class="bg-green-50 rounded-2xl p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('check-circle', 'sm', 'text-green-600') ?>
                    </div>
                    <p class="text-2xl font-bold text-green-600"><?= $visited ?></p>
                    <p class="text-xs text-gray-500"><?= Yii::t('app', 'Посещено') ?></p>
                </div>
                <div class="bg-red-50 rounded-2xl p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('x-circle', 'sm', 'text-red-600') ?>
                    </div>
                    <p class="text-2xl font-bold text-red-600"><?= $missed ?></p>
                    <p class="text-xs text-gray-500"><?= Yii::t('app', 'Пропущено') ?></p>
                </div>
                <div class="bg-amber-50 rounded-2xl p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('exclamation-triangle', 'sm', 'text-amber-600') ?>
                    </div>
                    <p class="text-2xl font-bold text-amber-600"><?= $valid ?></p>
                    <p class="text-xs text-gray-500"><?= Yii::t('app', 'Ув. причина') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios">
            <?= Yii::t('app', 'По месяцам') ?>
        </div>

        <div class="list-group-ios">
            <?php foreach ($monthlyStats as $data): ?>
                <?php
                $stats = $data['stats'];
                $monthTotal = $stats['total'] ?? 0;
                $monthVisited = $stats['visited'] ?? 0;
                $monthMissed = ($stats['missed_with_pay'] ?? 0) + ($stats['missed_without_pay'] ?? 0);
                $monthPercent = $monthTotal > 0 ? round($monthVisited / $monthTotal * 100) : 0;
                $monthPercentColor = $monthPercent >= 80 ? 'bg-green-500' : ($monthPercent >= 60 ? 'bg-amber-500' : 'bg-red-500');
                $monthTextColor = $monthPercent >= 80 ? 'text-green-600' : ($monthPercent >= 60 ? 'text-amber-500' : 'text-red-500');
                ?>
                <a href="<?= Url::to(['/cabinet/attendance/index', 'org' => $orgId, 'month' => $data['monthKey']]) ?>"
                   class="list-item-ios border-b border-gray-100 last:border-0">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <?= Icon::show('calendar', 'sm', 'text-gray-600') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900"><?= Html::encode($data['month']) ?></p>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-xs text-green-600"><?= $monthVisited ?> <?= Yii::t('app', 'посещ.') ?></span>
                            <span class="text-xs text-red-600"><?= $monthMissed ?> <?= Yii::t('app', 'проп.') ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <div class="w-20">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full <?= $monthPercentColor ?> rounded-full transition-all" style="width: <?= $monthPercent ?>%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-semibold <?= $monthTextColor ?> w-10 text-right"><?= $monthPercent ?>%</span>
                        <?= Icon::show('chevron-right', 'sm', 'text-gray-300') ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($monthlyStats)): ?>
            <div class="empty-ios py-12">
                <div class="empty-ios-icon">
                    <?= Icon::show('chart-bar', 'xl', 'text-gray-300') ?>
                </div>
                <p class="empty-ios-text"><?= Yii::t('app', 'Нет данных') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
