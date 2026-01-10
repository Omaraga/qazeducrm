<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'WhatsApp не подключен';
$this->params['breadcrumbs'][] = ['label' => 'CRM', 'url' => ['/crm']];
$this->params['breadcrumbs'][] = ['label' => 'WhatsApp', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Чаты';
?>

<div class="flex items-center justify-center min-h-[400px]">
    <div class="text-center">
        <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">WhatsApp не подключен</h2>
        <p class="text-gray-500 mb-6">Подключите WhatsApp чтобы видеть чаты</p>
        <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-success btn-lg">
            Подключить WhatsApp
        </a>
    </div>
</div>
