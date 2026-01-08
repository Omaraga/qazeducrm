<?php

use app\helpers\OrganizationUrl;
use app\models\PaymentChangeRequest;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $status */
/** @var int $pendingCount */

$this->title = 'Запросы на изменение платежей';
$this->params['breadcrumbs'][] = ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <?= Html::encode($this->title) ?>
                <?php if ($pendingCount > 0): ?>
                    <span class="badge badge-warning ml-2"><?= $pendingCount ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-gray-500 mt-1">Рассмотрение запросов от сотрудников</p>
        </div>
        <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">
            <?= Icon::show('arrow-left', 'sm') ?>
            К бухгалтерии
        </a>
    </div>

    <!-- Status Filter -->
    <div class="flex gap-2 flex-wrap">
        <a href="<?= OrganizationUrl::to(['payment/pending-requests', 'status' => PaymentChangeRequest::STATUS_PENDING]) ?>"
           class="btn <?= $status === PaymentChangeRequest::STATUS_PENDING ? 'btn-warning' : 'btn-secondary' ?> btn-sm">
            Ожидающие
        </a>
        <a href="<?= OrganizationUrl::to(['payment/pending-requests', 'status' => PaymentChangeRequest::STATUS_APPROVED]) ?>"
           class="btn <?= $status === PaymentChangeRequest::STATUS_APPROVED ? 'btn-success' : 'btn-secondary' ?> btn-sm">
            Одобренные
        </a>
        <a href="<?= OrganizationUrl::to(['payment/pending-requests', 'status' => PaymentChangeRequest::STATUS_REJECTED]) ?>"
           class="btn <?= $status === PaymentChangeRequest::STATUS_REJECTED ? 'btn-danger' : 'btn-secondary' ?> btn-sm">
            Отклонённые
        </a>
        <a href="<?= OrganizationUrl::to(['payment/pending-requests', 'status' => 'all']) ?>"
           class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
            Все
        </a>
    </div>

    <!-- Requests List -->
    <?php if (count($dataProvider->getModels()) > 0): ?>
    <div class="space-y-4">
        <?php foreach ($dataProvider->getModels() as $request): ?>
        <div class="card hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Request Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <?php if ($request->request_type === PaymentChangeRequest::TYPE_DELETE): ?>
                                <span class="badge badge-danger">Удаление</span>
                            <?php else: ?>
                                <span class="badge badge-primary">Изменение</span>
                            <?php endif; ?>
                            <span class="badge <?= $request->getStatusBadgeClass() ?>">
                                <?= $request->getStatusLabel() ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                <?= Yii::$app->formatter->asDatetime($request->created_at, 'dd.MM.yyyy HH:mm') ?>
                            </span>
                        </div>

                        <!-- Payment Info -->
                        <div class="mb-3">
                            <?php if ($request->payment): ?>
                                <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $request->payment_id]) ?>" class="text-lg font-semibold text-primary-600 hover:text-primary-800">
                                    Платёж #<?= $request->payment_id ?>
                                </a>
                                <p class="text-sm text-gray-600">
                                    <?= Html::encode($request->payment->pupil->fio ?? '—') ?> •
                                    <span class="font-medium"><?= number_format($request->payment->amount, 0, '.', ' ') ?> ₸</span> •
                                    <?= Yii::$app->formatter->asDate($request->payment->date, 'dd.MM.yyyy') ?>
                                </p>
                            <?php else: ?>
                                <span class="text-gray-400">Платёж удалён</span>
                            <?php endif; ?>
                        </div>

                        <!-- Requested By -->
                        <p class="text-sm text-gray-500">
                            <?= Icon::show('user', 'xs', 'inline-block') ?>
                            Запросил: <span class="font-medium"><?= Html::encode($request->requestedByUser->fio ?? '—') ?></span>
                        </p>

                        <!-- Reason -->
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-700">Причина:</p>
                            <p class="text-sm text-gray-600 mt-1"><?= nl2br(Html::encode($request->reason)) ?></p>
                        </div>

                        <!-- Changed Fields (for updates) -->
                        <?php
                        $changes = $request->getChangedFields();
                        if (!empty($changes)):
                        ?>
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm font-medium text-blue-700">Изменения:</p>
                            <ul class="text-sm text-blue-600 mt-1 space-y-1">
                                <?php foreach ($changes as $field => $data): ?>
                                <li>
                                    <?= Html::encode($data['label']) ?>:
                                    <span class="line-through text-gray-400"><?= Html::encode($data['old'] ?? '—') ?></span>
                                    →
                                    <span class="font-medium"><?= Html::encode($data['new']) ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Admin Comment (if processed) -->
                        <?php if ($request->admin_comment): ?>
                        <div class="mt-3 p-3 bg-amber-50 rounded-lg">
                            <p class="text-sm font-medium text-amber-700">Комментарий:</p>
                            <p class="text-sm text-amber-600 mt-1"><?= nl2br(Html::encode($request->admin_comment)) ?></p>
                            <?php if ($request->processedByUser): ?>
                                <p class="text-xs text-amber-500 mt-2">
                                    — <?= Html::encode($request->processedByUser->fio) ?>,
                                    <?= Yii::$app->formatter->asDatetime($request->processed_at, 'dd.MM.yyyy HH:mm') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions (only for pending) -->
                    <?php if ($request->isPending()): ?>
                    <div class="flex lg:flex-col gap-2 lg:min-w-[140px]">
                        <a href="<?= OrganizationUrl::to(['payment/view-request', 'id' => $request->id]) ?>" class="btn btn-primary btn-sm flex-1">
                            <?= Icon::show('eye', 'sm') ?>
                            Рассмотреть
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body py-12 text-center text-gray-500">
            <?= Icon::show('check-circle', 'xl', 'mx-auto text-green-400') ?>
            <p class="mt-4">
                <?php if ($status === PaymentChangeRequest::STATUS_PENDING): ?>
                    Нет ожидающих запросов
                <?php else: ?>
                    Запросы не найдены
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($dataProvider->pagination->pageCount > 1): ?>
    <div class="flex justify-center">
        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
    <?php endif; ?>
</div>
