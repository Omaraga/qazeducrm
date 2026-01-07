<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use app\widgets\tailwind\PupilTabs;
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
                <?= Icon::show('user', 'md', 'text-primary-600') ?>
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
    <?= PupilTabs::widget(['model' => $model, 'activeTab' => 'payment']) ?>

    <!-- Actions -->
    <div class="flex flex-wrap items-center gap-3">
        <?= Html::a(
            Icon::show('plus', 'sm') . ' ' . Yii::t('main', 'Добавить оплату'),
            OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_PAY]),
            ['class' => 'btn btn-primary', 'title' => 'Добавить новую оплату']
        ) ?>
        <?= Html::a(
            Icon::show('arrow-left', 'sm') . ' ' . Yii::t('main', 'Добавить возврат'),
            OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_REFUND]),
            ['class' => 'btn btn-warning', 'title' => 'Оформить возврат средств']
        ) ?>
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
                            <?= Icon::show('wallet', 'xl', 'mx-auto text-gray-400') ?>
                            <p class="mt-2">У ученика нет записей об оплате</p>
                            <?= Html::a(
                                Icon::show('plus', 'xs') . ' Добавить первую оплату',
                                OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id, 'type' => Payment::TYPE_PAY]),
                                ['class' => 'btn btn-primary mt-4']
                            ) ?>
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
