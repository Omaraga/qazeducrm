<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\OrganizationAccessSettings $model */
/** @var array $settings */
/** @var array $groups */
/** @var array $labels */
/** @var array $hints */

$this->title = 'Настройки доступа';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => OrganizationUrl::to(['settings/index'])];
$this->params['breadcrumbs'][] = $this->title;

$saveUrl = OrganizationUrl::to(['settings/ajax-save-setting']);
?>

<div class="space-y-6" x-data="accessSettings()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                <?= Icon::show('shield-check', 'md', 'text-primary-600') ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Настройка прав доступа для ролей</p>
            </div>
        </div>
        <!-- Статус сохранения -->
        <div x-show="saveStatus" x-transition class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-full"
             :class="{
                'bg-gray-100 text-gray-600': saveStatus === 'saving',
                'bg-success-100 text-success-700': saveStatus === 'success',
                'bg-danger-100 text-danger-700': saveStatus === 'error'
             }">
            <template x-if="saveStatus === 'saving'">
                <span class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Сохранение...
                </span>
            </template>
            <template x-if="saveStatus === 'success'">
                <span class="flex items-center gap-2">
                    <?= Icon::show('check', 'sm') ?>
                    Сохранено
                </span>
            </template>
            <template x-if="saveStatus === 'error'">
                <span class="flex items-center gap-2">
                    <?= Icon::show('x-mark', 'sm') ?>
                    Ошибка
                </span>
            </template>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
            <?= Icon::show('info', 'md', 'text-blue-600 flex-shrink-0') ?>
            <div class="text-sm text-blue-800">
                <p class="font-medium">Права директора и генерального директора</p>
                <p class="mt-1">Директор и генеральный директор всегда имеют полный доступ ко всем функциям системы. Эти настройки применяются только к ролям "Администратор" и "Преподаватель".</p>
            </div>
        </div>
    </div>

    <?php foreach ($groups as $groupKey => $group): ?>
    <!-- <?= Html::encode($group['label']) ?> -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <?php if ($groupKey === 'admin'): ?>
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <?= Icon::show('user-circle', 'md', 'text-orange-600') ?>
                    </div>
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <?= Icon::show('academic-cap', 'md', 'text-green-600') ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900"><?= Html::encode($group['label']) ?></h2>
                    <p class="text-sm text-gray-500"><?= Html::encode($group['description']) ?></p>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php foreach ($group['settings'] as $subgroupName => $settingKeys): ?>
            <div class="border-b border-gray-100 last:border-b-0">
                <div class="px-6 py-3 bg-gray-50">
                    <h3 class="text-sm font-medium text-gray-700"><?= Html::encode($subgroupName) ?></h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($settingKeys as $key): ?>
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">
                                <?= Html::encode($labels[$key] ?? $key) ?>
                            </div>
                            <?php if (isset($hints[$key])): ?>
                            <p class="text-sm text-gray-500 mt-0.5"><?= Html::encode($hints[$key]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex-shrink-0">
                            <!-- Radio-style Toggle -->
                            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-100">
                                <button type="button"
                                        @click="setSetting('<?= $key ?>', false)"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                        :class="!settings['<?= $key ?>']
                                            ? 'bg-white text-gray-900 shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700'">
                                    Нет
                                </button>
                                <button type="button"
                                        @click="setSetting('<?= $key ?>', true)"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                        :class="settings['<?= $key ?>']
                                            ? 'bg-primary-600 text-white shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700'">
                                    Да
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Last Updated Info -->
    <?php if ($model->updated_at && $model->updatedByUser): ?>
    <div class="text-sm text-gray-500 text-center">
        Последнее обновление: <?= date('d.m.Y H:i', strtotime($model->updated_at)) ?>
        пользователем <?= Html::encode($model->updatedByUser->fio ?? $model->updatedByUser->username) ?>
    </div>
    <?php endif; ?>
</div>

<script>
function accessSettings() {
    return {
        settings: <?= json_encode(array_map(function($v) { return (bool)$v; }, $settings)) ?>,
        saveStatus: null,
        saveTimeout: null,

        async setSetting(key, value) {
            // Если значение не изменилось - ничего не делаем
            if (this.settings[key] === value) {
                return;
            }

            // Обновляем значение
            this.settings[key] = value;

            // Показываем статус сохранения
            this.saveStatus = 'saving';

            try {
                const response = await fetch('<?= $saveUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: new URLSearchParams({
                        key: key,
                        value: value ? '1' : '0'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.saveStatus = 'success';
                } else {
                    // Откатываем изменение
                    this.settings[key] = !value;
                    this.saveStatus = 'error';
                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').error(data.message || 'Ошибка сохранения');
                    }
                }
            } catch (error) {
                console.error('Error saving setting:', error);
                // Откатываем изменение
                this.settings[key] = !value;
                this.saveStatus = 'error';
                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    Alpine.store('toast').error('Ошибка сети');
                }
            }

            // Скрываем статус через 2 секунды
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveStatus = null;
            }, 2000);
        }
    };
}
</script>
