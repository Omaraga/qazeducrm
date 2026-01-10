<?php

/** @var yii\web\View $this */
/** @var app\models\DocsSection $model */
/** @var app\models\DocsChapter $chapter */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'Создание раздела' : 'Редактирование раздела';
$this->params['breadcrumbs'][] = ['label' => 'Документация', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="docs-section-form">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Глава: <?= Html::encode($chapter->title) ?></p>
        </div>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Назад
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <?php $form = ActiveForm::begin(); ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <?= $form->field($model, 'title', ['options' => ['class' => '']])->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'Название раздела',
            ]) ?>

            <?= $form->field($model, 'slug', ['options' => ['class' => '']])->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'url-slug',
            ])->hint('URL: /docs/' . Html::encode($chapter->slug) . '/<slug>') ?>
        </div>

        <?= $form->field($model, 'excerpt')->textarea([
            'rows' => 2,
            'class' => 'form-control',
            'placeholder' => 'Краткое описание (для поиска и превью)',
        ]) ?>

        <?= $form->field($model, 'content')->textarea([
            'rows' => 20,
            'class' => 'form-control tinymce-editor',
            'id' => 'section-content',
        ])->label('Содержимое (HTML)') ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <?= $form->field($model, 'sort_order', ['options' => ['class' => '']])->textInput([
                'type' => 'number',
                'class' => 'form-control',
                'min' => 0,
            ]) ?>

            <?= $form->field($model, 'is_active', ['options' => ['class' => 'flex items-center']])->checkbox([
                'class' => 'form-check-input',
            ]) ?>
        </div>

        <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
            <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', [
                'class' => 'btn btn-primary'
            ]) ?>
            <?php if (!$model->isNewRecord): ?>
                <a href="<?= Url::to(['preview', 'id' => $model->id]) ?>" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-eye mr-2"></i>
                    Предпросмотр
                </a>
            <?php endif; ?>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">Отмена</a>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <!-- Подсказки по виджетам -->
    <div class="mt-6 bg-blue-50 rounded-lg p-4">
        <h3 class="font-semibold text-blue-800 mb-2">
            <i class="fas fa-info-circle mr-2"></i>
            Подсказки по оформлению
        </h3>
        <div class="text-sm text-blue-700 space-y-2">
            <p><strong>Заголовки:</strong> Используйте &lt;h2&gt; и &lt;h3&gt; с атрибутом id для навигации.</p>
            <p><strong>Изображения:</strong> Загружайте через кнопку в редакторе. Путь: /images/docs/uploads/</p>
            <p><strong>Советы:</strong> Используйте классы для блоков:</p>
            <code class="block bg-blue-100 p-2 rounded mt-1">
                &lt;div class="bg-green-50 border border-green-200 rounded-lg p-4"&gt;Совет&lt;/div&gt;
            </code>
        </div>
    </div>
</div>

<?php
// TinyMCE редактор
$uploadUrl = Url::to(['upload-image']);
$this->registerJsFile('https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js', ['referrerpolicy' => 'origin']);
$js = <<<JS
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#section-content',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'image link codesample | removeformat | help',
        content_style: 'body { font-family: Inter, sans-serif; font-size: 16px; line-height: 1.7; }',
        images_upload_url: '{$uploadUrl}',
        automatic_uploads: true,
        images_reuse_filename: true,
        file_picker_types: 'image',
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Bash', value: 'bash' }
        ],
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
}
JS;
$this->registerJs($js);
?>
