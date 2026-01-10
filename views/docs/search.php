<?php

/** @var yii\web\View $this */
/** @var string $query */
/** @var app\models\DocsSection[] $results */
/** @var app\models\DocsChapter[] $chapters */

use app\models\DocsSection;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $query ? "Поиск: {$query}" : 'Поиск';

$this->params['chapters'] = $chapters;
$this->params['currentChapter'] = null;
$this->params['currentSection'] = null;
?>

<div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Поиск по документации</h1>

        <!-- Search form -->
        <form action="<?= Url::to(['/docs/search']) ?>" method="get" class="mt-4">
            <div class="relative">
                <input type="text"
                       name="q"
                       value="<?= Html::encode($query) ?>"
                       placeholder="Введите поисковый запрос..."
                       class="w-full pl-12 pr-4 py-3 bg-white border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl text-lg transition-all"
                       autofocus>
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                <?php if ($query): ?>
                    <a href="<?= Url::to(['/docs/search']) ?>" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Results -->
    <?php if ($query): ?>
        <?php if (!empty($results)): ?>
            <div class="mb-4 text-gray-600">
                Найдено результатов: <span class="font-semibold text-gray-900"><?= count($results) ?></span>
            </div>

            <div class="space-y-4">
                <?php foreach ($results as $section): ?>
                    <a href="<?= Url::to(['/docs/section', 'chapter' => $section->chapter->slug, 'slug' => $section->slug]) ?>"
                       class="group block bg-white border border-gray-200 rounded-xl p-5 hover:border-orange-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-gray-100 group-hover:bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-file-lines text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-orange-500 font-medium mb-1">
                                    <?= Html::encode($section->chapter->title) ?>
                                </div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-orange-600 transition-colors mb-1">
                                    <?= DocsSection::highlightSearchQuery(Html::encode($section->title), $query) ?>
                                </h3>
                                <p class="text-sm text-gray-500 line-clamp-2">
                                    <?= DocsSection::highlightSearchQuery(Html::encode($section->getExcerptText(200)), $query) ?>
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 group-hover:text-orange-500 transition-colors mt-3"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gray-100 rounded-xl p-8 text-center">
                <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ничего не найдено</h3>
                <p class="text-gray-500 mb-4">
                    По запросу «<?= Html::encode($query) ?>» результатов не найдено.
                </p>
                <p class="text-sm text-gray-400">
                    Попробуйте изменить поисковый запрос или используйте навигацию слева.
                </p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Empty state -->
        <div class="bg-gray-100 rounded-xl p-8 text-center">
            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Введите поисковый запрос</h3>
            <p class="text-gray-500">
                Найдите нужную информацию в документации по ключевым словам.
            </p>
        </div>

        <!-- Popular searches -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Популярные запросы</h3>
            <div class="flex flex-wrap gap-2">
                <?php
                $popularSearches = ['регистрация', 'ученик', 'группа', 'расписание', 'платеж', 'зарплата', 'воронка', 'whatsapp'];
                foreach ($popularSearches as $search):
                ?>
                    <a href="<?= Url::to(['/docs/search', 'q' => $search]) ?>"
                       class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:border-orange-300 hover:text-orange-600 transition-colors">
                        <?= Html::encode($search) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
