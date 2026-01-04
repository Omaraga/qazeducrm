<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\widgets\tailwind\Alert;
use app\widgets\tailwind\Breadcrumbs;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\SidebarMenu;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$user = Yii::$app->user->identity;

// Конфигурация меню
$menuConfig = [
    [
        'section' => 'Меню',
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['/crm/default/index'], 'controller' => 'default'],
            ['label' => 'Ученики', 'icon' => 'users', 'url' => ['/crm/pupil/index'], 'controller' => 'pupil'],
            ['label' => 'Группы', 'icon' => 'group', 'url' => ['/crm/group/index'], 'controller' => 'group'],
            ['label' => 'Расписание', 'icon' => 'calendar', 'url' => ['/crm/schedule/index'], 'controller' => 'schedule'],
            ['label' => 'Платежи', 'icon' => 'payment', 'url' => ['/crm/payment/index'], 'controller' => 'payment'],
            ['label' => 'Зарплаты', 'icon' => 'wallet', 'url' => ['/crm/salary/index'], 'controller' => 'salary'],
            ['label' => 'Лиды', 'icon' => 'funnel', 'url' => ['/crm/lids/index'], 'controller' => 'lids'],
            ['label' => 'SMS', 'icon' => 'sms', 'url' => ['/crm/sms/index'], 'controller' => 'sms'],
            ['label' => 'Отчёты', 'icon' => 'chart', 'url' => ['/crm/reports/index'], 'controller' => 'reports'],
        ]
    ],
    [
        'section' => 'Настройки',
        'collapsible' => true,
        'collapsed' => true,
        'items' => [
            ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/crm/user/index'], 'controller' => 'user'],
            ['label' => 'Предметы', 'icon' => 'book', 'url' => ['/crm/subject/index'], 'controller' => 'subject'],
            ['label' => 'Тарифы', 'icon' => 'tag', 'url' => ['/crm/tariff/index'], 'controller' => 'tariff'],
            ['label' => 'Способы оплаты', 'icon' => 'card', 'url' => ['/crm/pay-method/index'], 'controller' => 'pay-method'],
        ]
    ],
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

<!-- Flash Messages to Toast (hidden, picked up by JS) -->
<?php
$flashTypes = ['success', 'error', 'warning', 'info', 'danger'];
foreach ($flashTypes as $type):
    $flash = Yii::$app->session->getFlash($type);
    if ($flash):
        $toastType = ($type === 'danger') ? 'error' : $type;
        if (is_array($flash)):
            foreach ($flash as $message): ?>
                <div data-flash-message="<?= Html::encode($message) ?>" data-flash-type="<?= $toastType ?>" style="display:none;"></div>
            <?php endforeach;
        else: ?>
            <div data-flash-message="<?= Html::encode($flash) ?>" data-flash-type="<?= $toastType ?>" style="display:none;"></div>
        <?php endif;
    endif;
endforeach;
?>

<!-- Toast Notifications Container -->
<div x-data class="fixed top-4 right-4 z-[100] space-y-2 pointer-events-none" style="max-width: 24rem;">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto flex items-start gap-3 p-4 rounded-lg shadow-lg border"
             :class="{
                 'bg-green-50 border-green-200': toast.type === 'success',
                 'bg-red-50 border-red-200': toast.type === 'error',
                 'bg-yellow-50 border-yellow-200': toast.type === 'warning',
                 'bg-blue-50 border-blue-200': toast.type === 'info'
             }">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <!-- Success Icon -->
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <!-- Error Icon -->
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <!-- Warning Icon -->
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <!-- Info Icon -->
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </template>
            </div>
            <!-- Message -->
            <p class="flex-1 text-sm font-medium"
               :class="{
                   'text-green-800': toast.type === 'success',
                   'text-red-800': toast.type === 'error',
                   'text-yellow-800': toast.type === 'warning',
                   'text-blue-800': toast.type === 'info'
               }"
               x-text="toast.message"></p>
            <!-- Close Button -->
            <button @click="$store.toast.dismiss(toast.id)"
                    class="flex-shrink-0 rounded p-1 hover:bg-black/5 transition-colors"
                    :class="{
                        'text-green-600 hover:text-green-800': toast.type === 'success',
                        'text-red-600 hover:text-red-800': toast.type === 'error',
                        'text-yellow-600 hover:text-yellow-800': toast.type === 'warning',
                        'text-blue-600 hover:text-blue-800': toast.type === 'info'
                    }">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </template>
</div>

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
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-300 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-200">
        <span class="text-gray-900 font-bold text-xl">QazEdu</span>
        <span class="bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">CRM</span>
    </div>

    <!-- Navigation -->
    <?= SidebarMenu::widget(['items' => $menuConfig]) ?>

    <!-- Footer -->
    <div class="px-4 py-4 border-t border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-semibold">
                <?= mb_substr($user->username ?? 'U', 0, 1) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-gray-900 text-sm font-medium truncate"><?= Html::encode($user->username ?? 'User') ?></div>
                <div class="text-gray-500 text-xs"><?= Html::encode($user->active_role ?? 'Пользователь') ?></div>
            </div>
            <a href="<?= Url::to(['/logout']) ?>" class="text-gray-400 hover:text-danger-600 p-1 transition-colors" data-method="post" title="Выйти">
                <?= Icon::show('logout') ?>
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
                <?= Icon::show('menu', 'lg') ?>
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
                    <?= Icon::show('settings', 'sm') ?>
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
