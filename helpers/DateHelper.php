<?php

namespace app\helpers;

use Yii;
use yii\base\InvalidArgumentException;

/**
 * DateHelper - централизованный хелпер для работы с датами
 *
 * Предоставляет единый интерфейс для:
 * - Получения текущей даты/времени
 * - Форматирования дат для SQL и отображения
 * - Вычисления относительных дат
 * - Парсинга дат из разных форматов
 *
 * Использование:
 *   DateHelper::now()                    // '2026-01-07 12:30:45'
 *   DateHelper::today()                  // '2026-01-07'
 *   DateHelper::format($date, 'd.m.Y')   // '07.01.2026'
 *   DateHelper::relative('-7 days')      // '2025-12-31'
 *   DateHelper::startOfWeek()            // '2026-01-05' (понедельник)
 */
class DateHelper
{
    /**
     * SQL форматы
     */
    const SQL_DATE = 'Y-m-d';
    const SQL_DATETIME = 'Y-m-d H:i:s';
    const SQL_DATETIME_SHORT = 'Y-m-d H:i';

    /**
     * Форматы для отображения
     */
    const DISPLAY_DATE = 'd.m.Y';
    const DISPLAY_DATETIME = 'd.m.Y H:i';
    const DISPLAY_TIME = 'H:i';

    /**
     * Текущая дата и время в SQL формате
     *
     * @return string Y-m-d H:i:s
     */
    public static function now(): string
    {
        return date(self::SQL_DATETIME);
    }

    /**
     * Текущая дата в SQL формате
     *
     * @return string Y-m-d
     */
    public static function today(): string
    {
        return date(self::SQL_DATE);
    }

    /**
     * Текущее время
     *
     * @return string H:i:s
     */
    public static function time(): string
    {
        return date('H:i:s');
    }

    /**
     * Преобразовать в SQL дату (Y-m-d)
     *
     * @param mixed $date дата в любом формате
     * @param string|null $default значение по умолчанию если $date пустой
     * @return string|null
     */
    public static function toSqlDate($date, ?string $default = null): ?string
    {
        if (empty($date)) {
            return $default;
        }

        $timestamp = self::toTimestamp($date);
        return $timestamp ? date(self::SQL_DATE, $timestamp) : $default;
    }

    /**
     * Преобразовать в SQL datetime (Y-m-d H:i:s)
     *
     * @param mixed $date дата в любом формате
     * @param string|null $default значение по умолчанию если $date пустой
     * @return string|null
     */
    public static function toSqlDatetime($date, ?string $default = null): ?string
    {
        if (empty($date)) {
            return $default;
        }

        $timestamp = self::toTimestamp($date);
        return $timestamp ? date(self::SQL_DATETIME, $timestamp) : $default;
    }

    /**
     * Преобразовать в SQL datetime short (Y-m-d H:i)
     *
     * @param mixed $date дата в любом формате
     * @param string|null $default значение по умолчанию если $date пустой
     * @return string|null
     */
    public static function toSqlDatetimeShort($date, ?string $default = null): ?string
    {
        if (empty($date)) {
            return $default;
        }

        $timestamp = self::toTimestamp($date);
        return $timestamp ? date(self::SQL_DATETIME_SHORT, $timestamp) : $default;
    }

    /**
     * Форматировать дату для отображения через Yii Formatter
     *
     * @param mixed $date дата
     * @param string $format PHP формат даты (d.m.Y)
     * @return string
     */
    public static function format($date, string $format = self::DISPLAY_DATE): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Yii::$app->formatter->asDate($date, 'php:' . $format);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Форматировать дату и время для отображения
     *
     * @param mixed $date дата
     * @param string $format PHP формат
     * @return string
     */
    public static function formatDatetime($date, string $format = self::DISPLAY_DATETIME): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Yii::$app->formatter->asDatetime($date, 'php:' . $format);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Относительная дата от текущей
     *
     * @param string $modifier модификатор ('+1 day', '-7 days', '+1 month')
     * @param bool $datetime возвращать datetime (true) или date (false)
     * @return string
     */
    public static function relative(string $modifier, bool $datetime = false): string
    {
        $format = $datetime ? self::SQL_DATETIME : self::SQL_DATE;
        return date($format, strtotime($modifier));
    }

