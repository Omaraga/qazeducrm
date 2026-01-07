<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\PupilTabs;
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
                <?= Icon::show('user', 'md', 'text-primary-600') ?>
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
    <?= PupilTabs::widget(['model' => $model, 'activeTab' => 'edu']) ?>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <?= Html::a(
            Icon::show('plus', 'sm') . ' ' . Yii::t('main', 'Добавить обучение'),
            OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]),
            ['class' => 'btn btn-primary', 'title' => 'Добавить новое обучение']
        ) ?>
    </div>

    <!-- Education Cards -->
    <div class="space-y-4">
        <?php foreach ($model->educations as $education): ?>
            <?= $this->render('_card', ['model' => $education]) ?>
        <?php endforeach; ?>

        <?php if (empty($model->educations)): ?>
        <div class="card">
            <div class="card-body py-12 text-center text-gray-500">
                <?= Icon::show('book', 'xl', 'mx-auto text-gray-400') ?>
                <p class="mt-2">У ученика нет записей об обучении</p>
                <?= Html::a(
                    Icon::show('plus', 'xs') . ' Добавить первое обучение',
                    OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]),
                    ['class' => 'btn btn-primary mt-4']
                ) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
