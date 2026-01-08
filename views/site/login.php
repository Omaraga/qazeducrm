<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Вход в систему';
?>

<div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4 bg-gray-50">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-2xl text-orange-500"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Добро пожаловать</h1>
                <p class="text-gray-500">Войдите в свой аккаунт</p>
            </div>

            <!-- Form -->
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'enableClientValidation' => true,
                'fieldConfig' => [
                    'template' => "<div class=\"mb-5\">{label}{input}{error}</div>",
                    'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-2'],
                    'inputOptions' => ['class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors outline-none'],
                    'errorOptions' => ['class' => 'mt-1 text-sm text-red-500'],
                ],
            ]); ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Введите email',
            ])->label('Email') ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Введите пароль',
            ])->label('Пароль') ?>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <?= Html::activeCheckbox($model, 'rememberMe', [
                        'class' => 'w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500',
                        'label' => false,
                    ]) ?>
                    <span class="text-sm text-gray-600">Запомнить меня</span>
                </label>
                <a href="#" class="text-sm text-orange-500 hover:text-orange-600 font-medium transition-colors">
                    Забыли пароль?
                </a>
            </div>

            <!-- Submit -->
            <?= Html::submitButton('Войти', [
                'class' => 'w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition-all shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50',
                'name' => 'login-button',
            ]) ?>

            <?php ActiveForm::end(); ?>

            <!-- Divider -->
            <div class="flex items-center gap-4 my-6">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-sm text-gray-400">или</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- Register link -->
            <p class="text-center text-gray-600">
                Нет аккаунта?
                <a href="<?= Url::to(['/register']) ?>" class="text-orange-500 hover:text-orange-600 font-semibold transition-colors">
                    Зарегистрироваться
                </a>
            </p>
        </div>

        <!-- Back to home -->
        <p class="text-center mt-6">
            <a href="<?= Url::to(['/']) ?>" class="text-gray-500 hover:text-gray-700 text-sm transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Вернуться на главную
            </a>
        </p>
    </div>
</div>
