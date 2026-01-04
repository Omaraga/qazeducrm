<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lids $model */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center text-primary-600">
                <?= Icon::show('user', 'lg') ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка лида</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['lids/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <?= Icon::show('edit', 'sm') ?>
                Редактировать
            </a>
            <?= Html::a(Icon::show('trash', 'sm') . ' Удалить',
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
        <?= StatusBadge::show('lids', $model->status, ['size' => 'lg']) ?>
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
                        <?= Icon::show('phone', 'sm') ?>
                        Позвонить
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $model->phone) ?>" target="_blank" class="btn btn-success w-full justify-center">
                        <?= Icon::show('whatsapp', 'sm') ?>
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
