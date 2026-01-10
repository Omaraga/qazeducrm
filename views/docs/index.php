<?php

/** @var yii\web\View $this */
/** @var app\models\DocsChapter[] $chapters */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Руководство пользователя';

$this->params['chapters'] = $chapters;
$this->params['currentChapter'] = null;
$this->params['currentSection'] = null;

// Иконки для глав (FontAwesome Free)
$chapterIcons = [
    'rocket' => 'fa-rocket',
    'users' => 'fa-users',
    'user-group' => 'fa-users',           // fa-user-group не существует в FA Free
    'user-tie' => 'fa-user-tie',
    'calendar' => 'fa-calendar',          // fa-calendar-days может не работать
    'clipboard-check' => 'fa-clipboard-check',
    'credit-card' => 'fa-credit-card',
    'money-bill' => 'fa-money-bill-wave',
    'funnel' => 'fa-filter',
    'comments' => 'fa-comments',
    'cog' => 'fa-cog',                    // fa-gear может не работать, fa-cog стандартная
    'book' => 'fa-book',
];
?>

<div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Руководство пользователя</h1>
        <p class="text-lg text-gray-600">
            Полная документация по работе с системой QazEduCRM. Здесь вы найдёте пошаговые инструкции
            от регистрации организации до управления зарплатами преподавателей.
        </p>
    </div>

    <!-- Quick start -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 mb-8 text-white">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-rocket text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold mb-2">Быстрый старт</h2>
                <p class="text-orange-100 mb-4">
                    Новый пользователь? Начните с раздела "Начало работы" чтобы зарегистрировать организацию
                    и познакомиться с интерфейсом системы.
                </p>
                <?php if (!empty($chapters)): ?>
                    <?php $firstSection = $chapters[0]->getFirstSection(); ?>
                    <?php if ($firstSection): ?>
                        <a href="<?= Url::to(['/docs/section', 'chapter' => $chapters[0]->slug, 'slug' => $firstSection->slug]) ?>"
                           class="inline-flex items-center gap-2 bg-white text-orange-600 px-4 py-2 rounded-lg font-medium hover:bg-orange-50 transition-colors">
                            Начать изучение
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chapters grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($chapters as $index => $chapter): ?>
            <?php
            $iconClass = $chapterIcons[$chapter->icon] ?? 'fa-book';
            $firstSection = $chapter->getFirstSection();
            $sectionCount = $chapter->getSectionCount();
            ?>
            <a href="<?= $firstSection ? Url::to(['/docs/section', 'chapter' => $chapter->slug, 'slug' => $firstSection->slug]) : Url::to(['/docs/chapter', 'slug' => $chapter->slug]) ?>"
               class="group block bg-white border border-gray-200 rounded-xl p-5 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                        <i class="fas <?= $iconClass ?>"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs text-gray-400 font-medium"><?= $index + 1 ?></span>
                            <h3 class="font-semibold text-gray-900 group-hover:text-orange-600 transition-colors">
                                <?= Html::encode($chapter->title) ?>
                            </h3>
                        </div>
                        <?php if ($chapter->description): ?>
                            <p class="text-sm text-gray-500 line-clamp-2"><?= Html::encode($chapter->description) ?></p>
                        <?php endif; ?>
                        <div class="mt-2 text-xs text-gray-400">
                            <?= $sectionCount ?> <?= Yii::t('app', '{n,plural,=0{разделов} =1{раздел} one{раздел} few{раздела} many{разделов} other{разделов}}', ['n' => $sectionCount]) ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-orange-500 transition-colors"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Help section -->
    <div class="mt-12 bg-gray-100 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Нужна помощь?</h2>
        <p class="text-gray-600 mb-4">
            Если вы не нашли ответ на свой вопрос в документации, свяжитесь с нашей службой поддержки.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="<?= Url::to(['/contact']) ?>" class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-envelope"></i>
                Связаться с нами
            </a>
        </div>
    </div>
</div>
