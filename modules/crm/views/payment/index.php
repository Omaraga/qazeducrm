<?php

use app\models\Payment;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\PaymentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Бухгалтерия');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Финансовые операции</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['payment/create', 'type' => Payment::TYPE_PAY]) ?>" class="btn btn-success">
                <?= Icon::show('arrow-up', 'sm') ?>
                Приход
            </a>
            <a href="<?= OrganizationUrl::to(['payment/create', 'type' => Payment::TYPE_SPENDING]) ?>" class="btn btn-danger">
                <?= Icon::show('arrow-down', 'sm') ?>
                Расход
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Поступления</p>
                        <p class="text-2xl font-bold text-success-600"><?= number_format($searchModel->totalIncome, 0, '.', ' ') ?> ₸</p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                        <?= Icon::show('arrow-up', 'lg', 'text-success-600') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Расходы и возвраты</p>
                        <p class="text-2xl font-bold text-danger-600"><?= number_format($searchModel->totalExpense, 0, '.', ' ') ?> ₸</p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 rounded-full flex items-center justify-center">
                        <?= Icon::show('arrow-down', 'lg', 'text-danger-600') ?>
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
                        <?= Icon::show('calculator', 'lg', 'text-primary-600') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics by Payment Method -->
    <?php if (!empty($searchModel->incomeByMethod) || !empty($searchModel->expenseByMethod)): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if (!empty($searchModel->incomeByMethod)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Поступления по способам оплаты</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <?php foreach ($searchModel->incomeByMethod as $method): ?>
                    <div class="flex items-center justify-between p-3 bg-success-50 rounded-lg">
                        <span class="text-sm text-gray-700"><?= Html::encode($method['name']) ?></span>
                        <span class="text-lg font-bold text-success-600">
                            +<?= number_format($method['total'], 0, '.', ' ') ?> ₸
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($searchModel->expenseByMethod)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Расходы по способам оплаты</h3>
            </div>
            <div class="card-body">
                <div class="space-y-3">
                    <?php foreach ($searchModel->expenseByMethod as $method): ?>
                    <div class="flex items-center justify-between p-3 bg-danger-50 rounded-lg">
                        <span class="text-sm text-gray-700"><?= Html::encode($method['name']) ?></span>
                        <span class="text-lg font-bold text-danger-600">
                            -<?= number_format($method['total'], 0, '.', ' ') ?> ₸
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

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
                            <?php elseif ($model->type == Payment::TYPE_REFUND): ?>
                                <span class="badge badge-warning">Возврат</span>
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
                            <?= $model->method ? Html::encode($model->method->name) : '' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($model->pupil): ?>
                                <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="text-primary-600 hover:text-primary-800">
                                    <?= Html::encode($model->pupil->fio) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= Html::encode($model->comment ?? '') ?>">
                            <?= Html::encode($model->comment ?? '') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary">
                                <?= Icon::show('eye', 'sm') ?>
                            </a>
                            <a href="<?= OrganizationUrl::to(['payment/update', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary">
                                <?= Icon::show('edit', 'sm') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <?= Icon::show('calculator', 'xl', 'mx-auto text-gray-400') ?>
                            <p class="mt-4 font-medium">Платежи не найдены</p>
                            <p class="text-sm text-gray-400 mt-1">Добавьте первый платёж или расход</p>
                            <div class="flex gap-3 justify-center mt-4">
                                <a href="<?= OrganizationUrl::to(['payment/create', 'type' => Payment::TYPE_PAY]) ?>" class="btn btn-success">
                                    <?= Icon::show('plus', 'sm') ?> Добавить приход
                                </a>
                                <a href="<?= OrganizationUrl::to(['payment/create', 'type' => Payment::TYPE_SPENDING]) ?>" class="btn btn-secondary">
                                    <?= Icon::show('minus', 'sm') ?> Добавить расход
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($dataProvider->getModels())): ?>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900">Итого</td>
                        <td class="px-6 py-4 text-sm font-bold <?= ($searchModel->sum ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' ?>"><?= number_format($searchModel->sum ?? 0, 0, '.', ' ') ?> ₸</td>
                        <td colspan="5"></td>
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
