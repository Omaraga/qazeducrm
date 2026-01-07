<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель индивидуальной скидки организации.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $discount_type percent|fixed
 * @property float $discount_value
 * @property string|null $reason
 * @property string|null $valid_from
 * @property string|null $valid_until
 * @property string $applies_to subscription|addon|all
 * @property int|null $created_by
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property User $creator
 */
class OrganizationDiscount extends ActiveRecord
{
    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED = 'fixed';

    const APPLIES_SUBSCRIPTION = 'subscription';
    const APPLIES_ADDON = 'addon';
    const APPLIES_ALL = 'all';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_discount}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'discount_type', 'discount_value'], 'required'],
            [['organization_id', 'created_by'], 'integer'],
            [['discount_type'], 'in', 'range' => [self::TYPE_PERCENT, self::TYPE_FIXED]],
            [['applies_to'], 'in', 'range' => [self::APPLIES_SUBSCRIPTION, self::APPLIES_ADDON, self::APPLIES_ALL]],
            [['discount_value'], 'number', 'min' => 0],
            [['discount_value'], 'validateDiscountValue'],
            [['reason'], 'string', 'max' => 255],
            [['valid_from', 'valid_until'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['valid_until'], 'validateDateRange'],
            [['is_active'], 'boolean'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['organization_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * Валидация значения скидки
     */
    public function validateDiscountValue($attribute)
    {
        if ($this->discount_type === self::TYPE_PERCENT && $this->$attribute > 100) {
            $this->addError($attribute, 'Процент скидки не может превышать 100%');
        }
    }

    /**
     * Валидация диапазона дат
     */
    public function validateDateRange($attribute)
    {
        if ($this->valid_from && $this->valid_until) {
            if (strtotime($this->valid_until) <= strtotime($this->valid_from)) {
                $this->addError($attribute, 'Дата окончания должна быть позже даты начала');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'discount_type' => 'Тип скидки',
            'discount_value' => 'Значение',
            'reason' => 'Причина',
            'valid_from' => 'Действует с',
            'valid_until' => 'Действует до',
            'applies_to' => 'Применяется к',
            'created_by' => 'Создал',
            'is_active' => 'Активна',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    /**
     * Связь с организацией
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Связь с создателем
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Список типов скидок
     */
    public static function getDiscountTypeList(): array
    {
        return [
            self::TYPE_PERCENT => 'Процент (%)',
            self::TYPE_FIXED => 'Фиксированная сумма (KZT)',
        ];
    }

    /**
     * Список "применяется к"
     */
    public static function getAppliesToList(): array
    {
        return [
            self::APPLIES_SUBSCRIPTION => 'Подписки',
            self::APPLIES_ADDON => 'Аддоны',
            self::APPLIES_ALL => 'Всё',
        ];
    }

    /**
     * Метка типа скидки
     */
    public function getDiscountTypeLabel(): string
    {
        return self::getDiscountTypeList()[$this->discount_type] ?? $this->discount_type;
    }

    /**
     * Метка "применяется к"
     */
    public function getAppliesToLabel(): string
    {
        return self::getAppliesToList()[$this->applies_to] ?? $this->applies_to;
    }

    /**
     * Форматированное значение скидки
     */
    public function getFormattedDiscount(): string
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            return $this->discount_value . '%';
        }
        return number_format($this->discount_value, 0, '.', ' ') . ' KZT';
    }

    /**
     * Проверка действительности скидки
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        if ($this->valid_from && $this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Проверка применимости к подпискам
     */
    public function isApplicableToSubscription(): bool
    {
        return in_array($this->applies_to, [self::APPLIES_SUBSCRIPTION, self::APPLIES_ALL]);
    }

    /**
     * Проверка применимости к аддонам
     */
    public function isApplicableToAddon(): bool
    {
        return in_array($this->applies_to, [self::APPLIES_ADDON, self::APPLIES_ALL]);
    }

    /**
     * Рассчитать скидку
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            return round($amount * ($this->discount_value / 100), 2);
        }
        return min($this->discount_value, $amount);
    }

    /**
     * Найти активные скидки организации
     */
    public static function findActiveForOrganization(int $organizationId)
    {
        $now = date('Y-m-d H:i:s');
        return self::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['is_active' => true])
            ->andWhere(['or', ['valid_from' => null], ['<=', 'valid_from', $now]])
            ->andWhere(['or', ['valid_until' => null], ['>=', 'valid_until', $now]]);
    }

    /**
     * Найти максимальную активную скидку организации для подписок
     */
    public static function findBestForSubscription(int $organizationId): ?self
    {
        return self::findActiveForOrganization($organizationId)
            ->andWhere(['in', 'applies_to', [self::APPLIES_SUBSCRIPTION, self::APPLIES_ALL]])
            ->orderBy(['discount_value' => SORT_DESC])
            ->one();
    }

    /**
     * Найти максимальную активную скидку организации для аддонов
     */
    public static function findBestForAddon(int $organizationId): ?self
    {
        return self::findActiveForOrganization($organizationId)
            ->andWhere(['in', 'applies_to', [self::APPLIES_ADDON, self::APPLIES_ALL]])
            ->orderBy(['discount_value' => SORT_DESC])
            ->one();
    }

    /**
     * Статус скидки
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = date('Y-m-d H:i:s');

        if ($this->valid_until && $this->valid_until < $now) {
            return 'expired';
        }

        if ($this->valid_from && $this->valid_from > $now) {
            return 'scheduled';
        }

        return 'active';
    }

    /**
     * Метка статуса
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'active' => 'Активна',
            'inactive' => 'Отключена',
            'expired' => 'Истекла',
            'scheduled' => 'Запланирована',
        ];
        return $labels[$this->getStatus()] ?? 'Неизвестно';
    }

    /**
     * CSS класс статуса
     */
    public function getStatusBadgeClass(): string
    {
        $classes = [
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'expired' => 'badge-danger',
            'scheduled' => 'badge-info',
        ];
        return $classes[$this->getStatus()] ?? 'badge-light';
    }

    /**
     * Описание скидки (короткое)
     */
    public function getShortDescription(): string
    {
        $desc = $this->getFormattedDiscount();
        if ($this->reason) {
            $desc .= ' (' . $this->reason . ')';
        }
        return $desc;
    }
}
