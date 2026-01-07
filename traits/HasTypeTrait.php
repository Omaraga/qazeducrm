<?php

namespace app\traits;

/**
 * HasTypeTrait - методы для моделей с полем type
 *
 * Требует реализации:
 * - static getTypeList(): array - список [type => label]
 *
 * Опционально переопределить:
 * - static getTypeIcons(): array - список [type => icon]
 * - static getTypeColors(): array - список [type => color]
 *
 * Использование:
 * ```php
 * class MyModel extends ActiveRecord
 * {
 *     use HasTypeTrait;
 *
 *     const TYPE_CALL = 'call';
 *     const TYPE_MESSAGE = 'message';
 *
 *     public static function getTypeList(): array
 *     {
 *         return [
 *             self::TYPE_CALL => 'Звонок',
 *             self::TYPE_MESSAGE => 'Сообщение',
 *         ];
 *     }
 *
 *     public static function getTypeIcons(): array
 *     {
 *         return [
 *             self::TYPE_CALL => 'phone',
 *             self::TYPE_MESSAGE => 'chat-bubble-left',
 *         ];
 *     }
 * }
 *
 * // Использование
 * $model->getTypeLabel();   // "Звонок"
 * $model->getTypeIcon();    // "phone"
 * $model->isType(MyModel::TYPE_CALL); // true/false
 * ```
 */
trait HasTypeTrait
{
    use HasEnumFieldTrait;

    /**
     * Получить список типов [value => label]
     * Должен быть переопределён в модели
     *
     * @return array
     */
    abstract public static function getTypeList(): array;

    /**
     * Получить label текущего типа
     *
     * @return string
     */
    public function getTypeLabel(): string
    {
        return $this->getEnumLabel($this->type, static::getTypeList());
    }

    /**
     * Получить иконку текущего типа (если определены)
     *
     * @return string
     */
    public function getTypeIcon(): string
    {
        if (!method_exists(static::class, 'getTypeIcons')) {
            return 'information-circle';
        }
        return $this->getEnumIcon($this->type, static::getTypeIcons());
    }

    /**
     * Получить цвет текущего типа (если определены)
     *
     * @return string
     */
    public function getTypeColor(): string
    {
        if (!method_exists(static::class, 'getTypeColors')) {
            return 'gray';
        }
        return $this->getEnumColor($this->type, static::getTypeColors());
    }

    /**
     * Проверить, является ли текущий тип указанным
     *
     * @param int|string $type
     * @return bool
     */
    public function isType($type): bool
    {
        return $this->type === $type;
    }

    /**
     * Проверить, является ли текущий тип одним из указанных
     *
     * @param array $types
     * @return bool
     */
    public function isTypeIn(array $types): bool
    {
        return $this->isEnumValue($this->type, $types);
    }

    /**
     * Получить список типов для dropdown/select
     * Алиас для getTypeList() для совместимости с Yii формами
     *
     * @return array
     */
    public static function getTypeOptions(): array
    {
        return static::getTypeList();
    }
}
