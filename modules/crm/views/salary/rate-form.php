<?php

use app\helpers\OrganizationUrl;
use app\models\TeacherRate;
use app\models\Subject;
use app\models\Group;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\TeacherRate $model */
/** @var app\models\User[] $teachers */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Добавить ставку' : 'Редактировать ставку';
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => OrganizationUrl::to(['salary/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Ставки учителей', 'url' => OrganizationUrl::to(['salary/rates'])];
$this->params['breadcrumbs'][] = $this->title;

$subjects = Subject::find()->byOrganization()->andWhere(['!=', 'is_deleted', 1])->all();
$groups = Group::find()
    ->andWhere(['organization_id' => \app\models\Organizations::getCurrentOrganizationId()])
    ->andWhere(['!=', 'is_deleted', 1])
    ->all();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">
                <?= $isNew ? 'Создание новой ставки для преподавателя' : 'Изменение параметров ставки' ?>
            </p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['salary/rates']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Параметры ставки</h3>
            </div>
            <div class="card-body">
                <form method="post" class="space-y-4">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div>
                        <label class="form-label">Преподаватель <span class="text-danger-500">*</span></label>
                        <select name="TeacherRate[teacher_id]" class="form-select" required>
                            <option value="">Выберите преподавателя...</option>
                            <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher->id ?>" <?= $model->teacher_id == $teacher->id ? 'selected' : '' ?>>
                                <?= Html::encode($teacher->fio) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($model->hasErrors('teacher_id')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('teacher_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="form-label">Тип ставки <span class="text-danger-500">*</span></label>
                        <select name="TeacherRate[rate_type]" id="rate-type" class="form-select" required>
                            <?php foreach (TeacherRate::getRateTypeList() as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $model->rate_type == $value ? 'selected' : '' ?>>
                                <?= Html::encode($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Значение ставки <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="TeacherRate[rate_value]" class="form-input pr-12"
                                   value="<?= $model->rate_value ?>" min="0" step="any" required>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500" id="rate-suffix">₸</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500" id="rate-hint">Сумма в тенге</p>
                        <?php if ($model->hasErrors('rate_value')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('rate_value') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Область применения (опционально)</h4>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Предмет</label>
                                <select name="TeacherRate[subject_id]" class="form-select">
                                    <option value="">Все предметы</option>
                                    <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject->id ?>" <?= $model->subject_id == $subject->id ? 'selected' : '' ?>>
                                        <?= Html::encode($subject->name) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Оставьте пустым для применения ко всем предметам</p>
                            </div>

                            <div>
                                <label class="form-label">Группа</label>
                                <select name="TeacherRate[group_id]" class="form-select">
                                    <option value="">Все группы</option>
                                    <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group->id ?>" <?= $model->group_id == $group->id ? 'selected' : '' ?>>
                                        <?= Html::encode($group->name) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Оставьте пустым для применения ко всем группам</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="hidden" name="TeacherRate[is_active]" value="0">
                                <input type="checkbox" name="TeacherRate[is_active]" id="is_active" value="1"
                                       class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                       <?= $model->is_active ? 'checked' : '' ?>>
                                <label for="is_active" class="text-sm text-gray-700">Активна</label>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                        <a href="<?= OrganizationUrl::to(['salary/rates']) ?>" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= $isNew ? 'Создать' : 'Сохранить' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Приоритет ставок</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-500 mb-4">При расчёте зарплаты система ищет ставку в следующем порядке:</p>
                    <ol class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-bold">1</span>
                            <div>
                                <span class="font-medium text-gray-900">Ставка для конкретной группы</span>
                                <p class="text-xs text-gray-500">Наивысший приоритет</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-bold">2</span>
                            <div>
                                <span class="font-medium text-gray-900">Ставка для предмета</span>
                                <p class="text-xs text-gray-500">Средний приоритет</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-bold">3</span>
                            <div>
                                <span class="font-medium text-gray-900">Общая ставка учителя</span>
                                <p class="text-xs text-gray-500">Базовая ставка</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <div>
                        <h4 class="font-medium text-primary-900">Рекомендация</h4>
                        <p class="text-sm text-primary-800 mt-1">
                            Создайте общую ставку для каждого учителя, а затем добавляйте специфичные ставки для отдельных групп или предметов при необходимости.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Examples Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Примеры расчёта</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-700">За ученика (500 ₸)</div>
                        <div class="text-xs text-gray-500 mt-1">5 учеников × 500 ₸ = <strong>2 500 ₸</strong> за урок</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-700">За урок (3 000 ₸)</div>
                        <div class="text-xs text-gray-500 mt-1">Фикс <strong>3 000 ₸</strong> независимо от учеников</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-700">Процент (30%)</div>
                        <div class="text-xs text-gray-500 mt-1">30% от 10 000 ₸ = <strong>3 000 ₸</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rateTypeSelect = document.getElementById('rate-type');
    const rateSuffix = document.getElementById('rate-suffix');
    const rateHint = document.getElementById('rate-hint');

    function updateRateUI() {
        const type = parseInt(rateTypeSelect.value);
        if (type === 3) { // Процент
            rateSuffix.textContent = '%';
            rateHint.textContent = 'Процент от оплаты ученика';
        } else {
            rateSuffix.textContent = '₸';
            rateHint.textContent = type === 1 ? 'Сумма за каждого ученика' : 'Сумма за урок';
        }
    }

    rateTypeSelect.addEventListener('change', updateRateUI);
    updateRateUI();
});
</script>
