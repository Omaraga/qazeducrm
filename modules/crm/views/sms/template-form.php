<?php

use app\helpers\OrganizationUrl;
use app\models\SmsTemplate;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SmsTemplate $model */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Новый шаблон' : 'Редактировать шаблон';
$this->params['breadcrumbs'][] = ['label' => 'SMS уведомления', 'url' => ['index']];
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <?= $form->field($model, 'type')->dropDownList(SmsTemplate::getTypeList(), [
                                'id' => 'template-type',
                                'prompt' => 'Выберите тип...',
                                'class' => 'form-input'
                            ]) ?>
                        </div>
                        <div>
                            <?= $form->field($model, 'code')->dropDownList([], [
                                'id' => 'template-code',
                                'prompt' => 'Сначала выберите тип...',
                                'class' => 'form-input'
                            ]) ?>
                        </div>
                        <div>
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Название для удобства',
                                'class' => 'form-input'
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'content')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Здравствуйте, {name}! Напоминаем о занятии {date} в {time}. {org_name}',
                        'class' => 'form-input'
                    ])->hint('Используйте плейсхолдеры из списка справа') ?>

                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <?= Html::activeCheckbox($model, 'is_active', [
                                'class' => 'sr-only peer',
                                'label' => false
                            ]) ?>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Активен</span>
                        </label>
                    </div>

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
$templateCodes = json_encode([
    'sms' => SmsTemplate::getCodeList(),
    'whatsapp' => SmsTemplate::getWhatsAppCodeList()
]);
$currentType = Html::encode($model->type);
$currentCode = Html::encode($model->code);

$js = <<<JS
// Коды для каждого типа шаблона
var templateCodes = {$templateCodes};
var currentType = '{$currentType}';
var currentCode = '{$currentCode}';

// Обновить список кодов при смене типа
function updateCodeList() {
    var typeSelect = document.getElementById('template-type');
    var codeSelect = document.getElementById('template-code');
    var selectedType = typeSelect.value;

    // Очищаем список кодов
    codeSelect.innerHTML = '<option value="">Выберите тип...</option>';

    if (selectedType && templateCodes[selectedType]) {
        var codes = templateCodes[selectedType];
        for (var code in codes) {
            var option = document.createElement('option');
            option.value = code;
            option.textContent = codes[code];
            if (code === currentCode && selectedType === currentType) {
                option.selected = true;
            }
            codeSelect.appendChild(option);
        }
    }
}

// Инициализация
document.getElementById('template-type').addEventListener('change', updateCodeList);
// Инициализируем при загрузке если тип уже выбран
if (currentType) {
    updateCodeList();
}

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
