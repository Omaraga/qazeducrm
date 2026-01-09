<?php

use app\helpers\OrganizationUrl;
use app\models\SmsTemplate;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SmsTemplate $model */
/** @var bool $smsConfigured */

$smsConfigured = $smsConfigured ?? false;
$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Новый шаблон' : 'Редактировать шаблон';
$this->params['breadcrumbs'][] = ['label' => 'Рассылка', 'url' => ['automations']];
$this->params['breadcrumbs'][] = ['label' => 'Шаблоны', 'url' => ['templates']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= $isNew ? 'Создание нового шаблона сообщения' : 'Редактирование шаблона' ?></p>
        </div>
        <a href="<?= OrganizationUrl::to(['sms/templates']) ?>" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'space-y-4'],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{hint}\n{error}",
                            'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-1'],
                            'inputOptions' => ['class' => 'form-input'],
                            'hintOptions' => ['class' => 'mt-1 text-sm text-gray-500'],
                            'errorOptions' => ['class' => 'mt-1 text-sm text-danger-600'],
                        ],
                    ]); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if ($smsConfigured): ?>
                        <div>
                            <?= $form->field($model, 'type')->dropDownList(SmsTemplate::getTypeList(), [
                                'id' => 'template-type',
                                'prompt' => 'Выберите тип...',
                                'class' => 'form-input'
                            ])->hint('SMS или WhatsApp') ?>
                        </div>
                        <?php else: ?>
                        <div>
                            <?= Html::activeHiddenInput($model, 'type', ['value' => SmsTemplate::TYPE_WHATSAPP, 'id' => 'template-type']) ?>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                            <div class="form-input bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                WhatsApp
                            </div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Например: Напоминание о занятии',
                                'class' => 'form-input'
                            ])->hint('Название для удобного поиска') ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'content')->textarea([
                        'rows' => 6,
                        'placeholder' => 'Здравствуйте, {name}! Напоминаем о занятии {date} в {time}. {org_name}',
                        'class' => 'form-input'
                    ])->hint('Используйте плейсхолдеры из списка справа для персонализации') ?>

                    <!-- Hidden field for code - set to custom -->
                    <?= Html::activeHiddenInput($model, 'code', ['value' => $model->code ?: SmsTemplate::CODE_CUSTOM]) ?>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="<?= OrganizationUrl::to(['sms/templates']) ?>" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Сохранить
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Плейсхолдеры</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-500 mb-4">Нажмите на плейсхолдер, чтобы вставить его в текст:</p>
                    <ul class="space-y-3">
                        <?php foreach (SmsTemplate::getPlaceholders() as $placeholder => $description): ?>
                            <li>
                                <code class="placeholder-btn inline-block px-2 py-0.5 bg-primary-50 text-primary-600 rounded cursor-pointer hover:bg-primary-100 transition-colors" title="Нажмите, чтобы вставить"><?= $placeholder ?></code>
                                <p class="text-sm text-gray-500 mt-0.5"><?= $description ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Советы</h3>
                </div>
                <div class="card-body">
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Длина SMS до 70 символов кириллицей
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Латиницей — до 160 символов
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Всегда указывайте название организации
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Избегайте спам-слов
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
// Вставка плейсхолдеров
document.querySelectorAll('.placeholder-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var textarea = document.querySelector('textarea[name="SmsTemplate[content]"]');
        var placeholder = this.textContent;

        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;

        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
        textarea.focus();
    });
});
JS;

$this->registerJs($js);
?>
