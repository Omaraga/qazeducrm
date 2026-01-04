<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\forms\TypicalLessonForm $model */

$this->title = Yii::t('main', 'Заполнение расписания на основе типового');
$this->params['breadcrumbs'][] = ['label' => 'Расписание', 'url' => OrganizationUrl::to(['schedule/index'])];
$this->params['breadcrumbs'][] = $this->title;

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('prev-week');
    const nextBtn = document.getElementById('next-week');
    const startInput = document.getElementById('date_start_week');
    const endInput = document.getElementById('date_end_week');
    const periodDisplay = document.getElementById('week-period');

    function parseDate(dateStr) {
        const parts = dateStr.split('.');
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return day + '.' + month + '.' + year;
    }

    function updatePeriod(days) {
        const start = parseDate(startInput.value);
        const end = parseDate(endInput.value);
        start.setDate(start.getDate() + days);
        end.setDate(end.getDate() + days);
        startInput.value = formatDate(start);
        endInput.value = formatDate(end);
        periodDisplay.textContent = formatDate(start) + ' - ' + formatDate(end);
    }

    prevBtn.addEventListener('click', () => updatePeriod(-7));
    nextBtn.addEventListener('click', () => updatePeriod(7));

    // Checkbox toggle
    document.querySelectorAll('.is-copy-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const weekDropdown = this.closest('.week-block').querySelector('.week-dropdown');
            weekDropdown.disabled = !this.checked;
        });
    });
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Копирование занятий из типового расписания</p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к расписанию
            </a>
        </div>
    </div>

    <!-- Week Navigation -->
    <div class="flex items-center gap-3">
        <button type="button" id="prev-week" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Предыдущая неделя
        </button>
        <button type="button" id="next-week" class="btn btn-secondary">
            Следующая неделя
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    <form action="" method="post" class="space-y-6">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="TypicalLessonForm[date_start]" id="date_start_week" value="<?= date('d.m.Y', strtotime($model->date_start)) ?>">
        <input type="hidden" name="TypicalLessonForm[date_end]" id="date_end_week" value="<?= date('d.m.Y', strtotime($model->date_end)) ?>">

        <!-- Week Period -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Период') ?></h3>
            </div>
            <div class="card-body">
                <div class="inline-flex items-center px-4 py-2 bg-warning-50 border border-warning-200 rounded-lg text-warning-800 font-medium" id="week-period">
                    <?= date('d.m.Y', strtotime($model->date_start)) ?> - <?= date('d.m.Y', strtotime($model->date_end)) ?>
                </div>
            </div>
        </div>

        <!-- Days Mapping -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Соответствие дней недели') ?></h3>
            </div>
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Копировать</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">День типового расписания</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заполнить для дня</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($model->weeks as $k => $week): ?>
                            <tr class="week-block">
                                <td class="px-4 py-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="TypicalLessonForm[weeks][<?= $k ?>][is_copy]"
                                               value="1"
                                               class="is-copy-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                               <?= !empty($week['is_copy']) ? 'checked' : '' ?>>
                                        <span class="text-sm font-medium text-gray-700"><?= Lists::getWeekDays()[$k] ?></span>
                                    </label>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600"><?= Lists::getWeekDays()[$k] ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <?= Html::dropDownList(
                                        "TypicalLessonForm[weeks][$k][week]",
                                        $week['week'] ?? $k,
                                        Lists::getWeekDays(),
                                        [
                                            'class' => 'form-select week-dropdown',
                                            'disabled' => empty($week['is_copy'])
                                        ]
                                    ) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Создать расписание
            </button>
            <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
