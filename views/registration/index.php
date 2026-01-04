<?php

/** @var yii\web\View $this */
/** @var app\models\forms\OrganizationRegistrationForm $model */
/** @var array $plans */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Регистрация';
?>

<style>
.reg-container {
    max-width: 540px;
    margin: 0 auto;
}
.reg-steps {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 32px;
}
.reg-step {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--border);
    transition: all var(--transition);
}
.reg-step.active {
    background: var(--primary);
    width: 32px;
    border-radius: var(--radius-sm);
}
.reg-step.done {
    background: var(--success);
}
.step-title {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.step-heading {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 24px;
}
.form-group label {
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.form-control {
    padding: 10px 14px;
    border-radius: var(--radius);
    border: 1px solid var(--border-dark);
}
.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(254, 141, 0, 0.15);
}
.hint-block {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: 4px;
}
.btn-next, .btn-submit {
    padding: 12px 24px;
    font-weight: 500;
    border-radius: var(--radius);
}
.btn-back {
    padding: 12px 24px;
    font-weight: 500;
    border-radius: var(--radius);
    background: var(--bg-subtle);
    border: none;
    color: var(--text-secondary);
}
.btn-back:hover {
    background: var(--border);
    color: var(--text-primary);
}
.plan-option {
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
    cursor: pointer;
    transition: all var(--transition);
    margin-bottom: 12px;
    background: var(--bg-white);
}
.plan-option:hover {
    border-color: var(--primary);
    background: var(--primary-lighter);
}
.plan-option.selected {
    border-color: var(--primary);
    background: var(--primary-light);
}
.plan-option input[type="radio"] {
    display: none;
}
.plan-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--text-primary);
}
.plan-price {
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--primary);
}
.plan-trial {
    font-size: 0.875rem;
    color: var(--success);
}
.plan-limits {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 8px;
}
.terms-check {
    font-size: 0.9rem;
}
.terms-check a {
    color: var(--primary);
}
.terms-check a:hover {
    color: var(--primary-hover);
}
.field-error .form-control {
    border-color: var(--error);
}
.field-error .invalid-feedback {
    display: block;
    color: var(--error);
    font-size: 0.8rem;
    margin-top: 4px;
}
</style>

