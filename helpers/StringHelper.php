<?php

namespace app\helpers;

use yii\helpers\StringHelper as YiiStringHelper;

/**
 * StringHelper - утилиты для работы со строками
 *
 * Расширяет Yii StringHelper дополнительными методами
 */
class StringHelper extends YiiStringHelper
{
    /**
     * Является ли строка JSON
     *
     * @param mixed $string
     * @return bool
     */
    public static function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Преобразовать строку в URL-slug (транслитерация)
     *
     * @param string $text Исходный текст
     * @param string $separator Разделитель (по умолчанию -)
     * @return string
     */
    public static function slugify(string $text, string $separator = '-'): string
    {
        // Транслитерация кириллицы
        $transliteration = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            // Казахский
            'ә' => 'a', 'ғ' => 'g', 'қ' => 'q', 'ң' => 'n', 'ө' => 'o',
            'ұ' => 'u', 'ү' => 'u', 'һ' => 'h', 'і' => 'i',
        ];

        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, $transliteration);
        $text = strtr($text, array_change_key_case($transliteration, CASE_UPPER));

        // Заменяем не-буквенно-цифровые символы на разделитель
        $text = preg_replace('/[^a-z0-9]+/', $separator, $text);
        // Удаляем дублирующиеся разделители
        $text = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $text);
        // Удаляем разделители в начале и конце
        return trim($text, $separator);
    }

    /**
     * Обрезать строку с многоточием
     *
     * @param string $text Исходный текст
     * @param int $length Максимальная длина
     * @param string $suffix Суффикс (по умолчанию ...)
     * @param bool $asHtml Учитывать HTML теги
     * @return string
     */
    public static function truncateText(string $text, int $length, string $suffix = '...', bool $asHtml = false): string
    {
        if ($asHtml) {
            return parent::truncate($text, $length, $suffix, null, true);
        }

        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
    }

    /**
     * Обрезать по словам
     *
     * @param string $text Исходный текст
     * @param int $count Количество слов
     * @param string $suffix Суффикс
     * @param bool $asHtml Обрабатывать как HTML
     * @return string
     */
    public static function truncateWords($text, $count, $suffix = '...', $asHtml = false): string
    {
        return parent::truncateWords($text, $count, $suffix, $asHtml);
    }

    /**
     * Подсветить совпадения в тексте
     *
     * @param string $text Исходный текст
     * @param string $query Поисковый запрос
     * @param string $tag HTML тег для подсветки
     * @param string $class CSS класс
     * @return string
     */
    public static function highlightMatches(string $text, string $query, string $tag = 'mark', string $class = ''): string
    {
        if (empty($query)) {
            return $text;
        }

        $words = preg_split('/\s+/', $query);
        $words = array_filter($words, fn($w) => mb_strlen($w) >= 2);

        if (empty($words)) {
            return $text;
        }

        $classAttr = $class ? " class=\"{$class}\"" : '';
        $pattern = '/(' . implode('|', array_map('preg_quote', $words)) . ')/iu';

        return preg_replace($pattern, "<{$tag}{$classAttr}>$1</{$tag}>", $text);
    }

    /**
     * Преобразовать CamelCase в snake_case
     *
     * @param string $text
     * @return string
     */
    public static function camelToSnake(string $text): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $text));
    }

    /**
     * Преобразовать snake_case в CamelCase
     *
     * @param string $text
     * @param bool $capitalizeFirst Заглавная первая буква
     * @return string
     */
    public static function snakeToCamel(string $text, bool $capitalizeFirst = true): string
    {
        $result = str_replace(' ', '', ucwords(str_replace('_', ' ', $text)));
        return $capitalizeFirst ? $result : lcfirst($result);
    }

    /**
     * Преобразовать snake_case в kebab-case
     *
     * @param string $text
     * @return string
     */
    public static function snakeToKebab(string $text): string
    {
        return str_replace('_', '-', $text);
    }

    /**
     * Преобразовать kebab-case в snake_case
     *
     * @param string $text
     * @return string
     */
    public static function kebabToSnake(string $text): string
    {
        return str_replace('-', '_', $text);
    }

    /**
     * Очистить телефон от лишних символов
     *
     * @param string|null $phone
     * @return string
     */
    public static function cleanPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Форматировать телефон для отображения
     *
     * @param string|null $phone
     * @param string $format Формат: 'kz' (+7 XXX XXX XX XX), 'int' (+X XXX XXX XXXX)
     * @return string
     */
    public static function formatPhone(?string $phone, string $format = 'kz'): string
    {
        $phone = self::cleanPhone($phone);

        if (strlen($phone) < 10) {
            return $phone;
        }

        // Убираем + если есть
        $phone = ltrim($phone, '+');

        // Приводим 8 к 7 для KZ
        if ($format === 'kz' && strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }

        if ($format === 'kz' && strlen($phone) === 11) {
            return '+' . $phone[0] . ' ' .
                   substr($phone, 1, 3) . ' ' .
                   substr($phone, 4, 3) . ' ' .
                   substr($phone, 7, 2) . ' ' .
                   substr($phone, 9, 2);
        }

        return '+' . $phone;
    }

    /**
     * Сгенерировать инициалы из ФИО
     *
     * @param string|null $fio
     * @param int $count Количество букв (1-3)
     * @return string
     */
    public static function initials(?string $fio, int $count = 2): string
    {
        if (!$fio) {
            return '??';
        }

        $parts = preg_split('/\s+/', trim($fio));
        $initials = '';

        foreach ($parts as $part) {
            if ($count <= 0) break;
            $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
            $count--;
        }

        return $initials ?: '??';
    }

    /**
     * Маскировать email
     *
     * @param string|null $email
     * @return string
     */
    public static function maskEmail(?string $email): string
    {
        if (!$email || !str_contains($email, '@')) {
            return $email ?? '';
        }

        [$name, $domain] = explode('@', $email);
        $len = mb_strlen($name);

        if ($len <= 2) {
            $masked = $name[0] . '*';
        } else {
            $masked = $name[0] . str_repeat('*', $len - 2) . mb_substr($name, -1);
        }

        return $masked . '@' . $domain;
    }

    /**
     * Маскировать телефон
     *
     * @param string|null $phone
     * @return string
     */
    public static function maskPhone(?string $phone): string
    {
        $phone = self::cleanPhone($phone);

        if (strlen($phone) < 10) {
            return $phone;
        }

        // +7 *** *** ** 45
        return mb_substr($phone, 0, 2) . ' *** *** ** ' . mb_substr($phone, -2);
    }

    /**
     * Безопасно получить первые N символов
     *
     * @param string|null $text
     * @param int $length
     * @return string
     */
    public static function first(?string $text, int $length): string
    {
        if (!$text) {
            return '';
        }
        return mb_substr($text, 0, $length, 'UTF-8');
    }

    /**
     * Безопасно получить последние N символов
     *
     * @param string|null $text
     * @param int $length
     * @return string
     */
    public static function last(?string $text, int $length): string
    {
        if (!$text) {
            return '';
        }
        return mb_substr($text, -$length, null, 'UTF-8');
    }

    /**
     * Проверить, содержит ли строка подстроку (без учёта регистра)
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function containsIgnoreCase(string $haystack, string $needle): bool
    {
        return mb_stripos($haystack, $needle) !== false;
    }

    /**
     * Проверить, начинается ли строка с подстроки
     *
     * @param string $haystack
     * @param string $needle
     * @param bool $caseSensitive
     * @return bool
     */
    public static function startsWith($haystack, $needle, $caseSensitive = true): bool
    {
        return parent::startsWith($haystack, $needle, $caseSensitive);
    }

    /**
     * Проверить, заканчивается ли строка подстрокой
     *
     * @param string $haystack
     * @param string $needle
     * @param bool $caseSensitive
     * @return bool
     */
    public static function endsWith($haystack, $needle, $caseSensitive = true): bool
    {
        return parent::endsWith($haystack, $needle, $caseSensitive);
    }

    /**
     * Pluralize Russian word based on count
     * Склонение слова по числу (1 ученик, 2 ученика, 5 учеников)
     *
     * @param int $count Число
     * @param string $one Форма для 1 (ученик)
     * @param string $few Форма для 2-4 (ученика)
     * @param string $many Форма для 5+ (учеников)
     * @return string
     */
    public static function pluralize(int $count, string $one, string $few, string $many): string
    {
        $count = abs($count);
        $mod10 = $count % 10;
        $mod100 = $count % 100;

        if ($mod100 >= 11 && $mod100 <= 19) {
            return $many;
        }

        if ($mod10 === 1) {
            return $one;
        }

        if ($mod10 >= 2 && $mod10 <= 4) {
            return $few;
        }

        return $many;
    }

    /**
     * Pluralize with count prefix
     * Склонение с числом (5 учеников)
     *
     * @param int $count
     * @param string $one
     * @param string $few
     * @param string $many
     * @return string
     */
    public static function pluralizeWithCount(int $count, string $one, string $few, string $many): string
    {
        return $count . ' ' . self::pluralize($count, $one, $few, $many);
    }
}
