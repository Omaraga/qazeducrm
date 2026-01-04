<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PayMethod $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Методы оплаты'), 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Информация о методе оплаты</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['pay-method/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <?= Yii::t('main', 'Редактировать') ?>
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> ' . Yii::t('main', 'Удалить'),
                OrganizationUrl::to(['pay-method/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('main', 'Вы действительно хотите удалить этот метод оплаты?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Основная информация</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->id ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Название</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium"><?= Html::encode($model->name) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">История</h3>
                </div>
                <div class="card-body text-sm text-gray-500 space-y-2">
                    <div>
                        <span class="font-medium">Создан:</span>
                        <?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') : '—' ?>
                    </div>
                    <div>
                        <span class="font-medium">Обновлён:</span>
                        <?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <a href="<?= OrganizationUrl::to(['pay-method/index']) ?>" class="btn btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к списку
            </a>
        </div>
    </div>
</div>
