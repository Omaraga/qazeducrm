<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\FormValidation;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

// Convert birth_date for HTML5 date input
$birthDate = $model->birth_date ? date('Y-m-d', strtotime($model->birth_date)) : '';

// Правила валидации для Alpine.js
$validationRules = [
    'iin' => ['required' => true, 'iin' => true],
    'first_name' => ['required' => true, 'minLength' => 2],
    'last_name' => ['required' => true, 'minLength' => 2],
    'email' => ['email' => true],
    'phone' => ['phone' => true],
    'home_phone' => ['phone' => true],
    'parent_phone' => ['phone' => true],
];
?>

<div class="space-y-6">
    <?= $this->render('balance', ['model' => $model]) ?>

    <form method="post" class="space-y-6"
          x-data="formValidation(<?= \yii\helpers\Json::htmlEncode($validationRules) ?>)"
          @submit="handleSubmit($event)">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <!-- Основные данные -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label form-label-required" for="pupil-iin">ИИН</label>
                        <?= Html::activeTextInput($model, 'iin', [
                            'class' => 'form-input',
                            'id' => 'pupil-iin',
                            'maxlength' => 12,
                            'x-mask-iin' => true,
                            ':class' => 'inputClass("iin")',
                            '@blur' => 'validateField("iin", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('iin')">
                            <p class="form-error-message" x-text="getError('iin')"></p>
                        </template>
                        <?php if ($model->hasErrors('iin')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('iin') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label form-label-required" for="pupil-first_name">Имя</label>
                        <?= Html::activeTextInput($model, 'first_name', [
                            'class' => 'form-input',
                            'id' => 'pupil-first_name',
                            ':class' => 'inputClass("first_name")',
                            '@blur' => 'validateField("first_name", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('first_name')">
                            <p class="form-error-message" x-text="getError('first_name')"></p>
                        </template>
                        <?php if ($model->hasErrors('first_name')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('first_name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label form-label-required" for="pupil-last_name">Фамилия</label>
                        <?= Html::activeTextInput($model, 'last_name', [
                            'class' => 'form-input',
                            'id' => 'pupil-last_name',
                            ':class' => 'inputClass("last_name")',
                            '@blur' => 'validateField("last_name", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('last_name')">
                            <p class="form-error-message" x-text="getError('last_name')"></p>
                        </template>
                        <?php if ($model->hasErrors('last_name')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('last_name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-middle_name">Отчество</label>
                        <?= Html::activeTextInput($model, 'middle_name', ['class' => 'form-input', 'id' => 'pupil-middle_name']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-sex">Пол</label>
                        <?= Html::activeDropDownList($model, 'sex', \app\helpers\Lists::getGenders(), ['class' => 'form-select', 'id' => 'pupil-sex']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-birth_date">Дата рождения</label>
                        <input type="date" name="Pupil[birth_date]" value="<?= $birthDate ?>" class="form-input" id="pupil-birth_date">
                    </div>
                </div>
            </div>
        </div>

        <!-- Контактные данные -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Контактные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="pupil-email">Email</label>
                        <?= Html::activeTextInput($model, 'email', [
                            'class' => 'form-input',
                            'id' => 'pupil-email',
                            'type' => 'email',
                            ':class' => 'inputClass("email")',
                            '@blur' => 'validateField("email", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('email')">
                            <p class="form-error-message" x-text="getError('email')"></p>
                        </template>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-phone">Телефон</label>
                        <?= Html::activeTextInput($model, 'phone', [
                            'class' => 'form-input',
                            'id' => 'pupil-phone',
                            'placeholder' => '+7 (XXX) XXX-XX-XX',
                            'x-mask-phone' => true,
                            ':class' => 'inputClass("phone")',
                            '@blur' => 'validateField("phone", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('phone')">
                            <p class="form-error-message" x-text="getError('phone')"></p>
                        </template>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-home_phone">Домашний телефон</label>
                        <?= Html::activeTextInput($model, 'home_phone', [
                            'class' => 'form-input',
                            'id' => 'pupil-home_phone',
                            'placeholder' => '+7 (XXX) XXX-XX-XX',
                            'x-mask-phone' => true,
                            ':class' => 'inputClass("home_phone")',
                            '@blur' => 'validateField("home_phone", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('home_phone')">
                            <p class="form-error-message" x-text="getError('home_phone')"></p>
                        </template>
                    </div>
                    <div class="md:col-span-3">
                        <label class="form-label" for="pupil-address">Адрес</label>
                        <?= Html::activeTextInput($model, 'address', ['class' => 'form-input', 'id' => 'pupil-address']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основное место обучения -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основное место обучения') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="pupil-school_name">Название школы</label>
                        <?= Html::activeTextInput($model, 'school_name', ['class' => 'form-input', 'id' => 'pupil-school_name']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-class_id">Класс</label>
                        <?= Html::activeDropDownList($model, 'class_id', \app\helpers\Lists::getGrades(), ['class' => 'form-select', 'id' => 'pupil-class_id']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Сведения о родителях -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Сведения о родителях') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="pupil-parent_fio">ФИО родителя</label>
                        <?= Html::activeTextInput($model, 'parent_fio', ['class' => 'form-input', 'id' => 'pupil-parent_fio']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-parent_phone">Телефон родителя</label>
                        <?= Html::activeTextInput($model, 'parent_phone', [
                            'class' => 'form-input',
                            'id' => 'pupil-parent_phone',
                            'placeholder' => '+7 (XXX) XXX-XX-XX',
                            'x-mask-phone' => true,
                            ':class' => 'inputClass("parent_phone")',
                            '@blur' => 'validateField("parent_phone", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('parent_phone')">
                            <p class="form-error-message" x-text="getError('parent_phone')"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <template x-if="!isSubmitting">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="isSubmitting">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isSubmitting ? 'Сохранение...' : 'Сохранить'"></span>
            </button>
            <a href="<?= OrganizationUrl::to(['pupil/index']) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
