<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\KnowledgeCategory $category */
/** @var app\models\KnowledgeArticle[] $articles */

$this->title = $category->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'База знаний'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="hover:text-primary-600">База знаний</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($category->name) ?></h1>
            <?php if ($category->description): ?>
            <p class="text-gray-500 mt-1"><?= Html::encode($category->description) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['knowledge/search']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Поиск
            </a>
        </div>
    </div>

    <!-- Articles List -->
    <div class="card">
        <?php if (empty($articles)): ?>
        <div class="p-12 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>В этой категории пока нет статей</p>
            <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="btn btn-primary mt-4">
                Вернуться в базу знаний
            </a>
        </div>
        <?php else: ?>
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
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <?= $article->views ?> просмотров
                        </span>
                        <?php if ($article->is_featured): ?>
                        <span class="flex items-center gap-1 text-yellow-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Избранное
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 group-hover:translate-x-1 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
