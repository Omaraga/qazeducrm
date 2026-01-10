<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\TailwindAsset;
use app\helpers\SettingsHelper;
use app\helpers\SystemRoles;
use app\models\DocsChapter;
use app\models\Organizations;
use yii\helpers\Html;
use yii\helpers\Url;

TailwindAsset::register($this);

$isGuest = Yii::$app->user->isGuest;
$user = $isGuest ? null : Yii::$app->user->identity;
$isSuperAdmin = $user && $user->system_role === SystemRoles::SUPER;
$siteName = SettingsHelper::getSiteName() ?: 'Qazaq Education CRM';

// Получаем главы для sidebar (если не переданы через view)
$chapters = $this->params['chapters'] ?? DocsChapter::getActiveChaptersWithSections();
$currentChapter = $this->params['currentChapter'] ?? null;
$currentSection = $this->params['currentSection'] ?? null;
$headings = $this->params['headings'] ?? [];

// Иконки для глав (FontAwesome Free)
$chapterIcons = [
    'rocket' => 'fa-rocket',
    'users' => 'fa-users',
    'user-group' => 'fa-users',           // fa-user-group не существует в FA Free
    'user-tie' => 'fa-user-tie',
    'calendar' => 'fa-calendar',          // fa-calendar-days может не работать
    'clipboard-check' => 'fa-clipboard-check',
    'credit-card' => 'fa-credit-card',
    'money-bill' => 'fa-money-bill-wave',
    'funnel' => 'fa-filter',
    'comments' => 'fa-comments',
    'cog' => 'fa-cog',                    // fa-gear может не работать, fa-cog стандартная
    'book' => 'fa-book',
];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full scroll-smooth">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="description" content="Документация QazEduCRM - полное руководство по работе с системой">
    <meta name="theme-color" content="#FE8D00">

    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Документация <?= Html::encode($siteName) ?></title>

    <?php $this->head() ?>
    <script defer src="https://kit.fontawesome.com/baa51cad6c.js" crossorigin="anonymous"></script>

    <style>
        /* Docs specific styles */
        .docs-content h2 {
            scroll-margin-top: 5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .docs-content h3 {
            scroll-margin-top: 5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #334155;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .docs-content p {
            color: #475569;
            line-height: 1.75;
            margin-bottom: 1rem;
        }
        .docs-content ul, .docs-content ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .docs-content li {
            color: #475569;
            line-height: 1.75;
            margin-bottom: 0.25rem;
        }
        .docs-content ul li {
            list-style-type: disc;
        }
        .docs-content ol li {
            list-style-type: decimal;
        }
        .docs-content img {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
        }
        .docs-content code {
            background: #f1f5f9;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
            color: #e11d48;
        }
        .docs-content pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .docs-content pre code {
            background: transparent;
            padding: 0;
            color: inherit;
        }
        .docs-content a {
            color: #f97316;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .docs-content a:hover {
            color: #ea580c;
        }
        .docs-toc-link.active {
            color: #f97316;
            font-weight: 500;
        }

        /* Lightbox */
        .docs-lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            cursor: zoom-out;
        }
        .docs-lightbox img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            box-shadow: none;
            margin: 0;
        }
        .docs-lightbox.hidden {
            display: none !important;
        }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
<?php $this->beginBody() ?>

<!-- Skip to content -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-orange-500 text-white px-4 py-2 rounded-lg z-50">
    Перейти к содержимому
</a>

