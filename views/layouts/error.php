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
    <?php $this->head() ?>
</head>
<body class="h-full bg-gray-100 font-sans antialiased">
<?php $this->beginBody() ?>

<div class="min-h-full flex flex-col">
    <!-- Header -->
    <header class="bg-gray-900 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="<?= Url::to(['/']) ?>" class="flex items-center gap-3">
                    <img src="/images/logo-text.svg" alt="Qazaq Education" class="h-7">
                    <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded">CRM</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
            &copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
