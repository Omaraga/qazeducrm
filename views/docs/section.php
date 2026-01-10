<?php

/** @var yii\web\View $this */
/** @var app\models\DocsChapter $chapter */
/** @var app\models\DocsSection $section */
/** @var app\models\DocsChapter[] $chapters */
/** @var app\models\DocsSection|null $prevSection */
/** @var app\models\DocsSection|null $nextSection */
/** @var array $headings */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $section->title;

$this->params['chapters'] = $chapters;
$this->params['currentChapter'] = $chapter;
$this->params['currentSection'] = $section;
$this->params['headings'] = $headings;
?>

<div class="max-w-4xl">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= Url::to(['/docs']) ?>" class="hover:text-orange-500 transition-colors">Документация</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <a href="<?= Url::to(['/docs/chapter', 'slug' => $chapter->slug]) ?>" class="hover:text-orange-500 transition-colors">
            <?= Html::encode($chapter->title) ?>
        </a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-900"><?= Html::encode($section->title) ?></span>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= Html::encode($section->title) ?></h1>
        <?php if ($section->excerpt): ?>
            <p class="text-lg text-gray-600"><?= Html::encode($section->excerpt) ?></p>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="docs-content prose prose-slate max-w-none">
        <?php if ($section->content): ?>
            <?= $section->content ?>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <i class="fas fa-pencil text-3xl text-yellow-400 mb-3"></i>
                <p class="text-yellow-700 font-medium">Этот раздел находится в разработке</p>
                <p class="text-yellow-600 text-sm mt-1">Содержимое скоро появится</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Prev/Next navigation -->
    <div class="mt-12 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between gap-4">
            <?php if ($prevSection): ?>
                <a href="<?= Url::to(['/docs/section', 'chapter' => $prevSection->chapter->slug, 'slug' => $prevSection->slug]) ?>"
                   class="group flex items-center gap-3 max-w-[45%]">
                    <div class="w-10 h-10 bg-gray-100 group-hover:bg-orange-100 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 mb-0.5">Назад</div>
                        <div class="font-medium text-gray-900 group-hover:text-orange-600 truncate transition-colors">
                            <?= Html::encode($prevSection->title) ?>
                        </div>
                    </div>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <?php if ($nextSection): ?>
                <a href="<?= Url::to(['/docs/section', 'chapter' => $nextSection->chapter->slug, 'slug' => $nextSection->slug]) ?>"
                   class="group flex items-center gap-3 max-w-[45%] text-right">
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 mb-0.5">Далее</div>
                        <div class="font-medium text-gray-900 group-hover:text-orange-600 truncate transition-colors">
                            <?= Html::encode($nextSection->title) ?>
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 group-hover:bg-orange-100 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Last updated -->
    <div class="mt-8 text-sm text-gray-400">
        Последнее обновление: <?= Yii::$app->formatter->asDate($section->updated_at, 'long') ?>
    </div>
</div>
