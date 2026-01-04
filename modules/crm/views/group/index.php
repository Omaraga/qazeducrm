<?php

use app\models\Group;
use app\models\Subject;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\search\GroupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Группы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Всего: <?= $dataProvider->getTotalCount() ?> групп</p>
        </div>
        <div>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> Создать группу', OrganizationUrl::to(['create']), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Код</label>
                    <?= Html::activeTextInput($searchModel, 'code', ['class' => 'form-input', 'placeholder' => 'Код группы...']) ?>
                </div>
                <div>
                    <label class="form-label">Предмет</label>
                    <?= Html::activeDropDownList($searchModel, 'subject_id', ArrayHelper::map(Subject::find()->all(), 'id', 'name'), ['class' => 'form-select', 'prompt' => 'Все предметы']) ?>
                </div>
                <div>
                    <label class="form-label">Категория</label>
                    <?= Html::activeDropDownList($searchModel, 'category_id', \app\helpers\Lists::getGroupCategories(), ['class' => 'form-select', 'prompt' => 'Все категории']) ?>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Найти
                    </button>
                    <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary">Сброс</a>
                </div>
            </form>
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
                        <svg class="w-5 h-5" style="color: <?= Html::encode($model->color ?: '#3b82f6') ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span><?= Html::encode($model->subject->name ?? 'Не указан') ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span><?= \app\helpers\Lists::getGroupCategories()[$model->category_id] ?? 'Не указана' ?></span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <?= Html::a('Посмотреть', OrganizationUrl::to(['group/view', 'id' => $model->id]), ['class' => 'btn btn-secondary btn-sm w-full']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body py-12 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-2">Группы не найдены</p>
            <a href="<?= OrganizationUrl::to(['create']) ?>" class="btn btn-primary mt-4">Создать первую группу</a>
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
