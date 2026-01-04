<?php

use app\helpers\OrganizationUrl;
use app\helpers\OrganizationRoles;
use app\models\Tariff;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Тарифы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;

$canManage = Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <div class="flex items-center gap-2 mt-1">
                    <?php if ($model->status == Tariff::STATUS_ACTIVE): ?>
                        <span class="badge badge-success">Активный</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Архивный</span>
                    <?php endif; ?>
                    <span class="badge badge-info"><?= Html::encode($model->typeLabel) ?></span>
                </div>
            </div>
        </div>
        <?php if ($canManage): ?>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['tariff/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['tariff/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить этот тариф?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Основная информация</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Название</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium"><?= Html::encode($model->name) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Продолжительность</dt>
                            <dd class="mt-1">
                                <span class="badge badge-secondary"><?= Html::encode($model->durationLabel) ?></span>
                            </dd>
                        </div>
                        <?php if ($model->lesson_amount): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Количество занятий</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->lesson_amount ?></dd>
                        </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Тип тарифа</dt>
                            <dd class="mt-1">
                                <span class="badge badge-info"><?= Html::encode($model->typeLabel) ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Subjects -->
            <?php if ($model->subjectsLabel): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Предметы</h3>
                </div>
                <div class="card-body">
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($model->subjectsRelation as $tariffSubject): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary-50 text-primary-700 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <?= Html::encode($tariffSubject->getSubjectName()) ?>
                                <span class="text-primary-500">(<?= Tariff::getAmounts()[$tariffSubject->lesson_amount] ?? $tariffSubject->lesson_amount ?>)</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Description -->
            <?php if ($model->description): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Описание</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= Html::encode($model->description) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Price Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Стоимость</h3>
                </div>
                <div class="card-body text-center">
                    <div class="text-3xl font-bold text-primary-600">
                        <?= number_format($model->price, 0, '.', ' ') ?> ₸
                    </div>
                    <p class="text-sm text-gray-500 mt-1"><?= Html::encode($model->durationLabel) ?></p>
                </div>
            </div>

            <!-- Meta Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Информация</h3>
                </div>
                <div class="card-body text-sm text-gray-500 space-y-2">
                    <div>
                        <span class="font-medium">ID:</span> <?= $model->id ?>
                    </div>
                    <div>
                        <span class="font-medium">Статус:</span>
                        <?php if ($model->status == Tariff::STATUS_ACTIVE): ?>
                            <span class="text-success-600">Активный</span>
                        <?php else: ?>
                            <span class="text-gray-500">Архивный</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <a href="<?= OrganizationUrl::to(['tariff/index']) ?>" class="btn btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к списку
            </a>
        </div>
    </div>
</div>
