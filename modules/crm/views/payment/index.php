<?php

use app\models\Payment;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Бухгалтерия');
$this->params['breadcrumbs'][] = $this->title;

// Calculate totals
$totalIncome = 0;
$totalExpense = 0;
foreach ($dataProvider->getModels() as $payment) {
    if ($payment->type == Payment::TYPE_PAY) {
        $totalIncome += $payment->amount;
    } else {
        $totalExpense += $payment->amount;
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Финансовые операции</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Поступления</p>
                        <p class="text-2xl font-bold text-success-600"><?= number_format($totalIncome, 0, '.', ' ') ?> ₸</p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Расходы</p>
                        <p class="text-2xl font-bold text-danger-600"><?= number_format($totalExpense, 0, '.', ' ') ?> ₸</p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Итого за период</p>
                        <p class="text-2xl font-bold <?= ($searchModel->sum ?? 0) >= 0 ? 'text-primary-600' : 'text-danger-600' ?>"><?= number_format($searchModel->sum ?? 0, 0, '.', ' ') ?> ₸</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <?= $this->render('_search', ['model' => $searchModel]) ?>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Назначение</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Способ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ученик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Комментарий</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($model->type == Payment::TYPE_PAY): ?>
                                <span class="badge badge-success">Приход</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Расход</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($model->type == Payment::TYPE_PAY): ?>
                                <span class="text-sm font-semibold text-success-600">+<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                            <?php else: ?>
                                <span class="text-sm font-semibold text-danger-600">-<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $model->type == Payment::TYPE_PAY ? Html::encode($model->purposeLabel ?? '') : '' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $model->type == Payment::TYPE_PAY && $model->method ? Html::encode($model->method->name) : '' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $model->pupil ? Html::encode($model->pupil->fio) : '' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= Html::encode($model->number ?? '') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            <?= Html::encode($model->comment ?? '') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <?php if ($model->pupil_id): ?>
                                <?= Html::a('Посмотреть', OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]), ['class' => 'btn btn-sm btn-secondary']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2">Платежи не найдены</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($dataProvider->getModels())): ?>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900">Итого</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900"><?= number_format($searchModel->sum ?? 0, 0, '.', ' ') ?> ₸</td>
                        <td colspan="6"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
