<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var app\models\Organizations $organization */

$this->title = 'Настройки организации';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => OrganizationUrl::to(['settings/index'])];
$this->params['breadcrumbs'][] = $this->title;

$saveUrl = OrganizationUrl::to(['settings/ajax-save-organization']);
$uploadLogoUrl = OrganizationUrl::to(['settings/upload-logo']);
$deleteLogoUrl = OrganizationUrl::to(['settings/delete-logo']);

// Часовые пояса Казахстана
$timezones = [
    'Asia/Almaty' => 'Алматы, Нур-Султан (UTC+5)',
    'Asia/Aqtobe' => 'Актобе (UTC+5)',
    'Asia/Aqtau' => 'Актау (UTC+5)',
    'Asia/Atyrau' => 'Атырау (UTC+5)',
    'Asia/Oral' => 'Уральск (UTC+5)',
    'Asia/Qyzylorda' => 'Кызылорда (UTC+5)',
    'Asia/Qostanay' => 'Костанай (UTC+6)',
    'Europe/Moscow' => 'Москва (UTC+3)',
    'Europe/Kiev' => 'Киев (UTC+2)',
];

// Языки
$locales = [
    'ru-RU' => 'Русский',
    'kk-KZ' => 'Қазақша',
    'en-US' => 'English',
];

// Валюты
$currencies = [
    'KZT' => 'Тенге (₸)',
    'RUB' => 'Рубль (₽)',
    'USD' => 'Доллар ($)',
];

// Форматы даты
$dateFormats = [
    'd.m.Y' => '31.12.2024',
    'Y-m-d' => '2024-12-31',
    'd/m/Y' => '31/12/2024',
];

// Продолжительность занятий
$durations = [
    30 => '30 минут',
    45 => '45 минут',
    60 => '1 час',
    90 => '1.5 часа',
    120 => '2 часа',
];

// Дни недели
$weekDays = [
    1 => 'Пн',
    2 => 'Вт',
    3 => 'Ср',
    4 => 'Чт',
    5 => 'Пт',
    6 => 'Сб',
    7 => 'Вс',
];

// Подготовка данных для JavaScript
$fieldsData = [
    'name' => $organization->name ?? '',
    'legal_name' => $organization->legal_name ?? '',
    'bin' => $organization->bin ?? '',
    'phone' => $organization->phone ?? '',
    'email' => $organization->email ?? '',
    'address' => $organization->address ?? '',
    'timezone' => $organization->timezone ?? 'Asia/Almaty',
    'locale' => $organization->locale ?? 'ru-RU',
    'instagram' => $organization->instagram ?? '',
    'whatsapp' => $organization->whatsapp ?? '',
    'telegram' => $organization->telegram ?? '',
    'currency' => $organization->currency ?? 'KZT',
    'date_format' => $organization->date_format ?? 'd.m.Y',
    'work_hours_start' => $organization->work_hours_start ?? '09:00',
    'work_hours_end' => $organization->work_hours_end ?? '18:00',
    'working_days' => $organization->working_days ?? [1, 2, 3, 4, 5],
    'first_day_of_week' => $organization->first_day_of_week ?? 1,
    'default_lesson_duration' => $organization->default_lesson_duration ?? 60,
    'auto_deduct_enabled' => (bool)($organization->auto_deduct_enabled ?? false),
    'lesson_notifications_enabled' => (bool)($organization->lesson_notifications_enabled ?? true),
];
?>

