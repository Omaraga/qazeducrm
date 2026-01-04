<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$this->title = Yii::t('main', 'Платеж') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Бухгалтерия'), 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = $this->title;
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
                    <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?>
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['payment/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['payment/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить этот платеж?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Payment Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Информация о платеже</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Тип операции</dt>
                            <dd class="mt-1">
                                <?php if ($model->type == Payment::TYPE_PAY): ?>
                                    <span class="badge badge-success">Приход</span>
                                <?php elseif ($model->type == Payment::TYPE_REFUND): ?>
                                    <span class="badge badge-warning">Возврат</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Расход</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Сумма</dt>
                            <dd class="mt-1">
                                <?php if ($model->type == Payment::TYPE_PAY): ?>
                                    <span class="text-xl font-bold text-success-600">+<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                                <?php else: ?>
                                    <span class="text-xl font-bold text-danger-600">-<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ученик</dt>
                            <dd class="mt-1">
                                <?php if ($model->pupil): ?>
                                    <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                        <?= Html::encode($model->pupil->fio) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if ($model->type == Payment::TYPE_PAY): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Назначение</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->purposeLabel ?? '—') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Способ оплаты</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->method->name ?? '—') ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->number): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Номер квитанции</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->number) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Comment -->
            <?php if ($model->comment): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Комментарий</h3>
                </div>
                <div class="card-body">
                    <p class="text-gray-700"><?= nl2br(Html::encode($model->comment)) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Meta Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Системная информация</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->id ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Создано</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->created_at, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Обновлено</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->updated_at, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Back Button -->
            <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к бухгалтерии
            </a>

            <?php if ($model->pupil): ?>
            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Быстрые ссылки</h3>
                </div>
                <div class="card-body space-y-2">
                    <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Карточка ученика
                    </a>
                    <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]) ?>" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Все платежи ученика
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
