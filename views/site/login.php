<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$this->title = 'Вход в систему';
?>

<style>
.login-container {
    max-width: 420px;
    margin: 0 auto;
}
.login-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    text-align: center;
}
.login-subtitle {
    color: var(--text-muted);
    text-align: center;
    margin-bottom: 2rem;
    font-size: 0.95rem;
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
.btn-login {
    width: 100%;
    padding: 12px;
    font-weight: 500;
    border-radius: var(--radius);
}
.remember-me {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.remember-me .custom-control-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}
.forgot-link {
    color: var(--primary);
    font-size: 0.9rem;
}
.forgot-link:hover {
    color: var(--primary-hover);
}
.divider {
    display: flex;
    align-items: center;
    margin: 1.5rem 0;
    color: var(--text-light);
    font-size: 0.85rem;
}
.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}
.divider span {
    padding: 0 1rem;
}
</style>

<div class="login-container">
    <div class="guest-card p-4 p-md-5">
        <h1 class="login-title">Добро пожаловать</h1>
        <p class="login-subtitle">Войдите в свой аккаунт</p>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'enableClientValidation' => true,
        ]); ?>

        <?= $form->field($model, 'username')->textInput([
            'autofocus' => true,
            'placeholder' => 'Введите email',
        ])->label('Email') ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'Введите пароль',
        ])->label('Пароль') ?>

        <div class="remember-me">
            <div class="custom-control custom-checkbox">
                <?= Html::activeCheckbox($model, 'rememberMe', [
                    'class' => 'custom-control-input',
                    'id' => 'remember-me',
                ]) ?>
                <label class="custom-control-label" for="remember-me">Запомнить меня</label>
            </div>
            <a href="#" class="forgot-link">Забыли пароль?</a>
        </div>

        <?= Html::submitButton('Войти', [
            'class' => 'btn btn-primary btn-login',
            'name' => 'login-button',
        ]) ?>

        <?php ActiveForm::end(); ?>

        <div class="divider"><span>или</span></div>

        <p class="text-center mb-0">
            <span class="text-muted">Нет аккаунта?</span>
            <a href="<?= Url::to(['/register']) ?>">Зарегистрироваться</a>
        </p>
    </div>
</div>
