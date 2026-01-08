<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\helpers\SettingsHelper;
use app\helpers\SystemRoles;
use app\models\Organizations;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$currentAction = Yii::$app->controller->action->id;
$isGuest = Yii::$app->user->isGuest;
$user = $isGuest ? null : Yii::$app->user->identity;
$isSuperAdmin = $user && $user->system_role === SystemRoles::SUPER;
$currentOrg = $isGuest ? null : Organizations::getCurrentOrganization();

// SEO данные
$metaTitle = SettingsHelper::getMetaTitle();
$metaDescription = SettingsHelper::getMetaDescription();
$social = SettingsHelper::getSocialLinks();
$siteName = SettingsHelper::getSiteName() ?: 'Qazaq Education CRM';
$canonicalUrl = Yii::$app->request->absoluteUrl;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= Html::encode($metaDescription) ?>">
    <meta name="keywords" content="<?= Html::encode(SettingsHelper::getMetaKeywords()) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= Html::encode($canonicalUrl) ?>">
    <meta property="og:title" content="<?= Html::encode($this->title) ?> — <?= Html::encode($siteName) ?>">
    <meta property="og:description" content="<?= Html::encode($metaDescription) ?>">
    <meta property="og:image" content="/images/og-image.png">
    <meta property="og:locale" content="ru_RU">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= Html::encode($this->title) ?> — <?= Html::encode($siteName) ?>">
    <meta name="twitter:description" content="<?= Html::encode($metaDescription) ?>">
    <meta name="twitter:image" content="/images/og-image.png">

    <!-- Mobile App Meta -->
    <meta name="theme-color" content="#FE8D00">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= Html::encode($canonicalUrl) ?>">

    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — <?= Html::encode($siteName) ?></title>

    <?php $this->head() ?>
    <script defer src="https://kit.fontawesome.com/baa51cad6c.js" crossorigin="anonymous"></script>
</head>
<body class="h-full bg-white font-sans antialiased">
<?php $this->beginBody() ?>

<!-- Navigation -->
<nav id="mainNav" class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 transition-all duration-200">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Brand -->
            <a href="<?= Url::to(['/landing/index']) ?>" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-text-dark.svg" alt="Qazaq Education" class="h-7">
                <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded">CRM</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center gap-8">
                <a href="<?= Url::to(['/']) ?>" class="text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors <?= $currentAction === 'index' ? 'text-orange-500' : '' ?>">Главная</a>
                <a href="<?= Url::to(['/features']) ?>" class="text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors <?= $currentAction === 'features' ? 'text-orange-500' : '' ?>">Возможности</a>
                <a href="<?= Url::to(['/pricing']) ?>" class="text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors <?= $currentAction === 'pricing' ? 'text-orange-500' : '' ?>">Тарифы</a>
                <a href="<?= Url::to(['/contact']) ?>" class="text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors <?= $currentAction === 'contact' ? 'text-orange-500' : '' ?>">Контакты</a>
            </div>

            <!-- Right side -->
            <div class="flex items-center gap-4">
                <?php if ($isGuest): ?>
                    <a href="<?= Url::to(['/login']) ?>" class="hidden sm:block text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors">Войти</a>
                    <a href="<?= Url::to(['/register']) ?>" class="hidden sm:inline-flex bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-all shadow-sm hover:shadow-md">
                        Попробовать бесплатно
                    </a>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-semibold text-sm">
                            <?= mb_substr($user->fio ?? $user->username ?? 'U', 0, 1) ?>
                        </div>
                        <div class="flex flex-col">
                            <a href="<?= Url::to($isSuperAdmin ? ['/superadmin'] : ['/crm']) ?>" class="text-gray-900 text-sm font-medium hover:text-orange-500 transition-colors">
                                <?= Html::encode($user->fio ?? $user->username ?? 'Пользователь') ?>
                            </a>
                            <?php if ($isSuperAdmin): ?>
                                <span class="text-gray-500 text-xs">Супер-администратор</span>
                            <?php elseif ($currentOrg): ?>
                                <span class="text-gray-500 text-xs"><?= Html::encode($currentOrg->name) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= Url::to($isSuperAdmin ? ['/superadmin'] : ['/crm']) ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-all shadow-sm hover:shadow-md">
                        <?= $isSuperAdmin ? 'Админка' : 'CRM' ?>
                    </a>
                    <a href="<?= Url::to(['/logout']) ?>" class="text-gray-500 hover:text-red-500 border border-gray-300 hover:border-red-400 px-3 py-1.5 rounded-lg text-sm transition-all" data-method="post">
                        Выйти
                    </a>
                <?php endif; ?>

                <!-- Mobile menu button -->
                <button id="mobileMenuBtn" class="lg:hidden flex flex-col justify-center items-center w-8 h-8 gap-1.5">
                    <span class="block w-5 h-0.5 bg-gray-600 transition-transform"></span>
                    <span class="block w-5 h-0.5 bg-gray-600 transition-opacity"></span>
                    <span class="block w-5 h-0.5 bg-gray-600 transition-transform"></span>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div id="mobileMenu" class="fixed top-16 left-0 right-0 bg-white z-40 hidden lg:hidden border-b border-gray-200 shadow-lg">
    <div class="container mx-auto px-4 py-4">
        <a href="<?= Url::to(['/']) ?>" class="block py-3 text-gray-700 hover:text-orange-500 font-medium <?= $currentAction === 'index' ? 'text-orange-500' : '' ?>">Главная</a>
        <a href="<?= Url::to(['/features']) ?>" class="block py-3 text-gray-700 hover:text-orange-500 font-medium <?= $currentAction === 'features' ? 'text-orange-500' : '' ?>">Возможности</a>
        <a href="<?= Url::to(['/pricing']) ?>" class="block py-3 text-gray-700 hover:text-orange-500 font-medium <?= $currentAction === 'pricing' ? 'text-orange-500' : '' ?>">Тарифы</a>
        <a href="<?= Url::to(['/contact']) ?>" class="block py-3 text-gray-700 hover:text-orange-500 font-medium <?= $currentAction === 'contact' ? 'text-orange-500' : '' ?>">Контакты</a>
        <?php if ($isGuest): ?>
        <div class="flex gap-3 mt-4 pt-4 border-t border-gray-200">
            <a href="<?= Url::to(['/login']) ?>" class="text-gray-700 hover:text-orange-500 font-medium">Войти</a>
            <a href="<?= Url::to(['/register']) ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm">
                Попробовать бесплатно
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Main Content -->
<main class="pt-16">
    <?= $content ?>
