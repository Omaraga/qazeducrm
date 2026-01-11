<?php

/** @var yii\web\View $this */
/** @var app\modules\cabinet\models\LoginForm $model */
/** @var app\models\Organizations $organization */

use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Личный кабинет');
?>

<div class="flex justify-center">
    <div class="w-full max-w-md space-y-4">
        <!-- Login Card -->
        <div class="card-glass-solid overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-[22px] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <?= Icon::show('device-phone-mobile', 'xl', 'text-white') ?>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= Yii::t('app', 'Личный кабинет') ?></h1>
                    <p class="text-gray-500 mt-2 text-sm"><?= Yii::t('app', 'Для родителей и учеников') ?></p>
                </div>

                <p class="text-center text-sm text-gray-500 mb-6 leading-relaxed">
                    <?= Yii::t('app', 'Введите номер телефона родителя, указанный при регистрации ученика') ?>
                </p>

                <!-- Form -->
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'fieldConfig' => [
                        'template' => "{label}\n{input}\n{error}",
                        'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 mb-2'],
                        'inputOptions' => ['class' => 'input-ios'],
                        'errorOptions' => ['class' => 'mt-2 text-sm text-red-600'],
                    ],
                ]); ?>

                <div class="mb-6">
                    <?= $form->field($model, 'phone')->textInput([
                        'class' => 'input-ios text-lg',
                        'placeholder' => '+7 (___) ___-__-__',
                        'autofocus' => true,
                        'inputmode' => 'tel',
                        'x-mask-phone' => true,
                        'maxlength' => true,
                    ]) ?>
                </div>

                <?= Html::hiddenInput('LoginForm[organization_id]', $model->organization_id) ?>

                <button type="submit" class="btn-ios-primary w-full text-base">
                    <?= Icon::show('paper-airplane', 'sm') ?>
                    <?= Yii::t('app', 'Получить код') ?>
                </button>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card-glass-solid overflow-hidden">
            <div class="p-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                    <?= Icon::show('information-circle', 'sm', 'text-indigo-500') ?>
                    <?= Yii::t('app', 'Как войти?') ?>
                </h3>
                <ol class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">1</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Введите номер телефона, указанный при регистрации ученика') ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">2</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Получите SMS-код на этот номер') ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">3</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Введите код для входа') ?></span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
