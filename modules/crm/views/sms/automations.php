<?php

use app\helpers\OrganizationUrl;
use app\models\NotificationSetting;
use app\models\SmsTemplate;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $settings NotificationSetting[] indexed by type */
/** @var array $templates SmsTemplate[] */
/** @var bool $smsConfigured */
/** @var bool $whatsappConnected */

$this->title = 'Авторассылки';
$this->params['breadcrumbs'][] = ['label' => 'Рассылка', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Группируем шаблоны по типу для JS
$templatesByType = ['sms' => [], 'whatsapp' => []];
foreach ($templates as $template) {
    $templatesByType[$template->type][$template->id] = $template->name;
}

// Метаданные триггеров
$typesMeta = NotificationSetting::getTypesMeta();

// Группируем по категориям
$categories = [
    'lessons' => ['name' => 'Занятия', 'icon' => 'academic-cap'],
    'payments' => ['name' => 'Платежи', 'icon' => 'banknotes'],
    'other' => ['name' => 'Прочее', 'icon' => 'sparkles'],
];

$settingsByCategory = [];
foreach ($settings as $type => $setting) {
    $meta = $typesMeta[$type] ?? ['category' => 'other'];
    $category = $meta['category'] ?? 'other';
    $settingsByCategory[$category][$type] = $setting;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Настройте автоматическую отправку уведомлений</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['sms/templates']) ?>" class="btn btn-secondary">
                <?= Icon::widget(['name' => 'document-text', 'class' => 'w-4 h-4']) ?>
                Шаблоны
            </a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- WhatsApp Status -->
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $whatsappConnected ? 'bg-green-100' : 'bg-gray-100' ?>">
                    <svg class="w-6 h-6 <?= $whatsappConnected ? 'text-green-600' : 'text-gray-400' ?>" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">WhatsApp</h3>
                    <?php if ($whatsappConnected): ?>
                        <p class="text-sm text-green-600">Подключен и готов к отправке</p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Не подключен</p>
                    <?php endif; ?>
                </div>
                <?php if (!$whatsappConnected): ?>
                    <a href="<?= OrganizationUrl::to(['whatsapp/index']) ?>" class="btn btn-sm btn-primary">Подключить</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- SMS Status -->
        <div class="card">
            <div class="card-body flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $smsConfigured ? 'bg-primary-100' : 'bg-gray-100' ?>">
                    <?= Icon::widget(['name' => 'chat-bubble-left-right', 'class' => 'w-6 h-6 ' . ($smsConfigured ? 'text-primary-600' : 'text-gray-400')]) ?>
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">SMS</h3>
                    <?php if ($smsConfigured): ?>
                        <p class="text-sm text-primary-600">Провайдер настроен</p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Провайдер не настроен</p>
                    <?php endif; ?>
                </div>
                <?php if (!$smsConfigured): ?>
                    <a href="<?= OrganizationUrl::to(['sms/settings']) ?>" class="btn btn-sm btn-secondary">Настроить</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Automations by Category -->
    <?php foreach ($categories as $categoryKey => $categoryInfo): ?>
        <?php if (!empty($settingsByCategory[$categoryKey])): ?>
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <?= Icon::widget(['name' => $categoryInfo['icon'], 'class' => 'w-5 h-5 text-gray-400']) ?>
                <?= $categoryInfo['name'] ?>
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <?php foreach ($settingsByCategory[$categoryKey] as $type => $setting): ?>
                    <?php
                    $meta = $typesMeta[$type];
                    $colorClasses = [
                        'primary' => ['bg' => 'bg-primary-100', 'text' => 'text-primary-600', 'border' => 'border-primary-200'],
                        'success' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'border' => 'border-green-200'],
                        'warning' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                        'danger' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'border' => 'border-red-200'],
                        'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600', 'border' => 'border-pink-200'],
                    ];
                    $colors = $colorClasses[$meta['color']] ?? $colorClasses['primary'];
                    ?>
                    <div class="card automation-card" data-type="<?= $type ?>" data-setting-id="<?= $setting->id ?>">
                        <div class="card-body">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 <?= $colors['bg'] ?>">
                                    <?= Icon::widget(['name' => $meta['icon'], 'class' => 'w-6 h-6 ' . $colors['text']]) ?>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="font-medium text-gray-900"><?= Html::encode($meta['name']) ?></h3>
                                        <!-- Toggle -->
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer automation-toggle"
                                                   data-type="<?= $type ?>"
                                                   <?= $setting->is_active ? 'checked' : '' ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1"><?= Html::encode($meta['description']) ?></p>

                                    <!-- Settings -->
                                    <div class="mt-4 space-y-3 automation-settings <?= $setting->is_active ? '' : 'hidden' ?>">
                                        <!-- Channel -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600 w-24">Канал:</label>
                                            <select class="form-input form-input-sm flex-1 automation-channel" data-type="<?= $type ?>">
                                                <option value="whatsapp" <?= $setting->channel === 'whatsapp' ? 'selected' : '' ?> <?= !$whatsappConnected ? 'disabled' : '' ?>>
                                                    WhatsApp <?= !$whatsappConnected ? '(не подключен)' : '' ?>
                                                </option>
                                                <?php if ($smsConfigured): ?>
                                                <option value="sms" <?= $setting->channel === 'sms' ? 'selected' : '' ?>>SMS</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <!-- Template -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600 w-24">Шаблон:</label>
                                            <select class="form-input form-input-sm flex-1 automation-template" data-type="<?= $type ?>">
                                                <option value="">-- Выберите шаблон --</option>
                                                <?php foreach ($templates as $template): ?>
                                                    <?php if ($template->type === $setting->channel): ?>
                                                    <option value="<?= $template->id ?>" <?= $setting->template_id == $template->id ? 'selected' : '' ?>>
                                                        <?= Html::encode($template->name) ?>
                                                    </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <?php if (!empty($meta['hasHoursBefore'])): ?>
                                        <!-- Hours Before -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600 w-24">За сколько:</label>
                                            <select class="form-input form-input-sm flex-1 automation-hours" data-type="<?= $type ?>">
                                                <?php foreach (NotificationSetting::getHoursBeforeList() as $hours => $label): ?>
                                                <option value="<?= $hours ?>" <?= $setting->hours_before == $hours ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($meta['hasFrequency'])): ?>
                                        <!-- Frequency -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600 w-24">Частота:</label>
                                            <select class="form-input form-input-sm flex-1 automation-frequency" data-type="<?= $type ?>">
                                                <?php foreach (NotificationSetting::getFrequencyList() as $freq => $label): ?>
                                                <option value="<?= $freq ?>" <?= $setting->frequency == $freq ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>

                                        <!-- No template warning -->
                                        <div class="no-template-warning <?= $setting->template_id ? 'hidden' : '' ?> p-2 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-700">
                                            <?= Icon::widget(['name' => 'exclamation-triangle', 'class' => 'w-4 h-4 inline-block mr-1']) ?>
                                            Выберите шаблон сообщения
                                        </div>
                                    </div>

                                    <!-- Status when disabled -->
                                    <div class="mt-2 automation-disabled-hint <?= $setting->is_active ? 'hidden' : '' ?>">
                                        <span class="text-xs text-gray-400">Включите для настройки</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Empty Templates Warning -->
    <?php if (empty($templates)): ?>
    <div class="card">
        <div class="card-body text-center py-8">
            <?= Icon::widget(['name' => 'document-text', 'class' => 'w-12 h-12 mx-auto text-gray-300']) ?>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Нет шаблонов сообщений</h3>
            <p class="mt-2 text-gray-500">Создайте шаблоны сообщений, чтобы использовать их в авторассылках</p>
            <a href="<?= OrganizationUrl::to(['sms/templates']) ?>" class="btn btn-primary mt-4">
                Создать шаблоны
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$saveUrl = OrganizationUrl::to(['sms/save-automation']);
$templatesByTypeJson = json_encode($templatesByType);