    /**
     * Относительная дата от указанной даты
     *
     * @param mixed $date базовая дата
     * @param string $modifier модификатор
     * @param bool $datetime возвращать datetime
     * @return string|null
     */
    public static function relativeFrom($date, string $modifier, bool $datetime = false): ?string
    {
        $timestamp = self::toTimestamp($date);
        if (!$timestamp) {
            return null;
        }

        $format = $datetime ? self::SQL_DATETIME : self::SQL_DATE;
        return date($format, strtotime($modifier, $timestamp));
    }

    /**
     * Начало недели (понедельник)
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function startOfWeek($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date(self::SQL_DATE, strtotime('monday this week', $timestamp));
        }
        return date(self::SQL_DATE, strtotime('monday this week'));
    }

    /**
     * Конец недели (воскресенье)
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function endOfWeek($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date(self::SQL_DATE, strtotime('sunday this week', $timestamp));
        }
        return date(self::SQL_DATE, strtotime('sunday this week'));
    }

    /**
     * Начало месяца
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function startOfMonth($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date('Y-m-01', $timestamp);
        }
        return date('Y-m-01');
    }

    /**
     * Конец месяца
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function endOfMonth($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date('Y-m-t', $timestamp);
        }
        return date('Y-m-t');
    }

    /**
     * Начало года
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function startOfYear($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date('Y-01-01', $timestamp);
        }
        return date('Y-01-01');
    }

    /**
     * Конец года
     *
     * @param mixed $date дата (по умолчанию сегодня)
     * @return string Y-m-d
     */
    public static function endOfYear($date = null): string
    {
        if ($date) {
            $timestamp = self::toTimestamp($date);
            return date('Y-12-31', $timestamp);
        }
        return date('Y-12-31');
    }

    /**
     * Вчера
     *
     * @return string Y-m-d
     */
    public static function yesterday(): string
    {
        return self::relative('-1 day');
    }

    /**
     * Завтра
     *
     * @return string Y-m-d
     */
    public static function tomorrow(): string
    {
        return self::relative('+1 day');
    }

