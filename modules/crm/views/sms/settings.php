<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Organizations $org */
/** @var array $providers */
/** @var bool $smsConfigured */
/** @var bool $whatsappConnected */

$this->title = 'Настройки рассылки';
$this->params['breadcrumbs'][] = ['label' => 'Рассылка', 'url' => ['automations']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Настройка SMS провайдера и подключений</p>
        </div>
        <a href="<?= OrganizationUrl::to(['sms/automations']) ?>" class="btn btn-secondary">
            <?= Icon::widget(['name' => 'arrow-left', 'class' => 'w-4 h-4']) ?>
            Авторассылки
        </a>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- WhatsApp Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </h3>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center <?= $whatsappConnected ? 'bg-green-100' : 'bg-gray-100' ?>">
                        <?php if ($whatsappConnected): ?>
                            <?= Icon::widget(['name' => 'check-circle', 'class' => 'w-8 h-8 text-green-500']) ?>
                        <?php else: ?>
                            <?= Icon::widget(['name' => 'x-circle', 'class' => 'w-8 h-8 text-gray-400']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">
                            <?= $whatsappConnected ? 'Подключен' : 'Не подключен' ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= $whatsappConnected
                                ? 'WhatsApp готов к отправке сообщений'
                                : 'Подключите WhatsApp для отправки сообщений' ?>
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="<?= OrganizationUrl::to(['whatsapp/index']) ?>" class="btn btn-secondary w-full">
                        <?= $whatsappConnected ? 'Управление WhatsApp' : 'Подключить WhatsApp' ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- SMS Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <?= Icon::widget(['name' => 'chat-bubble-left-right', 'class' => 'w-5 h-5 text-primary-500']) ?>
                    SMS
                </h3>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center <?= $smsConfigured ? 'bg-primary-100' : 'bg-gray-100' ?>">
                        <?php if ($smsConfigured): ?>
                            <?= Icon::widget(['name' => 'check-circle', 'class' => 'w-8 h-8 text-primary-500']) ?>
                        <?php else: ?>
                            <?= Icon::widget(['name' => 'x-circle', 'class' => 'w-8 h-8 text-gray-400']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">
                            <?= $smsConfigured ? 'Настроен' : 'Не настроен' ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= $smsConfigured
                                ? 'Провайдер: ' . ($org->sms_provider ?: '—')
                                : 'Настройте SMS провайдера ниже' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Provider Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Настройки SMS провайдера</h3>
        </div>
        <div class="card-body">
            <form method="post" class="space-y-4">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Провайдер</label>
                        <?= Html::dropDownList('sms_provider', $org->sms_provider, $providers, [
                            'class' => 'form-input',
                            'prompt' => '-- Выберите провайдера --'
                        ]) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API ключ</label>
                        <?= Html::textInput('sms_api_key', $org->sms_api_key, [
                            'class' => 'form-input',
                            'placeholder' => 'Введите API ключ'
                        ]) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Имя отправителя</label>
                        <?= Html::textInput('sms_sender', $org->sms_sender, [
                            'class' => 'form-input',
                            'placeholder' => 'INFO'
                        ]) ?>
                        <p class="mt-1 text-xs text-gray-500">Альфа-имя, зарегистрированное у провайдера</p>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <?= Icon::widget(['name' => 'check', 'class' => 'w-4 h-4']) ?>
                        Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($smsConfigured): ?>
    <!-- Test SMS -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Тестовая отправка SMS</h3>
        </div>
        <div class="card-body">
            <form method="post" action="<?= OrganizationUrl::to(['sms/test-send']) ?>" class="space-y-4">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер телефона</label>
                        <?= Html::textInput('phone', '', [
                            'class' => 'form-input',
                            'placeholder' => '+77001234567',
                            'required' => true
                        ]) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Сообщение</label>
                        <?= Html::textInput('message', 'Тестовое сообщение от ' . ($org->name ?? 'CRM'), [
                            'class' => 'form-input',
                            'required' => true
                        ]) ?>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="btn btn-secondary">
                        <?= Icon::widget(['name' => 'paper-airplane', 'class' => 'w-4 h-4']) ?>
                        Отправить тестовое SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Help -->
    <div class="card bg-gray-50">
        <div class="card-body">
            <h3 class="font-medium text-gray-900 mb-2">Как настроить SMS?</h3>
            <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                <li>Зарегистрируйтесь у SMS провайдера (например, smsc.kz, mobizon.kz)</li>
                <li>Получите API ключ в личном кабинете провайдера</li>
                <li>Зарегистрируйте альфа-имя (имя отправителя)</li>
                <li>Введите данные выше и сохраните настройки</li>
                <li>Отправьте тестовое SMS для проверки</li>
            </ol>
        </div>
    </div>
</div>
