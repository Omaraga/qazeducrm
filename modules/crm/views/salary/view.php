<?php

use app\helpers\OrganizationUrl;
use app\models\TeacherSalary;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TeacherSalary $model */
/** @var app\models\TeacherSalaryDetail[] $details */

$this->title = 'Зарплата: ' . ($model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id);
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => OrganizationUrl::to(['salary/index'])];
$this->params['breadcrumbs'][] = $this->title;

$statusClass = match($model->status) {
    TeacherSalary::STATUS_DRAFT => 'badge-secondary',
    TeacherSalary::STATUS_APPROVED => 'badge-warning',
    TeacherSalary::STATUS_PAID => 'badge-success',
    default => 'badge-secondary'
};
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1"><?= $model->getPeriodLabel() ?></p>
            </div>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['salary/index']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Card -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Информация</h3>
                    <span class="badge <?= $statusClass ?>"><?= $model->getStatusLabel() ?></span>
                </div>
                <div class="card-body">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Преподаватель</dt>
                            <dd class="text-sm font-medium text-gray-900"><?= $model->teacher ? Html::encode($model->teacher->fio) : 'Не указан' ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Период</dt>
                            <dd class="text-sm text-gray-900"><?= $model->getPeriodLabel() ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Уроков</dt>
                            <dd class="text-sm text-gray-900"><?= $model->lessons_count ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Учеников</dt>
                            <dd class="text-sm text-gray-900"><?= $model->students_count ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Amounts Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Суммы</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Базовая сумма</dt>
                            <dd class="text-sm text-gray-900"><?= number_format($model->base_amount, 0, ',', ' ') ?> ₸</dd>
                        </div>
                        <?php if ($model->bonus_amount > 0): ?>
                        <div class="flex justify-between text-success-600">
                            <dt class="text-sm">Бонусы</dt>
                            <dd class="text-sm font-medium">+<?= number_format($model->bonus_amount, 0, ',', ' ') ?> ₸</dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->deduction_amount > 0): ?>
                        <div class="flex justify-between text-danger-600">
                            <dt class="text-sm">Вычеты</dt>
                            <dd class="text-sm font-medium">-<?= number_format($model->deduction_amount, 0, ',', ' ') ?> ₸</dd>
                        </div>
                        <?php endif; ?>
                        <div class="pt-3 border-t border-gray-200">
                            <div class="flex justify-between">
                                <dt class="text-base font-bold text-gray-900">ИТОГО</dt>
                                <dd class="text-xl font-bold text-primary-600"><?= $model->getFormattedTotal() ?></dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Действия</h3>
                </div>
                <div class="card-body space-y-2">
                    <?php if ($model->status == TeacherSalary::STATUS_DRAFT): ?>
                    <a href="<?= OrganizationUrl::to(['salary/update', 'id' => $model->id]) ?>" class="btn btn-secondary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Редактировать
                    </a>
                    <a href="<?= OrganizationUrl::to(['salary/recalculate', 'id' => $model->id]) ?>" class="btn btn-secondary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Пересчитать
                    </a>
                    <form action="<?= OrganizationUrl::to(['salary/approve', 'id' => $model->id]) ?>" method="post">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-success w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Утвердить
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($model->status == TeacherSalary::STATUS_APPROVED): ?>
                    <form action="<?= OrganizationUrl::to(['salary/pay', 'id' => $model->id]) ?>" method="post">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-primary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Отметить как выплаченную
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($model->status == TeacherSalary::STATUS_PAID): ?>
                    <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-success-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">
                                Выплачено: <?= Yii::$app->formatter->asDatetime($model->paid_at, 'php:d.m.Y H:i') ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($model->status != TeacherSalary::STATUS_PAID): ?>
                    <form action="<?= OrganizationUrl::to(['salary/delete', 'id' => $model->id]) ?>" method="post"
                          onsubmit="return confirm('Удалить эту зарплату?')">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-danger w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Удалить
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($model->notes): ?>
            <!-- Notes Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Примечания</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-700"><?= nl2br(Html::encode($model->notes)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Details Table -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Детализация</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Группа</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Учеников</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ставка</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Нет данных
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($details as $detail): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= Yii::$app->formatter->asDate($detail->lesson_date, 'php:d.m.Y') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($detail->group): ?>
                                        <?= Html::encode($detail->group->name) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Группа #<?= $detail->group_id ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    <?= $detail->students_paid ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="text-xs text-gray-400"><?= $detail->getRateTypeLabel() ?>:</span>
                                    <?= $detail->getFormattedRate() ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                    <?= $detail->getFormattedAmount() ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-primary-50">
                            <tr>
                                <th colspan="4" class="px-6 py-4 text-left text-sm font-bold text-gray-900">Итого</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-primary-600">
                                    <?= number_format($model->base_amount, 0, ',', ' ') ?> ₸
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
