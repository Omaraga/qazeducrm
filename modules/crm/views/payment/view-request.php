<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PaymentChangeRequest;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PaymentChangeRequest $model */

$this->title = 'Запрос #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Запросы', 'url' => OrganizationUrl::to(['payment/pending-requests'])];
$this->params['breadcrumbs'][] = $this->title;

$payment = $model->payment;
$oldValues = is_string($model->old_values) ? json_decode($model->old_values, true) : $model->old_values;
$newValues = is_string($model->new_values) ? json_decode($model->new_values, true) : $model->new_values;
?>

<div class="space-y-6 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="text-center">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto
            <?= $model->request_type === PaymentChangeRequest::TYPE_DELETE ? 'bg-red-100' : 'bg-blue-100' ?>">
            <?php if ($model->request_type === PaymentChangeRequest::TYPE_DELETE): ?>
                <?= Icon::show('trash', 'xl', 'text-red-600') ?>
            <?php else: ?>
                <?= Icon::show('edit', 'xl', 'text-blue-600') ?>
            <?php endif; ?>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4">
            <?= $model->request_type === PaymentChangeRequest::TYPE_DELETE ? 'Запрос на удаление' : 'Запрос на изменение' ?>
        </h1>
        <p class="text-gray-500 mt-1">
            От: <?= Html::encode($model->requestedByUser->fio ?? '—') ?> •
            <?= Yii::$app->formatter->asDatetime($model->created_at, 'dd.MM.yyyy HH:mm') ?>
        </p>
    </div>

    <!-- Status Badge -->
    <div class="text-center">
        <span class="badge <?= $model->getStatusBadgeClass() ?> text-lg px-4 py-2">
            <?= $model->getStatusLabel() ?>
        </span>
    </div>

    <!-- Reason -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Причина запроса</h3>
        </div>
        <div class="card-body">
            <p class="text-gray-700"><?= nl2br(Html::encode($model->reason)) ?></p>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">
                <?php if ($payment): ?>
                    <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $payment->id]) ?>" class="text-primary-600 hover:text-primary-800">
                        Платёж #<?= $payment->id ?>
                    </a>
                <?php else: ?>
                    Платёж #<?= $model->payment_id ?>
                <?php endif; ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if ($oldValues): ?>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Ученик</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($oldValues['pupil_name'] ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Сумма</dt>
                    <dd class="font-medium text-green-600"><?= number_format($oldValues['amount'] ?? 0, 0, '.', ' ') ?> ₸</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Дата</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($oldValues['date'] ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Способ оплаты</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($oldValues['method_name'] ?? '—') ?></dd>
                </div>
                <?php if (!empty($oldValues['comment'])): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Комментарий</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($oldValues['comment']) ?></dd>
                </div>
                <?php endif; ?>
            </dl>
            <?php elseif ($payment): ?>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Ученик</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($payment->pupil->fio ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Сумма</dt>
                    <dd class="font-medium text-green-600"><?= number_format($payment->amount, 0, '.', ' ') ?> ₸</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Дата</dt>
                    <dd class="font-medium text-gray-900"><?= Yii::$app->formatter->asDatetime($payment->date, 'dd.MM.yyyy HH:mm') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Способ оплаты</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($payment->method->name ?? '—') ?></dd>
                </div>
            </dl>
            <?php endif; ?>
        </div>
    </div>

    <!-- Changes (for update requests) -->
    <?php
    $changes = $model->getChangedFields();
    if (!empty($changes)):
    ?>
    <div class="card border-blue-200 bg-blue-50">
        <div class="card-header bg-blue-100">
            <h3 class="text-lg font-semibold text-blue-900">Запрошенные изменения</h3>
        </div>
        <div class="card-body">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-blue-700">
                        <th class="pb-2">Поле</th>
                        <th class="pb-2">Было</th>
                        <th class="pb-2">Станет</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-200">
                    <?php foreach ($changes as $field => $data): ?>
                    <tr>
                        <td class="py-2 font-medium text-blue-800"><?= Html::encode($data['label']) ?></td>
                        <td class="py-2 text-gray-600 line-through"><?= Html::encode($data['old'] ?? '—') ?></td>
                        <td class="py-2 font-medium text-blue-800"><?= Html::encode($data['new']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions (only for pending) -->
    <?php if ($model->isPending()): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Действия</h3>
        </div>
        <div class="card-body space-y-4">
            <!-- Approve Form -->
            <form method="post" action="<?= OrganizationUrl::to(['payment/approve-request', 'id' => $model->id]) ?>" class="p-4 bg-green-50 rounded-lg border border-green-200">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" name="comment" class="form-input flex-1" placeholder="Комментарий (опционально)">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Вы уверены? <?= $model->request_type === PaymentChangeRequest::TYPE_DELETE ? 'Платёж будет удалён.' : 'Платёж будет изменён.' ?>')">
                        <?= Icon::show('check', 'sm') ?>
                        Одобрить
                    </button>
                </div>
            </form>

            <!-- Reject Form -->
            <form method="post" action="<?= OrganizationUrl::to(['payment/reject-request', 'id' => $model->id]) ?>" class="p-4 bg-red-50 rounded-lg border border-red-200">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" name="comment" class="form-input flex-1" placeholder="Причина отклонения (обязательно)" required>
                    <button type="submit" class="btn btn-danger">
                        <?= Icon::show('x-mark', 'sm') ?>
                        Отклонить
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Processed Info (if already processed) -->
    <?php if (!$model->isPending() && $model->admin_comment): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Результат рассмотрения</h3>
        </div>
        <div class="card-body">
            <p class="text-gray-700"><?= nl2br(Html::encode($model->admin_comment)) ?></p>
            <?php if ($model->processedByUser): ?>
                <p class="text-sm text-gray-500 mt-3">
                    — <?= Html::encode($model->processedByUser->fio) ?>,
                    <?= Yii::$app->formatter->asDatetime($model->processed_at, 'dd.MM.yyyy HH:mm') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="text-center">
        <a href="<?= OrganizationUrl::to(['payment/pending-requests']) ?>" class="btn btn-secondary">
            <?= Icon::show('arrow-left', 'sm') ?>
            Назад к запросам
        </a>
    </div>
</div>
