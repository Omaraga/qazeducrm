<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Html;
use yii\helpers\Url;

AppAsset::register($this);

$currentController = Yii::$app->controller->id;
$user = Yii::$app->user->identity;

$menuItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => ['/crm/default/index'], 'controller' => 'default'],
    ['label' => 'Ученики', 'icon' => 'fa-user-graduate', 'url' => ['/crm/pupil/index'], 'controller' => 'pupil'],
    ['label' => 'Группы', 'icon' => 'fa-users', 'url' => ['/crm/group/index'], 'controller' => 'group'],
    ['label' => 'Расписание', 'icon' => 'fa-calendar-alt', 'url' => ['/crm/schedule/index'], 'controller' => 'schedule'],
    ['label' => 'Платежи', 'icon' => 'fa-money-bill-wave', 'url' => ['/crm/payment/index'], 'controller' => 'payment'],
    ['label' => 'Лиды', 'icon' => 'fa-funnel-dollar', 'url' => ['/crm/lids/index'], 'controller' => 'lids'],
    ['label' => 'Отчёты', 'icon' => 'fa-chart-line', 'url' => ['/crm/reports/index'], 'controller' => 'reports'],
    ['label' => 'Сотрудники', 'icon' => 'fa-user-tie', 'url' => ['/crm/user/index'], 'controller' => 'user'],
];

$settingsItems = [
    ['label' => 'Предметы', 'icon' => 'fa-book', 'url' => ['/crm/subject/index'], 'controller' => 'subject'],
    ['label' => 'Тарифы', 'icon' => 'fa-tags', 'url' => ['/crm/tariff/index'], 'controller' => 'tariff'],
    ['label' => 'Способы оплаты', 'icon' => 'fa-credit-card', 'url' => ['/crm/pay-method/index'], 'controller' => 'pay-method'],
];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Qazaq Education CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
    <?php $this->head() ?>
    <script src="https://kit.fontawesome.com/baa51cad6c.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
        }

        body {
            background-color: var(--bg-light);
        }

        /* Sidebar */
        .crm-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--dark);
            z-index: 1000;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .crm-sidebar .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--dark-border);
            flex-shrink: 0;
        }

        .crm-sidebar .sidebar-brand img {
            height: 28px;
        }

        .crm-sidebar .sidebar-brand span {
            background: var(--primary);
            padding: 0.2rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-white);
            letter-spacing: 0.05em;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 0 0.75rem;
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: var(--dark-text-muted);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin: 0.125rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
            color: var(--dark-text-muted);
            text-decoration: none;
            border-radius: var(--radius);
            transition: all var(--transition);
            font-size: 0.9rem;
        }

        .nav-link:hover {
            background: var(--dark-light);
            color: var(--text-white);
            text-decoration: none;
        }

        .nav-link.active {
            background: var(--primary);
            color: var(--text-white);
        }

        .nav-link i {
            width: 1.5rem;
            margin-right: 0.75rem;
            font-size: 0.95rem;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--dark-border);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: var(--radius);
            color: var(--dark-text-muted);
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            background: var(--dark-light);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-white);
            font-weight: 600;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            color: var(--text-white);
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.75rem;
            color: var(--dark-text-muted);
        }

        /* Content */
        .crm-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .crm-header {
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

        .crm-header .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .crm-header .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .crm-main {
            padding: 1.5rem;
        }

        /* Cards & Tables */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .crm-sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition);
            }

            .crm-sidebar.show {
                transform: translateX(0);
            }

            .crm-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Sidebar -->
<aside class="crm-sidebar">
    <div class="sidebar-brand">
        <img src="/images/logo-text.svg" alt="Qazaq Education">
        <span>CRM</span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Меню</div>
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
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Настройки</div>
            <ul class="nav-menu">
                <?php foreach ($settingsItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?= Url::to($item['url']) ?>"
                           class="nav-link <?= $currentController === $item['controller'] ? 'active' : '' ?>">
                            <i class="fas <?= $item['icon'] ?>"></i>
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?= mb_substr($user->username ?? 'U', 0, 1) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= Html::encode($user->username ?? 'User') ?></div>
                <div class="sidebar-user-role"><?= Html::encode($user->active_role ?? 'Пользователь') ?></div>
            </div>
            <a href="<?= Url::to(['/logout']) ?>" class="text-muted" data-method="post" title="Выйти">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Content -->
<div class="crm-content">
    <header class="crm-header">
        <button class="btn btn-link mobile-toggle" onclick="document.querySelector('.crm-sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
        <div class="header-actions">
            <?php if (Yii::$app->user->can('superadmin')): ?>
            <a href="<?= Url::to(['/superadmin']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cog"></i> Админка
            </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="crm-main">
        <?= Alert::widget() ?>
        <?= $content ?>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
