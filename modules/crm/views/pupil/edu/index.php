<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Ученики', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Карточка ученика</p>
            </div>
        </div>
    </div>

    <!-- Balance -->
    <?= $this->render('../balance', ['model' => $model]) ?>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <?= Yii::t('main', 'Основные данные') ?>
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/edu', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                <?= Yii::t('main', 'Обучение') ?>
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <?= Yii::t('main', 'Оплата') ?>
            </a>
        </nav>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <a href="<?= OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]) ?>" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <?= Yii::t('main', 'Добавить обучение') ?>
        </a>
    </div>

    <!-- Education Cards -->
    <div class="space-y-4">
        <?php foreach ($model->educations as $education): ?>
            <?= $this->render('_card', ['model' => $education]) ?>
        <?php endforeach; ?>

        <?php if (empty($model->educations)): ?>
        <div class="card">
            <div class="card-body py-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="mt-2">У ученика нет записей об обучении</p>
                <a href="<?= OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]) ?>" class="btn btn-primary mt-4">
                    Добавить первое обучение
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
