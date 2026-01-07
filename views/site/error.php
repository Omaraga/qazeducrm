<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $name;

// Определяем код ошибки
$statusCode = $exception instanceof \yii\web\HttpException ? $exception->statusCode : 500;

// Иконка и цвет в зависимости от типа ошибки
$iconColor = $statusCode === 404 ? 'text-amber-500' : 'text-red-500';
$bgColor = $statusCode === 404 ? 'bg-amber-50' : 'bg-red-50';
$borderColor = $statusCode === 404 ? 'border-amber-200' : 'border-red-200';
?>

<div class="w-full max-w-lg">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Error Code Header -->
        <div class="<?= $bgColor ?> px-8 py-12 text-center border-b <?= $borderColor ?>">
            <div class="<?= $iconColor ?> mb-4">
                <?php if ($statusCode === 404): ?>
                    <!-- Search/Not Found Icon -->
                    <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10H7"/>
                    </svg>
                <?php elseif ($statusCode === 403): ?>
                    <!-- Lock Icon -->
                    <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                <?php elseif ($statusCode === 500): ?>
                    <!-- Server Error Icon -->
                    <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                <?php else: ?>
                    <!-- Generic Error Icon -->
                    <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                <?php endif; ?>
            </div>
            <h1 class="text-6xl font-bold text-gray-800 mb-2"><?= $statusCode ?></h1>
            <p class="text-xl font-medium text-gray-600"><?= Html::encode($name) ?></p>
        </div>

        <!-- Error Message -->
        <div class="px-8 py-6">
            <div class="text-center mb-6">
                <p class="text-gray-600 leading-relaxed">
                    <?= nl2br(Html::encode($message)) ?>
                </p>
            </div>

            <?php if ($statusCode === 404): ?>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Страница, которую вы ищете, не существует или была перемещена.
                </p>
            <?php elseif ($statusCode === 403): ?>
                <p class="text-sm text-gray-500 text-center mb-6">
                    У вас нет прав доступа к этой странице.
                </p>
            <?php elseif ($statusCode === 500): ?>
                <p class="text-sm text-gray-500 text-center mb-6">
                    Произошла внутренняя ошибка сервера. Пожалуйста, попробуйте позже.
                </p>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="history.back()" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Вернуться назад
                </button>

                <a href="<?= Url::to(['/']) ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    На главную
                </a>
            </div>
        </div>
    </div>

    <!-- Help Text -->
    <p class="text-center text-sm text-gray-500 mt-6">
        Если проблема повторяется, свяжитесь с
        <a href="mailto:support@qazedu.kz" class="text-indigo-600 hover:text-indigo-800">технической поддержкой</a>
    </p>
</div>
