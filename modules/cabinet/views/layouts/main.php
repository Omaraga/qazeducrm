<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, viewport-fit=cover']);
$this->registerMetaTag(['name' => 'apple-mobile-web-app-capable', 'content' => 'yes']);
$this->registerMetaTag(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => 'black-translucent']);
$this->registerMetaTag(['name' => 'theme-color', 'content' => '#4f46e5']);

$isLoggedIn = Module::checkParentAuth();
$organization = Module::getCurrentOrganization();
$orgId = $organization ? $organization->id : null;

$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;

// Получаем настройки кабинета из организации
$cabinetSettings = [
    'enabled' => (bool)($organization->cabinet_enabled ?? true),
    'primaryColor' => $organization->cabinet_primary_color ?? '#6366f1',
    'welcomeMessage' => $organization->cabinet_welcome_message ?? '',
    'showBalance' => (bool)($organization->cabinet_show_balance ?? true),
    'showSchedule' => (bool)($organization->cabinet_show_schedule ?? true),
    'showAttendance' => (bool)($organization->cabinet_show_attendance ?? true),
    'showPayments' => (bool)($organization->cabinet_show_payments ?? true),
    'showHomework' => (bool)($organization->cabinet_show_homework ?? true),
    'footerText' => $organization->cabinet_footer_text ?? '',
];

// Navigation items for bottom nav (фильтруем по настройкам)
$navItems = [];
$navItems[] = ['id' => 'home', 'label' => Yii::t('app', 'Главная'), 'url' => ['/cabinet/default/index', 'org' => $orgId], 'icon' => 'home', 'active' => $currentController === 'default' && $currentAction === 'index'];

if ($cabinetSettings['showSchedule']) {
    $navItems[] = ['id' => 'schedule', 'label' => Yii::t('app', 'Расписание'), 'url' => ['/cabinet/schedule/index', 'org' => $orgId], 'icon' => 'calendar', 'active' => $currentController === 'schedule'];
}
if ($cabinetSettings['showPayments']) {
    $navItems[] = ['id' => 'payment', 'label' => Yii::t('app', 'Платежи'), 'url' => ['/cabinet/payment/index', 'org' => $orgId], 'icon' => 'wallet', 'active' => $currentController === 'payment'];
}
if ($cabinetSettings['showAttendance']) {
    $navItems[] = ['id' => 'attendance', 'label' => Yii::t('app', 'Посещения'), 'url' => ['/cabinet/attendance/index', 'org' => $orgId], 'icon' => 'check-circle', 'active' => $currentController === 'attendance'];
}
if ($cabinetSettings['showHomework']) {
    $navItems[] = ['id' => 'homework', 'label' => Yii::t('app', 'Задания'), 'url' => ['/cabinet/homework/index', 'org' => $orgId], 'icon' => 'book-open', 'active' => $currentController === 'homework'];
}

