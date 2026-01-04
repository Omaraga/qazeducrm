<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $model */

// Convert dates for HTML5 date input
$dateStart = $model->date_start ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_start))) : '';
$dateEnd = $model->date_end ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_end))) : '';
?>

<form action="<?= OrganizationUrl::to(['payment/index']) ?>" method="get" class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div>
        <label class="form-label">Дата начала</label>
        <input type="date" name="PaymentSearch[date_start]" value="<?= $dateStart ?>" class="form-input">
    </div>
    <div>
        <label class="form-label">Дата окончания</label>
        <input type="date" name="PaymentSearch[date_end]" value="<?= $dateEnd ?>" class="form-input">
    </div>
    <div>
        <label class="form-label">Тип операции</label>
        <?= Html::activeDropDownList($model, 'type', \app\models\Payment::getTypeList(), [
            'class' => 'form-select',
            'prompt' => 'Все операции'
        ]) ?>
    </div>
    <div class="flex items-end gap-2 md:col-span-2">
        <button type="submit" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Поиск
        </button>
        <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">Сбросить</a>
    </div>
</form>
