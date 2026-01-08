<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Мои группы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-gray-500 mt-1">Группы, в которых вы ведёте занятия</p>
        </div>
    </div>

    <!-- Groups Grid -->
    <?php if (count($dataProvider->getModels()) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($dataProvider->getModels() as $model): ?>
        <div class="card hover:shadow-lg transition-shadow">
            <div class="h-2 rounded-t-lg" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>"></div>
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="badge badge-primary"><?= Html::encode($model->code) ?></span>
                        <h3 class="text-lg font-semibold text-gray-900 mt-2"><?= Html::encode($model->name) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>20">
                        <?= Icon::show('users', 'md', '', ['style' => 'color: ' . Html::encode($model->color ?: '#3b82f6')]) ?>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <?= Icon::show('book', 'sm', 'text-gray-400') ?>
                        <span><?= Html::encode($model->subject->name ?? 'Не указан') ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?= Icon::show('user-group', 'sm', 'text-gray-400') ?>
                        <span><?= $model->getPupilsCount() ?> учеников</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2">
                    <?= Html::a(
                        Icon::show('eye', 'sm') . ' Смотреть',
                        OrganizationUrl::to(['group/view', 'id' => $model->id]),
                        ['class' => 'btn btn-secondary btn-sm flex-1']
                    ) ?>
                    <?= Html::a(
                        Icon::show('users', 'sm') . ' Ученики',
                        OrganizationUrl::to(['group/pupils', 'id' => $model->id]),
                        ['class' => 'btn btn-primary btn-sm flex-1']
                    ) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body py-12 text-center text-gray-500">
            <?= Icon::show('users', 'xl', 'mx-auto text-gray-400') ?>
            <p class="mt-4">У вас пока нет групп</p>
            <p class="text-sm text-gray-400 mt-1">Обратитесь к администратору для назначения на группы</p>
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
