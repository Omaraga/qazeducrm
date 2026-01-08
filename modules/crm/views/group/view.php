<?php

use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\widgets\tailwind\GroupTabs;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$canDelete = RoleChecker::canDeleteGroups();

$this->title = $model->code . '-' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>20">
                <?= Icon::show('users', 'lg', '', ['style' => 'color: ' . Html::encode($model->color ?: '#3b82f6')]) ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка группы</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['group/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <?= Icon::show('edit', 'sm') ?>
                Редактировать
            </a>
            <?php if ($canDelete): ?>
                <?= Html::a(Icon::show('trash', 'sm') . ' Удалить',
                    OrganizationUrl::to(['group/delete', 'id' => $model->id]), [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы действительно хотите удалить группу?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <?= GroupTabs::widget(['model' => $model, 'activeTab' => 'view']) ?>

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
