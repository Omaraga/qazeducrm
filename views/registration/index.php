<?php

/** @var yii\web\View $this */
/** @var app\models\forms\OrganizationRegistrationForm $model */
/** @var array $plans */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Регистрация';
?>

<div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 bg-gray-50">
    <div class="w-full max-w-xl">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10">
            <!-- Steps indicator -->
            <div class="flex justify-center gap-2 mb-8">
                <div class="reg-step w-8 h-2 rounded-full bg-orange-500 transition-all" data-step="1"></div>
                <div class="reg-step w-2 h-2 rounded-full bg-gray-200 transition-all" data-step="2"></div>
                <div class="reg-step w-2 h-2 rounded-full bg-gray-200 transition-all" data-step="3"></div>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'reg-form',
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
                'fieldConfig' => [
                    'template' => "<div class=\"mb-4\">{label}{input}{hint}{error}</div>",
                    'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-2'],
                    'inputOptions' => ['class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors outline-none'],
                    'errorOptions' => ['class' => 'mt-1 text-sm text-red-500'],
                    'hintOptions' => ['class' => 'mt-1 text-xs text-gray-400'],
                ],
            ]); ?>

            <?php if ($model->hasErrors()): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
                <div class="font-semibold mb-2">Ошибка регистрации:</div>
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($model->getErrors() as $attribute => $errors): ?>
                        <?php foreach ($errors as $error): ?>
                            <li><?= Html::encode($error) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Step 1: Organization -->
            <div class="step-content" data-step="1">
                <div class="text-center mb-6">
                    <div class="text-sm text-gray-400 mb-1">Шаг 1 из 3</div>
                    <h2 class="text-xl font-bold text-gray-900">Данные организации</h2>
                </div>

                <?= $form->field($model, 'org_name')->textInput([
                    'placeholder' => 'Введите название',
                    'autofocus' => true,
                ])->label('Название учебного центра *') ?>

                <div class="grid md:grid-cols-2 gap-4">
                    <?= $form->field($model, 'org_email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'info@example.kz',
                    ])->label('Email организации *') ?>

                    <?= $form->field($model, 'org_phone')->textInput([
                        'placeholder' => '+7 777 123 4567',
                    ])->label('Телефон *') ?>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <?= $form->field($model, 'org_bin')->textInput([
                        'placeholder' => '123456789012',
                        'maxlength' => 12,
                    ])->label('БИН')->hint('Необязательно') ?>

                    <?= $form->field($model, 'org_address')->textInput([
                        'placeholder' => 'г. Алматы',
                    ])->label('Город/Адрес')->hint('Необязательно') ?>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-all" onclick="goToStep(2)">
                        Далее
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Admin -->
            <div class="step-content hidden" data-step="2">
                <div class="text-center mb-6">
                    <div class="text-sm text-gray-400 mb-1">Шаг 2 из 3</div>
                    <h2 class="text-xl font-bold text-gray-900">Данные администратора</h2>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <?= $form->field($model, 'admin_last_name')->textInput([
                        'placeholder' => 'Иванов',
                    ])->label('Фамилия *') ?>

                    <?= $form->field($model, 'admin_first_name')->textInput([
                        'placeholder' => 'Иван',
                    ])->label('Имя *') ?>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <?= $form->field($model, 'admin_email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'admin@example.kz',
                    ])->label('Email для входа *') ?>

                    <?= $form->field($model, 'admin_phone')->textInput([
                        'placeholder' => '+7 777 123 4567',
                    ])->label('Телефон')->hint('Необязательно') ?>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <?= $form->field($model, 'admin_password')->passwordInput([
                        'placeholder' => 'Минимум 8 символов',
                    ])->label('Пароль *') ?>

                    <?= $form->field($model, 'admin_password_repeat')->passwordInput([
                        'placeholder' => 'Повторите пароль',
                    ])->label('Подтверждение *') ?>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Назад
                    </button>
                    <button type="button" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-all" onclick="goToStep(3)">
                        Далее
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Plan -->
            <div class="step-content hidden" data-step="3">
                <div class="text-center mb-6">
                    <div class="text-sm text-gray-400 mb-1">Шаг 3 из 3</div>
                    <h2 class="text-xl font-bold text-gray-900">Выберите тариф</h2>
                </div>

                <div class="space-y-3">
                    <?php foreach ($plans as $planId => $plan): ?>
                    <label class="plan-option block border-2 rounded-xl p-5 cursor-pointer transition-all <?= $model->plan_id == $planId ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300' ?>" data-plan="<?= $planId ?>">
                        <input type="radio" name="OrganizationRegistrationForm[plan_id]" value="<?= $planId ?>" class="hidden" <?= $model->plan_id == $planId ? 'checked' : '' ?>>
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-gray-900"><?= Html::encode($plan['label']) ?></div>
                                <div class="text-sm text-green-600 mt-1">
                                    <i class="fas fa-gift mr-1"></i>
                                    <?= $plan['trial_days'] ?> дней бесплатно
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    <?= $plan['limits']['pupils'] ?> учеников &bull;
                                    <?= $plan['limits']['teachers'] ?> учителей &bull;
                                    <?= $plan['limits']['groups'] ?> групп
                                </div>
                            </div>
                            <div class="text-xl font-bold text-orange-500"><?= $plan['price'] ?></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Terms -->
                <div class="mt-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" id="agree-terms" name="OrganizationRegistrationForm[agree_terms]" value="1" class="mt-1 w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                        <span class="text-sm text-gray-600">
                            Я принимаю <a href="#" class="text-orange-500 hover:text-orange-600 font-medium">условия использования</a>
                        </span>
                    </label>
                    <div id="terms-error" class="hidden mt-1 text-sm text-red-500">
                        Необходимо принять условия использования
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Назад
                    </button>
                    <?= Html::submitButton('<span>Зарегистрироваться</span>', [
                        'class' => 'px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-all shadow-lg shadow-orange-500/30',
                        'id' => 'submit-btn',
                    ]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <!-- Login link -->
        <p class="text-center mt-6 text-gray-600">
            Уже есть аккаунт?
            <a href="<?= Url::to(['/login']) ?>" class="text-orange-500 hover:text-orange-600 font-semibold transition-colors">Войти</a>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Plan selection
    document.querySelectorAll('.plan-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.plan-option').forEach(function(p) {
                p.classList.remove('border-orange-500', 'bg-orange-50');
                p.classList.add('border-gray-200');
            });
            this.classList.remove('border-gray-200');
            this.classList.add('border-orange-500', 'bg-orange-50');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Auto-select first plan
    var firstPlan = document.querySelector('.plan-option');
    if (firstPlan && !document.querySelector('.plan-option.border-orange-500')) {
        firstPlan.click();
    }

    // Form submit validation
    document.getElementById('reg-form').addEventListener('submit', function(e) {
        var termsCheckbox = document.getElementById('agree-terms');
        var termsError = document.getElementById('terms-error');
        var submitBtn = document.getElementById('submit-btn');

        if (!termsCheckbox.checked) {
            e.preventDefault();
            termsError.classList.remove('hidden');
            return false;
        }
        termsError.classList.add('hidden');

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Регистрация...';
    });

    document.getElementById('agree-terms').addEventListener('change', function() {
        document.getElementById('terms-error').classList.add('hidden');
    });
});