<div class="space-y-6" x-data="organizationSettings()">
    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="<?= OrganizationUrl::to(['settings/organization']) ?>"
               class="border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <?= Icon::show('building-office', 'sm') ?>
                Организация
            </a>
            <a href="<?= OrganizationUrl::to(['settings/access']) ?>"
               class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <?= Icon::show('shield-check', 'sm') ?>
                Права доступа
            </a>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <?= Icon::show('building-office', 'md', 'text-blue-600') ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Общие настройки вашей организации</p>
            </div>
        </div>
        <!-- Статус сохранения -->
        <div x-show="saveStatus" x-transition class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-full"
             :class="{
                'bg-gray-100 text-gray-600': saveStatus === 'saving',
                'bg-success-100 text-success-700': saveStatus === 'success',
                'bg-danger-100 text-danger-700': saveStatus === 'error'
             }">
            <template x-if="saveStatus === 'saving'">
                <span class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Сохранение...
                </span>
            </template>
            <template x-if="saveStatus === 'success'">
                <span class="flex items-center gap-2">
                    <?= Icon::show('check', 'sm') ?>
                    Сохранено
                </span>
            </template>
            <template x-if="saveStatus === 'error'">
                <span class="flex items-center gap-2">
                    <?= Icon::show('x-mark', 'sm') ?>
                    Ошибка
                </span>
            </template>
        </div>
    </div>

    <!-- Группа 1: Основные данные -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <?= Icon::show('building-office', 'md', 'text-blue-600') ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Основные данные</h2>
                    <p class="text-sm text-gray-500">Информация об организации</p>
                </div>
            </div>
        </div>
        <div class="card-body p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Логотип -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Логотип</label>
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50">
                            <template x-if="logoUrl">
                                <img :src="logoUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoUrl">
                                <?= Icon::show('photo', 'xl', 'text-gray-400') ?>
                            </template>
                        </div>
                        <div class="flex flex-col gap-2">
                            <input type="file" x-ref="logoInput" @change="uploadLogo" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp">
                            <button type="button" @click="$refs.logoInput.click()" class="btn btn-secondary btn-sm">
                                <?= Icon::show('arrow-up-tray', 'sm') ?>
                                Загрузить
                            </button>
                            <button type="button" x-show="logoUrl" @click="deleteLogo" class="btn btn-outline-danger btn-sm">
                                <?= Icon::show('trash', 'sm') ?>
                                Удалить
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Рекомендуемый размер: 200x200 px. Форматы: PNG, JPG, GIF, WebP. Максимум 2MB.</p>
                </div>

                <!-- Название -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Название организации <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" class="form-input w-full"
                           x-model="fields.name"
                           @blur="saveField('name', fields.name)"
                           placeholder="Учебный центр 'Знание'">
                    <p class="mt-1 text-sm text-gray-500">Отображается в интерфейсе и документах</p>
                </div>

                <!-- Юридическое название -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Юридическое название</label>
                    <input type="text" class="form-input w-full"
                           x-model="fields.legal_name"
                           @blur="saveField('legal_name', fields.legal_name)"
                           placeholder="ТОО 'Знание'">
                    <p class="mt-1 text-sm text-gray-500">Полное юридическое название для договоров</p>
                </div>

                <!-- БИН -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">БИН</label>
                    <input type="text" class="form-input w-full"
                           x-model="fields.bin"
                           @blur="saveField('bin', fields.bin)"
                           placeholder="123456789012"
                           maxlength="12">
                    <p class="mt-1 text-sm text-gray-500">Бизнес-идентификационный номер (12 цифр)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Группа 2: Контактные данные -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <?= Icon::show('phone', 'md', 'text-green-600') ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Контактные данные</h2>
                    <p class="text-sm text-gray-500">Контакты для связи с организацией</p>
                </div>
            </div>
        </div>
        <div class="card-body p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Телефон -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                    <input type="tel" class="form-input w-full"
                           x-model="fields.phone"
                           @blur="saveField('phone', fields.phone)"
                           placeholder="+7 (777) 123-45-67">
                    <p class="mt-1 text-sm text-gray-500">Основной номер для связи</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" class="form-input w-full"
                           x-model="fields.email"
                           @blur="saveField('email', fields.email)"
                           placeholder="info@example.kz">
                    <p class="mt-1 text-sm text-gray-500">Email для уведомлений и связи</p>
                </div>

                <!-- Адрес -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                    <input type="text" class="form-input w-full"
                           x-model="fields.address"
                           @blur="saveField('address', fields.address)"
                           placeholder="г. Алматы, ул. Абая, 10">
                    <p class="mt-1 text-sm text-gray-500">Физический адрес организации</p>
                </div>

                <!-- Социальные сети -->
                <div class="lg:col-span-2 pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">Социальные сети</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Instagram -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    Instagram
                                </span>
                            </label>
                            <input type="text" class="form-input w-full"
                                   x-model="fields.instagram"
                                   @blur="saveField('instagram', fields.instagram)"
                                   placeholder="your_account">
                        </div>

                        <!-- WhatsApp -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    WhatsApp
                                </span>
                            </label>
                            <input type="text" class="form-input w-full"
                                   x-model="fields.whatsapp"
                                   @blur="saveField('whatsapp', fields.whatsapp)"
                                   placeholder="+77771234567">
                        </div>

                        <!-- Telegram -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                    Telegram
                                </span>
                            </label>
                            <input type="text" class="form-input w-full"
                                   x-model="fields.telegram"
                                   @blur="saveField('telegram', fields.telegram)"
                                   placeholder="@your_channel">
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Ссылки на социальные сети для клиентов</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Группа 3: Региональные настройки -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <?= Icon::show('globe-alt', 'md', 'text-purple-600') ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Региональные настройки</h2>
                    <p class="text-sm text-gray-500">Язык, валюта и форматы отображения</p>
                </div>
            </div>
        </div>
        <div class="card-body p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Часовой пояс -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Часовой пояс</label>
                    <select class="form-select w-full" x-model="fields.timezone" @change="saveField('timezone', fields.timezone)">
                        <?php foreach ($timezones as $tz => $label): ?>
                            <option value="<?= $tz ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Влияет на время занятий</p>
                </div>

                <!-- Язык -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Язык интерфейса</label>
                    <select class="form-select w-full" x-model="fields.locale" @change="saveField('locale', fields.locale)">
                        <?php foreach ($locales as $loc => $label): ?>
                            <option value="<?= $loc ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Язык по умолчанию</p>
                </div>

                <!-- Валюта -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Валюта</label>
                    <select class="form-select w-full" x-model="fields.currency" @change="saveField('currency', fields.currency)">
                        <?php foreach ($currencies as $cur => $label): ?>
                            <option value="<?= $cur ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Для отображения цен</p>
                </div>

                <!-- Формат даты -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Формат даты</label>
                    <select class="form-select w-full" x-model="fields.date_format" @change="saveField('date_format', fields.date_format)">
                        <?php foreach ($dateFormats as $fmt => $example): ?>
                            <option value="<?= $fmt ?>"><?= Html::encode($example) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Отображение дат</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Группа 4: Рабочее время -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                    <?= Icon::show('clock', 'md', 'text-orange-600') ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Рабочее время</h2>
                    <p class="text-sm text-gray-500">График работы организации</p>
                </div>
            </div>
        </div>
        <div class="card-body p-6">
            <div class="space-y-6">
                <!-- Время работы -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Начало рабочего дня</label>
                        <input type="time" class="form-input w-full"
                               x-model="fields.work_hours_start"
                               @change="saveField('work_hours_start', fields.work_hours_start)">
                        <p class="mt-1 text-sm text-gray-500">Время открытия организации</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Конец рабочего дня</label>
                        <input type="time" class="form-input w-full"
                               x-model="fields.work_hours_end"
                               @change="saveField('work_hours_end', fields.work_hours_end)">
                        <p class="mt-1 text-sm text-gray-500">Время закрытия организации</p>
                    </div>
                </div>

                <!-- Рабочие дни -->
                <div class="pt-4 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Рабочие дни</label>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($weekDays as $day => $name): ?>
                            <button type="button"
                                    @click="toggleWorkingDay(<?= $day ?>)"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all border"
                                    :class="fields.working_days?.includes(<?= $day ?>)
                                        ? 'bg-primary-600 text-white border-primary-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'">
                                <?= $name ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Дни, когда организация работает</p>
                </div>

                <!-- Первый день недели -->
                <div class="pt-4 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Первый день недели</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="first_day" value="1"
                                   :checked="fields.first_day_of_week == 1"
                                   @change="saveField('first_day_of_week', 1)"
                                   class="form-radio text-primary-600">
                            <span class="ml-2 text-sm text-gray-700">Понедельник</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="first_day" value="7"
                                   :checked="fields.first_day_of_week == 7"
                                   @change="saveField('first_day_of_week', 7)"
                                   class="form-radio text-primary-600">
                            <span class="ml-2 text-sm text-gray-700">Воскресенье</span>
                        </label>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Первый день в календаре расписания</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Группа 5: Настройки занятий -->
    <div class="card">
        <div class="card-header border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <?= Icon::show('academic-cap', 'md', 'text-indigo-600') ?>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Настройки занятий</h2>
                    <p class="text-sm text-gray-500">Параметры по умолчанию для занятий</p>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Продолжительность -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="text-sm font-medium text-gray-900">Продолжительность занятия по умолчанию</div>
                        <p class="text-sm text-gray-500 mt-0.5">Стандартная длительность при создании занятия</p>
                    </div>
                    <select class="form-select w-auto"
                            x-model="fields.default_lesson_duration"
                            @change="saveField('default_lesson_duration', parseInt(fields.default_lesson_duration))">
                        <?php foreach ($durations as $mins => $label): ?>
                            <option value="<?= $mins ?>"><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Toggle: Автоматическое списание -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">Автоматическое списание</div>
                        <p class="text-sm text-gray-500 mt-0.5">Автоматически списывать оплату за проведённые занятия</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-100">
                            <button type="button"
                                    @click="saveField('auto_deduct_enabled', false); fields.auto_deduct_enabled = false"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                    :class="!fields.auto_deduct_enabled
                                        ? 'bg-white text-gray-900 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700'">
                                Нет
                            </button>
                            <button type="button"
                                    @click="saveField('auto_deduct_enabled', true); fields.auto_deduct_enabled = true"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                    :class="fields.auto_deduct_enabled
                                        ? 'bg-primary-600 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700'">
                                Да
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toggle: Уведомления о занятиях -->
            <div class="px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">Уведомления о занятиях</div>
                        <p class="text-sm text-gray-500 mt-0.5">Отправлять напоминания ученикам о предстоящих занятиях</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-100">
                            <button type="button"
                                    @click="saveField('lesson_notifications_enabled', false); fields.lesson_notifications_enabled = false"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                    :class="!fields.lesson_notifications_enabled
                                        ? 'bg-white text-gray-900 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700'">
                                Нет
                            </button>
                            <button type="button"
                                    @click="saveField('lesson_notifications_enabled', true); fields.lesson_notifications_enabled = true"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                                    :class="fields.lesson_notifications_enabled
                                        ? 'bg-primary-600 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700'">
                                Да
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function organizationSettings() {
    return {
        fields: <?= Json::encode($fieldsData) ?>,
        logoUrl: <?= Json::encode($organization->logo) ?>,
        saveStatus: null,
        saveTimeout: null,

        async saveField(field, value) {
            this.saveStatus = 'saving';

            try {
                const response = await fetch('<?= $saveUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: new URLSearchParams({
                        field: field,
                        value: typeof value === 'object' ? JSON.stringify(value) : String(value)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.saveStatus = 'success';
                } else {
                    this.saveStatus = 'error';
                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').error(data.message || 'Ошибка сохранения');
                    }
                }
            } catch (error) {
                console.error('Error saving field:', error);
                this.saveStatus = 'error';
                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    Alpine.store('toast').error('Ошибка сети');
                }
            }

            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveStatus = null;
            }, 2000);
        },

        toggleWorkingDay(day) {
            let days = this.fields.working_days || [];
            // Убедимся что это массив
            if (!Array.isArray(days)) {
                days = [];
            }
            const index = days.indexOf(day);
            if (index > -1) {
                days.splice(index, 1);
            } else {
                days.push(day);
            }
            days.sort((a, b) => a - b);
            this.fields.working_days = [...days];
            this.saveField('working_days', days);
        },

        async uploadLogo(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.saveStatus = 'saving';

            const formData = new FormData();
            formData.append('logo', file);
            formData.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');

            try {
                const response = await fetch('<?= $uploadLogoUrl ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.logoUrl = data.url;
                    this.saveStatus = 'success';
                } else {
                    this.saveStatus = 'error';
                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').error(data.message || 'Ошибка загрузки');
                    } else {
                        alert(data.message);
                    }
                }
            } catch (error) {
                console.error('Error uploading logo:', error);
                this.saveStatus = 'error';
                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    Alpine.store('toast').error('Ошибка загрузки');
                } else {
                    alert('Ошибка загрузки');
                }
            }

            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveStatus = null;
            }, 2000);

            // Очищаем input
            event.target.value = '';
        },

        async deleteLogo() {
            if (!confirm('Удалить логотип?')) return;

            this.saveStatus = 'saving';

            try {
                const response = await fetch('<?= $deleteLogoUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: '<?= Yii::$app->request->csrfParam ?>=<?= Yii::$app->request->csrfToken ?>'
                });
                const data = await response.json();

                if (data.success) {
                    this.logoUrl = null;
                    this.saveStatus = 'success';
                } else {
                    this.saveStatus = 'error';
                }
            } catch (error) {
                console.error('Error deleting logo:', error);
                this.saveStatus = 'error';
            }

            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveStatus = null;
            }, 2000);
        }
    };
}
</script>
