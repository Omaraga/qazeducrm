<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\KnowledgeArticle $article */
/** @var app\models\KnowledgeArticle[] $relatedArticles */

$this->title = $article->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'База знаний'), 'url' => ['index']];
if ($article->category) {
    $this->params['breadcrumbs'][] = ['label' => $article->category->name, 'url' => ['category', 'slug' => $article->category->slug]];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="hover:text-primary-600">База знаний</a>
        <?php if ($article->category): ?>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="<?= OrganizationUrl::to(['knowledge/category', 'slug' => $article->category->slug]) ?>" class="hover:text-primary-600">
            <?= Html::encode($article->category->name) ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Article Content -->
    <article class="card">
        <div class="p-8">
            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4"><?= Html::encode($article->title) ?></h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <?php if ($article->category): ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <?= Html::encode($article->category->name) ?>
                    </span>
                    <?php endif; ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <?= $article->views ?> просмотров
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Обновлено: <?= Yii::$app->formatter->asDate($article->updated_at, 'php:d.m.Y') ?>
                    </span>
                </div>
            </header>

            <!-- Content -->
            <div class="prose prose-primary max-w-none">
                <?= $article->content ?>
            </div>
        </div>
    </article>

    <?php if (!empty($relatedArticles)): ?>
    <!-- Related Articles -->
    <div class="mt-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Связанные статьи</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($relatedArticles as $related): ?>
            <a href="<?= OrganizationUrl::to(['knowledge/view', 'slug' => $related->slug]) ?>"
               class="card p-4 hover:shadow-md transition-shadow group">
                <h3 class="font-medium text-gray-900 group-hover:text-primary-600 transition-colors mb-1">
                    <?= Html::encode($related->title) ?>
                </h3>
                <?php if ($related->excerpt): ?>
                <p class="text-sm text-gray-500 line-clamp-2"><?= Html::encode($related->excerpt) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="mt-8 flex justify-between items-center">
        <a href="<?= OrganizationUrl::to($article->category ? ['knowledge/category', 'slug' => $article->category->slug] : ['knowledge/index']) ?>"
           class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад
        </a>
        <a href="<?= OrganizationUrl::to(['knowledge/index']) ?>" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            В базу знаний
        </a>
    </div>
</div>
