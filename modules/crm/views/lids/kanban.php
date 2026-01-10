<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\models\LidTag;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\Modal;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $columns */
/** @var array $filters */
/** @var array $managers */

$this->title = 'Лиды — Kanban';
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Kanban';

$filters = $filters ?? [];
$managers = $managers ?? [];

$statusColors = [
    Lids::STATUS_NEW => 'sky',
    Lids::STATUS_CONTACTED => 'blue',
    Lids::STATUS_TRIAL => 'amber',
    Lids::STATUS_THINKING => 'gray',
    Lids::STATUS_ENROLLED => 'indigo',
    Lids::STATUS_PAID => 'green',
    Lids::STATUS_LOST => 'red',
    Lids::STATUS_NOT_TARGET => 'slate',
    Lids::STATUS_IN_TRAINING => 'purple',
];

// URL для новых actions
$markNotTargetUrl = OrganizationUrl::to(['lids/mark-not-target']);
$markInTrainingUrl = OrganizationUrl::to(['lids/mark-in-training']);
$linkToPupilUrl = OrganizationUrl::to(['lids/link-to-pupil']);

$getLidUrl = OrganizationUrl::to(['lids/get-lid']);
$changeStatusUrl = OrganizationUrl::to(['lids-funnel/change-status']);
$pupilUpdateUrl = OrganizationUrl::to(['pupil/update']);
$kanbanUrl = OrganizationUrl::to(['lids-funnel/kanban']);

// Подсчёт активных фильтров
$activeFiltersCount = count(array_filter($filters, fn($v) => $v !== '' && $v !== null));
?>

<!-- Alpine Store для лидов (если еще не инициализирован) -->
<script>
document.addEventListener('alpine:init', () => {
    if (!Alpine.store('lids')) {
        Alpine.store('lids', {
            viewingLid: null,
            editingLid: null,
            isLoading: false,

            async loadLid(id) {
                this.isLoading = true;
                try {
                    const response = await fetch('<?= $getLidUrl ?>?id=' + id);
                    const data = await response.json();
                    if (data.success) {
                        this.viewingLid = data.lid;
                        return true;
                    }
                } catch (e) {
                    console.error('Error loading lid:', e);
                } finally {
                    this.isLoading = false;
                }
                return false;
            },

            openView(id) {
                this.loadLid(id).then(success => {
                    if (success) {
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'lids-view-modal' }));
                    }
                });
            },

            openEdit() {
                this.editingLid = this.viewingLid;
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'lids-view-modal' }));
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'lids-edit-modal' }));
            }
        });
    }
});
</script>

