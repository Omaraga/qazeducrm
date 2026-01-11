<?php

/**
 * Список пробных занятий
 *
 * @var yii\web\View $this
 * @var app\models\search\TrialLessonSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $stats
 * @var app\models\TrialLesson[] $todayTrials
 */

use app\helpers\OrganizationUrl;
use app\models\TrialLesson;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Пробные занятия');
$this->params['breadcrumbs'][] = $this->title;

$hasFilters = $searchModel->status || $searchModel->date_from || $searchModel->date_to || $searchModel->lid_name;

// Подключаем Flatpickr
$this->registerCssFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="space-y-4">
    <!-- Header + Stats Row -->
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-sm text-gray-500"><?= Yii::t('app', 'Управление пробными занятиями') ?></p>
            </div>
            <?= Html::a(Icon::show('plus', 'sm') . ' ' . Yii::t('app', 'Записать'), OrganizationUrl::to(['trial/create']), ['class' => 'btn btn-primary btn-sm']) ?>
        </div>

        <!-- Статистика - inline блоки -->
        <div class="flex flex-wrap gap-3">
            <div class="inline-flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                <span class="text-xs text-gray-500"><?= Yii::t('app', 'Всего') ?></span>
                <span class="text-lg font-semibold text-gray-900"><?= $stats['total'] ?></span>
            </div>
            <div class="inline-flex items-center gap-2 bg-green-50 rounded-lg px-3 py-2">
                <span class="text-xs text-green-600"><?= Yii::t('app', 'Проведено') ?></span>
                <span class="text-lg font-semibold text-green-700"><?= $stats['completed'] ?></span>
            </div>
            <div class="inline-flex items-center gap-2 bg-purple-50 rounded-lg px-3 py-2">
                <span class="text-xs text-purple-600"><?= Yii::t('app', 'Конверт.') ?></span>
                <span class="text-lg font-semibold text-purple-700"><?= $stats['converted'] ?></span>
            </div>
            <div class="inline-flex items-center gap-2 bg-red-50 rounded-lg px-3 py-2">
                <span class="text-xs text-red-600"><?= Yii::t('app', 'Не пришли') ?></span>
                <span class="text-lg font-semibold text-red-700"><?= $stats['no_show'] ?></span>
            </div>
            <div class="inline-flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                <span class="text-xs text-gray-500"><?= Yii::t('app', 'Отмена') ?></span>
                <span class="text-lg font-semibold text-gray-600"><?= $stats['cancelled'] ?></span>
            </div>
            <div class="inline-flex items-center gap-2 bg-blue-50 rounded-lg px-3 py-2">
                <span class="text-xs text-blue-600"><?= Yii::t('app', 'Конверсия') ?></span>
                <span class="text-lg font-semibold text-blue-700"><?= $stats['conversion_rate'] ?>%</span>
            </div>
            <div class="inline-flex items-center gap-2 bg-orange-50 rounded-lg px-3 py-2">
                <span class="text-xs text-orange-600"><?= Yii::t('app', 'Неявки') ?></span>
                <span class="text-lg font-semibold text-orange-700"><?= $stats['no_show_rate'] ?>%</span>
            </div>
        </div>
    </div>

    <!-- Пробные на сегодня -->
    <?php if (!empty($todayTrials)): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800">
                <?= Icon::show('clock', 'sm', 'text-amber-600') ?>
                <?= Yii::t('app', 'Сегодня') ?>:
            </span>
            <?php foreach ($todayTrials as $trial): ?>
                <a href="<?= OrganizationUrl::to(['trial/view', 'id' => $trial->id]) ?>"
                   class="inline-flex items-center gap-1.5 bg-white rounded px-2 py-1 text-sm border border-amber-200 hover:border-amber-400">
                    <span class="font-medium text-gray-900"><?= substr($trial->time, 0, 5) ?></span>
                    <span class="text-gray-600"><?= Html::encode($trial->getLidName()) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Фильтры -->
    <form method="get" class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div style="width: 140px;">
                <label class="block text-xs text-gray-500 mb-1"><?= Yii::t('app', 'Статус') ?></label>
                <?= Html::activeDropDownList($searchModel, 'status', TrialLesson::getStatusList(), [
                    'class' => 'form-select',
                    'prompt' => Yii::t('app', 'Все'),
                    'style' => 'height: 36px; font-size: 14px;'
                ]) ?>
            </div>
            <div style="width: 140px;">
                <label class="block text-xs text-gray-500 mb-1"><?= Yii::t('app', 'Дата с') ?></label>
                <div class="relative">
                    <?= Html::activeTextInput($searchModel, 'date_from', [
                        'class' => 'form-input date-picker',
                        'placeholder' => 'дд.мм.гггг',
                        'readonly' => true,
                        'style' => 'height: 36px; font-size: 14px; cursor: pointer; background: white;'
                    ]) ?>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div style="width: 140px;">
                <label class="block text-xs text-gray-500 mb-1"><?= Yii::t('app', 'Дата по') ?></label>
                <div class="relative">
                    <?= Html::activeTextInput($searchModel, 'date_to', [
                        'class' => 'form-input date-picker',
                        'placeholder' => 'дд.мм.гггг',
                        'readonly' => true,
                        'style' => 'height: 36px; font-size: 14px; cursor: pointer; background: white;'
                    ]) ?>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div style="flex: 1; min-width: 180px; max-width: 280px;">
                <label class="block text-xs text-gray-500 mb-1"><?= Yii::t('app', 'Поиск') ?></label>
                <?= Html::activeTextInput($searchModel, 'lid_name', [
                    'class' => 'form-input',
                    'placeholder' => Yii::t('app', 'Имя или телефон'),
                    'style' => 'height: 36px; font-size: 14px;'
                ]) ?>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm" style="height: 36px;">
                    <?= Icon::show('search', 'sm') ?>
                </button>
                <?php if ($hasFilters): ?>
                <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary btn-sm" style="height: 36px;">
                    <?= Icon::show('x', 'sm') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Таблица -->
    <?php if (count($dataProvider->getModels()) > 0): ?>
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= Yii::t('app', 'Дата') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= Yii::t('app', 'Лид') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= Yii::t('app', 'Группа') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= Yii::t('app', 'Статус') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= Yii::t('app', 'Оценка') ?></th>
                        <th class="px-4 py-3 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <?php /** @var TrialLesson $model */ ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= date('d.m.Y', strtotime($model->date)) ?></div>
                                <div class="text-xs text-gray-500"><?= substr($model->time, 0, 5) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($model->lid): ?>
                                    <a href="<?= OrganizationUrl::to(['/crm/lids/view', 'id' => $model->lid_id]) ?>" class="text-sm font-medium text-gray-900 hover:text-primary-600">
                                        <?= Html::encode($model->getLidName()) ?>
                                    </a>
                                    <?php if ($phone = $model->getLidPhone()): ?>
                                        <div class="text-xs text-gray-500"><?= Html::encode($phone) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400"><?= Yii::t('app', 'Лид удалён') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?= $model->group ? Html::encode($model->group->name) : '—' ?></span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $model->getStatusClass() ?>">
                                    <?= $model->getStatusLabel() ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if ($model->rating): ?>
                                    <div class="flex items-center gap-0.5">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="w-3 h-3 <?= $i <= $model->rating ? 'text-yellow-400' : 'text-gray-200' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="<?= OrganizationUrl::to(['trial/view', 'id' => $model->id]) ?>" class="text-gray-400 hover:text-primary-600">
                                    <?= Icon::show('chevron-right', 'sm') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg border border-gray-200 py-12 text-center">
        <?= Icon::show('calendar', 'xl', 'mx-auto text-gray-300') ?>
        <p class="mt-3 text-gray-600 font-medium"><?= Yii::t('app', 'Пробные занятия не найдены') ?></p>
        <p class="text-sm text-gray-400 mt-1"><?= Yii::t('app', 'Запишите лида на первое пробное занятие') ?></p>
        <?= Html::a(Icon::show('plus', 'sm') . ' ' . Yii::t('app', 'Записать'), OrganizationUrl::to(['trial/create']), ['class' => 'btn btn-primary btn-sm mt-4']) ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
    <div class="flex justify-center">
        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.date-picker', {
            locale: 'ru',
            dateFormat: 'd.m.Y',
            allowInput: true,
            disableMobile: true
        });
    }
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
