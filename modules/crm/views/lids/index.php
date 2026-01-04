<?php

use app\helpers\OrganizationUrl;
use app\helpers\StatusHelper;
use app\models\Lids;
use app\widgets\tailwind\EmptyState;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\search\LidsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Лиды';
$this->params['breadcrumbs'][] = $this->title;

// Статистика воронки
$funnelStats = Lids::getFunnelStats();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Воронка продаж и управление лидами</p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['lids/create']) ?>" class="btn btn-primary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить лид
            </a>
        </div>
    </div>

    <!-- Funnel Stats -->
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($funnelStats as $status => $stat): ?>
                    <?php
                    $isActive = $searchModel->status == $status;
                    $color = StatusHelper::getColor('lids', $status);
                    $colorClasses = [
                        'primary' => ['active' => 'bg-primary-600 text-white', 'inactive' => 'bg-primary-50 text-primary-700 hover:bg-primary-100'],
                        'success' => ['active' => 'bg-success-600 text-white', 'inactive' => 'bg-success-50 text-success-700 hover:bg-success-100'],
                        'warning' => ['active' => 'bg-warning-500 text-white', 'inactive' => 'bg-warning-50 text-warning-700 hover:bg-warning-100'],
                        'danger' => ['active' => 'bg-danger-600 text-white', 'inactive' => 'bg-danger-50 text-danger-700 hover:bg-danger-100'],
                        'info' => ['active' => 'bg-blue-600 text-white', 'inactive' => 'bg-blue-50 text-blue-700 hover:bg-blue-100'],
                        'gray' => ['active' => 'bg-gray-600 text-white', 'inactive' => 'bg-gray-100 text-gray-700 hover:bg-gray-200'],
                        'purple' => ['active' => 'bg-purple-600 text-white', 'inactive' => 'bg-purple-50 text-purple-700 hover:bg-purple-100'],
                        'indigo' => ['active' => 'bg-indigo-600 text-white', 'inactive' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'],
                    ];
                    $btnClass = $isActive
                        ? ($colorClasses[$color]['active'] ?? $colorClasses['gray']['active']) . ' shadow-md ring-2 ring-offset-2 ring-' . $color . '-600'
                        : ($colorClasses[$color]['inactive'] ?? $colorClasses['gray']['inactive']);
                    ?>
                    <a href="<?= Url::to(['index', 'LidsSearch[status]' => $status]) ?>"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= $btnClass ?>">
                        <?= Html::encode($stat['label']) ?>
                        <?= StatusBadge::count($stat['count'], ['color' => $isActive ? 'gray' : $color]) ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($searchModel->status): ?>
                    <a href="<?= Url::to(['index']) ?>" class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                        <?= Icon::show('x', 'sm') ?>
                        Сбросить
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="table-container table-container-scrollable">
            <table class="data-table data-table-sticky">
                <thead>
                    <tr>
                        <th>Контакт</th>
                        <th>Источник</th>
                        <th>Статус</th>
                        <th>След. контакт</th>
                        <th>Менеджер</th>
                        <th>Дата</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0 text-primary-600">
                                    <?= Icon::show('user') ?>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= Html::encode($model->fio) ?></div>
                                    <?php if ($model->phone): ?>
                                        <div class="text-sm text-gray-500"><?= Html::encode($model->phone) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($model->source): ?>
                                <div class="flex items-center gap-2 text-gray-600">
                                    <?php
                                    $sourceIcons = [
                                        'instagram' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>',
                                        'whatsapp' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>',
                                        '2gis' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                                        'website' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
                                    ];
                                    echo $sourceIcons[$model->source] ?? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                                    ?>
                                    <span><?= Html::encode($model->getSourceLabel()) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= StatusBadge::show('lids', $model->status) ?>
                        </td>
                        <td>
                            <?php if ($model->next_contact_date): ?>
                                <?php
                                $date = strtotime($model->next_contact_date);
                                $today = strtotime(date('Y-m-d'));
                                $colorClass = 'text-gray-500';
                                if ($date < $today) {
                                    $colorClass = 'text-danger-600 font-medium';
                                } elseif ($date == $today) {
                                    $colorClass = 'text-warning-600 font-medium';
                                }
                                ?>
                                <span class="<?= $colorClass ?>"><?= date('d.m.Y', $date) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-gray-500">
                            <?= Html::encode($model->manager ? $model->manager->fio : ($model->manager_name ?: '—')) ?>
                        </td>
                        <td class="text-gray-500">
                            <?= $model->date ? date('d.m.Y', strtotime($model->date)) : '—' ?>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="<?= OrganizationUrl::to(['lids/view', 'id' => $model->id]) ?>" class="table-action-btn" title="Просмотр">
                                    <?= Icon::show('eye', 'sm') ?>
                                </a>
                                <a href="<?= OrganizationUrl::to(['lids/update', 'id' => $model->id]) ?>" class="table-action-btn" title="Редактировать">
                                    <?= Icon::show('edit', 'sm') ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <?= EmptyState::tableRow(7, 'funnel', 'Лиды не найдены', 'Добавьте первый лид в воронку продаж', ['lids/create'], 'Добавить лид') ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
