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

$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['/crm/default/index'], 'controller' => 'default'],
    ['label' => 'Ученики', 'icon' => 'users', 'url' => ['/crm/pupil/index'], 'controller' => 'pupil'],
    ['label' => 'Группы', 'icon' => 'group', 'url' => ['/crm/group/index'], 'controller' => 'group'],
    ['label' => 'Расписание', 'icon' => 'calendar', 'url' => ['/crm/schedule/index'], 'controller' => 'schedule'],
    ['label' => 'Платежи', 'icon' => 'payment', 'url' => ['/crm/payment/index'], 'controller' => 'payment'],
    ['label' => 'Зарплаты', 'icon' => 'wallet', 'url' => ['/crm/salary/index'], 'controller' => 'salary'],
    ['label' => 'Лиды', 'icon' => 'funnel', 'url' => ['/crm/lids/index'], 'controller' => 'lids'],
    ['label' => 'SMS', 'icon' => 'sms', 'url' => ['/crm/sms/index'], 'controller' => 'sms'],
    ['label' => 'Отчёты', 'icon' => 'chart', 'url' => ['/crm/reports/index'], 'controller' => 'reports'],
];

$settingsItems = [
    ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/crm/user/index'], 'controller' => 'user'],
    ['label' => 'Предметы', 'icon' => 'book', 'url' => ['/crm/subject/index'], 'controller' => 'subject'],
    ['label' => 'Тарифы', 'icon' => 'tag', 'url' => ['/crm/tariff/index'], 'controller' => 'tariff'],
    ['label' => 'Способы оплаты', 'icon' => 'card', 'url' => ['/crm/pay-method/index'], 'controller' => 'pay-method'],
];

$icons = [
    'dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
    'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
    'group' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'calendar' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    'payment' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'wallet' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
    'funnel' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>',
    'sms' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
    'chart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
    'user' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    'book' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
    'tag' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
    'card' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
];
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
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
<?php $this->beginBody() ?>

<!-- Mobile sidebar backdrop -->
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
     @click="sidebarOpen = false"
     style="display: none;"></div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col transform transition-transform duration-300 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
        <span class="text-white font-bold text-xl">QazEdu</span>
        <span class="bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">CRM</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin">
        <div class="px-3 mb-6">
            <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Меню</div>
            <?php foreach ($menuItems as $item): ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1 <?= $currentController === $item['controller'] ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <?= $icons[$item['icon']] ?? '' ?>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="px-3 mb-6">
            <div class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Настройки</div>
            <?php foreach ($settingsItems as $item): ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1 <?= $currentController === $item['controller'] ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <?= $icons[$item['icon']] ?? '' ?>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- Footer -->
    <div class="px-4 py-4 border-t border-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-700 text-white flex items-center justify-center font-semibold">
                <?= mb_substr($user->username ?? 'U', 0, 1) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate"><?= Html::encode($user->username ?? 'User') ?></div>
                <div class="text-gray-500 text-xs"><?= Html::encode($user->active_role ?? 'Пользователь') ?></div>
            </div>
            <a href="<?= Url::to(['/logout']) ?>" class="text-gray-500 hover:text-white p-1" data-method="post" title="Выйти">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</aside>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button type="button" class="lg:hidden -ml-2 p-2 text-gray-500 hover:text-gray-700" @click="sidebarOpen = true">
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
        </div>
    </header>

    <!-- Page Content -->
    <main class="p-4 lg:p-6">
        <?= Alert::widget() ?>
        <?= $content ?>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