<!-- Top Navigation -->
<header class="fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 h-16">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
        <!-- Left: Logo & Menu button -->
        <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Logo -->
            <a href="<?= Url::to(['/']) ?>" class="flex items-center gap-2">
                <img src="<?= Yii::$app->request->baseUrl ?>/images/logo-text-dark.svg" alt="QazEduCRM" class="h-6">
            </a>

            <span class="text-gray-300">|</span>

            <a href="<?= Url::to(['/docs']) ?>" class="text-gray-700 font-medium hover:text-orange-500 transition-colors">
                Документация
            </a>
        </div>

        <!-- Right: Search & Actions -->
        <div class="flex items-center gap-4">
            <!-- Search (desktop) -->
            <form action="<?= Url::to(['/docs/search']) ?>" method="get" class="hidden md:block">
                <div class="relative">
                    <input type="text"
                           name="q"
                           placeholder="Поиск..."
                           value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>"
                           class="w-64 pl-10 pr-4 py-2 bg-gray-100 border border-transparent focus:bg-white focus:border-gray-300 rounded-lg text-sm transition-all">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>

            <?php if ($isGuest): ?>
                <a href="<?= Url::to(['/login']) ?>" class="text-gray-600 hover:text-orange-500 font-medium text-sm transition-colors">
                    Войти
                </a>
                <a href="<?= Url::to(['/register']) ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-all">
                    Регистрация
                </a>
            <?php else: ?>
                <a href="<?= Url::to($isSuperAdmin ? ['/superadmin'] : ['/crm']) ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-all">
                    <?= $isSuperAdmin ? 'Админка' : 'CRM' ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Mobile sidebar overlay -->
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
     style="display: none;"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed top-16 left-0 bottom-0 w-72 bg-white border-r border-gray-200 z-40 overflow-y-auto transition-transform duration-200 lg:translate-x-0">
    <!-- Mobile search -->
    <div class="p-4 border-b border-gray-200 md:hidden">
        <form action="<?= Url::to(['/docs/search']) ?>" method="get">
            <div class="relative">
                <input type="text"
                       name="q"
                       placeholder="Поиск..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-100 border border-transparent focus:bg-white focus:border-gray-300 rounded-lg text-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </form>
    </div>

    <!-- Navigation -->
    <nav class="p-4">
        <?php foreach ($chapters as $chapter): ?>
            <?php
            $isCurrentChapter = $currentChapter && $currentChapter->id === $chapter->id;
            $iconClass = $chapterIcons[$chapter->icon] ?? 'fa-book';
            $sections = $chapter->activeSections;
            ?>
            <div class="mb-2" x-data="{ open: <?= $isCurrentChapter ? 'true' : 'false' ?> }">
                <!-- Chapter header -->
                <button @click="open = !open"
                        class="w-full flex items-center justify-between py-2 px-3 rounded-lg text-left hover:bg-gray-100 transition-colors <?= $isCurrentChapter ? 'bg-orange-50 text-orange-600' : 'text-gray-700' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fas <?= $iconClass ?> w-4 text-center <?= $isCurrentChapter ? 'text-orange-500' : 'text-gray-400' ?>"></i>
                        <span class="font-medium text-sm"><?= Html::encode($chapter->title) ?></span>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" :class="open && 'rotate-180'"></i>
                </button>

                <!-- Sections -->
                <?php if (!empty($sections)): ?>
                    <div x-show="open" x-collapse class="mt-1 ml-7 border-l-2 border-gray-200 pl-4">
                        <?php foreach ($sections as $section): ?>
                            <?php $isCurrentSection = $currentSection && $currentSection->id === $section->id; ?>
                            <a href="<?= Url::to(['/docs/section', 'chapter' => $chapter->slug, 'slug' => $section->slug]) ?>"
                               class="block py-1.5 text-sm transition-colors <?= $isCurrentSection ? 'text-orange-600 font-medium' : 'text-gray-600 hover:text-gray-900' ?>">
                                <?= Html::encode($section->title) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200 mt-auto">
        <div class="text-xs text-gray-500">
            <a href="<?= Url::to(['/']) ?>" class="hover:text-orange-500 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Вернуться на сайт
            </a>
        </div>
    </div>
</aside>

<!-- Main content area -->
<div class="lg:ml-72 pt-16">
    <div class="flex">
        <!-- Main content -->
        <main id="main-content" class="flex-1 min-w-0 p-6 lg:p-8 xl:pr-80">
            <?= $content ?>
        </main>

        <!-- Table of Contents (desktop) -->
        <?php if (!empty($headings)): ?>
            <aside class="hidden xl:block fixed right-0 top-16 w-64 h-[calc(100vh-4rem)] border-l border-gray-200 bg-white overflow-y-auto p-6">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    На этой странице
                </div>
                <nav class="space-y-1" x-data="tocHighlight()">
                    <?php foreach ($headings as $heading): ?>
                        <a href="#<?= Html::encode($heading['id']) ?>"
                           class="docs-toc-link block py-1 text-sm text-gray-600 hover:text-gray-900 transition-colors <?= $heading['level'] === 3 ? 'pl-4' : '' ?>"
                           :class="activeId === '<?= Html::encode($heading['id']) ?>' && 'active'">
                            <?= Html::encode($heading['text']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox -->
<div id="docs-lightbox" class="docs-lightbox hidden" onclick="closeLightbox()">
    <img src="" alt="Screenshot">
</div>

<script>
// TOC highlighting
function tocHighlight() {
    return {
        activeId: '',
        init() {
            const headings = document.querySelectorAll('.docs-content h2[id], .docs-content h3[id]');
            if (headings.length === 0) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.activeId = entry.target.id;
                    }
                });
            }, {
                rootMargin: '-80px 0px -80% 0px',
                threshold: 0
            });

            headings.forEach(heading => observer.observe(heading));
        }
    }
}

// Lightbox
function openLightbox(src) {
    const lightbox = document.getElementById('docs-lightbox');
    const img = lightbox.querySelector('img');
    img.src = src;
    lightbox.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('docs-lightbox');
    lightbox.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Make images clickable for lightbox
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.docs-content img').forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function() {
            openLightbox(this.src);
        });
    });
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
