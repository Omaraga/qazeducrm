<?php

namespace app\components;

use Yii;
use yii\validators\Validator;

/**
 * PhoneNumberValidator - валидатор телефонных номеров
 *
 * Поддерживает форматы:
 * - KZ: Казахстан (+7 xxx xxx xx xx) - 11 цифр
 * - RU: Россия (+7 xxx xxx xx xx) - 11 цифр
 * - UZ: Узбекистан (+998 xx xxx xx xx) - 12 цифр
 * - KG: Кыргызстан (+996 xxx xxx xxx) - 12 цифр
 * - BY: Беларусь (+375 xx xxx xx xx) - 12 цифр
 * - INTERNATIONAL: любой международный формат
 *
 * Использование:
 *   // Базовое (по умолчанию KZ)
 *   [['phone'], PhoneNumberValidator::class],
 *
 *   // С указанием страны
 *   [['phone'], PhoneNumberValidator::class, 'country' => 'UZ'],
 *
 *   // Международный формат
 *   [['phone'], PhoneNumberValidator::class, 'country' => 'INTERNATIONAL'],
 *
 *   // Несколько стран
 *   [['phone'], PhoneNumberValidator::class, 'countries' => ['KZ', 'RU']],
 */
class PhoneNumberValidator extends Validator
{
    /**
     * @var string код страны по умолчанию
     */
    public $country = 'KZ';

    /**
     * @var array|null массив допустимых стран (если указан, $country игнорируется)
     */
    public $countries;

    /**
     * @var bool разрешить пустое значение
     */
    public $allowEmpty = true;

    /**
     * @var bool нормализовать номер (удалить лишние символы, привести к стандарту)
     */
    public $normalize = true;

    /**
     * @var bool сохранять + в начале номера
     */
    public $keepPlus = false;

