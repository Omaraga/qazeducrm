<?php

use app\helpers\OrganizationUrl;
use app\models\PayMethod;
use app\widgets\tailwind\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $model */

// Convert dates for HTML5 date input
$dateStart = $model->date_start ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_start))) : '';
$dateEnd = $model->date_end ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_end))) : '';

// Получаем методы оплаты для фильтра
$payMethods = PayMethod::find()->byOrganization()->notDeleted()->orderBy(['name' => SORT_ASC])->all();
?>

<form action="<?= OrganizationUrl::to(['payment/index']) ?>" method="get" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
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
    <div>
        <label class="form-label">Способ оплаты</label>
        <?= Html::activeDropDownList($model, 'method_id', ArrayHelper::map($payMethods, 'id', 'name'), [
            'class' => 'form-select',
            'prompt' => 'Все способы'
        ]) ?>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="btn btn-primary">
            <?= Icon::show('search', 'sm') ?>
            Поиск
        </button>
        <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">Сбросить</a>
    </div>
</form>