$js = <<<JS
var templatesByType = {$templatesByTypeJson};
var saveUrl = '{$saveUrl}';

// Toggle automation on/off
document.querySelectorAll('.automation-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        var type = this.dataset.type;
        var card = this.closest('.automation-card');
        var settings = card.querySelector('.automation-settings');
        var hint = card.querySelector('.automation-disabled-hint');

        if (this.checked) {
            settings.classList.remove('hidden');
            hint.classList.add('hidden');
        } else {
            settings.classList.add('hidden');
            hint.classList.remove('hidden');
        }

        saveAutomation(type);
    });
});

// Channel change - update template options
document.querySelectorAll('.automation-channel').forEach(function(select) {
    select.addEventListener('change', function() {
        var type = this.dataset.type;
        var card = this.closest('.automation-card');
        var templateSelect = card.querySelector('.automation-template');
        var channel = this.value;

        // Update template options
        templateSelect.innerHTML = '<option value="">-- Выберите шаблон --</option>';
        var templates = templatesByType[channel] || {};
        for (var id in templates) {
            var option = document.createElement('option');
            option.value = id;
            option.textContent = templates[id];
            templateSelect.appendChild(option);
        }

        saveAutomation(type);
    });
});

// Template change
document.querySelectorAll('.automation-template').forEach(function(select) {
    select.addEventListener('change', function() {
        var type = this.dataset.type;
        var card = this.closest('.automation-card');
        var warning = card.querySelector('.no-template-warning');

        if (this.value) {
            warning.classList.add('hidden');
        } else {
            warning.classList.remove('hidden');
        }

        saveAutomation(type);
    });
});

// Hours/Frequency change
document.querySelectorAll('.automation-hours, .automation-frequency').forEach(function(select) {
    select.addEventListener('change', function() {
        var type = this.dataset.type;
        saveAutomation(type);
    });
});

// Save automation settings
function saveAutomation(type) {
    var card = document.querySelector('.automation-card[data-type="' + type + '"]');
    var toggle = card.querySelector('.automation-toggle');
    var channelSelect = card.querySelector('.automation-channel');
    var templateSelect = card.querySelector('.automation-template');
    var hoursSelect = card.querySelector('.automation-hours');
    var frequencySelect = card.querySelector('.automation-frequency');

    var data = {
        type: type,
        is_active: toggle.checked ? 1 : 0,
        channel: channelSelect ? channelSelect.value : 'whatsapp',
        template_id: templateSelect ? templateSelect.value : '',
        hours_before: hoursSelect ? hoursSelect.value : '',
        frequency: frequencySelect ? frequencySelect.value : ''
    };

    // Show saving indicator
    card.style.opacity = '0.7';

    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        card.style.opacity = '1';
        if (!result.success) {
            console.error('Save failed:', result.message);
            // Could show a toast notification here
        }
    })
    .catch(error => {
        card.style.opacity = '1';
        console.error('Save error:', error);
    });
}
JS;

$this->registerJs($js);
?>
