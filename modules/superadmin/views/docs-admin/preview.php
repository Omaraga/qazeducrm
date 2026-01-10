<?php

/** @var yii\web\View $this */
/** @var app\models\DocsSection $model */

use yii\helpers\Html;

$this->title = 'Предпросмотр: ' . $model->title;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .docs-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .docs-content h3 {
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
        .docs-content ul li { list-style-type: disc; }
        .docs-content ol li { list-style-type: decimal; }
        .docs-content img {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
            max-width: 100%;
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="bg-yellow-100 border-b border-yellow-200 px-4 py-2 text-center text-sm text-yellow-800">
        <i class="fas fa-eye mr-2"></i>
        Режим предпросмотра — <?= Html::encode($model->chapter->title) ?> / <?= Html::encode($model->title) ?>
    </div>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= Html::encode($model->title) ?></h1>

            <?php if ($model->excerpt): ?>
                <p class="text-lg text-gray-600 mb-6"><?= Html::encode($model->excerpt) ?></p>
            <?php endif; ?>

            <div class="docs-content">
                <?php if ($model->content): ?>
                    <?= $model->content ?>
                <?php else: ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-yellow-700">Контент не заполнен</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
