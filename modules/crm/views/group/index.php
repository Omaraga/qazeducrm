<?php

use app\helpers\FeatureHelper;
use app\models\Group;
use app\models\Subject;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LimitProgress;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\search\GroupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Группы';
$this->params['breadcrumbs'][] = $this->title;

$canAddGroup = FeatureHelper::canAddGroup();
$buttonStatus = LimitProgress::addButtonStatus('groups');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <?= Html::encode($this->title) ?>
                <?= LimitProgress::inline('groups', ['options' => ['class' => 'ml-2']]) ?>
            </h1>
            <p class="text-gray-500 mt-1">Всего: <?= $dataProvider->getTotalCount() ?> групп</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($canAddGroup): ?>
                <?= Html::a(Icon::show('plus', 'sm') . ' Создать группу', OrganizationUrl::to(['create']), ['class' => 'btn btn-primary']) ?>
            <?php else: ?>
                <span class="btn btn-secondary opacity-50 cursor-not-allowed" title="<?= Html::encode($buttonStatus['message'] ?? '') ?>">
                    <?= Icon::show('lock', 'sm') ?>
                    Создать группу
                </span>
                <?= Html::a('Увеличить лимит', OrganizationUrl::to(['subscription/upgrade']), ['class' => 'btn btn-warning btn-sm']) ?>
            <?php endif; ?>
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
                    <?= Html::activeDropDownList($searchModel, 'subject_id', ArrayHelper::map(Subject::find()->byOrganization()->all(), 'id', 'name'), ['class' => 'form-select', 'prompt' => 'Все предметы']) ?>
                </div>
                <div>
                    <label class="form-label">Категория</label>
                    <?= Html::activeDropDownList($searchModel, 'category_id', \app\helpers\Lists::getGroupCategories(), ['class' => 'form-select', 'prompt' => 'Все категории']) ?>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary" title="Поиск по указанным критериям">
                        <?= Icon::show('search', 'sm') ?>
                        Найти
                    </button>
                    <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary" title="Сбросить все фильтры">Сброс</a>
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
                        <?= Icon::show('users', 'md', '', ['style' => 'color: ' . Html::encode($model->color ?: '#3b82f6')]) ?>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <?= Icon::show('book', 'sm', 'text-gray-400') ?>
                        <span><?= Html::encode($model->subject->name ?? 'Не указан') ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?= Icon::show('tag', 'sm', 'text-gray-400') ?>
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
            <?= Icon::show('users', 'xl', 'mx-auto text-gray-400') ?>
            <p class="mt-4">Группы не найдены</p>
            <p class="text-sm text-gray-400 mt-1">Создайте первую группу для начала работы</p>
            <a href="<?= OrganizationUrl::to(['create']) ?>" class="btn btn-primary mt-4"><?= Icon::show('plus', 'sm') ?> Создать первую группу</a>
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
