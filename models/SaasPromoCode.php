<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель промокода.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $discount_type percent|fixed
 * @property float $discount_value
 * @property string $applies_to subscription|addon|all
 * @property array|null $applicable_plans
 * @property array|null $applicable_addons
 * @property float $min_amount
 * @property float|null $max_discount
 * @property int|null $usage_limit
 * @property int $usage_per_org
 * @property string|null $valid_from
 * @property string|null $valid_until
 * @property bool $first_payment_only
 * @property bool $new_customers_only
 * @property int|null $created_by
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $creator
 * @property SaasPromoCodeUsage[] $usages
 */
class SaasPromoCode extends ActiveRecord
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
        return '{{%saas_promo_code}}';
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
            [['code', 'name', 'discount_type', 'discount_value'], 'required'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['code'], 'filter', 'filter' => 'strtoupper'],
            [['code'], 'match', 'pattern' => '/^[A-Z0-9_-]+$/', 'message' => 'Код может содержать только латинские буквы, цифры, дефис и подчёркивание'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['discount_type'], 'in', 'range' => [self::TYPE_PERCENT, self::TYPE_FIXED]],
            [['applies_to'], 'in', 'range' => [self::APPLIES_SUBSCRIPTION, self::APPLIES_ADDON, self::APPLIES_ALL]],
            [['discount_value', 'min_amount', 'max_discount'], 'number', 'min' => 0],
            [['discount_value'], 'validateDiscountValue'],
            [['usage_limit', 'usage_per_org', 'created_by'], 'integer'],
            [['usage_limit'], 'integer', 'min' => 1],
            [['usage_per_org'], 'integer', 'min' => 1],
            [['valid_from', 'valid_until'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['valid_until'], 'validateDateRange'],
            [['first_payment_only', 'new_customers_only', 'is_active'], 'boolean'],
            [['applicable_plans', 'applicable_addons'], 'safe'],
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
            'code' => 'Код',
            'name' => 'Название',
            'description' => 'Описание',
            'discount_type' => 'Тип скидки',
            'discount_value' => 'Значение',
            'applies_to' => 'Применяется к',
            'applicable_plans' => 'Тарифы',
            'applicable_addons' => 'Аддоны',
            'min_amount' => 'Мин. сумма',
            'max_discount' => 'Макс. скидка',
            'usage_limit' => 'Лимит использований',
            'usage_per_org' => 'Лимит на организацию',
            'valid_from' => 'Действует с',
            'valid_until' => 'Действует до',
            'first_payment_only' => 'Только первый платёж',
            'new_customers_only' => 'Только новые клиенты',
            'created_by' => 'Создал',
            'is_active' => 'Активен',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * Связь с создателем
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Связь с использованиями
     */
    public function getUsages()
    {
        return $this->hasMany(SaasPromoCodeUsage::class, ['promo_code_id' => 'id']);
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
     * Количество использований
     */
    public function getUsageCount(): int
    {
        return (int)$this->getUsages()->count();
    }

    /**
     * Количество использований организацией
     */
    public function getUsageCountByOrg(int $organizationId): int
    {
        return (int)$this->getUsages()
            ->andWhere(['organization_id' => $organizationId])
            ->count();
    }

    /**
     * Проверка действительности промокода
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

        if ($this->usage_limit && $this->getUsageCount() >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Проверка применимости к организации
     */
    public function isApplicableToOrganization(Organizations $organization): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Проверка лимита на организацию
        if ($this->getUsageCountByOrg($organization->id) >= $this->usage_per_org) {
            return false;
        }

        // Проверка "только новые клиенты"
        if ($this->new_customers_only && $organization->hasEverPaid()) {
            return false;
        }

        // Проверка "только первый платёж"
        if ($this->first_payment_only && $organization->getPaymentCount() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Проверка применимости к плану
     */
    public function isApplicableToPlan(?SaasPlan $plan): bool
    {
        if (!$plan) {
            return true;
        }

        if ($this->applies_to === self::APPLIES_ADDON) {
            return false;
        }

        if (empty($this->applicable_plans)) {
            return true;
        }

        $plans = is_array($this->applicable_plans)
            ? $this->applicable_plans
            : json_decode($this->applicable_plans, true);

        return in_array($plan->code, $plans);
    }

    /**
     * Проверка применимости к аддону
     */
    public function isApplicableToAddon(?SaasFeature $addon): bool
    {
        if (!$addon) {
            return true;
        }

        if ($this->applies_to === self::APPLIES_SUBSCRIPTION) {
            return false;
        }

        if (empty($this->applicable_addons)) {
            return true;
        }

        $addons = is_array($this->applicable_addons)
            ? $this->applicable_addons
            : json_decode($this->applicable_addons, true);

        return in_array($addon->code, $addons);
    }

    /**
     * Рассчитать скидку
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_amount) {
            return 0;
        }

        if ($this->discount_type === self::TYPE_PERCENT) {
            $discount = $amount * ($this->discount_value / 100);
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
        } else {
            $discount = $this->discount_value;
        }

        // Скидка не может превышать сумму
        return min($discount, $amount);
    }

    /**
     * Зарегистрировать использование
     */
    public function registerUsage(int $organizationId, float $discountAmount, ?int $paymentId = null): bool
    {
        $usage = new SaasPromoCodeUsage([
            'promo_code_id' => $this->id,
            'organization_id' => $organizationId,
            'payment_id' => $paymentId,
            'discount_amount' => $discountAmount,
        ]);

        return $usage->save();
    }

    /**
     * Найти по коду
     */
    public static function findByCode(string $code): ?self
    {
        return self::find()
            ->where(['code' => strtoupper($code)])
            ->andWhere(['is_active' => true])
            ->one();
    }

    /**
     * Найти активные промокоды
     */
    public static function findActive()
    {
        $now = date('Y-m-d H:i:s');
        return self::find()
            ->where(['is_active' => true])
            ->andWhere(['or', ['valid_from' => null], ['<=', 'valid_from', $now]])
            ->andWhere(['or', ['valid_until' => null], ['>=', 'valid_until', $now]]);
    }

    /**
     * Генерация уникального кода
     */
    public static function generateCode(int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::find()->where(['code' => $code])->exists());

        return $code;
    }

    /**
     * Остаток использований
     */
    public function getRemainingUsage(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }
        return max(0, $this->usage_limit - $this->getUsageCount());
    }

    /**
     * Проверка истёк ли промокод
     */
    public function isExpired(): bool
    {
        if ($this->valid_until === null) {
            return false;
        }
        return $this->valid_until < date('Y-m-d H:i:s');
    }

    /**
     * Ещё не начался
     */
    public function isNotStarted(): bool
    {
        if ($this->valid_from === null) {
            return false;
        }
        return $this->valid_from > date('Y-m-d H:i:s');
    }

    /**
     * Статус промокода
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isNotStarted()) {
            return 'scheduled';
        }
        if ($this->usage_limit && $this->getUsageCount() >= $this->usage_limit) {
            return 'exhausted';
        }
        return 'active';
    }

    /**
     * Метка статуса
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'active' => 'Активен',
            'inactive' => 'Отключён',
            'expired' => 'Истёк',
            'scheduled' => 'Запланирован',
            'exhausted' => 'Исчерпан',
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
            'exhausted' => 'badge-warning',
        ];
        return $classes[$this->getStatus()] ?? 'badge-light';
    }

    /**
     * Общая сумма скидок по этому промокоду
     */
    public function getTotalDiscountGiven(): float
    {
        return (float)$this->getUsages()->sum('discount_amount');
    }
}
