<?php

namespace app\widgets\tailwind;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveField as BaseActiveField;

/**
 * ActiveField Widget для Tailwind CSS
 */
class ActiveField extends BaseActiveField
{
    /**
     * @var string шаблон поля
     */
    public $template = "{label}\n{input}\n{hint}\n{error}";

    /**
     * @var array опции для label
     */
    public $labelOptions = ['class' => 'form-label'];

    /**
     * @var array опции для input
     */
    public $inputOptions = ['class' => 'form-input'];

    /**
     * @var array опции для hint
     */
    public $hintOptions = ['class' => 'form-hint'];

    /**
     * @var array опции для error
     */
    public $errorOptions = ['class' => 'form-error'];

    /**
     * @var array опции контейнера
     */
    public $options = ['class' => 'form-group'];

    /**
     * {@inheritdoc}
     */
    public function textInput($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        return parent::textInput($options);
    }

    /**
     * {@inheritdoc}
     */
    public function passwordInput($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        return parent::passwordInput($options);
    }

    /**
     * {@inheritdoc}
     */
    public function textarea($options = [])
    {
        $options = array_merge(['class' => 'form-textarea'], $options);
        return parent::textarea($options);
    }

    /**
     * {@inheritdoc}
     */
    public function dropDownList($items, $options = [])
    {
        $options = array_merge(['class' => 'form-select'], $options);
        return parent::dropDownList($items, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        $options = array_merge(['class' => 'form-checkbox'], $options);

        if ($enclosedByLabel) {
            $this->template = "<div class=\"flex items-center gap-2\">\n{input}\n{label}\n</div>\n{hint}\n{error}";
            $this->labelOptions = ['class' => 'text-sm text-gray-700 cursor-pointer'];
        }

        return parent::checkbox($options, false);
    }

    /**
     * {@inheritdoc}
     */
    public function radioList($items, $options = [])
    {
        $options = ArrayHelper::merge([
            'item' => function ($index, $label, $name, $checked, $value) {
                $id = Html::getInputId($this->model, $this->attribute) . '-' . $index;
                $radio = Html::radio($name, $checked, [
                    'value' => $value,
                    'id' => $id,
                    'class' => 'form-checkbox',
                ]);
                $labelTag = Html::label($label, $id, ['class' => 'text-sm text-gray-700 cursor-pointer']);
                return "<div class=\"flex items-center gap-2\">{$radio}{$labelTag}</div>";
            },
            'class' => 'space-y-2',
        ], $options);

        return parent::radioList($items, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function checkboxList($items, $options = [])
    {
        $options = ArrayHelper::merge([
            'item' => function ($index, $label, $name, $checked, $value) {
                $id = Html::getInputId($this->model, $this->attribute) . '-' . $index;
                $checkbox = Html::checkbox($name, $checked, [
                    'value' => $value,
                    'id' => $id,
                    'class' => 'form-checkbox',
                ]);
                $labelTag = Html::label($label, $id, ['class' => 'text-sm text-gray-700 cursor-pointer']);
                return "<div class=\"flex items-center gap-2\">{$checkbox}{$labelTag}</div>";
            },
            'class' => 'space-y-2',
        ], $options);

        return parent::checkboxList($items, $options);
    }

    /**
     * Кастомное поле с иконкой
     */
    public function textInputWithIcon($icon, $options = [])
    {
        $options = array_merge($this->inputOptions, $options);

        $this->template = <<<HTML
{label}
<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        {$icon}
    </div>
    {input}
</div>
{hint}
{error}
HTML;

        $options['class'] = ($options['class'] ?? '') . ' pl-10';

        return $this->textInput($options);
    }

    /**
     * Поле для телефона
     */
    public function phoneInput($options = [])
    {
        $icon = '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>';

        $options = array_merge([
            'type' => 'tel',
            'placeholder' => '+7 (___) ___-__-__',
        ], $options);

        return $this->textInputWithIcon($icon, $options);
    }

    /**
     * Поле для email
     */
    public function emailInput($options = [])
    {
        $icon = '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';

        $options = array_merge([
            'type' => 'email',
            'placeholder' => 'email@example.com',
        ], $options);

        return $this->textInputWithIcon($icon, $options);
    }
}