function goToStep(step) {
    var currentStepEl = document.querySelector('.step-content:not(.hidden)');
    var currentStep = currentStepEl ? parseInt(currentStepEl.dataset.step) : 1;

    // Validate before going forward
    if (step > currentStep) {
        var errors = validateStep(currentStep);
        if (errors.length > 0) {
            return;
        }
    }

    // Hide all steps
    document.querySelectorAll('.step-content').forEach(function(el) {
        el.classList.add('hidden');
    });

    // Show target step
    document.querySelector('.step-content[data-step="' + step + '"]').classList.remove('hidden');

    // Update indicators
    document.querySelectorAll('.reg-step').forEach(function(el) {
        var s = parseInt(el.dataset.step);
        el.classList.remove('w-8', 'bg-orange-500', 'bg-green-500');
        el.classList.add('w-2', 'bg-gray-200');
        if (s < step) {
            el.classList.remove('bg-gray-200');
            el.classList.add('bg-green-500');
        } else if (s === step) {
            el.classList.remove('w-2', 'bg-gray-200');
            el.classList.add('w-8', 'bg-orange-500');
        }
    });
}

function validateStep(step) {
    var errors = [];
    clearErrors();

    if (step === 1) {
        var orgName = document.querySelector('[name="OrganizationRegistrationForm[org_name]"]');
        var orgEmail = document.querySelector('[name="OrganizationRegistrationForm[org_email]"]');
        var orgPhone = document.querySelector('[name="OrganizationRegistrationForm[org_phone]"]');

        if (!orgName.value.trim()) {
            showError(orgName, 'Введите название организации');
            errors.push('org_name');
        }
        if (!orgEmail.value.trim()) {
            showError(orgEmail, 'Введите email');
            errors.push('org_email');
        } else if (!isValidEmail(orgEmail.value)) {
            showError(orgEmail, 'Введите корректный email');
            errors.push('org_email');
        }
        if (!orgPhone.value.trim()) {
            showError(orgPhone, 'Введите телефон');
            errors.push('org_phone');
        }
    }

    if (step === 2) {
        var lastName = document.querySelector('[name="OrganizationRegistrationForm[admin_last_name]"]');
        var firstName = document.querySelector('[name="OrganizationRegistrationForm[admin_first_name]"]');
        var email = document.querySelector('[name="OrganizationRegistrationForm[admin_email]"]');
        var password = document.querySelector('[name="OrganizationRegistrationForm[admin_password]"]');
        var passwordRepeat = document.querySelector('[name="OrganizationRegistrationForm[admin_password_repeat]"]');

        if (!lastName.value.trim()) {
            showError(lastName, 'Введите фамилию');
            errors.push('last_name');
        }
        if (!firstName.value.trim()) {
            showError(firstName, 'Введите имя');
            errors.push('first_name');
        }
        if (!email.value.trim()) {
            showError(email, 'Введите email');
            errors.push('email');
        } else if (!isValidEmail(email.value)) {
            showError(email, 'Введите корректный email');
            errors.push('email');
        }
        if (!password.value) {
            showError(password, 'Введите пароль');
            errors.push('password');
        } else if (password.value.length < 8) {
            showError(password, 'Пароль должен быть минимум 8 символов');
            errors.push('password');
        }
        if (password.value !== passwordRepeat.value) {
            showError(passwordRepeat, 'Пароли не совпадают');
            errors.push('password_repeat');
        }
    }

    return errors;
}

function showError(input, message) {
    input.classList.add('border-red-500');
    var wrapper = input.closest('.mb-4');
    var feedback = wrapper.querySelector('.text-red-500');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'mt-1 text-sm text-red-500';
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearErrors() {
    document.querySelectorAll('.border-red-500').forEach(function(el) {
        el.classList.remove('border-red-500');
    });
    document.querySelectorAll('.mb-4 .text-red-500').forEach(function(el) {
        el.textContent = '';
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>
