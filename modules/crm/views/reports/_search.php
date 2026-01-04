<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\DateSearch $model */
/** @var bool $onlyMonth */

if (!isset($onlyMonth)) {
    $onlyMonth = true;
}

// Convert date for HTML5 date input
$dateValue = $model->date ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date))) : date('Y-m-d');

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('search-date-input');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            document.getElementById('date-search-form').submit();
        });
    }
});
JS;
$this->registerJs($js);
?>

<form action="" method="get" id="date-search-form" class="flex flex-wrap items-end gap-4">
    <div class="w-full sm:w-auto">
        <label class="form-label" for="search-date-input">Дата</label>
        <input type="date" name="DateSearch[date]" id="search-date-input"
               class="form-input" value="<?= $dateValue ?>">
        <?php if ($onlyMonth): ?>
        <p class="mt-1 text-xs text-gray-500">Отчет отображается за выбранный месяц</p>
        <?php endif; ?>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Показать
        </button>
    </div>
</form>
