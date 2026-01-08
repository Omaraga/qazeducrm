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
?>

<div class="space-y-6">
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
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
            <?= Icon::show('information-circle', 'md', 'text-blue-600 flex-shrink-0') ?>
            <div class="text-sm text-blue-800">
                <p class="font-medium">Права директора и генерального директора</p>
                <p class="mt-1">Директор и генеральный директор всегда имеют полный доступ ко всем функциям системы. Эти настройки применяются только к ролям "Администратор" и "Преподаватель".</p>
            </div>
        </div>
    </div>

    <?php $form = \yii\widgets\ActiveForm::begin([
        'id' => 'access-settings-form',
        'options' => ['class' => 'space-y-6'],
    ]); ?>

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
                        <div class="flex-1">
                            <label for="setting-<?= $key ?>" class="text-sm font-medium text-gray-900 cursor-pointer">
                                <?= Html::encode($labels[$key] ?? $key) ?>
                            </label>
                            <?php if (isset($hints[$key])): ?>
                            <p class="text-sm text-gray-500 mt-0.5"><?= Html::encode($hints[$key]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex-shrink-0">
                            <!-- Toggle Switch -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       name="settings[<?= $key ?>]"
                                       value="1"
                                       id="setting-<?= $key ?>"
                                       class="sr-only peer"
                                       <?= !empty($settings[$key]) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Save Button -->
    <div class="flex justify-end gap-3">
        <a href="<?= OrganizationUrl::to(['default/index']) ?>" class="btn btn-secondary">
            Отмена
        </a>
        <button type="submit" class="btn btn-primary">
            <?= Icon::show('check', 'sm') ?>
            Сохранить настройки
        </button>
    </div>

    <?php \yii\widgets\ActiveForm::end(); ?>

    <!-- Last Updated Info -->
    <?php if ($model->updated_at && $model->updatedByUser): ?>
    <div class="text-sm text-gray-500 text-center">
        Последнее обновление: <?= date('d.m.Y H:i', strtotime($model->updated_at)) ?>
        пользователем <?= Html::encode($model->updatedByUser->fio ?? $model->updatedByUser->username) ?>
    </div>
    <?php endif; ?>
</div>
