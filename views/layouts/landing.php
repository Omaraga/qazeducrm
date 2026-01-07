<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\helpers\SystemRoles;
use app\models\Organizations;
use yii\bootstrap4\Html;
use yii\helpers\Url;

AppAsset::register($this);

$currentAction = Yii::$app->controller->action->id;
$isGuest = Yii::$app->user->isGuest;
$user = $isGuest ? null : Yii::$app->user->identity;
$isSuperAdmin = $user && $user->system_role === SystemRoles::SUPER;
$currentOrg = $isGuest ? null : Organizations::getCurrentOrganization();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Qazaq Education CRM - современная система управления учебным центром. Учет учеников, расписание, финансы, отчёты.">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Qazaq Education CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
    <?php $this->head() ?>
    <script src="https://kit.fontawesome.com/baa51cad6c.js" crossorigin="anonymous"></script>
    <style>
        /* ========================================
           LANDING NAVIGATION (Dark Header)
           ======================================== */
        .landing-nav {
            background: var(--dark);
            padding: 0.875rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all var(--transition);
        }
        .landing-nav.scrolled {
            box-shadow: var(--shadow-lg);
            padding: 0.75rem 0;
        }
        .landing-nav .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .landing-nav .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .landing-nav .brand img {
            height: 30px;
        }
        .landing-nav .brand-crm {
            background: var(--primary);
            color: var(--text-white);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: var(--radius-sm);
            letter-spacing: 0.05em;
        }
        .landing-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }
        .landing-nav .nav-link {
            color: var(--dark-text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color var(--transition-fast);
            padding: 0.5rem 0;
            position: relative;
        }
        .landing-nav .nav-link:hover,
        .landing-nav .nav-link.active {
            color: var(--text-white);
        }
        .landing-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
            border-radius: 2px;
        }
        .landing-nav .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn-nav-login {
            color: var(--dark-text);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            transition: color var(--transition-fast);
        }
        .btn-nav-login:hover {
            color: var(--primary);
            text-decoration: none;
        }
        .btn-nav-register {
            background: var(--primary);
            color: var(--text-white);
            padding: 0.6rem 1.25rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all var(--transition);
        }
        .btn-nav-register:hover {
            background: var(--primary-hover);
            color: var(--text-white);
            text-decoration: none;
            box-shadow: var(--shadow-md);
        }
        /* Authenticated user info */
        .nav-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nav-user-info .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .nav-user-info .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .nav-user-info .user-name {
            color: var(--dark-text);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .nav-user-info .user-name:hover {
            color: var(--primary);
        }
        .nav-user-info .user-org {
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        .btn-nav-logout {
            color: var(--dark-text);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--dark-border);
            border-radius: var(--radius);
            transition: all var(--transition-fast);
        }
        .btn-nav-logout:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: white;
            text-decoration: none;
        }
        .btn-nav-dashboard {
            background: var(--primary);
            color: var(--text-white);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all var(--transition);
        }
        .btn-nav-dashboard:hover {
            background: var(--primary-hover);
            color: var(--text-white);
            text-decoration: none;
        }

        /* ========================================
           MAIN CONTENT
           ======================================== */
        .landing-main {
            padding-top: 60px;
            background: var(--bg-white);
        }

        /* ========================================
           SECTION STYLES
           ======================================== */
        .section {
            padding: 5rem 0;
        }
        .section-light {
            background: var(--bg-light);
        }
        .section-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .section-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 3rem;
        }

        /* ========================================
           FOOTER (Dark)
           ======================================== */
        .landing-footer {
            background: var(--dark);
            color: var(--dark-text-muted);
            padding: 4rem 0 2rem;
        }
        .landing-footer h5 {
            color: var(--text-white);
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
        }
        .landing-footer a {
            color: var(--dark-text-muted);
            text-decoration: none;
            display: block;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            transition: color var(--transition-fast);
        }
        .landing-footer a:hover {
            color: var(--primary);
        }
        .footer-bottom {
            border-top: 1px solid var(--dark-border);
            padding-top: 2rem;
            margin-top: 3rem;
        }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .footer-brand img {
            height: 26px;
        }
        .footer-brand span {
            background: var(--primary);
            color: var(--text-white);
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.15rem 0.4rem;
            border-radius: 3px;
        }
        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--dark-light);
            border-radius: var(--radius);
            color: var(--dark-text-muted);
            margin-right: 0.5rem;
            transition: all var(--transition-fast);
        }
        .footer-social a:hover {
            background: var(--primary);
            color: var(--text-white);
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 991px) {
            .landing-nav .nav-links {
                display: none;
            }
        }
        @media (max-width: 575px) {
            .btn-nav-login {
                display: none;
            }
            .section-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Navigation -->
<nav class="landing-nav" id="mainNav">
    <div class="container">
        <div class="nav-container">
            <a href="<?= Url::to(['/landing/index']) ?>" class="brand">
                <img src="/images/logo-text.svg" alt="Qazaq Education">
                <span class="brand-crm">CRM</span>
            </a>

            <div class="nav-links">
                <a href="<?= Url::to(['/']) ?>" class="nav-link <?= $currentAction === 'index' ? 'active' : '' ?>">Главная</a>
                <a href="<?= Url::to(['/features']) ?>" class="nav-link <?= $currentAction === 'features' ? 'active' : '' ?>">Возможности</a>
                <a href="<?= Url::to(['/pricing']) ?>" class="nav-link <?= $currentAction === 'pricing' ? 'active' : '' ?>">Тарифы</a>
                <a href="<?= Url::to(['/contact']) ?>" class="nav-link <?= $currentAction === 'contact' ? 'active' : '' ?>">Контакты</a>
            </div>

            <div class="nav-buttons">
                <?php if ($isGuest): ?>
                    <a href="<?= Url::to(['/login']) ?>" class="btn-nav-login">Войти</a>
                    <a href="<?= Url::to(['/register']) ?>" class="btn-nav-register">Попробовать бесплатно</a>
                <?php else: ?>
                    <div class="nav-user-info">
                        <div class="user-avatar">
                            <?= mb_substr($user->fio ?? $user->username ?? 'U', 0, 1) ?>
                        </div>
                        <div class="user-details">
                            <a href="<?= Url::to($isSuperAdmin ? ['/superadmin'] : ['/crm']) ?>" class="user-name">
                                <?= Html::encode($user->fio ?? $user->username ?? 'Пользователь') ?>
                            </a>
                            <?php if ($isSuperAdmin): ?>
                                <span class="user-org">Супер-администратор</span>
                            <?php elseif ($currentOrg): ?>
                                <span class="user-org"><?= Html::encode($currentOrg->name) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= Url::to($isSuperAdmin ? ['/superadmin'] : ['/crm']) ?>" class="btn-nav-dashboard">
                        <?= $isSuperAdmin ? 'Админка' : 'CRM' ?>
                    </a>
                    <a href="<?= Url::to(['/logout']) ?>" class="btn-nav-logout" data-method="post">Выйти</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="landing-main">
    <?= $content ?>
</main>

<!-- Footer -->
<footer class="landing-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="footer-brand">
                    <img src="/images/logo-text.svg" alt="Qazaq Education">
                    <span>CRM</span>
                </div>
                <p class="mb-3" style="font-size: 0.9rem; max-width: 280px;">
                    Современная система управления учебным центром. Автоматизируйте рутину и сосредоточьтесь на главном.
                </p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Продукт</h5>
                <a href="<?= Url::to(['/features']) ?>">Возможности</a>
                <a href="<?= Url::to(['/pricing']) ?>">Тарифы</a>
                <a href="#">Обновления</a>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Компания</h5>
                <a href="#">О нас</a>
                <a href="<?= Url::to(['/contact']) ?>">Контакты</a>
                <a href="#">Партнёрам</a>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Поддержка</h5>
                <a href="#">Документация</a>
                <a href="#">FAQ</a>
                <a href="#">Telegram</a>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Правовое</h5>
                <a href="#">Конфиденциальность</a>
                <a href="#">Условия</a>
                <a href="#">Оферта</a>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-2 mb-md-0" style="font-size: 0.85rem;">
                &copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.
            </p>
        </div>
    </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    var nav = document.getElementById('mainNav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
