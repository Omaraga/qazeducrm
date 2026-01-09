<?php

use app\helpers\OrganizationUrl;
use app\models\SmsTemplate;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $type */
/** @var bool $smsConfigured */

$smsConfigured = $smsConfigured ?? false;

// Если SMS не настроен, всегда показываем только WhatsApp
if (!$smsConfigured) {
    $this->title = 'Шаблоны WhatsApp';
} else {
    $this->title = $type === 'whatsapp' ? 'Шаблоны WhatsApp' : ($type === 'sms' ? 'Шаблоны SMS' : 'Шаблоны сообщений');
}
$this->params['breadcrumbs'][] = ['label' => 'Рассылка', 'url' => ['automations']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Библиотека текстов для рассылок</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['sms/automations']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Авторассылки
            </a>
            <a href="<?= OrganizationUrl::to(['sms/create-defaults']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Создать стандартные
            </a>
            <a href="<?= OrganizationUrl::to(['sms/create-template']) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Добавить
            </a>
        </div>
    </div>

    <!-- Type Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-4">
            <?php if ($smsConfigured): ?>
            <a href="<?= OrganizationUrl::to(['sms/templates']) ?>"
               class="py-2 px-1 border-b-2 text-sm font-medium <?= $type === 'all' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                Все
            </a>
            <a href="<?= OrganizationUrl::to(['sms/templates', 'type' => 'sms']) ?>"
               class="py-2 px-1 border-b-2 text-sm font-medium flex items-center gap-1 <?= $type === 'sms' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                SMS
            </a>
            <?php endif; ?>
            <a href="<?= OrganizationUrl::to(['sms/templates', 'type' => 'whatsapp']) ?>"
               class="py-2 px-1 border-b-2 text-sm font-medium flex items-center gap-1 <?= ($type === 'whatsapp' || !$smsConfigured) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp
            </a>
        </nav>
    </div>

    <!-- Templates Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текст сообщения</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($model->type === 'whatsapp'): ?>
                                <span class="inline-flex items-center gap-1 text-sm text-green-600">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    <?= $model->getTypeLabel() ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-sm text-primary-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                    <?= $model->getTypeLabel() ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= Html::encode($model->name) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php
                            $text = Html::encode($model->content);
                            if (mb_strlen($text) > 120) {
                                $text = mb_substr($text, 0, 120) . '...';
                            }
                            echo nl2br($text);
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= OrganizationUrl::to(['sms/update-template', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary" title="Редактировать">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="<?= OrganizationUrl::to(['sms/delete-template', 'id' => $model->id]) ?>"
                                   class="btn btn-sm btn-danger"
                                   title="Удалить"
                                   data-confirm="Удалить этот шаблон?"
                                   data-method="post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2">Шаблоны не найдены</p>
                            <div class="flex justify-center gap-2 mt-4">
                                <a href="<?= OrganizationUrl::to(['sms/create-defaults']) ?>" class="btn btn-secondary">
                                    Создать стандартные
                                </a>
                                <a href="<?= OrganizationUrl::to(['sms/create-template']) ?>" class="btn btn-primary">
                                    Добавить шаблон
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Placeholders Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Доступные плейсхолдеры</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-gray-500 mb-4">Используйте эти переменные в тексте шаблона - они будут заменены реальными данными при отправке:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach (SmsTemplate::getPlaceholders() as $placeholder => $description): ?>
                    <div class="flex items-start gap-2">
                        <code class="text-primary-600 bg-primary-50 px-2 py-0.5 rounded text-sm"><?= $placeholder ?></code>
                        <span class="text-sm text-gray-600">— <?= $description ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
