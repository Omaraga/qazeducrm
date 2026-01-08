<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\User;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\forms\TeacherForm $model */

// Convert date format for HTML5 date input
$birthDate = '';
if ($model->birth_date) {
    $timestamp = strtotime(str_replace('.', '-', $model->birth_date));
    if ($timestamp) {
        $birthDate = date('Y-m-d', $timestamp);
    }
}
?>

<?php
$formAction = empty($model->id)
    ? OrganizationUrl::to(['user/create'])
    : OrganizationUrl::to(['user/update', 'id' => $model->id]);
?>
<form action="<?= $formAction ?>" method="post" class="space-y-6">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Basic Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label" for="teacherform-username">Логин <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'username', [
                        'class' => 'form-input',
                        'id' => 'teacherform-username',
                        'placeholder' => 'Введите логин'
                    ]) ?>
                    <?php if ($model->hasErrors('username')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('username') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-iin">ИИН</label>
                    <?= Html::activeTextInput($model, 'iin', [
                        'class' => 'form-input',
                        'id' => 'teacherform-iin',
                        'placeholder' => '000000000000',
                        'maxlength' => 12
                    ]) ?>
                    <?php if ($model->hasErrors('iin')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('iin') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="teacherform-last_name">Фамилия <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'last_name', [
                        'class' => 'form-input',
                        'id' => 'teacherform-last_name',
                        'placeholder' => 'Иванов'
                    ]) ?>
                    <?php if ($model->hasErrors('last_name')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('last_name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-first_name">Имя <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'first_name', [
                        'class' => 'form-input',
                        'id' => 'teacherform-first_name',
                        'placeholder' => 'Иван'
                    ]) ?>
                    <?php if ($model->hasErrors('first_name')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('first_name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-middle_name">Отчество</label>
                    <?= Html::activeTextInput($model, 'middle_name', [
                        'class' => 'form-input',
                        'id' => 'teacherform-middle_name',
                        'placeholder' => 'Иванович'
                    ]) ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-sex">Пол</label>
                    <?= Html::activeDropDownList($model, 'sex', Lists::getGenders(), [
                        'class' => 'form-select',
                        'id' => 'teacherform-sex',
                        'prompt' => 'Выберите пол'
                    ]) ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-birth_date">Дата рождения</label>
                    <input type="date" name="TeacherForm[birth_date]" id="teacherform-birth_date" class="form-input" value="<?= $birthDate ?>" autocomplete="off">
                    <?php if ($model->hasErrors('birth_date')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('birth_date') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Контактные данные') ?></h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="teacherform-email">Email</label>
                    <?= Html::activeTextInput($model, 'email', [
                        'class' => 'form-input',
                        'id' => 'teacherform-email',
                        'type' => 'email',
                        'placeholder' => 'example@mail.com'
                    ]) ?>
                    <?php if ($model->hasErrors('email')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('email') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-phone">Телефон</label>
                    <?= Html::activeTextInput($model, 'phone', [
                        'class' => 'form-input',
                        'id' => 'teacherform-phone',
                        'type' => 'tel',
                        'placeholder' => '77001234567'
                    ]) ?>
                    <?php if ($model->hasErrors('phone')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('phone') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="teacherform-home_phone">Домашний телефон</label>
                    <?= Html::activeTextInput($model, 'home_phone', [
                        'class' => 'form-input',
                        'id' => 'teacherform-home_phone',
                        'type' => 'tel',
                        'placeholder' => '77272345678'
                    ]) ?>
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label" for="teacherform-address">Адрес</label>
                <?= Html::activeTextInput($model, 'address', [
                    'class' => 'form-input',
                    'id' => 'teacherform-address',
                    'placeholder' => 'г. Алматы, ул. Примерная, д. 1'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Системные сведения') ?></h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label" for="teacherform-status">Статус</label>
                    <?= Html::activeDropDownList($model, 'status', User::getStatusList(), [
                        'class' => 'form-select',
                        'id' => 'teacherform-status'
                    ]) ?>
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
            <?= Yii::t('main', 'Сохранить') ?>
        </button>
        <a href="<?= OrganizationUrl::to(['user/index']) ?>" class="btn btn-secondary">Отмена</a>
    </div>
</form>
