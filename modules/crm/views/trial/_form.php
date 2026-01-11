<?php

/**
 * Форма пробного занятия
 *
 * @var yii\web\View $this
 * @var app\models\TrialLesson $model
 * @var app\models\Lids[] $lids
 * @var app\models\Group[] $groups
 */

use app\helpers\OrganizationUrl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$lidItems = ArrayHelper::map($lids ?? [], 'id', function ($lid) {
    $name = $lid->fio ?: $lid->parent_fio ?: Yii::t('app', 'Без имени');
    $phone = $lid->phone ?: '';
    return $name . ($phone ? " ({$phone})" : '');
});

$groupItems = ArrayHelper::map($groups ?? [], 'id', 'name');

// Форматируем дату для отображения
$dateValue = $model->date ? date('d.m.Y', strtotime($model->date)) : date('d.m.Y');
$timeValue = $model->time ? substr($model->time, 0, 5) : '10:00';

// Подключаем Flatpickr
$this->registerCssFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="card">
    <form method="post" class="card-body space-y-4">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (!$model->isNewRecord || empty($lids)): ?>
                <?php if ($model->lid): ?>
                    <div class="md:col-span-2">
                        <label class="form-label"><?= Yii::t('app', 'Лид') ?></label>
                        <div class="text-gray-900 py-2">
                            <?= Html::encode($model->getLidName()) ?>
                            <?php if ($model->getLidPhone()): ?>
                                <span class="text-gray-500">(<?= $model->getLidPhone() ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="md:col-span-2">
                    <label class="form-label" for="triallesson-lid_id"><?= Yii::t('app', 'Лид') ?> <span class="text-danger-500">*</span></label>
                    <?= Html::activeDropDownList($model, 'lid_id', $lidItems, [
                        'prompt' => Yii::t('app', 'Выберите лида'),
                        'class' => 'form-select',
                        'id' => 'triallesson-lid_id',
                    ]) ?>
                    <?php if ($model->hasErrors('lid_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('lid_id') ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div>
                <label class="form-label" for="triallesson-date"><?= Yii::t('app', 'Дата') ?> <span class="text-danger-500">*</span></label>
                <div class="relative">
                    <?= Html::activeTextInput($model, 'date', [
                        'class' => 'form-input date-picker',
                        'id' => 'triallesson-date',
                        'value' => $dateValue,
                        'readonly' => true,
                        'style' => 'cursor: pointer; background: white;'
                    ]) ?>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <?php if ($model->hasErrors('date')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date') ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="form-label" for="triallesson-time"><?= Yii::t('app', 'Время') ?> <span class="text-danger-500">*</span></label>
                <div class="relative">
                    <?= Html::activeTextInput($model, 'time', [
                        'class' => 'form-input time-picker',
                        'id' => 'triallesson-time',
                        'value' => $timeValue,
                        'readonly' => true,
                        'style' => 'cursor: pointer; background: white;'
                    ]) ?>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <?php if ($model->hasErrors('time')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('time') ?></p>
                <?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="form-label" for="triallesson-group_id"><?= Yii::t('app', 'Группа') ?></label>
                <?= Html::activeDropDownList($model, 'group_id', $groupItems, [
                    'prompt' => Yii::t('app', 'Без группы (индивидуально)'),
                    'class' => 'form-select',
                    'id' => 'triallesson-group_id',
                ]) ?>
                <p class="mt-1 text-sm text-gray-500"><?= Yii::t('app', 'Выберите группу, если пробное будет в рамках существующей группы') ?></p>
                <?php if ($model->hasErrors('group_id')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('group_id') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="<?= OrganizationUrl::to(['trial/index']) ?>" class="btn btn-secondary">
                <?= Yii::t('app', 'Отмена') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?= $model->isNewRecord ? Yii::t('app', 'Записать на пробное') : Yii::t('app', 'Сохранить') ?>
            </button>
        </div>
    </form>
</div>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.date-picker', {
            locale: 'ru',
            dateFormat: 'd.m.Y',
            allowInput: false,
            disableMobile: true
        });

        flatpickr('.time-picker', {
            locale: 'ru',
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minuteIncrement: 15,
            allowInput: false,
            disableMobile: true
        });
    }
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