</main>

<!-- Footer -->
<footer class="bg-gray-50 border-t border-gray-200 text-gray-600 pt-16 pb-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- Brand -->
            <div class="md:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-text-dark.svg" alt="Qazaq Education" class="h-6">
                    <span class="bg-orange-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">CRM</span>
                </div>
                <p class="text-sm leading-relaxed mb-4 max-w-[280px]">
                    Современная система управления учебным центром. Автоматизируйте рутину и сосредоточьтесь на главном.
                </p>
                <div class="flex gap-2">
                    <?php if ($social['telegram'] !== '#'): ?>
                    <a href="<?= Html::encode($social['telegram']) ?>" class="w-9 h-9 bg-gray-200 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all" aria-label="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($social['instagram'] !== '#'): ?>
                    <a href="<?= Html::encode($social['instagram']) ?>" class="w-9 h-9 bg-gray-200 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($social['youtube'] !== '#'): ?>
                    <a href="<?= Html::encode($social['youtube']) ?>" class="w-9 h-9 bg-gray-200 hover:bg-orange-500 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product -->
            <div>
                <h5 class="text-gray-900 font-semibold mb-4">Продукт</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= Url::to(['/features']) ?>" class="hover:text-orange-500 transition-colors">Возможности</a></li>
                    <li><a href="<?= Url::to(['/pricing']) ?>" class="hover:text-orange-500 transition-colors">Тарифы</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h5 class="text-gray-900 font-semibold mb-4">Компания</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= Url::to(['/contact']) ?>" class="hover:text-orange-500 transition-colors">Контакты</a></li>
                    <?php if ($social['telegram'] !== '#'): ?>
                    <li><a href="<?= Html::encode($social['telegram']) ?>" class="hover:text-orange-500 transition-colors">Telegram</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div>
                <h5 class="text-gray-900 font-semibold mb-4">Начать</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= Url::to(['/register']) ?>" class="hover:text-orange-500 transition-colors">Регистрация</a></li>
                    <li><a href="<?= Url::to(['/login']) ?>" class="hover:text-orange-500 transition-colors">Войти</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="border-t border-gray-200 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">&copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.</p>
        </div>
    </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', function() {
    var nav = document.getElementById('mainNav');
    if (window.scrollY > 50) {
        nav.classList.add('shadow-lg');
    } else {
        nav.classList.remove('shadow-lg');
    }
});

// Mobile menu toggle
(function() {
    var menuBtn = document.getElementById('mobileMenuBtn');
    var mobileMenu = document.getElementById('mobileMenu');
    var spans = menuBtn ? menuBtn.querySelectorAll('span') : [];

    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
            var isOpen = !mobileMenu.classList.contains('hidden');
            if (isOpen) {
                mobileMenu.classList.add('hidden');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            } else {
                mobileMenu.classList.remove('hidden');
                spans[0].style.transform = 'rotate(45deg) translate(4px, 4px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(4px, -4px)';
            }
        });

        document.addEventListener('click', function(e) {
            if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                mobileMenu.classList.add('hidden');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });
    }
})();
</script>

<!-- Schema.org Organization -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?= Html::encode($siteName) ?>",
    "url": "<?= Html::encode(Yii::$app->request->hostInfo) ?>",
    "logo": "<?= Html::encode(Yii::$app->request->hostInfo) ?>/images/logo.svg",
    "description": "<?= Html::encode($metaDescription) ?>",
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "<?= Html::encode(SettingsHelper::getMainPhone()) ?>",
        "contactType": "customer service",
        "email": "<?= Html::encode(SettingsHelper::getEmail()) ?>",
        "areaServed": "KZ",
        "availableLanguage": ["Russian", "Kazakh"]
    },
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "KZ",
        "addressLocality": "Астана",
        "streetAddress": "<?= Html::encode(SettingsHelper::getAddress()) ?>"
    },
    "sameAs": [
        <?php $sameAs = []; ?>
        <?php if ($social['telegram'] !== '#'): $sameAs[] = '"' . Html::encode($social['telegram']) . '"'; endif; ?>
        <?php if ($social['instagram'] !== '#'): $sameAs[] = '"' . Html::encode($social['instagram']) . '"'; endif; ?>
        <?php if ($social['youtube'] !== '#'): $sameAs[] = '"' . Html::encode($social['youtube']) . '"'; endif; ?>
        <?= implode(",\n        ", $sameAs) ?>
    ]
}
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
