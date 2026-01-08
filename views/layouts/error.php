<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Qazaq Education CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php $this->head() ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
<?php $this->beginBody() ?>

<div class="min-h-full flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="<?= Url::to(['/']) ?>" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-text-dark.svg" alt="Qazaq Education" class="h-7">
                    <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded">CRM</span>
                </a>
                <a href="<?= Url::to(['/']) ?>" class="text-sm text-gray-600 hover:text-orange-500 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    На главную
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
            &copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