// Передаём настройки во view
$this->params['cabinetSettings'] = $cabinetSettings;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full">
<head>
    <title><?= Html::encode($this->title) ?> - <?= Yii::t('app', 'Личный кабинет') ?></title>
    <?php $this->head() ?>
    <?php if ($organization && $cabinetSettings['primaryColor'] !== '#6366f1'): ?>
    <style>
        :root {
            --cabinet-primary: <?= Html::encode($cabinetSettings['primaryColor']) ?>;
            --cabinet-primary-rgb: <?= implode(', ', sscanf($cabinetSettings['primaryColor'], '#%02x%02x%02x')) ?>;
        }
        /* Переопределяем indigo цвета на кастомный */
        .bg-indigo-50 { background-color: color-mix(in srgb, var(--cabinet-primary) 10%, white) !important; }
        .bg-indigo-100 { background-color: color-mix(in srgb, var(--cabinet-primary) 20%, white) !important; }
        .bg-indigo-600 { background-color: var(--cabinet-primary) !important; }
        .text-indigo-600 { color: var(--cabinet-primary) !important; }
        .text-indigo-500 { color: var(--cabinet-primary) !important; }
        .border-indigo-500 { border-color: var(--cabinet-primary) !important; }
        .ring-indigo-100 { --tw-ring-color: color-mix(in srgb, var(--cabinet-primary) 20%, white) !important; }
        .from-indigo-500 { --tw-gradient-from: var(--cabinet-primary) !important; }
        .to-indigo-600 { --tw-gradient-to: color-mix(in srgb, var(--cabinet-primary) 90%, black) !important; }
        .hover\:bg-indigo-50:hover { background-color: color-mix(in srgb, var(--cabinet-primary) 10%, white) !important; }
        .btn-ios-primary {
            background: linear-gradient(to bottom, var(--cabinet-primary), color-mix(in srgb, var(--cabinet-primary) 85%, black)) !important;
            box-shadow: 0 2px 8px rgba(var(--cabinet-primary-rgb), 0.4) !important;
        }
        .btn-ios-ghost { color: var(--cabinet-primary) !important; }
        .btn-ios-ghost:hover { background-color: color-mix(in srgb, var(--cabinet-primary) 10%, white) !important; }
        .header-ios-gradient {
            background: linear-gradient(135deg, var(--cabinet-primary) 0%, color-mix(in srgb, var(--cabinet-primary) 85%, black) 50%, color-mix(in srgb, var(--cabinet-primary) 70%, #7c3aed) 100%) !important;
        }
        .bottom-nav-item.active .nav-icon,
        .bottom-nav-item.active .nav-label { color: var(--cabinet-primary) !important; }
        .avatar-ios-sm, .avatar-ios-md, .avatar-ios-lg, .avatar-ios-xl {
            background: linear-gradient(135deg, var(--cabinet-primary), color-mix(in srgb, var(--cabinet-primary) 70%, #8b5cf6)) !important;
        }
        .segment-item.active { color: var(--cabinet-primary) !important; }
    </style>
    <?php endif; ?>
</head>
<body class="min-h-full bg-gray-50 flex flex-col antialiased" x-data>
<?php $this->beginBody() ?>

<?php if ($isLoggedIn && $organization): ?>
    <!-- iOS-style Header with blur -->
    <header class="header-ios">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <!-- Brand -->
                <a href="<?= Url::to(['/cabinet/default/index', 'org' => $orgId]) ?>"
                   class="flex items-center gap-3 text-gray-900">
                    <?php if ($organization->logo): ?>
                        <img src="<?= Html::encode($organization->logo) ?>"
                             alt=""
                             class="h-9 w-9 rounded-xl object-cover shadow-sm">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-sm">
                            <span class="text-white text-sm font-bold"><?= mb_substr($organization->name, 0, 1) ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="font-semibold hidden sm:block truncate max-w-[200px]"><?= Html::encode($organization->name) ?></span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-1">
                    <?php foreach ($navItems as $item): ?>
                        <a href="<?= Url::to($item['url']) ?>"
                           class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium transition-all
                                  <?= $item['active']
                                      ? 'bg-indigo-50 text-indigo-600'
                                      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
                            <?= Icon::show($item['icon'], 'sm') ?>
                            <span><?= $item['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Logout (Desktop) -->
                <a href="<?= Url::to(['/cabinet/default/logout', 'org' => $orgId]) ?>"
                   class="hidden md:flex touch-target text-gray-500 hover:text-gray-700 transition-colors rounded-xl hover:bg-gray-100"
                   title="<?= Yii::t('app', 'Выход') ?>">
                    <?= Icon::show('logout', 'md') ?>
                </a>

                <!-- Mobile: Only Logo visible, bottom nav handles navigation -->
                <a href="<?= Url::to(['/cabinet/default/logout', 'org' => $orgId]) ?>"
                   class="md:hidden touch-target text-gray-500 hover:text-gray-700 transition-colors">
                    <?= Icon::show('logout', 'sm') ?>
                </a>
            </div>
        </div>
    </header>

<?php elseif ($organization): ?>
    <!-- Login Header with gradient -->
    <header class="header-ios-gradient">
        <div class="py-10 px-4">
            <div class="flex flex-col items-center text-center">
                <?php if ($organization->logo): ?>
                    <img src="<?= Html::encode($organization->logo) ?>"
                         alt=""
                         class="h-20 w-20 rounded-2xl object-cover shadow-xl mb-4 ring-4 ring-white/20">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center shadow-xl mb-4">
                        <span class="text-white text-3xl font-bold"><?= mb_substr($organization->name, 0, 1) ?></span>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-white"><?= Html::encode($organization->name) ?></h1>
                <?php if ($organization->address): ?>
                    <p class="text-white/70 mt-2 text-sm"><?= Html::encode($organization->address) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </header>
<?php endif; ?>

<!-- Main Content -->
<main class="flex-1 <?= $isLoggedIn ? 'safe-area-padding' : '' ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <!-- Flash Messages (iOS style) -->
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <?php
            $alertClass = match($type) {
                'success' => 'alert-ios-success',
                'error', 'danger' => 'alert-ios-danger',
                'warning', 'debug_code' => 'alert-ios-warning',
                default => 'alert-ios-info',
            };
            ?>
            <div class="mb-4 <?= $alertClass ?>"
                 x-data="{ show: true }"
                 x-show="show"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="flex items-start gap-3">
                    <?php if ($type === 'success'): ?>
                        <?= Icon::show('check-circle', 'sm', 'text-green-500 flex-shrink-0 mt-0.5') ?>
                    <?php elseif ($type === 'error' || $type === 'danger'): ?>
                        <?= Icon::show('x-circle', 'sm', 'text-red-500 flex-shrink-0 mt-0.5') ?>
                    <?php elseif ($type === 'warning' || $type === 'debug_code'): ?>
                        <?= Icon::show('exclamation-triangle', 'sm', 'text-amber-500 flex-shrink-0 mt-0.5') ?>
                    <?php else: ?>
                        <?= Icon::show('information-circle', 'sm', 'text-blue-500 flex-shrink-0 mt-0.5') ?>
                    <?php endif; ?>
                    <div class="flex-1 text-sm">
                        <?php if ($type === 'debug_code'): ?>
                            <span class="font-medium">Debug:</span> <?= Yii::t('app', 'SMS-код') ?>:
                            <code class="bg-white/50 px-2 py-0.5 rounded-lg font-mono text-base"><?= Html::encode($message) ?></code>
                        <?php else: ?>
                            <?= Html::encode($message) ?>
                        <?php endif; ?>
                    </div>
                    <button @click="show = false" class="touch-target -m-2 text-current opacity-50 hover:opacity-100 transition-opacity">
                        <?= Icon::show('x-mark', 'sm') ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </div>
</main>

<?php if ($isLoggedIn && $organization): ?>
    <!-- iOS-style Bottom Navigation (Mobile Only) -->
    <nav class="bottom-nav-ios md:hidden">
        <div class="flex">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="bottom-nav-item <?= $item['active'] ? 'active' : '' ?>">
                    <span class="nav-icon">
                        <?= Icon::show($item['icon'], 'md') ?>
                    </span>
                    <span class="nav-label"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
<?php elseif ($organization): ?>
    <!-- Footer for non-logged in pages -->
    <footer class="bg-white border-t border-gray-100 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left">
                    <p class="font-medium text-gray-900"><?= Html::encode($organization->name) ?></p>
                    <?php if ($organization->phone): ?>
                        <a href="tel:<?= Html::encode(preg_replace('/[^+0-9]/', '', $organization->phone)) ?>"
                           class="text-sm text-gray-500 hover:text-indigo-600 transition-colors flex items-center justify-center sm:justify-start gap-2 mt-1">
                            <?= Icon::show('phone', 'xs', 'text-gray-400') ?>
                            <?= Html::encode($organization->phone) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Social Links -->
                <?php
                $instagram = $organization->instagram ?? null;
                $whatsapp = $organization->whatsapp ?? null;
                $telegram = $organization->telegram ?? null;
                $hasSocial = $instagram || $whatsapp || $telegram;
                ?>
                <?php if ($hasSocial): ?>
                    <div class="flex items-center gap-3">
                        <?php if ($instagram): ?>
                            <a href="https://instagram.com/<?= Html::encode(ltrim($instagram, '@')) ?>"
                               target="_blank"
                               class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-transform shadow-md">
                                <?= Icon::show('instagram', 'sm') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($whatsapp): ?>
                            <a href="https://wa.me/<?= Html::encode(preg_replace('/[^0-9]/', '', $whatsapp)) ?>"
                               target="_blank"
                               class="w-11 h-11 rounded-full bg-green-500 flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-transform shadow-md">
                                <?= Icon::show('whatsapp', 'sm') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($telegram): ?>
                            <a href="https://t.me/<?= Html::encode(ltrim($telegram, '@')) ?>"
                               target="_blank"
                               class="w-11 h-11 rounded-full bg-sky-500 flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-transform shadow-md">
                                <?= Icon::show('telegram', 'sm') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-gray-100 mt-4 pt-4">
                <p class="text-center text-sm text-gray-400">
                    <?php if (!empty($cabinetSettings['footerText'])): ?>
                        <?= Html::encode($cabinetSettings['footerText']) ?>
                    <?php else: ?>
                        &copy; <?= date('Y') ?> <?= Html::encode($organization->name) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </footer>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
