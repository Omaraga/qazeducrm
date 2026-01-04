<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Ученики', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка ученика</p>
            </div>
        </div>
    </div>

    <!-- Balance -->
    <?= $this->render('../balance', ['model' => $model]) ?>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <?= Yii::t('main', 'Основные данные') ?>
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/edu', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <?= Yii::t('main', 'Обучение') ?>
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                <?= Yii::t('main', 'Оплата') ?>
            </a>
        </nav>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_PAY]) ?>" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <?= Yii::t('main', 'Добавить оплату') ?>
        </a>
        <a href="<?= OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_REFUND]) ?>" class="btn btn-warning">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            <?= Yii::t('main', 'Добавить возврат') ?>
        </a>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Назначение</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Способ оплаты</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Комментарий</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $payment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d.m.Y H:i', strtotime($payment->date)) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="<?= OrganizationUrl::to(['pupil/create-payment', 'id' => $payment->id, 'pupil_id' => $payment->pupil_id, 'type' => $payment->type]) ?>"
                               class="badge <?= $payment->type == Payment::TYPE_PAY ? 'badge-success' : 'badge-warning' ?>">
                                <?= Html::encode($payment->typeLabel) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($payment->type == Payment::TYPE_PAY): ?>
                                <?= Html::encode($payment->purposeLabel ?? '') ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($payment->type == Payment::TYPE_PAY && $payment->method): ?>
                                <?= Html::encode($payment->method->name) ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right <?= $payment->type == Payment::TYPE_PAY ? 'text-success-600' : 'text-danger-600' ?>">
                            <?php if ($payment->type == Payment::TYPE_PAY): ?>
                                +<?= number_format($payment->amount, 0, '.', ' ') ?> ₸
                            <?php else: ?>
                                -<?= number_format($payment->amount, 0, '.', ' ') ?> ₸
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= Html::encode($payment->number ?: '') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= Html::encode($payment->comment) ?>">
                            <?= Html::encode($payment->comment) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="mt-2">У ученика нет записей об оплате</p>
                            <a href="<?= OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_PAY]) ?>" class="btn btn-primary mt-4">
                                Добавить первую оплату
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
