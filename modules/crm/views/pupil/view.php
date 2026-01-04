<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

$this->title = $model->fio;
$this->params['breadcrumbs'][] = ['label' => 'Ученики', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Карточка ученика</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['pupil/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                OrganizationUrl::to(['pupil/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить ученика?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Balance -->
    <?= $this->render('balance', ['model' => $model]) ?>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                Основные данные
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/edu', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Обучение
            </a>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Оплата
            </a>
        </nav>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Personal Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Личные данные</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= $model->id ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">ИИН</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->iin) ?: '—' ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Фамилия</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->last_name) ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Имя</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->first_name) ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Отчество</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->middle_name) ?: '—' ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Пол</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->genderLabel) ?: '—' ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Дата рождения</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= $model->birth_date ? Yii::$app->formatter->asDate($model->birth_date, 'long') : '—' ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Контактные данные</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php if ($model->email): ?>
                                <a href="mailto:<?= Html::encode($model->email) ?>" class="text-primary-600 hover:underline"><?= Html::encode($model->email) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Телефон</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php if ($model->phone): ?>
                                <a href="tel:+<?= Html::encode($model->phone) ?>" class="text-primary-600 hover:underline">+<?= Html::encode($model->phone) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Домашний телефон</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php if ($model->home_phone): ?>
                                <a href="tel:+<?= Html::encode($model->home_phone) ?>" class="text-primary-600 hover:underline">+<?= Html::encode($model->home_phone) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Адрес</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->address) ?: '—' ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- School Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Основное место обучения</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Школа</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->school_name) ?: '—' ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Класс</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php if ($model->class_id): ?>
                                <span class="badge badge-secondary"><?= \app\helpers\Lists::getGrades()[$model->class_id] ?? $model->class_id ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Parent Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Сведения о родителях</h3>
            </div>
            <div class="card-body">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">ФИО родителя</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><?= Html::encode($model->parent_fio) ?: '—' ?></dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Телефон родителя</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <?php if ($model->parent_phone): ?>
                                <a href="tel:+<?= Html::encode($model->parent_phone) ?>" class="text-primary-600 hover:underline">+<?= Html::encode($model->parent_phone) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
