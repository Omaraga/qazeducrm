<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = Yii::t('main', 'Посещаемость');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Управление посещаемостью занятий</p>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Отметка посещаемости</h3>
                    <p class="text-gray-600 mt-1">
                        Для отметки посещаемости перейдите в расписание и выберите нужное занятие.
                        В карточке занятия нажмите кнопку "Редактировать посещения".
                    </p>
                    <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-primary mt-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Перейти к расписанию
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Types Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Типы посещаемости</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 bg-success-50 rounded-lg border border-success-200">
                    <span class="badge badge-success">Посещение</span>
                    <p class="text-sm text-gray-700">Ученик был на уроке, преподаватель получит оплату за ученика</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-warning-50 rounded-lg border border-warning-200">
                    <span class="badge badge-warning whitespace-nowrap">Пропуск (с оплатой)</span>
                    <p class="text-sm text-gray-700">Ученика не было на уроке, преподаватель получит оплату. Используется на индивидуальных занятиях</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-danger-50 rounded-lg border border-danger-200">
                    <span class="badge badge-danger whitespace-nowrap">Пропуск (без оплаты)</span>
                    <p class="text-sm text-gray-700">Ученик не был на уроке, преподаватель не получит оплату. Используется на групповых занятиях</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="badge badge-secondary whitespace-nowrap">Уваж. причина</span>
                    <p class="text-sm text-gray-700">Пропуск по уважительной причине. Урок ученика переносится (оплата не сгорает)</p>
                </div>
            </div>
        </div>
    </div>
</div>
