<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

// Динамический заголовок в зависимости от типа
if ($model->type == Payment::TYPE_SPENDING) {
    $this->title = Yii::t('main', 'Добавить расход');
    $subtitle = 'Введите данные расхода';
} elseif ($model->type == Payment::TYPE_REFUND) {
    $this->title = Yii::t('main', 'Добавить возврат');
    $subtitle = 'Введите данные возврата';
} else {
    $this->title = Yii::t('main', 'Добавить приход');
    $subtitle = 'Введите данные платежа';
}

$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Бухгалтерия'), 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= $subtitle ?></p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к бухгалтерии
            </a>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
