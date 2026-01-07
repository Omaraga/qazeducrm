<?php

namespace app\traits;

/**
 * HasStatusTrait - методы для моделей с полем status
 *
 * Требует реализации:
 * - static getStatusList(): array - список [status => label]
 *
 * Опционально переопределить:
 * - static getStatusIcons(): array - список [status => icon]
 * - static getStatusColors(): array - список [status => color]
 *
 * Использование:
 * ```php
 * class MyModel extends ActiveRecord
 * {
 *     use HasStatusTrait;
 *
 *     const STATUS_ACTIVE = 1;
 *     const STATUS_INACTIVE = 0;
 *
 *     public static function getStatusList(): array
 *     {
 *         return [
 *             self::STATUS_ACTIVE => 'Активен',
 *             self::STATUS_INACTIVE => 'Неактивен',
 *         ];
 *     }
 * }
 *
 * // Использование
 * $model->getStatusLabel();   // "Активен"
 * $model->getStatusIcon();    // "check-circle" (если определён)
 * $model->getStatusColor();   // "green" (если определён)
 * $model->isStatus(MyModel::STATUS_ACTIVE); // true/false
 * MyModel::getStatusOptions(); // [1 => 'Активен', 0 => 'Неактивен']
 * ```
 */
trait HasStatusTrait
{
    use HasEnumFieldTrait;

    /**
     * Получить список статусов [value => label]
     * Должен быть переопределён в модели
     *
     * @return array
     */
    abstract public static function getStatusList(): array;

    /**
     * Получить label текущего статуса
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        return $this->getEnumLabel($this->status, static::getStatusList());
    }

    /**
     * Получить иконку текущего статуса (если определены)
     *
     * @return string
     */
    public function getStatusIcon(): string
    {
        if (!method_exists(static::class, 'getStatusIcons')) {
            return 'information-circle';
        }
        return $this->getEnumIcon($this->status, static::getStatusIcons());
    }

    /**
     * Получить цвет текущего статуса (если определены)
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        if (!method_exists(static::class, 'getStatusColors')) {
            return 'gray';
        }
        return $this->getEnumColor($this->status, static::getStatusColors());
    }

    /**
     * Проверить, является ли текущий статус указанным
     *
     * @param int|string $status
     * @return bool
     */
    public function isStatus($status): bool
    {
        return $this->status === $status;
    }

    /**
     * Проверить, является ли текущий статус одним из указанных
     *
     * @param array $statuses
     * @return bool
     */
    public function isStatusIn(array $statuses): bool
    {
        return $this->isEnumValue($this->status, $statuses);
    }

    /**
     * Получить список статусов для dropdown/select
     * Алиас для getStatusList() для совместимости с Yii формами
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return static::getStatusList();
    }
}
