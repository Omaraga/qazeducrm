<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

// Convert birth_date for HTML5 date input
$birthDate = $model->birth_date ? date('Y-m-d', strtotime($model->birth_date)) : '';
?>

<div class="space-y-6">
    <?= $this->render('balance', ['model' => $model]) ?>

    <form method="post" class="space-y-6">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <!-- Основные данные -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="pupil-iin">ИИН</label>
                        <?= Html::activeTextInput($model, 'iin', ['class' => 'form-input', 'id' => 'pupil-iin', 'maxlength' => 12]) ?>
                        <?php if ($model->hasErrors('iin')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('iin') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-first_name">Имя <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'first_name', ['class' => 'form-input', 'id' => 'pupil-first_name']) ?>
                        <?php if ($model->hasErrors('first_name')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('first_name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-last_name">Фамилия <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'last_name', ['class' => 'form-input', 'id' => 'pupil-last_name']) ?>
                        <?php if ($model->hasErrors('last_name')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('last_name') ?></p>
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
                        <?= Html::activeTextInput($model, 'email', ['class' => 'form-input', 'id' => 'pupil-email', 'type' => 'email']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-phone">Телефон</label>
                        <?= Html::activeTextInput($model, 'phone', ['class' => 'form-input', 'id' => 'pupil-phone', 'placeholder' => '+7(XXX)XXXXXXX']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="pupil-home_phone">Домашний телефон</label>
                        <?= Html::activeTextInput($model, 'home_phone', ['class' => 'form-input', 'id' => 'pupil-home_phone', 'placeholder' => '+7(XXX)XXXXXXX']) ?>
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
                        <?= Html::activeTextInput($model, 'parent_phone', ['class' => 'form-input', 'id' => 'pupil-parent_phone', 'placeholder' => '+7(XXX)XXXXXXX']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
            </button>
            <a href="<?= OrganizationUrl::to(['pupil/index']) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
