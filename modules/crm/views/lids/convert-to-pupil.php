<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lids $lid */

$this->title = 'Создание ученика из лида';
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = ['label' => $lid->fio ?: 'Лид #' . $lid->id, 'url' => ['view', 'id' => $lid->id]];
$this->params['breadcrumbs'][] = 'Создание ученика';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-success-100 flex items-center justify-center text-success-600">
            <?= Icon::show('user-plus', 'lg') ?>
        </div>
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-500 mt-2">Лид будет конвертирован в ученика</p>
    </div>

    <!-- Lead Data Preview -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Данные лида</h3>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ФИО ребёнка</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-medium"><?= Html::encode($lid->fio ?: '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Телефон ребёнка</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lid->phone ?: '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">ФИО родителя</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lid->parent_fio ?: '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Телефон родителя</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lid->parent_phone ?: '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Школа</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($lid->school ?: '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Класс</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php if ($lid->class_id): ?>
                            <?= \app\helpers\Lists::getGrades()[$lid->class_id] ?? $lid->class_id ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- What will be created -->
    <div class="card border-success-200 bg-success-50">
        <div class="card-body">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <?= Icon::show('information-circle', 'lg', 'text-success-600') ?>
                </div>
                <div>
                    <h4 class="font-medium text-success-800">Что будет создано:</h4>
                    <ul class="mt-2 text-sm text-success-700 space-y-1">
                        <li class="flex items-center gap-2">
                            <?= Icon::show('check', 'sm') ?>
                            Карточка ученика с данными из лида
                        </li>
                        <li class="flex items-center gap-2">
                            <?= Icon::show('check', 'sm') ?>
                            Данные родителя будут перенесены
                        </li>
                        <li class="flex items-center gap-2">
                            <?= Icon::show('check', 'sm') ?>
                            Лид будет помечен как конвертированный
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning about required fields -->
    <div class="card border-warning-200 bg-warning-50">
        <div class="card-body">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <?= Icon::show('exclamation-triangle', 'lg', 'text-warning-600') ?>
                </div>
                <div>
                    <h4 class="font-medium text-warning-800">Обратите внимание:</h4>
                    <ul class="mt-2 text-sm text-warning-700 space-y-1">
                        <li>После создания необходимо будет заполнить:</li>
                        <li class="ml-4">• ИИН ученика</li>
                        <li class="ml-4">• Пол</li>
                        <li class="ml-4">• Дату рождения</li>
                        <li class="ml-4">• Другие обязательные поля</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-center gap-4">
        <a href="<?= OrganizationUrl::to(['lids/view', 'id' => $lid->id]) ?>" class="btn btn-secondary">
            <?= Icon::show('arrow-left', 'sm') ?>
            Назад к лиду
        </a>
        <?= Html::beginForm(['lids/convert-to-pupil', 'id' => $lid->id], 'post') ?>
            <button type="submit" class="btn btn-success">
                <?= Icon::show('user-plus', 'sm') ?>
                Создать ученика
            </button>
        <?= Html::endForm() ?>
    </div>
</div>
