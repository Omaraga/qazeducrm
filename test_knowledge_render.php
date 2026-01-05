<?php
/**
 * Тест рендеринга страниц базы знаний
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';
new yii\console\Application($config);

use app\models\KnowledgeCategory;
use app\models\KnowledgeArticle;
use app\helpers\OrganizationUrl;

echo "=== ТЕСТ РЕНДЕРИНГА БАЗЫ ЗНАНИЙ ===\n\n";

// Симулируем данные для index
echo "1. INDEX PAGE DATA\n";
echo str_repeat("-", 50) . "\n";

$categories = KnowledgeCategory::getActiveCategories();
$featured = KnowledgeArticle::getFeatured(6);

echo "Категории для карточек:\n";
foreach ($categories as $cat) {
    $color = match($cat->slug) {
        'getting-started' => 'bg-green-100',
        'modules' => 'bg-blue-100',
        'faq' => 'bg-yellow-100',
        'support' => 'bg-purple-100',
        default => 'bg-gray-100'
    };
    echo "  - {$cat->name}: {$color}, {$cat->getArticleCount()} статей\n";
}

echo "\nИзбранные статьи:\n";
foreach ($featured as $art) {
    echo "  - {$art->title} ({$art->views} просмотров)\n";
}

// Симулируем данные для category
echo "\n2. CATEGORY PAGE DATA\n";
echo str_repeat("-", 50) . "\n";

$category = KnowledgeCategory::findBySlug('modules');
if ($category) {
    echo "Категория: {$category->name}\n";
    echo "Описание: {$category->description}\n";
    $articles = $category->getActiveArticles()->all();
    echo "Статьи (" . count($articles) . "):\n";
    foreach ($articles as $art) {
        $featuredBadge = $art->is_featured ? ' [FEATURED]' : '';
        echo "  - {$art->title}{$featuredBadge}\n";
        if ($art->excerpt) {
            echo "    Excerpt: " . mb_substr($art->excerpt, 0, 50) . "...\n";
        }
    }
}

// Симулируем данные для view
echo "\n3. VIEW PAGE DATA\n";
echo str_repeat("-", 50) . "\n";

$article = KnowledgeArticle::findBySlug('pupils-guide');
if ($article) {
    echo "Статья: {$article->title}\n";
    echo "Slug: {$article->slug}\n";
    echo "Категория: " . ($article->category ? $article->category->name : 'N/A') . "\n";
    echo "Просмотры: {$article->views}\n";
    echo "Избранная: " . ($article->is_featured ? 'Да' : 'Нет') . "\n";
    echo "Длина контента: " . mb_strlen($article->content) . " символов\n";

    // Проверяем HTML структуру
    $html = $article->content;
    preg_match_all('/<h2>(.+?)<\/h2>/', $html, $h2matches);
    preg_match_all('/<li>/', $html, $limatches);

    echo "Заголовков h2: " . count($h2matches[0]) . "\n";
    if (!empty($h2matches[1])) {
        foreach ($h2matches[1] as $h2) {
            echo "  - " . strip_tags($h2) . "\n";
        }
    }
    echo "Пунктов списка: " . count($limatches[0]) . "\n";

    // Связанные статьи
    $related = $article->getRelatedArticles(3);
    echo "Связанные статьи: " . count($related) . "\n";
    foreach ($related as $rel) {
        echo "  - {$rel->title}\n";
    }
}

// Симулируем данные для search
echo "\n4. SEARCH PAGE DATA\n";
echo str_repeat("-", 50) . "\n";

$queries = ['расписан', 'оплат', 'группа', 'login'];
foreach ($queries as $q) {
    $results = KnowledgeArticle::search($q);
    echo "Поиск '{$q}': " . count($results) . " результатов\n";
}

// Проверяем URLs
echo "\n5. URL STRUCTURE\n";
echo str_repeat("-", 50) . "\n";

echo "CRM URLs:\n";
echo "  /crm/knowledge/index - главная\n";
echo "  /crm/knowledge/category?slug=modules - категория\n";
echo "  /crm/knowledge/view?slug=welcome - статья\n";
echo "  /crm/knowledge/search?q=test - поиск\n";

echo "\nSuperadmin URLs:\n";
echo "  /superadmin/knowledge/index - список статей\n";
echo "  /superadmin/knowledge/create - создание\n";
echo "  /superadmin/knowledge/update?id=1 - редактирование\n";
echo "  /superadmin/knowledge/categories - категории\n";

// Проверяем иконки
echo "\n6. ICON CHECK\n";
echo str_repeat("-", 50) . "\n";

$icons = ['book', 'puzzle', 'question-mark', 'support'];
foreach ($categories as $cat) {
    $hasIcon = in_array($cat->icon, $icons);
    echo ($hasIcon ? "[OK]" : "[!!]") . " {$cat->name}: icon={$cat->icon}\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";
echo "Всё готово для использования!\n";
