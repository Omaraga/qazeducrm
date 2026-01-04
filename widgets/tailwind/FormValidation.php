<?php

namespace app\widgets\tailwind;

use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * FormValidation Widget
 *
 * Генерирует правила валидации для Alpine.js из Yii2 модели
 *
 * Использование:
 * ```php
 * <?= FormValidation::widget(['model' => $model]) ?>
 * ```
 *
 * В форме:
 * ```html
 * <form x-data="formValidation(<?= FormValidation::getRulesJson($model) ?>)"
 *       @submit="handleSubmit($event)">
 * ```
 */
class FormValidation extends Widget
{
    /**
     * @var Model модель для извлечения правил
     */
    public $model;

    /**
     * @var array дополнительные правила (перезаписывают автоматически сгенерированные)
     */
    public $rules = [];

    /**
     * @var array атрибуты для исключения
     */
    public $except = [];

    /**
     * @var array атрибуты для включения (если указано, только эти атрибуты будут обработаны)
     */
    public $only = [];

    /**
     * Генерирует JSON правил для использования в x-data
     */
    public static function getRulesJson(Model $model, array $options = []): string
    {
        $widget = new static(array_merge(['model' => $model], $options));
        return $widget->generateRulesJson();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->generateRulesJson();
    }

    /**
     * Генерирует JSON строку с правилами
     */
    public function generateRulesJson(): string
    {
        $rules = $this->generateRules();
        return Json::htmlEncode($rules);
    }

    /**
     * Генерирует массив правил из модели
     */
    public function generateRules(): array
    {
        if ($this->model === null) {
            return $this->rules;
        }

        $jsRules = [];

        foreach ($this->model->rules() as $rule) {
            $attributes = (array)$rule[0];
            $validator = $rule[1];
            $params = array_slice($rule, 2);

            foreach ($attributes as $attribute) {
                // Пропускаем исключенные атрибуты
                if (in_array($attribute, $this->except)) {
                    continue;
                }

                // Если указан only, проверяем вхождение
                if (!empty($this->only) && !in_array($attribute, $this->only)) {
                    continue;
                }

                // Инициализируем правила для атрибута
                if (!isset($jsRules[$attribute])) {
                    $jsRules[$attribute] = [];
                }

                // Конвертируем Yii2 валидатор в JS правило
                $jsRule = $this->convertValidator($validator, $params);
                if ($jsRule !== null) {
                    $jsRules[$attribute] = array_merge($jsRules[$attribute], $jsRule);
                }
            }
        }

        // Добавляем/перезаписываем пользовательские правила
        foreach ($this->rules as $attribute => $rules) {
            if (!isset($jsRules[$attribute])) {
                $jsRules[$attribute] = [];
            }
            $jsRules[$attribute] = array_merge($jsRules[$attribute], $rules);
        }

        return $jsRules;
    }

    /**
     * Конвертирует Yii2 валидатор в JS правило
     */
    protected function convertValidator(string $validator, array $params): ?array
    {
        // Проверяем, активно ли правило на сценарии
        if (isset($params['on'])) {
            $scenarios = (array)$params['on'];
            if (!in_array($this->model->scenario, $scenarios)) {
                return null;
            }
        }

        if (isset($params['except'])) {
            $exceptScenarios = (array)$params['except'];
            if (in_array($this->model->scenario, $exceptScenarios)) {
                return null;
            }
        }

        // Проверяем when условие (пропускаем, если есть - клиентская валидация не поддерживает)
        if (isset($params['when'])) {
            return null;
        }

        switch ($validator) {
            case 'required':
                return ['required' => true];

            case 'email':
                return ['email' => true];

            case 'string':
                $rules = [];
                if (isset($params['min'])) {
                    $rules['minLength'] = (int)$params['min'];
                }
                if (isset($params['max'])) {
                    $rules['maxLength'] = (int)$params['max'];
                }
                if (isset($params['length'])) {
                    $rules['minLength'] = (int)$params['length'];
                    $rules['maxLength'] = (int)$params['length'];
                }
                return empty($rules) ? null : $rules;

            case 'number':
            case 'double':
                $rules = ['number' => true];
                if (isset($params['min'])) {
                    $rules['min'] = $params['min'];
                }
                if (isset($params['max'])) {
                    $rules['max'] = $params['max'];
                }
                return $rules;

            case 'integer':
                $rules = ['integer' => true];
                if (isset($params['min'])) {
                    $rules['min'] = (int)$params['min'];
                }
                if (isset($params['max'])) {
                    $rules['max'] = (int)$params['max'];
                }
                return $rules;

            case 'date':
            case 'datetime':
                return ['date' => true];

            case 'match':
                if (isset($params['pattern'])) {
                    // Убираем delimiter'ы из regex
                    $pattern = $params['pattern'];
                    if (preg_match('/^\/(.+)\/[a-z]*$/i', $pattern, $matches)) {
                        $pattern = $matches[1];
                    }
                    return ['pattern' => $pattern];
                }
                return null;

            case 'compare':
                if (isset($params['compareAttribute'])) {
                    return ['match' => $params['compareAttribute']];
                }
                return null;

            // Кастомные валидаторы для QazEdu
            case 'phone':
                return ['phone' => true];

            case 'iin':
                return ['iin' => true];

            default:
                return null;
        }
    }

    /**
     * Возвращает массив лейблов атрибутов модели
     */
    public static function getLabelsJson(Model $model): string
    {
        return Json::htmlEncode($model->attributeLabels());
    }

    /**
     * Генерирует полный x-data для формы
     */
    public static function getFormData(Model $model, array $options = []): string
    {
        $rules = self::getRulesJson($model, $options);
        return "formValidation({$rules})";
    }
}
