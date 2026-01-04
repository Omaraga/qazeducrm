<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $currentController */
/** @var app\models\User $user */

$menuItems = [
    [
        'label' => 'Dashboard',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        'url' => ['/crm/default/index'],
        'controller' => 'default',
    ],
    [
        'label' => 'Ученики',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        'url' => ['/crm/pupil/index'],
        'controller' => 'pupil',
    ],
    [
        'label' => 'Группы',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'url' => ['/crm/group/index'],
        'controller' => 'group',
    ],
    [
        'label' => 'Расписание',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'url' => ['/crm/schedule/index'],
        'controller' => 'schedule',
    ],
    [
        'label' => 'Платежи',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'url' => ['/crm/payment/index'],
        'controller' => 'payment',
    ],
    [
        'label' => 'Зарплаты',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'url' => ['/crm/salary/index'],
        'controller' => 'salary',
    ],
    [
        'label' => 'Лиды',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'url' => ['/crm/lids/index'],
        'controller' => 'lids',
    ],
    [
        'label' => 'SMS',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
        'url' => ['/crm/sms/index'],
        'controller' => 'sms',
    ],
    [
        'label' => 'Отчёты',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
        'url' => ['/crm/reports/index'],
        'controller' => 'reports',
    ],
];

$settingsItems = [
    [
        'label' => 'Сотрудники',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'url' => ['/crm/user/index'],
        'controller' => 'user',
    ],
    [
        'label' => 'Предметы',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'url' => ['/crm/subject/index'],
        'controller' => 'subject',
    ],
    [
        'label' => 'Тарифы',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
        'url' => ['/crm/tariff/index'],
        'controller' => 'tariff',
    ],
    [
        'label' => 'Способы оплаты',
        'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'url' => ['/crm/pay-method/index'],
        'controller' => 'pay-method',
    ],
];
?>

<aside class="sidebar -translate-x-full lg:translate-x-0" id="sidebar" x-data="{ open: false }">
    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="/images/logo-text.svg" alt="Qazaq Education" class="h-7">
        <span class="bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">CRM</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav scrollbar-thin">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Меню</div>
            <?php foreach ($menuItems as $item): ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="sidebar-link <?= $currentController === $item['controller'] ? 'active' : '' ?>">
                    <?= $item['icon'] ?>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Настройки</div>
            <?php foreach ($settingsItems as $item): ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="sidebar-link <?= $currentController === $item['controller'] ? 'active' : '' ?>">
                    <?= $item['icon'] ?>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="flex items-center gap-3">
            <div class="avatar avatar-md bg-gray-700 text-white">
                <?= mb_substr($user->username ?? 'U', 0, 1) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate">
                    <?= Html::encode($user->username ?? 'User') ?>
                </div>
                <div class="text-gray-500 text-xs">
                    <?= Html::encode($user->active_role ?? 'Пользователь') ?>
                </div>
            </div>
            <a href="<?= Url::to(['/logout']) ?>" class="text-gray-500 hover:text-white p-1" data-method="post" title="Выйти">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden hidden" id="sidebar-overlay"
     onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');"></div>
