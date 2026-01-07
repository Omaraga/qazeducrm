<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\GroupTabs;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

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
                <p class="text-gray-500 mt-1">Преподаватели группы</p>
            </div>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['group/create-teacher', 'group_id' => $model->id]) ?>" class="btn btn-primary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить преподавателя
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <?= GroupTabs::widget(['model' => $model, 'activeTab' => 'teachers']) ?>

    <!-- Teachers Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Преподаватель</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип оплаты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ставка</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $teacher): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $teacher->id ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <?= Icon::show('user', 'md', 'text-primary-600') ?>
                                </div>
                                <div class="text-sm font-medium text-gray-900"><?= Html::encode($teacher->teacher->fio ?? '—') ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-secondary"><?= Html::encode($teacher->typeLabel ?? '—') ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $teacher->price ? number_format($teacher->price, 0, '.', ' ') . ' ₸' : '—' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <?= Html::a(Icon::show('trash', 'sm'),
                                OrganizationUrl::to(['group/delete-teacher', 'id' => $teacher->id]), [
                                'class' => 'btn btn-sm btn-danger',
                                'title' => 'Удалить преподавателя',
                                'data' => [
                                    'confirm' => 'Вы действительно хотите удалить преподавателя?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <?= Icon::show('user', 'xl', 'mx-auto text-gray-400') ?>
                            <p class="mt-4">Преподаватели не назначены</p>
                            <p class="text-sm text-gray-400 mt-1">Добавьте преподавателя для проведения занятий</p>
                            <a href="<?= OrganizationUrl::to(['group/create-teacher', 'group_id' => $model->id]) ?>" class="btn btn-primary mt-4">
                                <?= Icon::show('plus', 'sm') ?> Добавить первого преподавателя
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
