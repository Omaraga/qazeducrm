<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\search\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Преподаватели');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Всего преподавателей: <?= $dataProvider->getTotalCount() ?></p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['user/create']) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <?= Yii::t('main', 'Добавить преподавателя') ?>
            </a>
        </div>
    </div>

    <!-- Search/Filter -->
    <div class="card">
        <div class="card-body">
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Поиск по ФИО</label>
                    <input type="text" name="UserSearch[fio]" class="form-input" value="<?= Html::encode($searchModel->fio ?? '') ?>" placeholder="Введите ФИО...">
                </div>
                <div>
                    <label class="form-label">Логин</label>
                    <input type="text" name="UserSearch[username]" class="form-input" value="<?= Html::encode($searchModel->username ?? '') ?>" placeholder="Введите логин...">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Поиск
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Логин</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ФИО</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Контакты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $user->id ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?= Html::encode($user->username) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-medium text-gray-900"><?= Html::encode($user->fio) ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($user->phone): ?>
                                <div>+<?= Html::encode($user->phone) ?></div>
                            <?php endif; ?>
                            <?php if ($user->home_phone): ?>
                                <div class="text-gray-400">+<?= Html::encode($user->home_phone) ?></div>
                            <?php endif; ?>
                            <?php if ($user->email): ?>
                                <div class="text-primary-600"><?= Html::encode($user->email) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClass = 'badge-secondary';
                            if ($user->status == 10) $statusClass = 'badge-success';
                            elseif ($user->status == 0) $statusClass = 'badge-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= Html::encode($user->statusLabel ?? '—') ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <a href="<?= OrganizationUrl::to(['user/view', 'id' => $user->id]) ?>" class="btn btn-sm btn-secondary">
                                Посмотреть
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="mt-2">Преподаватели не найдены</p>
                            <a href="<?= OrganizationUrl::to(['user/create']) ?>" class="btn btn-primary mt-4">
                                Добавить первого преподавателя
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
