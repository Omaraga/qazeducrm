<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Tariff;
use app\models\relations\TariffSubject;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\forms\TariffForm $model */

$canManage = Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR) || Yii::$app->user->can(SystemRoles::SUPER);
?>

<form action="" method="post" class="space-y-6">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Main Data Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="tariff-name">
                        Наименование <span class="text-danger-500">*</span>
                    </label>
                    <?= Html::activeTextInput($model, 'name', [
                        'class' => 'form-input',
                        'id' => 'tariff-name',
                        'placeholder' => 'Название тарифа'
                    ]) ?>
                    <?php if ($model->hasErrors('name')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="tariff-duration">
                        Продолжительность <span class="text-danger-500">*</span>
                    </label>
                    <?= Html::activeDropDownList($model, 'duration', Lists::getTariffDurations(), [
                        'class' => 'form-select',
                        'id' => 'tariff-duration'
                    ]) ?>
                    <?php if ($model->hasErrors('duration')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('duration') ?></p>
                    <?php endif; ?>
                </div>
                <div id="lesson_amount_block" class="<?= $model->duration != 3 ? 'hidden' : '' ?>">
                    <label class="form-label" for="tariff-lesson_amount">Кол-во занятий</label>
                    <?= Html::activeTextInput($model, 'lesson_amount', [
                        'class' => 'form-input',
                        'id' => 'tariff-lesson_amount',
                        'type' => 'number',
                        'min' => 0,
                        'placeholder' => '0'
                    ]) ?>
                    <?php if ($model->hasErrors('lesson_amount')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('lesson_amount') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="tariff-type">
                        Тип тарифа <span class="text-danger-500">*</span>
                    </label>
                    <?= Html::activeDropDownList($model, 'type', Lists::getTariffTypes(), [
                        'class' => 'form-select',
                        'id' => 'tariff-type'
                    ]) ?>
                    <?php if ($model->hasErrors('type')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('type') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="tariff-price">Стоимость (₸)</label>
                    <?= Html::activeTextInput($model, 'price', [
                        'class' => 'form-input',
                        'id' => 'tariff-price',
                        'type' => 'number',
                        'min' => 0,
                        'placeholder' => '0'
                    ]) ?>
                    <?php if ($model->hasErrors('price')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('price') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="tariff-status">Статус</label>
                    <?= Html::activeDropDownList($model, 'status', Tariff::getStatusList(), [
                        'class' => 'form-select',
                        'id' => 'tariff-status'
                    ]) ?>
                </div>
            </div>
            <div>
                <label class="form-label" for="tariff-description">Описание</label>
                <?= Html::activeTextarea($model, 'description', [
                    'class' => 'form-input',
                    'id' => 'tariff-description',
                    'rows' => 4,
                    'placeholder' => 'Описание тарифного плана'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Subjects Card -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Предметы') ?></h3>
            <button type="button" id="add-subject" class="btn btn-sm btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Добавить предмет
            </button>
        </div>
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="subject-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('main', 'Предмет') ?></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('main', 'Количество занятий в неделю') ?></th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="subjects-tbody">
                        <?php foreach ($model->subjects ?: [new TariffSubject()] as $k => $subject): ?>
                        <tr class="subject-block">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="td-number text-sm text-gray-500"><?= $k + 1 ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?= Html::dropDownList("TariffForm[subjects][$k][subject_id]", $subject->subject_id ?? null, Tariff::getSubjectsMap(), [
                                    'class' => 'form-select'
                                ]) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?= Html::dropDownList("TariffForm[subjects][$k][lesson_amount]", $subject->lesson_amount ?? 1, Tariff::getAmounts(), [
                                    'class' => 'form-select'
                                ]) ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="remove-subject text-gray-400 hover:text-danger-600 transition-colors" title="Удалить">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <?php if ($canManage): ?>
    <div class="flex items-center gap-3">
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <?= Yii::t('main', 'Сохранить') ?>
        </button>
        <a href="<?= OrganizationUrl::to(['tariff/index']) ?>" class="btn btn-secondary">
            Отмена
        </a>
    </div>
    <?php endif; ?>
</form>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const durationSelect = document.getElementById('tariff-duration');
    const lessonAmountBlock = document.getElementById('lesson_amount_block');
    const addSubjectBtn = document.getElementById('add-subject');
    const subjectsTbody = document.getElementById('subjects-tbody');

    // Toggle lesson amount visibility
    function toggleLessonAmount() {
        if (durationSelect.value === '3') {
            lessonAmountBlock.classList.remove('hidden');
        } else {
            lessonAmountBlock.classList.add('hidden');
        }
    }

    durationSelect.addEventListener('change', toggleLessonAmount);

    // Add subject row
    addSubjectBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const rows = subjectsTbody.querySelectorAll('.subject-block');
        const lastRow = rows[rows.length - 1];
        const newRow = lastRow.cloneNode(true);
        const newIndex = rows.length;

        // Update row number
        newRow.querySelector('.td-number').textContent = newIndex + 1;

        // Update input names
        newRow.querySelectorAll('select[name], input[name]').forEach(function(el) {
            el.name = el.name.replace(/TariffForm\[subjects\]\[\d+\]/, 'TariffForm[subjects][' + newIndex + ']');
        });

        // Reset values
        newRow.querySelectorAll('select').forEach(function(sel) {
            sel.selectedIndex = 0;
        });

        subjectsTbody.appendChild(newRow);
        updateRemoveButtons();
    });

    // Remove subject row
    function updateRemoveButtons() {
        const rows = subjectsTbody.querySelectorAll('.subject-block');
        rows.forEach(function(row, index) {
            const removeBtn = row.querySelector('.remove-subject');
            if (rows.length <= 1) {
                removeBtn.style.visibility = 'hidden';
            } else {
                removeBtn.style.visibility = 'visible';
            }
            row.querySelector('.td-number').textContent = index + 1;
        });
    }

    subjectsTbody.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-subject');
        if (removeBtn) {
            e.preventDefault();
            const rows = subjectsTbody.querySelectorAll('.subject-block');
            if (rows.length > 1) {
                removeBtn.closest('.subject-block').remove();
                updateRemoveButtons();
                // Re-index remaining rows
                subjectsTbody.querySelectorAll('.subject-block').forEach(function(row, index) {
                    row.querySelectorAll('select[name], input[name]').forEach(function(el) {
                        el.name = el.name.replace(/TariffForm\[subjects\]\[\d+\]/, 'TariffForm[subjects][' + index + ']');
                    });
                });
            }
        }
    });

    updateRemoveButtons();
});
JS;
$this->registerJs($js);
?>
