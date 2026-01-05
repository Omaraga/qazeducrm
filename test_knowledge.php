<?php
/**
 * Тестирование модуля База знаний
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';
new yii\console\Application($config);

echo "=== ТЕСТИРОВАНИЕ БАЗЫ ЗНАНИЙ ===\n\n";

use app\models\KnowledgeCategory;
use app\models\KnowledgeArticle;

// 1. Проверка моделей
echo "1. ПРОВЕРКА МОДЕЛЕЙ\n";
echo str_repeat("-", 50) . "\n";

$categories = KnowledgeCategory::getActiveCategories();
echo "Активные категории: " . count($categories) . "\n";
foreach ($categories as $cat) {
    $articleCount = $cat->getArticleCount();
    echo "  [{$cat->id}] {$cat->name} ({$cat->slug}) - {$articleCount} статей\n";
}

$articles = KnowledgeArticle::find()->where(['is_deleted' => 0])->all();
echo "\nВсего статей: " . count($articles) . "\n";

$featured = KnowledgeArticle::getFeatured(10);
echo "Избранных: " . count($featured) . "\n";

echo "\n2. ПРОВЕРКА ПОИСКА\n";
echo str_repeat("-", 50) . "\n";

$results1 = KnowledgeArticle::search('ученик');
echo "Поиск 'ученик': " . count($results1) . " результатов\n";

$results2 = KnowledgeArticle::search('оплат');
echo "Поиск 'оплат': " . count($results2) . " результатов\n";

echo "\n3. ПРОВЕРКА СВЯЗЕЙ\n";
echo str_repeat("-", 50) . "\n";

$article = KnowledgeArticle::findBySlug('welcome');
if ($article) {
    echo "Статья: {$article->title}\n";
    echo "Категория: " . ($article->category ? $article->category->name : "ОШИБКА!") . "\n";
    $related = $article->getRelatedArticles(3);
    echo "Связанные: " . count($related) . "\n";
}

echo "\n4. ПРОВЕРКА КОНТЕНТА\n";
echo str_repeat("-", 50) . "\n";

if ($article && $article->content) {
    $len = mb_strlen($article->content);
    $hasH2 = strpos($article->content, '<h2>') !== false ? 'ДА' : 'НЕТ';
    $hasP = strpos($article->content, '<p>') !== false ? 'ДА' : 'НЕТ';
    echo "Длина контента: {$len} символов\n";
    echo "Содержит <h2>: {$hasH2}\n";
    echo "Содержит <p>: {$hasP}\n";
}

echo "\n5. ПРОВЕРКА ФАЙЛОВ\n";
echo str_repeat("-", 50) . "\n";

$files = [
    'models/KnowledgeCategory.php',
    'models/KnowledgeArticle.php',
    'modules/crm/controllers/KnowledgeController.php',
    'modules/crm/views/knowledge/index.php',
    'modules/crm/views/knowledge/category.php',
    'modules/crm/views/knowledge/view.php',
    'modules/crm/views/knowledge/search.php',
    'modules/superadmin/controllers/KnowledgeController.php',
    'modules/superadmin/views/knowledge/index.php',
    'modules/superadmin/views/knowledge/_form.php',
    'modules/superadmin/views/knowledge/categories.php',
    'modules/superadmin/models/search/KnowledgeArticleSearch.php',
];

$allOk = true;
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "[OK]" : "[!!]";
    echo "{$status} {$file}\n";
    if (!$exists) $allOk = false;
}

echo "\n=== ИТОГ ===\n";
echo "Категорий: " . count($categories) . "\n";
echo "Статей: " . count($articles) . "\n";
echo "Файлы: " . ($allOk ? "ВСЕ ОК" : "ЕСТЬ ПРОБЛЕМЫ") . "\n";
echo "\nТестирование завершено успешно!\n";
