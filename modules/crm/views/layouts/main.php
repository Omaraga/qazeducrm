<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\helpers\Lists;
use app\helpers\RoleChecker;
use app\models\Organizations;
use app\widgets\tailwind\Alert;
use app\widgets\tailwind\Breadcrumbs;
use app\widgets\tailwind\ConfirmModal;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\SidebarMenu;
use app\widgets\tailwind\SubscriptionAlert;
use app\widgets\tailwind\SubscriptionBadge;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$user = Yii::$app->user->identity;
$currentOrganization = Organizations::getCurrentOrganization();
$userOrganizations = $user->userOrganizations ?? [];
$rolesList = Lists::getRoles();
$hasMultipleOrganizations = count($userOrganizations) > 1;

// Переменные для проверки ролей
$isDirector = RoleChecker::isDirector();           // SUPER, GENERAL_DIRECTOR, DIRECTOR
$hasFinanceAccess = RoleChecker::hasFinanceAccess(); // Доступ к финансам (то же что isDirector)
$isAdminOrHigher = RoleChecker::isAdminOrHigher(); // SUPER, GENERAL_DIRECTOR, DIRECTOR, ADMIN
$isTeacherOnly = RoleChecker::isTeacherOnly();     // Только учитель

// Конфигурация меню
$menuConfig = [
    [
        'section' => 'Меню',
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['/crm/default/index'], 'controller' => 'default'],
            ['label' => 'Расписание', 'icon' => 'calendar', 'url' => ['/crm/schedule/index'], 'controller' => 'schedule'],
            // Пункт "Мои группы" только для учителя
            ['label' => 'Мои группы', 'icon' => 'group', 'url' => ['/crm/group/my-groups'], 'controller' => 'group', 'visible' => $isTeacherOnly],
            [
                'label' => 'Лиды',
                'icon' => 'funnel',
                'controller' => ['lids', 'lids-funnel', 'lids-interaction'],
                'visible' => $isAdminOrHigher,
                'items' => [
                    ['label' => 'Kanban', 'url' => ['/crm/lids-funnel/kanban']],
                    ['label' => 'Таблица', 'url' => ['/crm/lids/index']],
                    ['label' => 'Аналитика', 'url' => ['/crm/lids-funnel/analytics']],
                    ['label' => 'Скрипты продаж', 'url' => ['/crm/sales-script/index']],
                ],
            ],
            ['label' => 'Ученики', 'icon' => 'users', 'url' => ['/crm/pupil/index'], 'controller' => 'pupil', 'visible' => $isAdminOrHigher],
            ['label' => 'Группы', 'icon' => 'group', 'url' => ['/crm/group/index'], 'controller' => 'group', 'visible' => $isAdminOrHigher],
            ['label' => 'Шаблоны расписания', 'icon' => 'template', 'url' => ['/crm/schedule-template/index'], 'controller' => 'schedule-template', 'visible' => $isAdminOrHigher],
            ['label' => 'Платежи', 'icon' => 'payment', 'url' => ['/crm/payment/index'], 'controller' => 'payment', 'visible' => $hasFinanceAccess],
            ['label' => 'Зарплаты', 'icon' => 'wallet', 'url' => ['/crm/salary/index'], 'controller' => 'salary'],

            ['label' => 'SMS', 'icon' => 'sms', 'url' => ['/crm/sms/index'], 'controller' => 'sms', 'visible' => $isAdminOrHigher],
            [
                'label' => 'Отчёты',
                'icon' => 'chart',
                'controller' => 'reports',
                'items' => [
                    ['label' => 'Все отчёты', 'url' => ['/crm/reports/index']],
                    // Финансовые отчёты - только для директоров
                    ['label' => '─── Финансы ───', 'header' => true, 'visible' => $hasFinanceAccess],
                    ['label' => 'Доходы', 'url' => ['/crm/reports/view', 'type' => 'finance-income'], 'visible' => $hasFinanceAccess],
                    ['label' => 'Расходы', 'url' => ['/crm/reports/view', 'type' => 'finance-expenses'], 'visible' => $hasFinanceAccess],
                    ['label' => 'Задолженности', 'url' => ['/crm/reports/view', 'type' => 'finance-debts'], 'visible' => $hasFinanceAccess],
                    // Продажи - для админов и выше
                    ['label' => '─── Продажи ───', 'header' => true, 'visible' => $isAdminOrHigher],
                    ['label' => 'Воронка продаж', 'url' => ['/crm/reports/view', 'type' => 'leads-funnel'], 'visible' => $isAdminOrHigher],
                    ['label' => 'Источники лидов', 'url' => ['/crm/reports/view', 'type' => 'leads-sources'], 'visible' => $isAdminOrHigher],
                    ['label' => 'Менеджеры', 'url' => ['/crm/reports/view', 'type' => 'leads-managers'], 'visible' => $isAdminOrHigher],
                    // Ученики - для всех
                    ['label' => '─── Ученики ───', 'header' => true],
                    ['label' => 'Посещаемость', 'url' => ['/crm/reports/view', 'type' => 'pupils-attendance']],
                    // Учителя - зарплаты только для директоров
                    ['label' => '─── Учителя ───', 'header' => true, 'visible' => $hasFinanceAccess],
                    ['label' => 'Зарплаты учителей', 'url' => ['/crm/reports/view', 'type' => 'teachers-salary'], 'visible' => $hasFinanceAccess],
                    // Операции - для админов и выше
                    ['label' => '─── Операции ───', 'header' => true, 'visible' => $isAdminOrHigher],
                    ['label' => 'Загрузка групп', 'url' => ['/crm/reports/view', 'type' => 'operations-groups'], 'visible' => $isAdminOrHigher],
                ],
            ],
        ]
    ],
    [
        'section' => 'Настройки',
        'collapsible' => true,
        'collapsed' => true,
        'visible' => $isAdminOrHigher, // Вся секция скрыта для учителя
        'items' => [
            ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/crm/user/index'], 'controller' => 'user'],
            ['label' => 'Предметы', 'icon' => 'book', 'url' => ['/crm/subject/index'], 'controller' => 'subject'],
            ['label' => 'Тарифы', 'icon' => 'tag', 'url' => ['/crm/tariff/index'], 'controller' => 'tariff', 'visible' => $isDirector],
            ['label' => 'Кабинеты', 'icon' => 'building-office', 'url' => ['/crm/room/index'], 'controller' => 'room'],
            ['label' => 'Способы оплаты', 'icon' => 'card', 'url' => ['/crm/pay-method/index'], 'controller' => 'pay-method', 'visible' => $isDirector],
            ['label' => 'Права доступа', 'icon' => 'shield-check', 'url' => ['/crm/settings/access'], 'controller' => 'settings', 'visible' => $isDirector],
            ['label' => 'Подписка', 'icon' => 'credit-card', 'url' => ['/crm/subscription/index'], 'controller' => 'subscription'],
        ]
    ],
    [
        'section' => 'Помощь',
        'items' => [
            ['label' => 'База знаний', 'icon' => 'book-open', 'url' => ['/crm/knowledge/index'], 'controller' => 'knowledge'],
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
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
    }
}">
<?php $this->beginBody() ?>

