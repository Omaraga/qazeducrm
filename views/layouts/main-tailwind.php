<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\widgets\tailwind\Alert;
use app\widgets\tailwind\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$currentController = Yii::$app->controller->id;
$user = Yii::$app->user->identity;
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
<body class="h-full bg-gray-50 font-sans antialiased">
<?php $this->beginBody() ?>

<!-- Sidebar -->
<?= $this->render('partials/_sidebar', [
    'currentController' => $currentController,
    'user' => $user,
]) ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <header class="page-header">
        <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button type="button"
                    class="lg:hidden -ml-2 p-2 text-gray-500 hover:text-gray-700"
                    onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden');">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Breadcrumbs -->
            <?= Breadcrumbs::widget([
                'homeLabel' => 'CRM',
                'homeUrl' => ['/crm/default/index'],
                'links' => $this->params['breadcrumbs'] ?? [],
            ]) ?>
        </div>

        <div class="flex items-center gap-3">
            <?php if (Yii::$app->user->can('superadmin')): ?>
                <a href="<?= Url::to(['/superadmin']) ?>" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Админка
                </a>
            <?php endif; ?>

            <!-- User dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100">
                    <div class="avatar avatar-sm bg-primary-100 text-primary-600">
                        <?= mb_substr($user->username ?? 'U', 0, 1) ?>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="dropdown-menu">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <div class="font-medium text-gray-900"><?= Html::encode($user->username ?? 'User') ?></div>
                        <div class="text-xs text-gray-500"><?= Html::encode($user->email ?? '') ?></div>
                    </div>
                    <a href="<?= Url::to(['/site/profile']) ?>" class="dropdown-item">Профиль</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/logout']) ?>" class="dropdown-item text-danger-600" data-method="post">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-body">
        <?= Alert::widget() ?>
        <?= $content ?>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
