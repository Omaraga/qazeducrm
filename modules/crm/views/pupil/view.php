<?php

use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\PupilTabs;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

$canDelete = RoleChecker::canDeletePupils();
$canViewBalance = RoleChecker::canViewPupilBalance();

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
            <?= Html::a(Icon::show('book', 'sm') . ' Добавить обучение',
                OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]), [
                'class' => 'btn btn-success',
                'title' => 'Быстрое добавление нового обучения',
            ]) ?>
            <?= Html::a(Icon::show('edit', 'sm') . ' Редактировать',
                OrganizationUrl::to(['pupil/update', 'id' => $model->id]), [
                'class' => 'btn btn-primary',
            ]) ?>
            <?php if ($canDelete): ?>
                <?= Html::a(Icon::show('trash', 'sm') . ' Удалить',
                    OrganizationUrl::to(['pupil/delete', 'id' => $model->id]), [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы действительно хотите удалить ученика?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Balance -->
    <?php if ($canViewBalance): ?>
        <?= $this->render('balance', ['model' => $model]) ?>
    <?php endif; ?>

    <!-- Tabs -->
    <?= PupilTabs::widget(['model' => $model, 'activeTab' => 'view']) ?>

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
                        <dd class="text-sm text-gray-900 col-span-2"><?= $model->birth_date ? Yii::$app->formatter->asDate($model->birth_date, 'php:d.m.Y') : '—' ?></dd>
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