    /**
     * Преобразовать в timestamp
     *
     * @param mixed $date дата (string, int, DateTime)
     * @return int|null
     */
    public static function toTimestamp($date): ?int
    {
        if (empty($date)) {
            return null;
        }

        if (is_int($date)) {
            return $date;
        }

        if ($date instanceof \DateTime || $date instanceof \DateTimeImmutable) {
            return $date->getTimestamp();
        }

        if (is_string($date)) {
            // Обработка формата d.m.Y
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}/', $date)) {
                $date = str_replace('.', '-', $date);
            }
            $timestamp = strtotime($date);
            return $timestamp !== false ? $timestamp : null;
        }

        return null;
    }

    /**
     * Проверить, является ли дата сегодняшней
     *
     * @param mixed $date
     * @return bool
     */
    public static function isToday($date): bool
    {
        return self::toSqlDate($date) === self::today();
    }

    /**
     * Проверить, является ли дата вчерашней
     *
     * @param mixed $date
     * @return bool
     */
    public static function isYesterday($date): bool
    {
        return self::toSqlDate($date) === self::yesterday();
    }

    /**
     * Проверить, находится ли дата в прошлом
     *
     * @param mixed $date
     * @return bool
     */
    public static function isPast($date): bool
    {
        $sqlDate = self::toSqlDate($date);
        return $sqlDate && $sqlDate < self::today();
    }

    /**
     * Проверить, находится ли дата в будущем
     *
     * @param mixed $date
     * @return bool
     */
    public static function isFuture($date): bool
    {
        $sqlDate = self::toSqlDate($date);
        return $sqlDate && $sqlDate > self::today();
    }

    /**
     * Проверить, находится ли дата в текущей неделе
     *
     * @param mixed $date
     * @return bool
     */
    public static function isThisWeek($date): bool
    {
        $sqlDate = self::toSqlDate($date);
        return $sqlDate && $sqlDate >= self::startOfWeek() && $sqlDate <= self::endOfWeek();
    }

    /**
     * Проверить, находится ли дата в текущем месяце
     *
     * @param mixed $date
     * @return bool
     */
    public static function isThisMonth($date): bool
    {
        $sqlDate = self::toSqlDate($date);
        return $sqlDate && $sqlDate >= self::startOfMonth() && $sqlDate <= self::endOfMonth();
    }

    /**
     * Разница между датами в днях
     *
     * @param mixed $date1
     * @param mixed $date2
     * @return int|null
     */
    public static function diffInDays($date1, $date2): ?int
    {
        $ts1 = self::toTimestamp($date1);
        $ts2 = self::toTimestamp($date2);

        if (!$ts1 || !$ts2) {
            return null;
        }

        return (int)floor(($ts2 - $ts1) / 86400);
    }

    /**
     * Дней до даты (от сегодня)
     *
     * @param mixed $date
     * @return int|null положительное число если в будущем
     */
    public static function daysUntil($date): ?int
    {
        return self::diffInDays(self::today(), $date);
    }

    /**
     * Дней с даты (до сегодня)
     *
     * @param mixed $date
     * @return int|null положительное число если в прошлом
     */
    public static function daysSince($date): ?int
    {
        return self::diffInDays($date, self::today());
    }

    /**
     * Человекочитаемая относительная дата
     *
     * @param mixed $date
     * @return string "Сегодня", "Вчера", "3 дня назад" и т.д.
     */
    public static function relative_human($date): string
    {
        try {
            return Yii::$app->formatter->asRelativeTime($date);
        } catch (\Exception $e) {
            return self::format($date);
        }
    }

    /**
     * Сгенерировать массив дат в диапазоне
     *
     * @param mixed $start начальная дата
     * @param mixed $end конечная дата
     * @param string $format формат выходных дат
     * @return array
     */
    public static function range($start, $end, string $format = self::SQL_DATE): array
    {
        $startTs = self::toTimestamp($start);
        $endTs = self::toTimestamp($end);

        if (!$startTs || !$endTs || $startTs > $endTs) {
            return [];
        }

        $result = [];
        for ($ts = $startTs; $ts <= $endTs; $ts += 86400) {
            $result[] = date($format, $ts);
        }

        return $result;
    }

    /**
     * Получить название дня недели
     *
     * @param mixed $date
     * @param bool $short короткое название (Пн, Вт) или полное (Понедельник)
     * @return string
     */
    public static function dayOfWeek($date, bool $short = false): string
    {
        $timestamp = self::toTimestamp($date);
        if (!$timestamp) {
            return '';
        }

        $days = $short
            ? ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
            : ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        return $days[(int)date('w', $timestamp)];
    }

    /**
     * Получить название месяца
     *
     * @param mixed $date
     * @param bool $genitive родительный падеж ("января" вместо "Январь")
     * @return string
     */
    public static function monthName($date, bool $genitive = false): string
    {
        $timestamp = self::toTimestamp($date);
        if (!$timestamp) {
            return '';
        }

        $months = $genitive
            ? ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря']
            : ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        return $months[(int)date('n', $timestamp)];
    }

    /**
     * Форматированная дата с названием месяца
     * Пример: "7 января 2026"
     *
     * @param mixed $date
     * @param bool $withYear включать год
     * @return string
     */
    public static function formatWithMonth($date, bool $withYear = true): string
    {
        $timestamp = self::toTimestamp($date);
        if (!$timestamp) {
            return '';
        }

        $day = date('j', $timestamp);
        $month = self::monthName($date, true);

        if ($withYear) {
            return "{$day} {$month} " . date('Y', $timestamp);
        }

        return "{$day} {$month}";
    }

    /**
     * HTML5 datetime-local формат для input
     *
     * @param mixed $date
     * @return string Y-m-d\TH:i
     */
    public static function toHtmlDatetime($date): string
    {
        if (empty($date)) {
            return date('Y-m-d\TH:i');
        }

        $timestamp = self::toTimestamp($date);
        return $timestamp ? date('Y-m-d\TH:i', $timestamp) : date('Y-m-d\TH:i');
    }

    /**
     * HTML5 date формат для input
     *
     * @param mixed $date
     * @return string Y-m-d
     */
    public static function toHtmlDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        return self::toSqlDate($date) ?? '';
    }
}
