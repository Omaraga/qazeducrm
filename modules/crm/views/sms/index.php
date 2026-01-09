<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'История рассылки';
$this->params['breadcrumbs'][] = ['label' => 'Рассылка', 'url' => ['automations']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Лог отправленных SMS сообщений</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['sms/automations']) ?>" class="btn btn-secondary">
                <?= Icon::widget(['name' => 'arrow-left', 'class' => 'w-4 h-4']) ?>
                Авторассылки
            </a>
        </div>
    </div>

    <!-- SMS Log Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">История отправок</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Телефон</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сообщение</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Ошибка</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            <?= Html::encode($model->phone) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php
                            $text = Html::encode($model->message);
                            if (mb_strlen($text) > 80) {
                                $text = mb_substr($text, 0, 80) . '...';
                            }
                            echo $text;
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="<?= $model->getStatusBadgeClass() ?>">
                                <?= $model->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-danger-600">
                            <?= Html::encode($model->error_message) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <?= Icon::widget(['name' => 'chat-bubble-left-right', 'class' => 'w-12 h-12 mx-auto text-gray-300']) ?>
                            <p class="mt-2">SMS сообщения не найдены</p>
                            <p class="text-sm mt-1">Здесь будет история отправленных SMS</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
