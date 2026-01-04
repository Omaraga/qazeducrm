<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TeacherSalary $model */

$this->title = 'Редактирование: ' . ($model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id);
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => OrganizationUrl::to(['salary/index'])];
$this->params['breadcrumbs'][] = ['label' => $model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id, 'url' => OrganizationUrl::to(['salary/view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = 'Редактирование';

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const baseAmount = {$model->base_amount};
    const bonusInput = document.querySelector('input[name="bonus_amount"]');
    const deductionInput = document.querySelector('input[name="deduction_amount"]');
    const totalPreview = document.getElementById('total-preview');

    function updateTotal() {
        const bonus = parseFloat(bonusInput.value) || 0;
        const deduction = parseFloat(deductionInput.value) || 0;
        const total = baseAmount + bonus - deduction;
        totalPreview.textContent = new Intl.NumberFormat('ru-RU').format(total) + ' ₸';
    }

    bonusInput.addEventListener('input', updateTotal);
    deductionInput.addEventListener('input', updateTotal);
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= $model->getPeriodLabel() ?></p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['salary/view', 'id' => $model->id]) ?>" class="btn btn-secondary">
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
                <h3 class="text-lg font-semibold text-gray-900">Бонусы и вычеты</h3>
            </div>
            <div class="card-body">
                <form method="post" class="space-y-4">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-primary-700">Период:</dt>
                                <dd class="font-medium text-primary-900"><?= $model->getPeriodLabel() ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-primary-700">Базовая сумма:</dt>
                                <dd class="font-medium text-primary-900"><?= number_format($model->base_amount, 0, ',', ' ') ?> ₸</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <label class="form-label">Бонусы</label>
                        <div class="relative">
                            <input type="number" name="bonus_amount" class="form-input pr-12"
                                   value="<?= $model->bonus_amount ?>" min="0" step="100">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">₸</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Дополнительные начисления (за качество, переработку и т.д.)</p>
                    </div>

                    <div>
                        <label class="form-label">Вычеты</label>
                        <div class="relative">
                            <input type="number" name="deduction_amount" class="form-input pr-12"
                                   value="<?= $model->deduction_amount ?>" min="0" step="100">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">₸</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Удержания (штрафы, авансы и т.д.)</p>
                    </div>

                    <div>
                        <label class="form-label">Примечания</label>
                        <textarea name="notes" rows="3" class="form-input"><?= Html::encode($model->notes) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-500">Итого:</span>
                            <span class="text-xl font-bold text-primary-600 ml-2" id="total-preview">
                                <?= number_format($model->total_amount, 0, ',', ' ') ?> ₸
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Информация</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Преподаватель</dt>
                        <dd class="text-sm text-gray-900"><?= $model->teacher ? Html::encode($model->teacher->fio) : 'Не указан' ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Уроков</dt>
                        <dd class="text-sm text-gray-900"><?= $model->lessons_count ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Учеников с оплатой</dt>
                        <dd class="text-sm text-gray-900"><?= $model->students_count ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Создано</dt>
                        <dd class="text-sm text-gray-900"><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
