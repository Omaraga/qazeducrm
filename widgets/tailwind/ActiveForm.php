<?php

namespace app\widgets\tailwind;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm as BaseActiveForm;

/**
 * ActiveForm Widget для Tailwind CSS
 *
 * Использование:
 *   <?php $form = ActiveForm::begin(); ?>
 *       <?= $form->field($model, 'name') ?>
 *       <?= $form->field($model, 'email')->textInput(['type' => 'email']) ?>
 *   <?php ActiveForm::end(); ?>
 */
class ActiveForm extends BaseActiveForm
{
    /**
     * @var string CSS класс формы
     */
    public $options = ['class' => 'space-y-6'];

    /**
     * @var array конфигурация поля по умолчанию
     */
    public $fieldConfig = [
        'class' => ActiveField::class,
    ];

    /**
     * @var string шаблон ошибок
     */
    public $errorCssClass = 'has-error';

    /**
     * @var string шаблон успеха
     */
    public $successCssClass = 'has-success';
}
