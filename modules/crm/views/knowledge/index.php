<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\KnowledgeCategory[] $categories */
/** @var app\models\KnowledgeArticle[] $featured */

$this->title = Yii::t('main', 'База знаний');
$this->params['breadcrumbs'][] = $this->title;

$iconMap = [
    'rocket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>',
    'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
    'puzzle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>',
    'question-mark' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'support' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
];

$categoryColors = [
    'getting-started' => 'bg-green-100 text-green-600',
    'modules' => 'bg-blue-100 text-blue-600',
    'faq' => 'bg-yellow-100 text-yellow-600',
    'support' => 'bg-purple-100 text-purple-600',
];

function getIcon($icon, $iconMap) {
    return $iconMap[$icon] ?? $iconMap['book'];
}
?>

<div class="space-y-8">
    <!-- Header with Search -->
    <div class="text-center max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-600 mb-6">Найдите ответы на ваши вопросы, изучите инструкции и узнайте, как эффективно использовать систему.</p>

        <!-- Search Form -->
        <form action="<?= OrganizationUrl::to(['knowledge/search']) ?>" method="get" class="relative">
            <input type="text" name="q" placeholder="Поиск по базе знаний..."
                   class="w-full px-4 py-3 pl-12 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition">
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </form>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($categories as $category): ?>
        <a href="<?= OrganizationUrl::to(['knowledge/category', 'slug' => $category->slug]) ?>"
           class="card hover:shadow-lg transition-shadow group">
            <div class="p-6">
                <div class="w-12 h-12 rounded-xl <?= $categoryColors[$category->slug] ?? 'bg-gray-100 text-gray-600' ?> flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= getIcon($category->icon, $iconMap) ?>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= Html::encode($category->name) ?></h3>
                <p class="text-sm text-gray-500 mb-3"><?= Html::encode($category->description) ?></p>
                <div class="flex items-center text-sm text-primary-600 font-medium">
                    <span><?= $category->getArticleCount() ?> статей</span>
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($featured)): ?>
    <!-- Featured Articles -->
    <div class="mt-12">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Популярные статьи</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featured as $article): ?>
            <a href="<?= OrganizationUrl::to(['knowledge/view', 'slug' => $article->slug]) ?>"
               class="card hover:shadow-lg transition-shadow group">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors">
                                <?= Html::encode($article->title) ?>
                            </h3>
                            <p class="text-sm text-gray-500 line-clamp-2"><?= Html::encode($article->excerpt) ?></p>
                            <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <?= $article->views ?>
                                </span>
                                <?php if ($article->category): ?>
                                <span><?= Html::encode($article->category->name) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Help -->
    <div class="mt-12 bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 text-white">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-bold mb-2">Не нашли ответ?</h3>
                <p class="text-primary-100">Свяжитесь с нашей службой поддержки, мы поможем решить ваш вопрос.</p>
            </div>
            <a href="<?= OrganizationUrl::to(['knowledge/category', 'slug' => 'support']) ?>"
               class="btn bg-white text-primary-600 hover:bg-primary-50 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Связаться с поддержкой
            </a>
        </div>
    </div>
</div>
