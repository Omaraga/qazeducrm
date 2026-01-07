<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Html;
use yii\helpers\Url;

AppAsset::register($this);

$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;

// Счётчик ожидающих запросов
$pendingRequestsCount = \app\models\OrganizationSubscriptionRequest::getPendingCount();

$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => ['/superadmin/default/index'], 'controller' => 'default'],
    ['label' => 'Организации', 'icon' => 'fa-building', 'url' => ['/superadmin/organization/index'], 'controller' => 'organization'],
    ['label' => 'Тарифы', 'icon' => 'fa-tags', 'url' => ['/superadmin/plan/index'], 'controller' => 'plan'],
    [
        'label' => 'Подписки',
        'icon' => 'fa-credit-card',
        'url' => ['/superadmin/subscription/index'],
        'controller' => 'subscription',
        'submenu' => [
            ['label' => 'Все подписки', 'url' => ['/superadmin/subscription/index'], 'action' => 'index'],
            ['label' => 'Запросы', 'url' => ['/superadmin/subscription/requests'], 'action' => 'requests', 'badge' => $pendingRequestsCount],
        ],
    ],
    [
        'label' => 'Платежи',
        'icon' => 'fa-money-bill-wave',
        'url' => ['/superadmin/payment/index'],
        'controller' => 'payment',
        'submenu' => [
            ['label' => 'Все платежи', 'url' => ['/superadmin/payment/index'], 'action' => 'index'],
            ['label' => 'Продажи менеджеров', 'url' => ['/superadmin/payment/manager-sales'], 'action' => 'manager-sales'],
            ['label' => 'Ожидающие бонусы', 'url' => ['/superadmin/payment/pending-bonuses'], 'action' => 'pending-bonuses'],
        ],
    ],
    [
        'label' => 'Аналитика',
        'icon' => 'fa-chart-line',
        'url' => ['/superadmin/revenue/index'],
        'controller' => 'revenue',
        'submenu' => [
            ['label' => 'Обзор', 'url' => ['/superadmin/revenue/index'], 'action' => 'index'],
            ['label' => 'Отчёты', 'url' => ['/superadmin/revenue/reports'], 'action' => 'reports'],
            ['label' => 'KPI метрики', 'url' => ['/superadmin/revenue/metrics'], 'action' => 'metrics'],
            ['label' => 'Прогноз', 'url' => ['/superadmin/revenue/forecast'], 'action' => 'forecast'],
            ['label' => 'MRR под риском', 'url' => ['/superadmin/revenue/mrr-at-risk'], 'action' => 'mrr-at-risk'],
        ],
    ],
    ['label' => 'Дополнения', 'icon' => 'fa-puzzle-piece', 'url' => ['/superadmin/addon/index'], 'controller' => 'addon'],
    ['label' => 'Промокоды', 'icon' => 'fa-ticket-alt', 'url' => ['/superadmin/promo-code/index'], 'controller' => 'promo-code'],
    ['label' => 'База знаний', 'icon' => 'fa-book', 'url' => ['/superadmin/knowledge/index'], 'controller' => 'knowledge'],
];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> | Qazaq Education CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
    <?php $this->head() ?>
    <script src="https://kit.fontawesome.com/baa51cad6c.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            background-color: var(--bg-light);
        }

        /* Sidebar */
        .superadmin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--dark);
            z-index: 1000;
            overflow-y: auto;
        }

        .superadmin-sidebar .sidebar-brand {
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--dark-border);
        }

        .superadmin-sidebar .sidebar-brand img {
            height: 28px;
        }

        .superadmin-sidebar .sidebar-brand span {
            background: var(--primary);
            padding: 0.2rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-white);
            letter-spacing: 0.05em;
        }

        .superadmin-sidebar .nav-menu {
            padding: 1rem 0;
            list-style: none;
            margin: 0;
        }

        .superadmin-sidebar .nav-item {
            margin: 0.125rem 0.75rem;
        }

        .superadmin-sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--dark-text-muted);
            text-decoration: none;
            border-radius: var(--radius);
            transition: all var(--transition);
        }

        .superadmin-sidebar .nav-link:hover {
            background: var(--dark-light);
            color: var(--text-white);
        }

        .superadmin-sidebar .nav-link.active {
            background: var(--primary);
            color: var(--text-white);
        }

        .superadmin-sidebar .nav-link i {
            width: 1.5rem;
            margin-right: 0.75rem;
        }

        /* Submenu styles */
        .superadmin-sidebar .nav-item.has-submenu .nav-link {
            justify-content: space-between;
        }

        .superadmin-sidebar .nav-item.has-submenu .nav-link::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.75rem;
            transition: transform 0.2s ease;
        }

        .superadmin-sidebar .nav-item.has-submenu.open .nav-link::after {
            transform: rotate(180deg);
        }

        .superadmin-sidebar .submenu {
            display: none;
            list-style: none;
            padding: 0.25rem 0;
            margin: 0;
            background: rgba(0, 0, 0, 0.15);
            border-radius: var(--radius);
            margin-top: 0.25rem;
        }

        .superadmin-sidebar .nav-item.has-submenu.open .submenu {
            display: block;
        }

        .superadmin-sidebar .submenu li {
            margin: 0;
        }

        .superadmin-sidebar .submenu .nav-link {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            font-size: 0.875rem;
        }

        .superadmin-sidebar .submenu .nav-link.active {
            background: var(--primary);
        }

        .superadmin-sidebar .nav-link .badge {
            background: var(--error);
            color: white;
            padding: 0.15rem 0.4rem;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }

        .superadmin-sidebar .nav-link .menu-label {
            display: flex;
            align-items: center;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            border-top: 1px solid var(--dark-border);
        }

        /* Content */
        .superadmin-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .superadmin-header {
            background: var(--bg-white);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .superadmin-header .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .superadmin-main {
            padding: 1.5rem;
        }

        /* Stat Cards */
        .stat-card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--text-white);
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-card .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Custom Cards */
        .card-custom {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .card-custom .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-custom .card-body {
            padding: 1.25rem;
        }

        /* Status Badges */
        .badge-trial { background: var(--warning); color: var(--text-white); }
        .badge-active { background: var(--success); color: var(--text-white); }
        .badge-expired { background: var(--error); color: var(--text-white); }
        .badge-suspended { background: var(--text-muted); color: var(--text-white); }
        .badge-pending { background: var(--info); color: var(--text-white); }
        .badge-completed { background: var(--success); color: var(--text-white); }
        .badge-cancelled { background: var(--text-light); color: var(--text-white); }

        .badge-head { background: #4f46e5; color: var(--text-white); }
        .badge-branch { background: #0891b2; color: var(--text-white); }

        /* Tables */
        .table-hover tbody tr:hover {
            background-color: var(--bg-light);
        }

        .table th {
            color: var(--text-secondary);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
        }

        .table td {
            color: var(--text-primary);
            vertical-align: middle;
        }

        /* Form Controls */
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(254, 141, 0, 0.15);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .superadmin-sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition);
            }

            .superadmin-sidebar.show {
                transform: translateX(0);
            }

            .superadmin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Sidebar -->
<aside class="superadmin-sidebar">
    <div class="sidebar-brand">
        <img src="/images/logo-text.svg" alt="Qazaq Education" style="height: 28px;">
        <span>ADMIN</span>
    </div>
    <ul class="nav-menu">
        <?php foreach ($menuItems as $item): ?>
            <?php
            $hasSubmenu = !empty($item['submenu']);
            $isActive = $currentController === $item['controller'];
            $isOpen = $isActive && $hasSubmenu;
            ?>
            <li class="nav-item <?= $hasSubmenu ? 'has-submenu' : '' ?> <?= $isOpen ? 'open' : '' ?>">
                <?php if ($hasSubmenu): ?>
                    <a href="#" class="nav-link <?= $isActive && !$hasSubmenu ? 'active' : '' ?>" onclick="toggleSubmenu(this); return false;">
                        <span class="menu-label">
                            <i class="fas <?= $item['icon'] ?>"></i>
                            <?= $item['label'] ?>
                            <?php
                            // Показать badge если есть в любом подпункте
                            $totalBadge = 0;
                            foreach ($item['submenu'] as $sub) {
                                if (!empty($sub['badge'])) $totalBadge += $sub['badge'];
                            }
                            if ($totalBadge > 0): ?>
                                <span class="badge"><?= $totalBadge ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                    <ul class="submenu">
                        <?php foreach ($item['submenu'] as $sub): ?>
                            <?php $subActive = $isActive && $currentAction === $sub['action']; ?>
                            <li>
                                <a href="<?= Url::to($sub['url']) ?>" class="nav-link <?= $subActive ? 'active' : '' ?>">
                                    <?= $sub['label'] ?>
                                    <?php if (!empty($sub['badge'])): ?>
                                        <span class="badge"><?= $sub['badge'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <a href="<?= Url::to($item['url']) ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
                        <i class="fas <?= $item['icon'] ?>"></i>
                        <?= $item['label'] ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
    function toggleSubmenu(element) {
        const li = element.closest('.nav-item');
        li.classList.toggle('open');
    }
    </script>

    <div class="sidebar-footer">
        <a href="<?= Url::to(['/site/index']) ?>" class="nav-link">
            <i class="fas fa-arrow-left"></i>
            Вернуться в CRM
        </a>
    </div>
</aside>

<!-- Content -->
<div class="superadmin-content">
    <header class="superadmin-header">
        <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
        <div class="header-actions">
            <span class="text-muted mr-3">
                <i class="fas fa-user"></i>
                <?= Yii::$app->user->identity->username ?? 'Admin' ?>
            </span>
            <a href="<?= Url::to(['/site/logout']) ?>" class="btn btn-outline-secondary btn-sm" data-method="post">
                <i class="fas fa-sign-out-alt"></i> Выйти
            </a>
        </div>
    </header>

    <main class="superadmin-main">
        <?= Alert::widget() ?>
        <?= $content ?>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