<?php // Панель режима имитации пользователя (impersonate) ?>
<?php if (Yii::$app->has('impersonate') && Yii::$app->impersonate->isImpersonating()): ?>
    <?php $targetUser = Yii::$app->impersonate->getTargetUser(); ?>
    <div class="impersonate-bar" style="position: fixed; left: 0; right: 0; top: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; gap: 16px; padding: 8px 16px; color: white; font-weight: 500; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: linear-gradient(90deg, #dc3545, #fd7e14);">
        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span>Вы вошли как: <strong><?= Html::encode($targetUser->fio ?? $targetUser->username ?? 'Пользователь') ?></strong></span>
        <form action="<?= Url::to(['/impersonate/stop']) ?>" method="post" style="display: inline; margin: 0;">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <button type="submit" style="display: inline-flex; align-items: center; padding: 6px 12px; background: white; color: #374151; font-size: 14px; font-weight: 500; border-radius: 6px; border: none; cursor: pointer;">
                <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Вернуться к своему аккаунту
            </button>
        </form>
    </div>
    <style>
        body { padding-top: 44px !important; }
        aside.fixed { top: 44px !important; height: calc(100% - 44px) !important; }
    </style>
<?php endif; ?>

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
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-300"
       :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full': !sidebarOpen && !sidebarCollapsed,
           'lg:translate-x-0': !sidebarCollapsed,
           'lg:-translate-x-full': sidebarCollapsed
       }">
    <!-- Brand -->
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200">
        <a href="<?= Url::to(['/crm']) ?>" class="flex items-center gap-2">
            <img src="/images/logo-text-dark.svg" alt="Qazaq Education" class="h-8">
            <span class="text-white text-[10px] font-bold px-1.5 py-0.5 rounded" style="background: #FE8D00;">CRM</span>
        </a>
        <!-- Collapse button (desktop only) -->
        <button @click="toggleSidebar()" class="hidden lg:flex p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors" title="Свернуть меню">
            <?= Icon::show('chevron-left', 'w-4 h-4') ?>
        </button>
    </div>

    <!-- Navigation -->
    <?= SidebarMenu::widget(['items' => $menuConfig]) ?>

    <!-- Footer: User Info & Logout -->
    <div class="border-t border-gray-200 px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-sm font-medium">
            <?= mb_substr($user->username ?? 'U', 0, 1) ?>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-gray-900 text-sm font-medium truncate"><?= Html::encode($user->fio ?? $user->username ?? 'User') ?></div>
            <div class="text-gray-500 text-xs truncate"><?= Html::encode($user->email ?? '') ?></div>
        </div>
        <a href="<?= Url::to(['/logout']) ?>" class="text-gray-400 hover:text-danger-600 p-1.5 rounded-md hover:bg-gray-100 transition-colors" data-method="post" title="Выйти">
            <?= Icon::show('logout') ?>
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="min-h-screen transition-[margin] duration-300" :class="sidebarCollapsed ? 'lg:ml-0' : 'lg:ml-64'">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-30 overflow-visible">
        <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button type="button" class="lg:hidden -ml-2 p-2 text-gray-500 hover:text-gray-700" @click="sidebarOpen = true">
                <?= Icon::show('menu', 'lg') ?>
            </button>
            <!-- Desktop expand button (when collapsed) -->
            <button type="button"
                    x-show="sidebarCollapsed"
                    @click="toggleSidebar()"
                    class="hidden lg:flex -ml-2 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
                    title="Развернуть меню">
                <?= Icon::show('menu', 'lg') ?>
            </button>

            <!-- Breadcrumbs -->
            <?= Breadcrumbs::widget([
                'homeLabel' => false,
                'links' => $this->params['breadcrumbs'] ?? [],
            ]) ?>
        </div>

        <div class="flex items-center gap-3">
            <!-- Subscription Badge -->
            <?= SubscriptionBadge::widget(['compact' => false]) ?>

            <?php if ($hasMultipleOrganizations): ?>
            <!-- Organization Switcher -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition-colors text-left">
                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-gray-900 text-sm font-medium"><?= Html::encode($currentOrganization->name ?? 'Организация') ?></span>
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute right-0 mt-2 w-80 origin-top-right bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-[100]"
                     style="display: none;">
                    <div class="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        Переключить филиал
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <?php foreach ($userOrganizations as $userOrg): ?>
                            <?php
                            $isActive = $currentOrganization && $userOrg->target_id == $currentOrganization->id;
                            $orgName = $userOrg->organization->name ?? 'Организация';
                            $roleName = $rolesList[$userOrg->role] ?? $userOrg->role;
                            ?>
                            <a href="<?= Url::to(['/site/change-role', 'id' => $userOrg->id]) ?>"
                               class="block px-3 py-2.5 hover:bg-gray-50 transition-colors <?= $isActive ? 'bg-primary-50' : '' ?>">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-sm <?= $isActive ? 'text-primary-700 font-medium' : 'text-gray-900' ?>"><?= Html::encode($orgName) ?></div>
                                        <div class="text-xs text-gray-500"><?= Html::encode($roleName) ?></div>
                                    </div>
                                    <?php if ($isActive): ?>
                                        <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notifications Bell -->
            <?= $this->render('_notifications-bell') ?>

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
        <?= SubscriptionAlert::widget(['level' => 'warning']) ?>
        <?= $content ?>
    </main>
</div>

<?= ConfirmModal::widget() ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
