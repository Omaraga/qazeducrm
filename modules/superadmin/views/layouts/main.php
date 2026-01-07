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

$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => ['/superadmin/default/index'], 'controller' => 'default'],
    ['label' => 'Организации', 'icon' => 'fa-building', 'url' => ['/superadmin/organization/index'], 'controller' => 'organization'],
    ['label' => 'Тарифы', 'icon' => 'fa-tags', 'url' => ['/superadmin/plan/index'], 'controller' => 'plan'],
    ['label' => 'Подписки', 'icon' => 'fa-credit-card', 'url' => ['/superadmin/subscription/index'], 'controller' => 'subscription'],
    ['label' => 'Платежи', 'icon' => 'fa-money-bill-wave', 'url' => ['/superadmin/payment/index'], 'controller' => 'payment'],
    ['label' => 'Аналитика', 'icon' => 'fa-chart-line', 'url' => ['/superadmin/revenue/index'], 'controller' => 'revenue'],
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
            <li class="nav-item">
                <a href="<?= Url::to($item['url']) ?>"
                   class="nav-link <?= $currentController === $item['controller'] ? 'active' : '' ?>">
                    <i class="fas <?= $item['icon'] ?>"></i>
                    <?= $item['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

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
