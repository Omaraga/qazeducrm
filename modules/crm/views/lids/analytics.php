<?php

use app\helpers\OrganizationUrl;
use app\helpers\StatusHelper;
use app\models\Lids;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int|null $managerId */
/** @var array $funnelAnalytics */
/** @var array $managerStats */
/** @var array $lostReasons */
/** @var array $sourceStats */
/** @var array $managers */

$this->title = 'Аналитика лидов';
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = 'Аналитика';

$statusColors = [
    Lids::STATUS_NEW => ['bg' => 'bg-sky-500', 'text' => 'text-sky-700', 'light' => 'bg-sky-100'],
    Lids::STATUS_CONTACTED => ['bg' => 'bg-blue-500', 'text' => 'text-blue-700', 'light' => 'bg-blue-100'],
    Lids::STATUS_TRIAL => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'light' => 'bg-amber-100'],
    Lids::STATUS_THINKING => ['bg' => 'bg-gray-500', 'text' => 'text-gray-700', 'light' => 'bg-gray-100'],
    Lids::STATUS_ENROLLED => ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'light' => 'bg-indigo-100'],
    Lids::STATUS_PAID => ['bg' => 'bg-success-500', 'text' => 'text-success-700', 'light' => 'bg-success-100'],
    Lids::STATUS_LOST => ['bg' => 'bg-danger-500', 'text' => 'text-danger-700', 'light' => 'bg-danger-100'],
];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Статистика воронки продаж и эффективность менеджеров</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['lids/kanban']) ?>" class="btn btn-outline">
                <?= Icon::show('view-columns', 'sm') ?>
                Kanban
            </a>
            <a href="<?= OrganizationUrl::to(['lids/index']) ?>" class="btn btn-outline">
                <?= Icon::show('table-cells', 'sm') ?>
                Таблица
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Период с</label>
                    <input type="date" name="date_from" value="<?= Html::encode($dateFrom) ?>"
                           class="form-control">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">по</label>
                    <input type="date" name="date_to" value="<?= Html::encode($dateTo) ?>"
                           class="form-control">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Менеджер</label>
                    <select name="manager_id" class="form-control min-w-[200px]">
                        <option value="">Все менеджеры</option>
                        <?php foreach ($managers as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $managerId == $id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= Icon::show('funnel', 'sm') ?>
                        Применить
                    </button>
                    <a href="<?= OrganizationUrl::to(['lids/analytics']) ?>" class="btn btn-secondary">
                        Сбросить
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-gray-900"><?= $funnelAnalytics['total'] ?></div>
                <div class="text-sm text-gray-500 mt-1">Всего лидов</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-success-600"><?= $funnelAnalytics['converted'] ?></div>
                <div class="text-sm text-gray-500 mt-1">Конвертировано</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-danger-600"><?= $funnelAnalytics['lost'] ?></div>
                <div class="text-sm text-gray-500 mt-1">Потеряно</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <div class="text-3xl font-bold text-primary-600"><?= $funnelAnalytics['conversion_rate'] ?>%</div>
                <div class="text-sm text-gray-500 mt-1">Конверсия</div>
            </div>
        </div>
    </div>

    <!-- Funnel Visualization -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Воронка продаж</h3>
        </div>
        <div class="card-body">
            <?php if ($funnelAnalytics['total'] > 0): ?>
                <div class="space-y-3">
                    <?php
                    $maxCount = max(array_column($funnelAnalytics['funnel'], 'count'));
                    foreach ($funnelAnalytics['funnel'] as $status => $data):
                        $colors = $statusColors[$status] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700', 'light' => 'bg-gray-100'];
                        $width = $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0;
                    ?>
                        <div class="group">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-700"><?= Html::encode($data['label']) ?></span>
                                    <span class="text-xs text-gray-400">(<?= $data['count'] ?>)</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <?php if ($status != Lids::STATUS_LOST): ?>
                                        <span class="text-gray-500" title="Конверсия от предыдущего этапа">
                                            <?= $data['step_conversion'] ?>% <?= Icon::show('arrow-down', 'xs', 'inline opacity-50') ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="font-medium <?= $colors['text'] ?>" title="От общего числа">
                                        <?= $data['total_conversion'] ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="relative h-8 bg-gray-100 rounded-lg overflow-hidden">
                                <div class="absolute inset-y-0 left-0 <?= $colors['bg'] ?> transition-all duration-500 rounded-lg flex items-center justify-end pr-2"
                                     style="width: <?= max($width, 5) ?>%">
                                    <?php if ($data['count'] > 0): ?>
                                        <span class="text-xs font-medium text-white"><?= $data['count'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                        <?= Icon::show('chart-bar') ?>
                    </div>
                    <p>Нет данных за выбранный период</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Manager Stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Эффективность менеджеров</h3>
            </div>
            <?php if (!empty($managerStats)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Менеджер</th>
                                <th class="text-center">Всего</th>
                                <th class="text-center">Конв.</th>
                                <th class="text-center">Потер.</th>
                                <th class="text-center">Актив.</th>
                                <th class="text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($managerStats as $stat): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-xs font-medium">
                                                <?= mb_substr($stat['manager_name'], 0, 1) ?>
                                            </div>
                                            <span class="font-medium"><?= Html::encode($stat['manager_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $stat['total'] ?></td>
                                    <td class="text-center text-success-600"><?= $stat['converted'] ?></td>
                                    <td class="text-center text-danger-600"><?= $stat['lost'] ?></td>
                                    <td class="text-center text-blue-600"><?= $stat['active'] ?></td>
                                    <td class="text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $stat['conversion_rate'] >= 30 ? 'bg-success-100 text-success-700' : ($stat['conversion_rate'] >= 15 ? 'bg-warning-100 text-warning-700' : 'bg-gray-100 text-gray-700') ?>">
                                            <?= $stat['conversion_rate'] ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-8 text-gray-500">
                    <p>Нет данных о менеджерах</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lost Reasons -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Причины потерь</h3>
            </div>
            <?php if (!empty($lostReasons)): ?>
                <div class="card-body">
                    <div class="space-y-3">
                        <?php
                        $maxLost = max(array_column($lostReasons, 'count'));
                        foreach ($lostReasons as $reason):
                            $width = $maxLost > 0 ? ($reason['count'] / $maxLost) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700"><?= Html::encode($reason['lost_reason']) ?></span>
                                    <span class="font-medium text-gray-900"><?= $reason['count'] ?></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-danger-500 rounded-full" style="width: <?= $width ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-8 text-gray-500">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                        <?= Icon::show('face-smile') ?>
                    </div>
                    <p>Нет потерянных лидов</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Source Stats -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Статистика по источникам</h3>
        </div>
        <?php if (!empty($sourceStats)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Источник</th>
                            <th class="text-center">Всего</th>
                            <th class="text-center">Конвертировано</th>
                            <th class="text-center">Потеряно</th>
                            <th class="text-right">Конверсия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sourceStats as $stat): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <?php
                                        $sourceIcons = [
                                            'instagram' => '<svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>',
                                            'whatsapp' => '<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>',
                                            '2gis' => '<span class="w-5 h-5 flex items-center justify-center text-blue-500">' . Icon::show('map-pin', 'sm') . '</span>',
                                            'website' => '<span class="w-5 h-5 flex items-center justify-center text-gray-500">' . Icon::show('globe-alt', 'sm') . '</span>',
                                            'referral' => '<span class="w-5 h-5 flex items-center justify-center text-purple-500">' . Icon::show('users', 'sm') . '</span>',
                                            'walk_in' => '<span class="w-5 h-5 flex items-center justify-center text-indigo-500">' . Icon::show('user-plus', 'sm') . '</span>',
                                            'phone' => '<span class="w-5 h-5 flex items-center justify-center text-green-600">' . Icon::show('phone', 'sm') . '</span>',
                                            'other' => '<span class="w-5 h-5 flex items-center justify-center text-gray-400">' . Icon::show('question-mark-circle', 'sm') . '</span>',
                                        ];
                                        echo $sourceIcons[$stat['source']] ?? Icon::show('question-mark-circle', 'sm', 'text-gray-400');
                                        ?>
                                        <span class="font-medium"><?= Html::encode($stat['source_label']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?= $stat['total'] ?></td>
                                <td class="text-center text-success-600"><?= $stat['converted'] ?></td>
                                <td class="text-center text-danger-600"><?= $stat['lost'] ?></td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500 rounded-full" style="width: <?= $stat['conversion_rate'] ?>%"></div>
                                        </div>
                                        <span class="text-sm font-medium w-12 text-right"><?= $stat['conversion_rate'] ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card-body text-center py-8 text-gray-500">
                <p>Нет данных об источниках</p>
            </div>
        <?php endif; ?>
    </div>
</div>
