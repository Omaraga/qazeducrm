<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$this->title = $model->code . '-' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>20">
                <svg class="w-6 h-6" style="color: <?= Html::encode($model->color ?: '#3b82f6') ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка группы</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['group/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['group/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить группу?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['group/view', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                Основные данные
            </a>
            <a href="<?= OrganizationUrl::to(['group/teachers', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Преподаватели
            </a>
            <a href="<?= OrganizationUrl::to(['group/pupils', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Ученики
            </a>
        </nav>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Group Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Информация о группе</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= $model->id ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Предмет</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->subjectLabel ?? $model->subject->name ?? '—') ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Код</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="badge badge-primary"><?= Html::encode($model->code) ?></span>
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Название</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->name) ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Категория</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->categoryLabel ?? '—') ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Тип</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->type ?? '—') ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Настройки</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Цвет</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg shadow-inner" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>"></div>
                                <span class="text-gray-500"><?= Html::encode($model->color ?: 'Не указан') ?></span>
                            </div>
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Статус</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php
                            $statusClass = 'badge-secondary';
                            if ($model->status == 1) $statusClass = 'badge-success';
                            elseif ($model->status == 0) $statusClass = 'badge-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= Html::encode($model->statusLabel ?? '—') ?></span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