    /**
     * Паттерны для разных стран
     * Все паттерны проверяют номер БЕЗ + в начале
     */
    protected static $patterns = [
        'KZ' => [
            'pattern' => '/^7[0-9]{10}$/',
            'prefix' => '7',
            'length' => 11,
            'name' => 'Казахстан',
            'format' => '+7 (###) ###-##-##',
        ],
        'RU' => [
            'pattern' => '/^7[0-9]{10}$/',
            'prefix' => '7',
            'length' => 11,
            'name' => 'Россия',
            'format' => '+7 (###) ###-##-##',
        ],
        'UZ' => [
            'pattern' => '/^998[0-9]{9}$/',
            'prefix' => '998',
            'length' => 12,
            'name' => 'Узбекистан',
            'format' => '+998 ## ###-##-##',
        ],
        'KG' => [
            'pattern' => '/^996[0-9]{9}$/',
            'prefix' => '996',
            'length' => 12,
            'name' => 'Кыргызстан',
            'format' => '+996 ### ###-###',
        ],
        'BY' => [
            'pattern' => '/^375[0-9]{9}$/',
            'prefix' => '375',
            'length' => 12,
            'name' => 'Беларусь',
            'format' => '+375 ## ###-##-##',
        ],
        'INTERNATIONAL' => [
            'pattern' => '/^[1-9][0-9]{6,14}$/',
            'prefix' => null,
            'length' => null, // 7-15 цифр
            'name' => 'Международный',
            'format' => null,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        // Нормализуем номер
        $cleaned = self::clean($value);

        // Проверка на пустое значение
        if ($this->allowEmpty && $this->isEmpty($cleaned)) {
            return;
        }

        // Определяем список стран для проверки
        $countriesToCheck = $this->countries ?? [$this->country];

        // Валидация
        $isValid = false;
        $validCountry = null;

        foreach ($countriesToCheck as $country) {
            if ($this->validateForCountry($cleaned, $country)) {
                $isValid = true;
                $validCountry = $country;
                break;
            }
        }

        if (!$isValid) {
            $message = $this->message ?? $this->getDefaultMessage($countriesToCheck);
            $model->addError($attribute, $message);
            return;
        }

        // Нормализация: сохраняем очищенный номер
        if ($this->normalize) {
            $model->$attribute = $this->keepPlus ? '+' . $cleaned : $cleaned;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $cleaned = self::clean($value);

        if ($this->allowEmpty && $this->isEmpty($cleaned)) {
            return null;
        }

        $countriesToCheck = $this->countries ?? [$this->country];

        foreach ($countriesToCheck as $country) {
            if ($this->validateForCountry($cleaned, $country)) {
                return null;
            }
        }

        return [$this->message ?? $this->getDefaultMessage($countriesToCheck), []];
    }

    /**
     * Валидация для конкретной страны
     */
    protected function validateForCountry(string $phone, string $country): bool
    {
        // Для KZ/RU: конвертируем 8 в начале в 7
        if (in_array($country, ['KZ', 'RU']) && strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }

        $config = self::$patterns[$country] ?? self::$patterns['INTERNATIONAL'];
        return (bool)preg_match($config['pattern'], $phone);
    }

    /**
     * Сообщение об ошибке по умолчанию
     */
    protected function getDefaultMessage(array $countries): string
    {
        if (count($countries) === 1) {
            $country = $countries[0];
            $config = self::$patterns[$country] ?? null;

            if ($config && $country !== 'INTERNATIONAL') {
                return Yii::t('app', 'Введите корректный номер телефона ({country})', [
                    'country' => $config['name'],
                ]);
            }
        }

        return Yii::t('app', 'Введите корректный номер телефона');
    }

    /**
     * Очистить номер от лишних символов
     *
     * @param string|null $phone
     * @return string
     */
    public static function clean(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Удаляем всё кроме цифр
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Для KZ/RU: конвертируем 8 в начале в 7
        if (strlen($cleaned) === 11 && $cleaned[0] === '8') {
            $cleaned = '7' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Форматировать номер для отображения
     *
     * @param string|null $phone
     * @param string $country
     * @return string
     */
    public static function format(?string $phone, string $country = 'KZ'): string
    {
        $cleaned = self::clean($phone);

        if (empty($cleaned)) {
            return '';
        }

        $config = self::$patterns[$country] ?? null;

        if (!$config || !$config['format']) {
            return '+' . $cleaned;
        }

        // Проверяем длину
        if ($config['length'] && strlen($cleaned) !== $config['length']) {
            return '+' . $cleaned;
        }

        // Применяем формат
        $format = $config['format'];
        $result = '';
        $phoneIndex = 0;

        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] === '#') {
                if ($phoneIndex < strlen($cleaned)) {
                    $result .= $cleaned[$phoneIndex];
                    $phoneIndex++;
                }
            } else {
                $result .= $format[$i];
            }
        }

        return $result;
    }

    /**
     * Определить страну по номеру
     *
     * @param string|null $phone
     * @return string|null код страны или null
     */
    public static function detectCountry(?string $phone): ?string
    {
        $cleaned = self::clean($phone);

        if (empty($cleaned)) {
            return null;
        }

        // Проверяем по префиксам (от длинных к коротким)
        $prefixChecks = [
            '998' => 'UZ',
            '996' => 'KG',
            '375' => 'BY',
            '7' => 'KZ', // По умолчанию KZ для 7, хотя может быть и RU
        ];

        foreach ($prefixChecks as $prefix => $country) {
            if (strpos($cleaned, $prefix) === 0) {
                $config = self::$patterns[$country];
                if (preg_match($config['pattern'], $cleaned)) {
                    return $country;
                }
            }
        }

        // Если подходит под международный формат
        if (preg_match(self::$patterns['INTERNATIONAL']['pattern'], $cleaned)) {
            return 'INTERNATIONAL';
        }

        return null;
    }

    /**
     * Проверить валидность номера (статический метод)
     *
     * @param string|null $phone
     * @param string|array $country код страны или массив стран
     * @return bool
     */
    public static function isValid(?string $phone, $country = 'KZ'): bool
    {
        $cleaned = self::clean($phone);

        if (empty($cleaned)) {
            return false;
        }

        $countries = is_array($country) ? $country : [$country];

        foreach ($countries as $c) {
            $config = self::$patterns[$c] ?? self::$patterns['INTERNATIONAL'];
            if (preg_match($config['pattern'], $cleaned)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получить список поддерживаемых стран
     *
     * @return array
     */
    public static function getCountries(): array
    {
        $result = [];
        foreach (self::$patterns as $code => $config) {
            $result[$code] = $config['name'];
        }
        return $result;
    }

    /**
     * Получить конфигурацию страны
     *
     * @param string $country
     * @return array|null
     */
    public static function getCountryConfig(string $country): ?array
    {
        return self::$patterns[$country] ?? null;
    }
}
