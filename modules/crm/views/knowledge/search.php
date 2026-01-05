<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $query */
/** @var app\models\KnowledgeArticle[] $articles */

$this->title = Yii::t('main', 'Поиск по базе знаний');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'База знаний'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header with Search -->
    <div class="text-center max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-4"><?= Html::encode($this->title) ?></h1>

        <!-- Search Form -->
        <form action="<?= OrganizationUrl::to(['knowledge/search']) ?>" method="get" class="relative">
            <input type="text" name="q" value="<?= Html::encode($query) ?>" placeholder="Поиск по базе знаний..."
                   class="w-full px-4 py-3 pl-12 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition"
                   autofocus>
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <?php if ($query): ?>
            <a href="<?= OrganizationUrl::to(['knowledge/search']) ?>" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results -->
    <?php if ($query): ?>
    <div class="text-center text-sm text-gray-500 mb-4">
        <?php if (count($articles) > 0): ?>
        Найдено статей: <?= count($articles) ?>
        <?php else: ?>
        По запросу «<?= Html::encode($query) ?>» ничего не найдено
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($articles)): ?>
    <div class="card">
        <div class="divide-y divide-gray-100">
            <?php foreach ($articles as $article): ?>
            <a href="<?= OrganizationUrl::to(['knowledge/view', 'slug' => $article->slug]) ?>"
               class="flex items-start gap-4 p-6 hover:bg-gray-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors mb-1">
                        <?= Html::encode($article->title) ?>
                    </h3>
                    <?php if ($article->excerpt): ?>
                    <p class="text-sm text-gray-500 line-clamp-2"><?= Html::encode($article->excerpt) ?></p>
                    <?php endif; ?>
                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                        <?php if ($article->category): ?>
                        <span><?= Html::encode($article->category->name) ?></span>
                        <?php endif; ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <?= $article->views ?>
                        </span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 group-hover:translate-x-1 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif ($query): ?>
    <div class="card p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Ничего не найдено</h3>
        <p class="text-gray-500 mb-6">Попробуйте изменить поисковый запрос или просмотрите категории</p>
        <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="btn btn-primary">
            Перейти в базу знаний
        </a>
    </div>
    <?php else: ?>
    <div class="card p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Введите поисковый запрос</h3>
        <p class="text-gray-500">Минимум 2 символа для поиска</p>
    </div>
    <?php endif; ?>

    <!-- Back Link -->
    <div class="text-center">
        <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="text-primary-600 hover:text-primary-700 font-medium">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Вернуться в базу знаний
        </a>
    </div>
</div>
