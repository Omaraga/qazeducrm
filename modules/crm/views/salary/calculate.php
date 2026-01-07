<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User[] $teachers */
/** @var string $defaultStart */
/** @var string $defaultEnd */

$this->title = 'Расчёт зарплаты';
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => OrganizationUrl::to(['salary/index'])];
$this->params['breadcrumbs'][] = $this->title;

$periods = [
    [
        'label' => 'Текущий месяц',
        'start' => date('Y-m-01'),
        'end' => date('Y-m-d'),
    ],
    [
        'label' => 'Прошлый месяц',
        'start' => date('Y-m-01', strtotime('-1 month')),
        'end' => date('Y-m-t', strtotime('-1 month')),
    ],
    [
        'label' => 'Последние 2 недели',
        'start' => date('Y-m-d', strtotime('-2 weeks')),
        'end' => date('Y-m-d'),
    ],
];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Создание расчёта зарплаты за период</p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['salary/index']) ?>" class="btn btn-secondary">
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
                <h3 class="text-lg font-semibold text-gray-900">Параметры расчёта</h3>
            </div>
            <div class="card-body">
                <form method="post" class="space-y-4">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div>
                        <label class="form-label">Преподаватель <span class="text-danger-500">*</span></label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">Выберите преподавателя...</option>
                            <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher->id ?>"><?= Html::encode($teacher->fio) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Начало периода <span class="text-danger-500">*</span></label>
                            <input type="date" name="period_start" id="period_start" class="form-input"
                                   value="<?= date('Y-m-d', strtotime($defaultStart)) ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Конец периода <span class="text-danger-500">*</span></label>
                            <input type="date" name="period_end" id="period_end" class="form-input"
                                   value="<?= date('Y-m-d', strtotime($defaultEnd)) ?>" required>
                        </div>
                    </div>

                    <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-primary-800">
                                Будут учтены только завершённые уроки с выставленной посещаемостью.
                                Ставка берётся из настроек для учителя.
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Рассчитать зарплату
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick periods & Help -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Быстрый выбор периода</h3>
                </div>
                <div class="card-body space-y-2">
                    <?php foreach ($periods as $period): ?>
                    <button type="button" class="quick-period w-full text-left p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors"
                            data-start="<?= $period['start'] ?>"
                            data-end="<?= $period['end'] ?>">
                        <span class="font-medium text-gray-900"><?= $period['label'] ?></span>
                        <span class="block text-sm text-gray-500 mt-1">
                            <?= date('d.m.Y', strtotime($period['start'])) ?> — <?= date('d.m.Y', strtotime($period['end'])) ?>
                        </span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Алгоритм расчёта</h3>
                </div>
                <div class="card-body">
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-medium">1</span>
                            <span>Система находит все завершённые уроки преподавателя за период</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-medium">2</span>
                            <span>Для каждого урока считает учеников со статусом "Посещение" или "Пропуск с оплатой"</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-medium">3</span>
                            <span>Применяет ставку преподавателя (за ученика, за урок или процент)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-primary-100 text-primary-700 rounded-full text-xs flex items-center justify-center font-medium">4</span>
                            <span>Суммирует все начисления</span>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Example Card -->
            <div class="card bg-green-50 border-green-200">
                <div class="card-header border-green-200">
                    <h3 class="text-lg font-semibold text-green-800">Пример расчёта</h3>
                </div>
                <div class="card-body">
                    <div class="text-sm text-green-700 space-y-2">
                        <p><strong>Дано:</strong> ставка 500 ₸/ученик, 10 уроков за месяц</p>
                        <p><strong>Посещаемость:</strong> в среднем 5 учеников на уроке</p>
                        <div class="bg-green-100 rounded p-2 mt-2">
                            <p class="font-mono text-sm">
                                10 уроков × 5 учеников × 500 ₸ = <strong>25 000 ₸</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quick-period').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('period_start').value = this.dataset.start;
        document.getElementById('period_end').value = this.dataset.end;
    });
});
</script>