<div class="reg-container">
    <div class="guest-card p-4 p-md-5">
        <div class="reg-steps">
            <div class="reg-step active" data-step="1"></div>
            <div class="reg-step" data-step="2"></div>
            <div class="reg-step" data-step="3"></div>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'reg-form',
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
        ]); ?>

        <?php if ($model->hasErrors()): ?>
        <div class="alert alert-danger mb-4">
            <strong>Ошибка регистрации:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($model->getErrors() as $attribute => $errors): ?>
                    <?php foreach ($errors as $error): ?>
                        <li><?= Html::encode($error) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Шаг 1: Организация -->
        <div class="step-content" data-step="1">
            <div class="step-title">Шаг 1 из 3</div>
            <div class="step-heading">Данные организации</div>

            <?= $form->field($model, 'org_name')->textInput([
                'placeholder' => 'Введите название',
                'autofocus' => true,
            ])->label('Название учебного центра *') ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'org_email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'info@example.kz',
                    ])->label('Email организации *') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'org_phone')->textInput([
                        'placeholder' => '+7 777 123 4567',
                    ])->label('Телефон *') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'org_bin')->textInput([
                        'placeholder' => '123456789012',
                        'maxlength' => 12,
                    ])->label('БИН')->hint('Необязательно') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'org_address')->textInput([
                        'placeholder' => 'г. Алматы',
                    ])->label('Город/Адрес')->hint('Необязательно') ?>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-primary btn-next" onclick="goToStep(2)">
                    Далее
                </button>
            </div>
        </div>

        <!-- Шаг 2: Администратор -->
        <div class="step-content" data-step="2" style="display:none;">
            <div class="step-title">Шаг 2 из 3</div>
            <div class="step-heading">Данные администратора</div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_last_name')->textInput([
                        'placeholder' => 'Иванов',
                    ])->label('Фамилия *') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_first_name')->textInput([
                        'placeholder' => 'Иван',
                    ])->label('Имя *') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'admin@example.kz',
                    ])->label('Email для входа *') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_phone')->textInput([
                        'placeholder' => '+7 777 123 4567',
                    ])->label('Телефон')->hint('Необязательно') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_password')->passwordInput([
                        'placeholder' => 'Минимум 8 символов',
                    ])->label('Пароль *') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'admin_password_repeat')->passwordInput([
                        'placeholder' => 'Повторите пароль',
                    ])->label('Подтверждение *') ?>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-back" onclick="goToStep(1)">
                    Назад
                </button>
                <button type="button" class="btn btn-primary btn-next" onclick="goToStep(3)">
                    Далее
                </button>
            </div>
        </div>

        <!-- Шаг 3: Тариф -->
        <div class="step-content" data-step="3" style="display:none;">
            <div class="step-title">Шаг 3 из 3</div>
            <div class="step-heading">Выберите тариф</div>

            <div class="plans-list">
                <?php foreach ($plans as $planId => $plan): ?>
                <label class="plan-option <?= $model->plan_id == $planId ? 'selected' : '' ?>" data-plan="<?= $planId ?>">
                    <input type="radio" name="OrganizationRegistrationForm[plan_id]" value="<?= $planId ?>" <?= $model->plan_id == $planId ? 'checked' : '' ?>>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="plan-name"><?= Html::encode($plan['label']) ?></div>
                            <div class="plan-trial"><?= $plan['trial_days'] ?> дней бесплатно</div>
                            <div class="plan-limits">
                                <?= $plan['limits']['pupils'] ?> учеников &bull;
                                <?= $plan['limits']['teachers'] ?> учителей &bull;
                                <?= $plan['limits']['groups'] ?> групп
                            </div>
                        </div>
                        <div class="plan-price"><?= $plan['price'] ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="terms-check mt-4">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="agree-terms" name="OrganizationRegistrationForm[agree_terms]" value="1">
                    <label class="custom-control-label" for="agree-terms">
                        Я принимаю <a href="#" target="_blank">условия использования</a>
                    </label>
                </div>
                <div class="invalid-feedback" id="terms-error" style="display:none;">
                    Необходимо принять условия использования
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-back" onclick="goToStep(2)">
                    Назад
                </button>
                <?= Html::submitButton('Зарегистрироваться', [
                    'class' => 'btn btn-primary btn-submit',
                    'id' => 'submit-btn',
                ]) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <p class="text-center mt-4 text-muted">
        Уже есть аккаунт? <a href="<?= \yii\helpers\Url::to(['/login']) ?>">Войти</a>
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentStep = 1;

    // Выбор тарифа
    document.querySelectorAll('.plan-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.plan-option').forEach(function(p) {
                p.classList.remove('selected');
            });
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Автовыбор первого тарифа
    var firstPlan = document.querySelector('.plan-option');
    if (firstPlan && !document.querySelector('.plan-option.selected')) {
        firstPlan.click();
    }

    // Валидация при отправке
    document.getElementById('reg-form').addEventListener('submit', function(e) {
        var termsCheckbox = document.getElementById('agree-terms');
        var termsError = document.getElementById('terms-error');
        var submitBtn = document.getElementById('submit-btn');

        if (!termsCheckbox.checked) {
            e.preventDefault();
            termsError.style.display = 'block';
            return false;
        }
        termsError.style.display = 'none';

        // Показать индикатор загрузки
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Регистрация...';
    });

    document.getElementById('agree-terms').addEventListener('change', function() {
        document.getElementById('terms-error').style.display = 'none';
    });
});

function goToStep(step) {
    var currentStepEl = document.querySelector('.step-content[style=""]') || document.querySelector('.step-content:not([style*="none"])');
    var currentStep = currentStepEl ? parseInt(currentStepEl.dataset.step) : 1;

    // Валидация текущего шага перед переходом вперёд
    if (step > currentStep) {
        var errors = validateStep(currentStep);
        if (errors.length > 0) {
            return;
        }
    }

    // Скрыть все шаги
    document.querySelectorAll('.step-content').forEach(function(el) {
        el.style.display = 'none';
    });

    // Показать нужный шаг
    document.querySelector('.step-content[data-step="' + step + '"]').style.display = 'block';

    // Обновить индикаторы
    document.querySelectorAll('.reg-step').forEach(function(el) {
        var s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s < step) {
            el.classList.add('done');
        } else if (s === step) {
            el.classList.add('active');
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
    var group = input.closest('.form-group');
    group.classList.add('field-error');
    var feedback = group.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
    feedback.style.display = 'block';
}

function clearErrors() {
    document.querySelectorAll('.field-error').forEach(function(el) {
        el.classList.remove('field-error');
    });
    document.querySelectorAll('.invalid-feedback').forEach(function(el) {
        el.style.display = 'none';
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>
