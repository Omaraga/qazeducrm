<?php

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\models\TeacherSalary;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;

// Проверка прав на утверждение/выплату/удаление (только директора)
$canApprovePayDelete = Yii::$app->user->can(OrganizationRoles::DIRECTOR)
    || Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR)
    || Yii::$app->user->can('SUPER');

/** @var yii\web\View $this */
/** @var app\models\TeacherSalary $model */
/** @var app\models\TeacherSalaryDetail[] $details */

$this->title = 'Зарплата: ' . ($model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id);
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => OrganizationUrl::to(['salary/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center text-success-600">
                <?= Icon::show('wallet', 'lg') ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1"><?= $model->getPeriodLabel() ?></p>
            </div>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['salary/index']) ?>" class="btn btn-secondary">
                <?= Icon::show('arrow-left', 'sm') ?>
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
                    <?= StatusBadge::show('salary', $model->status) ?>
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
                            <dt class="text-sm text-gray-500 flex items-center gap-1">
                                Базовая сумма
                                <span class="cursor-help" title="Сумма всех начислений за уроки (на основе ставок)"><?= Icon::show('info', 'xs', 'text-gray-400') ?></span>
                            </dt>
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
                            <div class="flex justify-between items-center">
                                <dt class="text-base font-bold text-gray-900 flex items-center gap-1">
                                    ИТОГО
                                    <span class="cursor-help" title="Базовая сумма + Бонусы - Вычеты"><?= Icon::show('info', 'xs', 'text-gray-400') ?></span>
                                </dt>
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
                    <a href="<?= OrganizationUrl::to(['salary/update', 'id' => $model->id]) ?>" class="btn btn-secondary w-full justify-center" title="Добавить бонусы или вычеты">
                        <?= Icon::show('edit', 'sm') ?>
                        Редактировать
                    </a>
                    <a href="<?= OrganizationUrl::to(['salary/recalculate', 'id' => $model->id]) ?>" class="btn btn-secondary w-full justify-center" title="Пересчитать базовую сумму по текущим урокам и ставкам">
                        <?= Icon::show('refresh', 'sm') ?>
                        Пересчитать
                    </a>
                    <?php if ($canApprovePayDelete): ?>
                    <form action="<?= OrganizationUrl::to(['salary/approve', 'id' => $model->id]) ?>" method="post">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-success w-full justify-center" title="После утверждения редактирование невозможно">
                            <?= Icon::show('check', 'sm') ?>
                            Утвердить
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($model->status == TeacherSalary::STATUS_APPROVED && $canApprovePayDelete): ?>
                    <form action="<?= OrganizationUrl::to(['salary/pay', 'id' => $model->id]) ?>" method="post">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-primary w-full justify-center" title="Зафиксировать факт выплаты зарплаты">
                            <?= Icon::show('wallet', 'sm') ?>
                            Отметить как выплаченную
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($model->status == TeacherSalary::STATUS_PAID): ?>
                    <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-success-800">
                            <?= Icon::show('success', 'md') ?>
                            <span class="text-sm font-medium">
                                Выплачено: <?= Yii::$app->formatter->asDatetime($model->paid_at, 'php:d.m.Y H:i') ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($model->status != TeacherSalary::STATUS_PAID && $canApprovePayDelete): ?>
                    <form action="<?= OrganizationUrl::to(['salary/delete', 'id' => $model->id]) ?>" method="post"
                          onsubmit="return confirm('Удалить эту зарплату?')">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="btn btn-danger w-full justify-center">
                            <?= Icon::show('trash', 'sm') ?>
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
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        Детализация
                        <span class="text-sm font-normal text-gray-500">(<?= count($details) ?> уроков)</span>
                    </h3>
                </div>
                <!-- Пояснение к таблице -->
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <p class="text-xs text-gray-500">
                        Для каждого урока показано: количество учеников с оплаченной посещаемостью, применённая ставка и итоговая сумма.
                        Ставка выбирается автоматически по приоритету: сначала для группы, затем для предмета, затем общая.
                    </p>
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
