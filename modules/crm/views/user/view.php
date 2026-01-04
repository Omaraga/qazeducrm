<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Преподаватели', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка преподавателя</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['user/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['user/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить преподавателя?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200" x-data="{ activeTab: 'main' }">
        <nav class="flex gap-4" aria-label="Tabs">
            <button type="button" @click="activeTab = 'main'"
               :class="activeTab === 'main' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
               class="px-4 py-2 text-sm font-medium border-b-2">
                <?= Yii::t('main', 'Основные данные') ?>
            </button>
            <button type="button" @click="activeTab = 'security'"
               :class="activeTab === 'security' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
               class="px-4 py-2 text-sm font-medium border-b-2">
                <?= Yii::t('main', 'Безопасность') ?>
            </button>
        </nav>

        <!-- Tab Content: Main -->
        <div x-show="activeTab === 'main'" class="pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Личные данные</h3>
                    </div>
                    <div class="card-body">
                        <dl class="divide-y divide-gray-100">
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">ID</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= $model->id ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">ИИН</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->iin ?: '—') ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Фамилия</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->last_name ?: '—') ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Имя</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->first_name ?: '—') ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Отчество</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->middle_name ?: '—') ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Пол</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->genderLabel ?? '—') ?></dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Дата рождения</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    <?= $model->birth_date ? date('d.m.Y', strtotime($model->birth_date)) : '—' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Контактные данные</h3>
                    </div>
                    <div class="card-body">
                        <dl class="divide-y divide-gray-100">
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    <?php if ($model->email): ?>
                                        <a href="mailto:<?= Html::encode($model->email) ?>" class="text-primary-600 hover:text-primary-800">
                                            <?= Html::encode($model->email) ?>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Телефон</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    <?= $model->phone ? '+' . Html::encode($model->phone) : '—' ?>
                                </dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Домашний телефон</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    <?= $model->home_phone ? '+' . Html::encode($model->home_phone) : '—' ?>
                                </dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Адрес</dt>
                                <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->address ?: '—') ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card lg:col-span-2">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Системная информация</h3>
                    </div>
                    <div class="card-body">
                        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Статус</dt>
                                <dd class="mt-1">
                                    <?php
                                    $statusClass = 'badge-secondary';
                                    if ($model->status == 10) $statusClass = 'badge-success';
                                    elseif ($model->status == 0) $statusClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= Html::encode($model->statusLabel ?? '—') ?></span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Логин</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->username) ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Создан</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?= $model->created_at ? date('d.m.Y H:i', $model->created_at) : '—' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Обновлен</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?= $model->updated_at ? date('d.m.Y H:i', $model->updated_at) : '—' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Security -->
        <div x-show="activeTab === 'security'" class="pt-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-gray-500">Настройки безопасности будут добавлены в будущих версиях.</p>
                </div>
            </div>
        </div>
    </div>
</div>
