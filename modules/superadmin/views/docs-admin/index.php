<?php

/** @var yii\web\View $this */
/** @var app\models\DocsChapter[] $chapters */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Управление документацией';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docs-admin-index">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <div class="flex gap-3">
            <a href="<?= Url::to(['/docs']) ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt mr-2"></i>
                Открыть документацию
            </a>
            <a href="<?= Url::to(['create-chapter']) ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Добавить главу
            </a>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success mb-4">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <?php if (empty($chapters)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-book text-4xl mb-3 text-gray-300"></i>
                <p>Главы документации пока не созданы</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200" id="chapters-list">
                <?php foreach ($chapters as $chapter): ?>
                    <div class="chapter-item" data-id="<?= $chapter->id ?>">
                        <!-- Chapter header -->
                        <div class="flex items-center gap-4 p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600">
                                        <i class="fas fa-<?= Html::encode($chapter->icon ?: 'book') ?>"></i>
                                    </span>
                                    <div>
                                        <div class="font-semibold text-gray-900"><?= Html::encode($chapter->title) ?></div>
                                        <div class="text-sm text-gray-500">/docs/<?= Html::encode($chapter->slug) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <?php if (!$chapter->is_active): ?>
                                    <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded">Скрыта</span>
                                <?php endif; ?>

                                <span class="text-sm text-gray-500">
                                    <?= count($chapter->sections) ?> разделов
                                </span>

                                <div class="flex gap-1">
                                    <a href="<?= Url::to(['create-section', 'chapter_id' => $chapter->id]) ?>"
                                       class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded"
                                       title="Добавить раздел">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="<?= Url::to(['update-chapter', 'id' => $chapter->id]) ?>"
                                       class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded"
                                       title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= Url::to(['delete-chapter', 'id' => $chapter->id]) ?>"
                                       class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded"
                                       title="Удалить"
                                       data-method="post"
                                       data-confirm="Вы уверены? Все разделы главы также будут удалены.">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Sections -->
                        <?php if (!empty($chapter->sections)): ?>
                            <div class="sections-list pl-12 divide-y divide-gray-100" data-chapter="<?= $chapter->id ?>">
                                <?php foreach ($chapter->sections as $section): ?>
                                    <div class="section-item flex items-center gap-4 p-3 hover:bg-gray-50 transition-colors" data-id="<?= $section->id ?>">
                                        <div class="cursor-move text-gray-300 hover:text-gray-500 drag-handle">
                                            <i class="fas fa-grip-vertical text-sm"></i>
                                        </div>

                                        <div class="flex-1">
                                            <div class="text-gray-800"><?= Html::encode($section->title) ?></div>
                                            <div class="text-xs text-gray-400">/<?= Html::encode($section->slug) ?></div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <?php if (!$section->is_active): ?>
                                                <span class="px-2 py-0.5 text-xs bg-gray-200 text-gray-600 rounded">Скрыт</span>
                                            <?php endif; ?>
                                            <?php if (empty($section->content)): ?>
                                                <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded">Без контента</span>
                                            <?php endif; ?>

                                            <div class="flex gap-1">
                                                <a href="<?= Url::to(['preview', 'id' => $section->id]) ?>"
                                                   class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                                   target="_blank"
                                                   title="Предпросмотр">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                <a href="<?= Url::to(['update-section', 'id' => $section->id]) ?>"
                                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded"
                                                   title="Редактировать">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                <a href="<?= Url::to(['delete-section', 'id' => $section->id]) ?>"
                                                   class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded"
                                                   title="Удалить"
                                                   data-method="post"
                                                   data-confirm="Удалить этот раздел?">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Регистрируем скрипт для drag & drop сортировки
$reorderUrl = Url::to(['reorder']);
$js = <<<JS
// Сортировка глав
if (typeof Sortable !== 'undefined') {
    var chaptersList = document.getElementById('chapters-list');
    if (chaptersList) {
        new Sortable(chaptersList, {
            animation: 150,
            handle: '.chapter-item > div:first-child .drag-handle',
            onEnd: function(evt) {
                var items = Array.from(chaptersList.querySelectorAll('.chapter-item')).map(el => el.dataset.id);
                fetch('{$reorderUrl}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ type: 'chapters', items: items })
                });
            }
        });
    }

    // Сортировка секций
    document.querySelectorAll('.sections-list').forEach(function(list) {
        new Sortable(list, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function(evt) {
                var items = Array.from(list.querySelectorAll('.section-item')).map(el => el.dataset.id);
                fetch('{$reorderUrl}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ type: 'sections', items: items })
                });
            }
        });
    });
}
JS;
$this->registerJs($js);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js');
?>
