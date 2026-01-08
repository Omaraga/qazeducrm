<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Organizations $org */
/** @var array $providers */

$this->title = 'Настройки SMS';
$this->params['breadcrumbs'][] = ['label' => 'SMS уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Настройте SMS провайдера для отправки уведомлений</p>
        </div>
        <a href="<?= OrganizationUrl::to(['sms/index']) ?>" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Settings -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Настройки провайдера</h3>
                </div>
                <div class="card-body">
                    <form method="post" class="space-y-4">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SMS провайдер</label>
                            <select name="sms_provider" id="sms-provider" class="form-input">
                                <option value="">-- Выберите провайдера --</option>
                                <?php foreach ($providers as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= $org->sms_provider === $code ? 'selected' : '' ?>>
                                        <?= Html::encode($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Выберите провайдера для отправки SMS</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API ключ</label>
                            <input type="text" name="sms_api_key" class="form-input"
                                   value="<?= Html::encode($org->sms_api_key) ?>"
                                   placeholder="Введите API ключ от провайдера">
                            <p class="mt-1 text-sm text-gray-500">API ключ можно получить в личном кабинете провайдера</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Имя отправителя</label>
                            <input type="text" name="sms_sender" class="form-input"
                                   value="<?= Html::encode($org->sms_sender) ?>"
                                   placeholder="Например: MySchool" maxlength="11">
                            <p class="mt-1 text-sm text-gray-500">До 11 латинских символов. Должно быть зарегистрировано у провайдера</p>
                        </div>

                        <?php if ($org->sms_balance !== null): ?>
                            <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-lg border border-primary-200">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-primary-800">
                                    Баланс: <strong><?= number_format($org->sms_balance, 2, ',', ' ') ?> KZT</strong>
                                </span>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Сохранить настройки
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($org->sms_provider && $org->sms_api_key): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Тестовая отправка</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= OrganizationUrl::to(['sms/test-send']) ?>">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Номер телефона</label>
                                <input type="text" name="phone" class="form-input"
                                       placeholder="+77001234567" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Текст сообщения</label>
                                <input type="text" name="message" class="form-input"
                                       placeholder="Тестовое сообщение" required maxlength="160">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-secondary mt-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Отправить тестовое SMS
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Провайдеры SMS</h3>
                </div>
                <div class="card-body divide-y divide-gray-100">
                    <div class="pb-4">
                        <h4 class="font-medium text-gray-900">Mobizon</h4>
                        <p class="text-sm text-gray-500 mt-1">Международный провайдер SMS. Работает в Казахстане.</p>
                        <a href="https://mobizon.kz" target="_blank" class="btn btn-sm btn-secondary mt-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Перейти
                        </a>
                    </div>

                    <div class="py-4">
                        <h4 class="font-medium text-gray-900">SMS.kz</h4>
                        <p class="text-sm text-gray-500 mt-1">Казахстанский провайдер SMS рассылок.</p>
                        <a href="https://sms.kz" target="_blank" class="btn btn-sm btn-secondary mt-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Перейти
                        </a>
                    </div>

                    <div class="pt-4">
                        <h4 class="font-medium text-gray-900">Тестовый режим</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Для отладки. SMS записываются в лог, но не отправляются.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Автоматические уведомления</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-500 mb-3">
                        Для автоматической отправки SMS добавьте в cron:
                    </p>
                    <pre class="bg-gray-100 p-3 rounded-lg text-xs overflow-x-auto"><code># Напоминание о занятиях (18:00)
0 18 * * * php yii sms/lesson-reminder

# Задолженность (по понедельникам)
0 10 * * 1 php yii sms/payment-due

# День рождения (9:00)
0 9 * * * php yii sms/birthday</code></pre>
                    <p class="text-sm text-gray-500 mt-3">
                        Настройте шаблоны в разделе
                        <a href="<?= OrganizationUrl::to(['sms/templates']) ?>" class="text-primary-600 hover:text-primary-700">Шаблоны</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
