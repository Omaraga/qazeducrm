<?php

/** @var yii\web\View $this */
/** @var app\models\DocsChapter $chapter */
/** @var app\models\DocsChapter[] $chapters */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $chapter->title;

$this->params['chapters'] = $chapters;
$this->params['currentChapter'] = $chapter;
$this->params['currentSection'] = null;
?>

<div class="max-w-4xl">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= Url::to(['/docs']) ?>" class="hover:text-orange-500 transition-colors">Документация</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-900"><?= Html::encode($chapter->title) ?></span>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= Html::encode($chapter->title) ?></h1>
        <?php if ($chapter->description): ?>
            <p class="text-lg text-gray-600"><?= Html::encode($chapter->description) ?></p>
        <?php endif; ?>
    </div>

    <!-- Sections list -->
    <?php $sections = $chapter->activeSections; ?>
    <?php if (!empty($sections)): ?>
        <div class="space-y-3">
            <?php foreach ($sections as $index => $section): ?>
                <a href="<?= Url::to(['/docs/section', 'chapter' => $chapter->slug, 'slug' => $section->slug]) ?>"
                   class="group flex items-center gap-4 bg-white border border-gray-200 rounded-lg p-4 hover:border-orange-300 hover:shadow-sm transition-all">
                    <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center font-semibold text-sm group-hover:bg-orange-500 group-hover:text-white transition-colors">
                        <?= $index + 1 ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900 group-hover:text-orange-600 transition-colors">
                            <?= Html::encode($section->title) ?>
                        </h3>
                        <?php if ($section->excerpt): ?>
                            <p class="text-sm text-gray-500 mt-0.5"><?= Html::encode($section->excerpt) ?></p>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-orange-500 transition-colors"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-gray-100 rounded-lg p-8 text-center">
            <i class="fas fa-file-lines text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">В этой главе пока нет разделов</p>
        </div>
    <?php endif; ?>
</div>
