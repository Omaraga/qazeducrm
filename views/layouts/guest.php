<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Html;

AppAsset::register($this);
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
        body {
            background: var(--bg-light);
            min-height: 100vh;
        }

        /* Header */
        .guest-header {
            background: var(--dark);
            padding: 0.875rem 0;
        }
        .guest-header .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .guest-header .brand img {
            height: 28px;
        }
        .guest-header .brand-crm {
            background: var(--primary);
            color: var(--text-white);
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.2rem 0.45rem;
            border-radius: var(--radius-sm);
            letter-spacing: 0.05em;
        }
        .guest-header .btn-login {
            color: var(--dark-text);
            border: 1px solid var(--dark-border);
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        .guest-header .btn-login:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--text-white);
            text-decoration: none;
        }

        /* Card */
        .guest-card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        /* Footer */
        .guest-footer {
            color: var(--text-muted);
            padding: 2rem 0;
            font-size: 0.875rem;
        }
        .guest-footer a {
            color: var(--text-secondary);
        }
        .guest-footer a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header class="guest-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= \yii\helpers\Url::to(['/']) ?>" class="brand">
                <img src="/images/logo-text.svg" alt="Qazaq Education">
                <span class="brand-crm">CRM</span>
            </a>
            <div>
                <a href="<?= \yii\helpers\Url::to(['/login']) ?>" class="btn-login">
                    Войти
                </a>
            </div>
        </div>
    </div>
</header>

<main role="main" class="flex-shrink-0 py-5">
    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="guest-footer mt-auto">
    <div class="container text-center">
        <p class="mb-1">&copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.</p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
