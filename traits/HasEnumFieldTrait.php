<?php

namespace app\traits;

/**
 * HasEnumFieldTrait - базовые методы для работы с enum-подобными полями
 *
 * Предоставляет helper-методы для получения label, icon, color по значению.
 * Используется как основа для HasStatusTrait и HasTypeTrait.
 *
 * @see HasStatusTrait для полей status
 * @see HasTypeTrait для полей type
 */
trait HasEnumFieldTrait
{
    /**
     * Получить label для значения enum поля
     *
     * @param mixed $value Значение поля
     * @param array $list Массив [value => label]
     * @param string $default Значение по умолчанию
     * @return string
     */
    protected function getEnumLabel($value, array $list, string $default = 'Неизвестно'): string
    {
        return $list[$value] ?? $default;
    }

    /**
     * Получить icon для значения enum поля
     *
     * @param mixed $value Значение поля
     * @param array $icons Массив [value => icon]
     * @param string $default Иконка по умолчанию
     * @return string
     */
    protected function getEnumIcon($value, array $icons, string $default = 'information-circle'): string
    {
        return $icons[$value] ?? $default;
    }

    /**
     * Получить color для значения enum поля
     *
     * @param mixed $value Значение поля
     * @param array $colors Массив [value => color]
     * @param string $default Цвет по умолчанию
     * @return string
     */
    protected function getEnumColor($value, array $colors, string $default = 'gray'): string
    {
        return $colors[$value] ?? $default;
    }

    /**
     * Проверить, является ли значение одним из указанных
     *
     * @param mixed $value Значение поля
     * @param array $values Массив допустимых значений
     * @return bool
     */
    protected function isEnumValue($value, array $values): bool
    {
        return in_array($value, $values, true);
    }
}
