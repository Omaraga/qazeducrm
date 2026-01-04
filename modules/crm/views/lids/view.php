<?php

use app\helpers\OrganizationUrl;
use app\models\Lids;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lids $model */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;

// Status colors mapping
$statusColors = [
    Lids::STATUS_NEW => 'bg-blue-500',
    Lids::STATUS_CONTACTED => 'bg-indigo-500',
    Lids::STATUS_TRIAL => 'bg-yellow-500',
    Lids::STATUS_THINKING => 'bg-gray-500',
    Lids::STATUS_ENROLLED => 'bg-purple-500',
    Lids::STATUS_PAID => 'bg-green-500',
    Lids::STATUS_LOST => 'bg-red-500',
];
$statusBgColor = $statusColors[$model->status] ?? 'bg-gray-500';
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
                <p class="text-gray-500 mt-1">Карточка лида</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['lids/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['lids/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить лид?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white <?= $statusBgColor ?>">
            <?= Html::encode($model->getStatusLabel()) ?>
        </span>
        <?php if ($model->source): ?>
            <span class="badge badge-secondary"><?= Html::encode($model->getSourceLabel()) ?></span>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Контактные данные</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ФИО</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->fio ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Телефон</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->phone): ?>
                                    <a href="tel:<?= Html::encode($model->phone) ?>" class="text-primary-600 hover:text-primary-800">
                                        <?= Html::encode($model->phone) ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Школа</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->school ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Класс</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->class_id): ?>
                                    <span class="badge badge-secondary"><?= \app\helpers\Lists::getGrades()[$model->class_id] ?? $model->class_id ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Funnel Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Воронка продаж</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Источник</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->getSourceLabel() ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Менеджер</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->manager ? $model->manager->fio : ($model->manager_name ?: '—')) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата обращения</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->date ? date('d.m.Y', strtotime($model->date)) : '—' ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Следующий контакт</dt>
                            <dd class="mt-1 text-sm">
                                <?php if ($model->next_contact_date): ?>
                                    <?php
                                    $date = strtotime($model->next_contact_date);
                                    $today = strtotime(date('Y-m-d'));
                                    $colorClass = 'text-gray-900';
                                    if ($date < $today) {
                                        $colorClass = 'text-danger-600 font-medium';
                                    } elseif ($date == $today) {
                                        $colorClass = 'text-warning-600 font-medium';
                                    }
                                    ?>
                                    <span class="<?= $colorClass ?>"><?= date('d.m.Y', $date) ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if ($model->lost_reason): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Причина потери</dt>
                            <dd class="mt-1 text-sm text-danger-600"><?= Html::encode($model->lost_reason) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Test Results -->
            <?php if ($model->total_point || $model->sale || $model->total_sum): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Пробное тестирование</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Баллы</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold"><?= $model->total_point ?: '—' ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Скидка</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->sale): ?>
                                    <span class="badge badge-warning"><?= $model->sale ?>%</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Сумма</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                <?= $model->total_sum ? number_format($model->total_sum, 0, '.', ' ') . ' ₸' : '—' ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comment -->
            <?php if ($model->comment): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Комментарий</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= Html::encode($model->comment) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Быстрые действия</h3>
                </div>
                <div class="card-body space-y-2">
                    <?php if ($model->phone): ?>
                    <a href="tel:<?= Html::encode($model->phone) ?>" class="btn btn-secondary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Позвонить
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $model->phone) ?>" target="_blank" class="btn btn-success w-full justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        </svg>
                        WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">История</h3>
                </div>
                <div class="card-body text-sm text-gray-500 space-y-2">
                    <div>
                        <span class="font-medium">Создан:</span>
                        <?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') : '—' ?>
                    </div>
                    <div>
                        <span class="font-medium">Обновлён:</span>
                        <?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') : '—' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