<div class="space-y-4" x-data="{ showHelp: false, ...kanbanBoard() }" x-cloak>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Воронка продаж</h1>
            <p class="text-sm text-gray-500 mt-1">Перетаскивайте карточки для смены статуса</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Help Button -->
            <button type="button"
                    @click="showHelp = !showHelp"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors shadow-sm"
                    :class="showHelp && 'bg-blue-50 text-blue-600 border-blue-200'"
                    title="Подсказки">
                <?= Icon::show('question-mark-circle', 'sm') ?>
            </button>
            <!-- View Switcher -->
            <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600" title="Kanban">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="5" height="18" rx="1" stroke-width="2"/>
                        <rect x="10" y="3" width="5" height="12" rx="1" stroke-width="2"/>
                        <rect x="17" y="3" width="5" height="15" rx="1" stroke-width="2"/>
                    </svg>
                </span>
                <a href="<?= OrganizationUrl::to(['lids/index']) ?>"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
                   title="Таблица">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                </a>
            </div>
            <button type="button"
                    @click="$dispatch('open-modal', 'lids-form-modal')"
                    class="btn btn-primary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить лид
            </button>
        </div>
    </div>

    <!-- Help Tips (Collapsible) -->
    <div x-show="showHelp" x-collapse x-cloak>
        <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
            <div class="card-body">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <?= Icon::show('light-bulb', 'md', 'text-blue-600') ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">Как работать с Kanban-доской</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-blue-800">
                            <div>
                                <p class="font-medium mb-1"><?= Icon::show('arrows-right-left', 'sm', 'inline') ?> Перетаскивание</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>Захватите карточку мышью</li>
                                    <li>Перетащите в нужную колонку</li>
                                    <li>Статус обновится автоматически</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium mb-1"><?= Icon::show('cursor-arrow-rays', 'sm', 'inline') ?> Быстрые действия</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>Клик по карточке &mdash; детали</li>
                                    <li>Hover &mdash; кнопки звонка/WA</li>
                                    <li>Стрелка &mdash; быстрая смена статуса</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium mb-1"><?= Icon::show('bell-alert', 'sm', 'inline') ?> Индикаторы</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li><span class="inline-block w-2 h-2 rounded-full bg-danger-500"></span> Красный &mdash; просрочен</li>
                                    <li><span class="inline-block w-2 h-2 rounded-full bg-warning-500"></span> Желтый &mdash; сегодня</li>
                                    <li><span class="inline-block w-2 h-2 rounded-full bg-orange-400"></span> Оранж. &mdash; долго в статусе</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium mb-1"><?= Icon::show('funnel', 'sm', 'inline') ?> Фильтры</p>
                                <ul class="space-y-1 text-blue-700">
                                    <li>Используйте чекбоксы выше</li>
                                    <li>«Мои» &mdash; только ваши лиды</li>
                                    <li>«Ещё» &mdash; расширенные фильтры</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="showHelp = false" class="text-blue-400 hover:text-blue-600">
                        <?= Icon::show('x', 'sm') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="<?= $kanbanUrl ?>" x-data="{ showAdvanced: <?= $activeFiltersCount > 3 ? 'true' : 'false' ?> }">
            <!-- Quick Filters Row -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px] max-w-sm">
                    <div class="relative">
                        <input type="text" name="search" value="<?= Html::encode($filters['search'] ?? '') ?>"
                               placeholder="Поиск по ФИО или телефону..."
                               class="form-input pl-10 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icon::show('magnifying-glass', 'sm', 'text-gray-400') ?>
                        </div>
                    </div>
                </div>

                <!-- My leads only checkbox -->
                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition-colors
                    <?= !empty($filters['my_leads_only']) ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                    <input type="checkbox" name="my_leads_only" value="1"
                           <?= !empty($filters['my_leads_only']) ? 'checked' : '' ?>
                           class="form-checkbox text-primary-600">
                    <span class="text-sm font-medium">Мои</span>
                </label>

                <!-- Overdue only checkbox -->
                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition-colors
                    <?= !empty($filters['overdue_only']) ? 'bg-danger-50 border-danger-300 text-danger-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                    <input type="checkbox" name="overdue_only" value="1"
                           <?= !empty($filters['overdue_only']) ? 'checked' : '' ?>
                           class="form-checkbox text-danger-600">
                    <?= Icon::show('exclamation-circle', 'sm') ?>
                    <span class="text-sm font-medium">Просроч.</span>
                </label>

                <!-- Contact today checkbox -->
                <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border transition-colors
                    <?= !empty($filters['contact_today']) ? 'bg-warning-50 border-warning-300 text-warning-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                    <input type="checkbox" name="contact_today" value="1"
                           <?= !empty($filters['contact_today']) ? 'checked' : '' ?>
                           class="form-checkbox text-warning-600">
                    <?= Icon::show('bell', 'sm') ?>
                    <span class="text-sm font-medium">Сегодня</span>
                </label>

                <!-- Toggle Advanced -->
                <button type="button" @click="showAdvanced = !showAdvanced"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    <?= Icon::show('adjustments-horizontal', 'sm') ?>
                    <span class="hidden sm:inline">Ещё</span>
                    <?php if ($activeFiltersCount > 3): ?>
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white text-xs font-medium">
                            <?= $activeFiltersCount - 3 ?>
                        </span>
                    <?php endif; ?>
                    <svg class="w-4 h-4 transition-transform duration-150" :class="showAdvanced && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary btn-sm">
                    <?= Icon::show('magnifying-glass', 'sm') ?>
                    Применить
                </button>

                <?php if ($activeFiltersCount > 0): ?>
                    <a href="<?= $kanbanUrl ?>" class="btn btn-outline btn-sm text-gray-500 hover:text-gray-700">
                        <?= Icon::show('x-mark', 'sm') ?>
                        <span class="hidden sm:inline">Сбросить</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Active Filters Badges -->
            <?php if ($activeFiltersCount > 0): ?>
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-400">Активные:</span>
                    <?php if (!empty($filters['my_leads_only'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-primary-100 text-primary-700 text-xs">
                            Мои лиды
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['my_leads_only' => 1]))) ?>" class="hover:text-primary-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['overdue_only'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-danger-100 text-danger-700 text-xs">
                            Просроченные
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['overdue_only' => 1]))) ?>" class="hover:text-danger-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['contact_today'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-warning-100 text-warning-700 text-xs">
                            Контакт сегодня
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['contact_today' => 1]))) ?>" class="hover:text-warning-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['stale_only'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-orange-100 text-orange-700 text-xs">
                            Долго в статусе
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['stale_only' => 1]))) ?>" class="hover:text-orange-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['show_not_target'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-xs">
                            Нецелевые
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['show_not_target' => 1]))) ?>" class="hover:text-slate-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['manager_id'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs">
                            <?= Html::encode($managers[$filters['manager_id']] ?? 'Менеджер') ?>
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['manager_id' => 1]))) ?>" class="hover:text-blue-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['source'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs">
                            <?= Html::encode(Lids::getSourceList()[$filters['source']] ?? $filters['source']) ?>
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['source' => 1]))) ?>" class="hover:text-purple-900">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['search'])): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                            "<?= Html::encode(mb_substr($filters['search'], 0, 20)) ?>"
                            <a href="<?= OrganizationUrl::to(array_merge(['lids-funnel/kanban'], array_diff_key($filters, ['search' => 1]))) ?>" class="hover:text-gray-900">&times;</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Advanced Filters -->
            <div x-show="showAdvanced" x-collapse
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:leave="transition ease-in duration-150"
                 class="mt-4 pt-4 border-t border-gray-100">

                <!-- Quick Status Filters -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="text-xs text-gray-500 self-center mr-1">Статус:</span>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-lg border text-xs transition-colors
                        <?= !empty($filters['stale_only']) ? 'bg-orange-50 border-orange-300 text-orange-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                        <input type="checkbox" name="stale_only" value="1"
                               <?= !empty($filters['stale_only']) ? 'checked' : '' ?>
                               class="form-checkbox text-orange-500 w-3.5 h-3.5">
                        <?= Icon::show('clock', 'xs') ?>
                        <span>Долго в статусе</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-lg border text-xs transition-colors
                        <?= !empty($filters['show_not_target']) ? 'bg-slate-100 border-slate-400 text-slate-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                        <input type="checkbox" name="show_not_target" value="1"
                               <?= !empty($filters['show_not_target']) ? 'checked' : '' ?>
                               class="form-checkbox text-slate-500 w-3.5 h-3.5">
                        <?= Icon::show('x-circle', 'xs') ?>
                        <span>Показать нецелевые</span>
                    </label>
                </div>

                <!-- Tags Filter -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="text-xs text-gray-500 self-center mr-1">Метки:</span>
                    <?php $selectedTags = $filters['tags'] ?? []; ?>
                    <?php foreach (LidTag::getOrganizationTags() as $tag): ?>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-lg border text-xs transition-colors
                            <?= in_array($tag->id, $selectedTags) ? 'bg-' . $tag->color . '-50 border-' . $tag->color . '-300 text-' . $tag->color . '-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                            <input type="checkbox" name="tags[]" value="<?= $tag->id ?>"
                                   <?= in_array($tag->id, $selectedTags) ? 'checked' : '' ?>
                                   class="form-checkbox text-<?= $tag->color ?>-600 w-3.5 h-3.5">
                            <?= Icon::show($tag->icon, 'xs') ?>
                            <span><?= Html::encode($tag->name) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Dropdowns Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Source -->
                    <div>
                        <label class="form-label text-xs">Источник</label>
                        <select name="source" class="form-select text-sm">
                            <option value="">Все источники</option>
                            <?php foreach (Lids::getSourceList() as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($filters['source'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= Html::encode($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Manager -->
                    <div>
                        <label class="form-label text-xs">Менеджер</label>
                        <select name="manager_id" class="form-select text-sm">
                            <option value="">Все менеджеры</option>
                            <?php foreach ($managers as $id => $name): ?>
                                <option value="<?= $id ?>" <?= (($filters['manager_id'] ?? '') == $id) ? 'selected' : '' ?>>
                                    <?= Html::encode($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Class -->
                    <div>
                        <label class="form-label text-xs">Класс</label>
                        <select name="class_id" class="form-select text-sm">
                            <option value="">Все классы</option>
                            <?php foreach (Lists::getGrades() as $id => $name): ?>
                                <option value="<?= $id ?>" <?= (($filters['class_id'] ?? '') == $id) ? 'selected' : '' ?>>
                                    <?= Html::encode($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="form-label text-xs">Создан с</label>
                        <input type="date" name="date_from" value="<?= Html::encode($filters['date_from'] ?? '') ?>"
                               class="form-input text-sm">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="form-label text-xs">Создан по</label>
                        <input type="date" name="date_to" value="<?= Html::encode($filters['date_to'] ?? '') ?>"
                               class="form-input text-sm">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-4 -mx-4 px-4" style="min-height: calc(100vh - 320px);">
        <?php foreach ($columns as $status => $column): ?>
            <?php
            // Пропускаем пустые архивные колонки, кроме Lost и In Training (они всегда показываются)
            $alwaysShowStatuses = [Lids::STATUS_LOST, Lids::STATUS_IN_TRAINING];
            if (isset($column['archive']) && $column['archive'] && empty($column['items']) && !in_array($status, $alwaysShowStatuses)) continue;
            // Специальные колонки (IN_TRAINING) показываем всегда если есть флаг special
            if (empty($column['items']) && !isset($column['archive']) && !isset($column['special']) && $status > Lids::STATUS_ENROLLED) continue;
            $color = $statusColors[$status] ?? 'gray';
            $isCollapsible = !empty($column['collapsible']);
            $isSpecial = !empty($column['special']);
            $itemCount = count($column['items']);
            ?>

            <?php if ($isCollapsible): ?>
            <!-- Collapsible Lost Column -->
            <div class="kanban-column flex-shrink-0 bg-gray-100/80 rounded-xl flex flex-col transition-all duration-300"
                 :class="lostColumnCollapsed ? 'w-14 cursor-pointer' : 'w-72'"
                 @click="lostColumnCollapsed && toggleLostColumn()"
                 data-status="<?= $status ?>"
                 @dragover.prevent="handleDragOver($event)"
                 @dragleave="handleDragLeave($event)"
                 @drop="handleDrop($event, <?= $status ?>)">

                <!-- Collapsed State -->
                <div x-show="lostColumnCollapsed" class="flex-1 flex flex-col items-center py-4">
                    <button @click.stop="toggleLostColumn()" class="p-2 hover:bg-gray-200 rounded-lg transition-colors mb-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div class="writing-mode-vertical text-sm font-semibold text-gray-600 whitespace-nowrap">
                        Отказники
                    </div>
                    <span class="mt-3 inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full bg-red-100 text-red-700 text-xs font-medium">
                        <?= $itemCount ?>
                    </span>
                </div>

                <!-- Expanded State -->
                <template x-if="!lostColumnCollapsed">
                    <div class="flex flex-col h-full">
                        <!-- Column Header -->
                        <div class="p-3 border-b border-gray-200/50 bg-red-50/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button @click.stop="toggleLostColumn()" class="p-1 hover:bg-red-100 rounded transition-colors">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                                    <h3 class="font-semibold text-gray-700"><?= Html::encode($column['label']) ?></h3>
                                </div>
                                <span class="kanban-count inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full bg-red-100 text-red-700 text-xs font-medium">
                                    <?= $itemCount ?>
                                </span>
                            </div>
                        </div>

                        <!-- Cards Container -->
                        <div class="flex-1 p-2 space-y-2 overflow-y-auto kanban-cards" data-status="<?= $status ?>">
                            <?php foreach ($column['items'] as $lid): ?>
                                <div class="kanban-card bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-all group border-l-4 border-l-red-400"
                                     draggable="true"
                                     data-id="<?= $lid->id ?>"
                                     @dragstart="handleDragStart($event, <?= $lid->id ?>)"
                                     @dragend="handleDragEnd($event)"
                                     @click="$store.lids.openView(<?= $lid->id ?>)">

                                    <!-- Card Header -->
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <span class="font-medium text-gray-900 text-sm leading-tight hover:text-primary-600">
                                            <?= Html::encode($lid->fio ?: 'Без имени') ?>
                                        </span>
                                    </div>

                                    <!-- Lost Reason -->
                                    <?php if ($lid->lost_reason): ?>
                                        <div class="text-xs text-red-600 mb-2 flex items-center gap-1">
                                            <?= Icon::show('x-circle', 'xs') ?>
                                            <?= Html::encode($lid->lost_reason) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Phone -->
                                    <?php if ($lid->getContactPhone()): ?>
                                        <div class="text-xs text-gray-500 mb-2">
                                            <?= Html::encode($lid->getContactPhone()) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Footer -->
                                    <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                                        <?php if ($lid->source): ?>
                                            <span class="text-gray-400"><?= Html::encode($lid->getSourceLabel()) ?></span>
                                        <?php else: ?>
                                            <span></span>
                                        <?php endif; ?>

                                        <?php if ($lid->manager): ?>
                                            <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-[10px] font-medium" title="<?= Html::encode($lid->manager->fio) ?>">
                                                <?= mb_substr($lid->manager->fio, 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Empty state -->
                            <div class="kanban-empty text-center py-8 text-gray-400 text-sm <?= empty($column['items']) ? '' : 'hidden' ?>">
                                Нет отказников
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <?php else: ?>
            <!-- Regular Column -->
            <div class="kanban-column flex-shrink-0 w-72 bg-gray-50 rounded-xl flex flex-col"
                 data-status="<?= $status ?>"
                 @dragover.prevent="handleDragOver($event)"
                 @dragleave="handleDragLeave($event)"
                 @drop="handleDrop($event, <?= $status ?>)">

                <!-- Column Header -->
                <div class="p-3 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-<?= $color ?>-500"></div>
                            <h3 class="font-semibold text-gray-700"><?= Html::encode($column['label']) ?></h3>
                        </div>
                        <span class="kanban-count inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full bg-<?= $color ?>-100 text-<?= $color ?>-700 text-xs font-medium">
                            <?= $itemCount ?>
                        </span>
                    </div>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 p-2 space-y-2 overflow-y-auto kanban-cards" data-status="<?= $status ?>">
                    <?php foreach ($column['items'] as $lid): ?>
                        <?php
                        // Определяем класс левой границы
                        $borderClass = '';
                        if ($lid->isVip()) {
                            $borderClass = 'border-l-4 border-l-purple-500';
                        } elseif ($lid->isOverdue()) {
                            $borderClass = 'border-l-4 border-l-danger-500';
                        } elseif ($lid->isContactToday()) {
                            $borderClass = 'border-l-4 border-l-warning-500';
                        } elseif ($lid->isStaleInStatus()) {
                            $borderClass = 'border-l-4 border-l-orange-400';
                        }
                        ?>
                        <div class="kanban-card bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-all group <?= $borderClass ?>"
                             draggable="true"
                             data-id="<?= $lid->id ?>"
                             @dragstart="handleDragStart($event, <?= $lid->id ?>)"
                             @dragend="handleDragEnd($event)"
                             @click="$store.lids.openView(<?= $lid->id ?>)">

                            <!-- Card Header with indicators -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <?php if ($lid->isHot()): ?>
                                        <span class="flex-shrink-0 text-orange-500" title="Горячий">
                                            <?= Icon::show('fire', 'xs') ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-900 text-sm leading-tight hover:text-primary-600 truncate">
                                        <?= Html::encode($lid->fio ?: 'Без имени') ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <?php if ($lid->isOverdue()): ?>
                                        <span class="w-2 h-2 rounded-full bg-danger-500" title="Просрочен контакт"></span>
                                    <?php elseif ($lid->isContactToday()): ?>
                                        <span class="w-2 h-2 rounded-full bg-warning-500" title="Контакт сегодня"></span>
                                    <?php elseif ($lid->isStaleInStatus()): ?>
                                        <span class="w-2 h-2 rounded-full bg-orange-400" title="Долго в статусе (<?= $lid->getDaysInStatus() ?> дн.)"></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tags -->
                            <?php $tags = $lid->getTags(); if (!empty($tags)): ?>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-<?= $tag['color'] ?>-100 text-<?= $tag['color'] ?>-700">
                                            <?= Html::encode($tag['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Phone -->
                            <?php if ($lid->getContactPhone()): ?>
                                <div class="text-xs text-gray-500 mb-2">
                                    <?= Html::encode($lid->getContactPhone()) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Parent info -->
                            <?php if ($lid->parent_fio): ?>
                                <div class="text-xs text-gray-400 mb-2 flex items-center gap-1">
                                    <?= Icon::show('user', 'xs') ?>
                                    <?= Html::encode($lid->parent_fio) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Footer -->
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                                <?php if ($lid->source): ?>
                                    <span class="text-gray-400"><?= Html::encode($lid->getSourceLabel()) ?></span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>

                                <div class="flex items-center gap-2">
                                    <?php if ($lid->next_contact_date): ?>
                                        <span class="<?= $lid->isOverdue() ? 'text-danger-600 font-medium' : ($lid->isContactToday() ? 'text-warning-600 font-medium' : 'text-gray-400') ?>">
                                            <?= date('d.m', strtotime($lid->next_contact_date)) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($lid->manager): ?>
                                        <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-[10px] font-medium" title="<?= Html::encode($lid->manager->fio) ?>">
                                            <?= mb_substr($lid->manager->fio, 0, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Quick Actions (on hover) -->
                            <div class="hidden group-hover:flex items-center gap-1 mt-2 pt-2 border-t border-gray-100" @click.stop>
                                <?php if ($lid->getWhatsAppUrl()): ?>
                                    <a href="<?= $lid->getWhatsAppUrl() ?>" target="_blank" class="p-1.5 text-green-500 hover:bg-green-50 rounded cursor-pointer" title="WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($lid->getContactPhone()): ?>
                                    <a href="tel:<?= Html::encode($lid->getContactPhone()) ?>" class="p-1.5 text-green-600 hover:bg-green-50 rounded cursor-pointer" title="Позвонить">
                                        <?= Icon::show('phone', 'sm') ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Quick status change dropdown -->
                                <div x-data="{ open: false }" class="relative ml-auto" @click.stop>
                                    <button @click.stop="open = !open" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded cursor-pointer" title="Сменить статус">
                                        <?= Icon::show('arrow-right-circle', 'sm') ?>
                                    </button>
                                    <div x-show="open" @click.away="open = false" @click.stop
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 bottom-full mb-1 w-36 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                        <?php foreach (Lids::getKanbanStatusList() as $st => $lbl): ?>
                                            <?php if ($st == $lid->status) continue; ?>
                                            <button type="button"
                                                    @click="quickChangeStatus(<?= $lid->id ?>, <?= $st ?>); open = false"
                                                    class="w-full px-3 py-1.5 text-left text-xs hover:bg-gray-50 cursor-pointer">
                                                <?= Html::encode($lbl) ?>
                                            </button>
                                        <?php endforeach; ?>
                                        <div class="border-t border-gray-100 mt-1 pt-1">
                                            <button type="button"
                                                    @click="openLostReasonModal(<?= $lid->id ?>); open = false"
                                                    class="w-full px-3 py-1.5 text-left text-xs text-danger-600 hover:bg-danger-50 cursor-pointer">
                                                Потерян
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Empty state - показывается/скрывается динамически -->
                    <div class="kanban-empty text-center py-8 text-gray-400 text-sm <?= empty($column['items']) ? '' : 'hidden' ?>">
                        Нет лидов
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Lost Reason Modal (inside kanbanBoard scope) -->
    <div x-show="showLostReasonModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
         @click.self="showLostReasonModal = false"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center text-danger-600">
                    <?= Icon::show('x-circle', 'md') ?>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Причина отказа</h3>
                    <p class="text-sm text-gray-500">Укажите почему лид потерян</p>
                </div>
            </div>

            <div class="space-y-3">
                <?php
                $lostReasonsModal = ['Дорого', 'Нет времени', 'Выбрал конкурента', 'Передумал', 'Не отвечает', 'Другое'];
                foreach ($lostReasonsModal as $reason): ?>
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors"
                           :class="lostReason === '<?= $reason ?>' && 'border-danger-300 bg-danger-50'">
                        <input type="radio" name="kanban_lost_reason_modal" value="<?= $reason ?>"
                               x-model="lostReason"
                               class="form-radio text-danger-600">
                        <span class="text-sm text-gray-700"><?= $reason ?></span>
                    </label>
                <?php endforeach; ?>

                <!-- Custom reason input -->
                <div x-show="lostReason === 'Другое'" x-collapse
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:leave="transition ease-in duration-100">
                    <input type="text" x-model="customLostReason"
                           placeholder="Укажите причину..."
                           class="form-input w-full mt-2">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                        @click="showLostReasonModal = false"
                        class="btn btn-secondary flex-1">
                    Отмена
                </button>
                <button type="button"
                        @click="confirmLostReason()"
                        :disabled="!lostReason || (lostReason === 'Другое' && !customLostReason)"
                        class="btn btn-danger flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                    Подтвердить
                </button>
            </div>
        </div>
    </div>

    <!-- Conversion Modal (Alpine.js) - MUST be inside x-data="kanbanBoard()" scope -->
    <?= $this->render('_conversion-modal') ?>
</div>

<!-- Create Modal -->
<?php Modal::begin(['id' => 'lids-form-modal', 'title' => 'Новый лид', 'size' => 'lg']); ?>
<?= $this->render('_form-modal', ['isEdit' => false]) ?>
<?php Modal::end(); ?>

<!-- View Modal -->
<?php Modal::begin(['id' => 'lids-view-modal', 'title' => 'Карточка лида', 'size' => 'xl']); ?>
<?= $this->render('_view-modal') ?>
<?php Modal::end(); ?>

<!-- Edit Modal -->
<?php Modal::begin(['id' => 'lids-edit-modal', 'title' => 'Редактирование лида', 'size' => 'lg']); ?>
<?= $this->render('_form-modal', ['isEdit' => true]) ?>
<?php Modal::end(); ?>

<script>
function kanbanBoard() {
    return {
        dragging: false,
        draggedId: null,
        sourceColumn: null,
        lostColumnCollapsed: localStorage.getItem('lostColumnCollapsed') !== 'false',

        // Lost reason modal state
        showLostReasonModal: false,
        lostReasonLidId: null,
        lostReason: '',
        customLostReason: '',

        // Conversion modal state (из conversionMixin)
        ...conversionMixin,

        toggleLostColumn() {
            this.lostColumnCollapsed = !this.lostColumnCollapsed;
            localStorage.setItem('lostColumnCollapsed', this.lostColumnCollapsed);
        },

        // Quick status change from card dropdown
        async quickChangeStatus(lidId, newStatus) {
            try {
                const response = await fetch('<?= $changeStatusUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: `id=${lidId}&status=${newStatus}`
                });
                const data = await response.json();
                if (data.success) {
                    // Если нужна конверсия - показываем модалку
                    if (data.needs_conversion && data.lid) {
                        this.openConversionModal(data.lid);
                        return;
                    }

                    if (Alpine.store('toast')) Alpine.store('toast').success(data.message);
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                if (Alpine.store('toast')) Alpine.store('toast').error('Ошибка сети');
            }
        },

        // Open lost reason modal
        openLostReasonModal(lidId) {
            this.lostReasonLidId = lidId;
            this.lostReason = '';
            this.customLostReason = '';
            this.showLostReasonModal = true;
        },

        // Confirm lost with reason
        async confirmLostReason() {
            const reason = this.lostReason === 'Другое' ? this.customLostReason : this.lostReason;
            if (!reason) {
                if (Alpine.store('toast')) Alpine.store('toast').error('Укажите причину отказа');
                return;
            }

            try {
                const response = await fetch('<?= $changeStatusUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: `id=${this.lostReasonLidId}&status=<?= Lids::STATUS_LOST ?>&comment=${encodeURIComponent('Причина: ' + reason)}`
                });
                const data = await response.json();
                if (data.success) {
                    if (Alpine.store('toast')) Alpine.store('toast').success('Лид отмечен как потерянный');
                    this.showLostReasonModal = false;
                    location.reload();
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                if (Alpine.store('toast')) Alpine.store('toast').error('Ошибка сети');
            }
        },

        handleDragStart(event, id) {
            this.dragging = true;
            this.draggedId = id;
            this.sourceColumn = event.target.closest('.kanban-column');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', id);
            event.target.classList.add('opacity-50', 'scale-95');
        },

        handleDragEnd(event) {
            this.dragging = false;
            this.sourceColumn = null;
            event.target.classList.remove('opacity-50', 'scale-95');
        },

        handleDragOver(event) {
            event.currentTarget.classList.add('bg-primary-50');
        },

        handleDragLeave(event) {
            event.currentTarget.classList.remove('bg-primary-50');
        },

        handleDrop(event, newStatus) {
            event.preventDefault();
            event.currentTarget.classList.remove('bg-primary-50');

            if (!this.draggedId) return;

            const card = document.querySelector(`.kanban-card[data-id="${this.draggedId}"]`);
            if (!card) return;

            const targetColumn = event.currentTarget;
            const cardsContainer = targetColumn.querySelector('.kanban-cards');

            // Перемещаем карточку перед пустым состоянием
            const emptyState = cardsContainer.querySelector('.kanban-empty');
            if (emptyState) {
                cardsContainer.insertBefore(card, emptyState);
            } else {
                cardsContainer.appendChild(card);
            }

            // Обновляем счётчики и пустые состояния
            this.updateColumnState(this.sourceColumn);
            this.updateColumnState(targetColumn);

            const draggedId = this.draggedId;
            this.draggedId = null;

            fetch('<?= $changeStatusUrl ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: `id=${draggedId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Если нужна конверсия - показываем модалку
                    if (data.needs_conversion && data.lid) {
                        this.openConversionModal(data.lid);
                        return;
                    }

                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').success(data.message);
                    }
                    if (data.pupil_id) {
                        if (confirm('Ученик создан. Перейти к заполнению данных?')) {
                            window.location.href = '<?= $pupilUpdateUrl ?>?id=' + data.pupil_id;
                        }
                    }
                } else {
                    location.reload();
                }
            })
            .catch(() => location.reload());
        },

        updateColumnState(column) {
            if (!column) return;

            const cards = column.querySelectorAll('.kanban-card');
            const count = cards.length;

            // Обновляем счётчик
            const badge = column.querySelector('.kanban-count');
            if (badge) badge.textContent = count;

            // Показываем/скрываем пустое состояние
            const emptyState = column.querySelector('.kanban-empty');
            if (emptyState) {
                if (count === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }
    };
}
</script>

<style>
.kanban-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
}
.kanban-card:active {
    cursor: grabbing;
}
.kanban-column {
    transition: background-color 0.15s ease;
}
.kanban-cards {
    min-height: 100px;
}
</style>
