<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$this->title = Yii::t('main', 'Редактировать платеж') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Бухгалтерия'), 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Платеж #' . $model->id, 'url' => OrganizationUrl::to(['payment/view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = Yii::t('main', 'Редактировать');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center <?= $model->type == Payment::TYPE_PAY ? 'bg-success-100' : 'bg-danger-100' ?>">
                <?php if ($model->type == Payment::TYPE_PAY): ?>
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
                <?php else: ?>
                <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">
                    <?= number_format($model->amount, 0, '.', ' ') ?> ₸ &bull;
                    <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy') ?>
                </p>
            </div>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $model->id]) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к платежу
            </a>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
