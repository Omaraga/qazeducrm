<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\forms\EmployeeForm;
use app\models\User;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\forms\EmployeeForm $model */

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

    <!-- Role Selection -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Должность') ?></h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach (EmployeeForm::getRolesList() as $roleValue => $roleLabel): ?>
                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none <?= $model->role === $roleValue ? 'border-primary-500 ring-2 ring-primary-500' : 'border-gray-300' ?>">
                    <input type="radio"
                           name="EmployeeForm[role]"
                           value="<?= $roleValue ?>"
                           class="sr-only"
                           <?= $model->role === $roleValue ? 'checked' : '' ?>
                           onchange="this.closest('form').querySelectorAll('label.relative').forEach(l => l.classList.remove('border-primary-500', 'ring-2', 'ring-primary-500')); this.closest('label').classList.add('border-primary-500', 'ring-2', 'ring-primary-500');">
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-gray-900"><?= $roleLabel ?></span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                <?php if ($roleValue === 'teacher'): ?>
                                    Проводит занятия и отмечает посещаемость
                                <?php elseif ($roleValue === 'admin'): ?>
                                    Управляет учениками, группами и платежами
                                <?php elseif ($roleValue === 'director'): ?>
                                    Полный доступ к управлению филиалом
                                <?php endif; ?>
                            </span>
                        </span>
                    </span>
                    <svg class="h-5 w-5 text-primary-600 <?= $model->role === $roleValue ? '' : 'hidden' ?>" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.46L7.86 10.23a.75.75 0 10-1.22.875l2.02 2.82a.75.75 0 001.22.004l3.977-5.98z" clip-rule="evenodd" />
                    </svg>
                </label>
                <?php endforeach; ?>
            </div>
            <?php if ($model->hasErrors('role')): ?>
                <p class="mt-2 text-sm text-danger-600"><?= $model->getFirstError('role') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Basic Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label" for="employeeform-username">Логин <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'username', [
                        'class' => 'form-input',
                        'id' => 'employeeform-username',
                        'placeholder' => 'Введите логин'
                    ]) ?>
                    <?php if ($model->hasErrors('username')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('username') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-iin">ИИН</label>
                    <?= Html::activeTextInput($model, 'iin', [
                        'class' => 'form-input',
                        'id' => 'employeeform-iin',
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
                    <label class="form-label" for="employeeform-last_name">Фамилия <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'last_name', [
                        'class' => 'form-input',
                        'id' => 'employeeform-last_name'
                    ]) ?>
                    <?php if ($model->hasErrors('last_name')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('last_name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-first_name">Имя <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'first_name', [
                        'class' => 'form-input',
                        'id' => 'employeeform-first_name'
                    ]) ?>
                    <?php if ($model->hasErrors('first_name')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('first_name') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-middle_name">Отчество</label>
                    <?= Html::activeTextInput($model, 'middle_name', [
                        'class' => 'form-input',
                        'id' => 'employeeform-middle_name'
                    ]) ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-sex">Пол</label>
                    <?= Html::activeDropDownList($model, 'sex', Lists::getGenders(), [
                        'class' => 'form-select',
                        'id' => 'employeeform-sex',
                        'prompt' => 'Выберите пол'
                    ]) ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-birth_date">Дата рождения</label>
                    <input type="date" name="EmployeeForm[birth_date]" id="employeeform-birth_date" class="form-input" value="<?= $birthDate ?>" autocomplete="off">
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
                    <label class="form-label" for="employeeform-email">Email <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'email', [
                        'class' => 'form-input',
                        'id' => 'employeeform-email',
                        'type' => 'email',
                        'placeholder' => 'example@mail.com'
                    ]) ?>
                    <?php if ($model->hasErrors('email')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('email') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-phone">Телефон <span class="text-danger-500">*</span></label>
                    <?= Html::activeTextInput($model, 'phone', [
                        'class' => 'form-input',
                        'id' => 'employeeform-phone',
                        'type' => 'tel',
                        'placeholder' => '+7 (700) 123-45-67',
                        'x-mask-phone' => true
                    ]) ?>
                    <?php if ($model->hasErrors('phone')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('phone') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="employeeform-home_phone">Домашний телефон</label>
                    <?= Html::activeTextInput($model, 'home_phone', [
                        'class' => 'form-input',
                        'id' => 'employeeform-home_phone',
                        'type' => 'tel',
                        'placeholder' => '+7 (727) 234-56-78',
                        'x-mask-phone' => true
                    ]) ?>
                </div>
            </div>
            <div class="mt-4">
                <label class="form-label" for="employeeform-address">Адрес</label>
                <?= Html::activeTextInput($model, 'address', [
                    'class' => 'form-input',
                    'id' => 'employeeform-address',
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
                    <label class="form-label" for="employeeform-status">Статус</label>
                    <?= Html::activeDropDownList($model, 'status', User::getStatusList(), [
                        'class' => 'form-select',
                        'id' => 'employeeform-status'
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
