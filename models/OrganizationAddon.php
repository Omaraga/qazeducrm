<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель докупленных аддонов организации.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $feature_id
 * @property string $status
 * @property int $quantity
 * @property array|null $value
 * @property string $billing_period
 * @property float|null $price
 * @property string|null $started_at
 * @property string|null $expires_at
 * @property string|null $trial_ends_at
 * @property string|null $cancelled_at
 * @property int|null $created_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property SaasFeature $feature
 * @property User $createdByUser
 */
class OrganizationAddon extends ActiveRecord
{
    // Статусы аддона
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Периоды биллинга
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_addon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'feature_id'], 'required'],
            [['organization_id', 'feature_id', 'quantity', 'created_by'], 'integer'],
            [['quantity'], 'default', 'value' => 1],
            [['quantity'], 'integer', 'min' => 1],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_TRIAL, self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_CANCELLED]],
            [['billing_period'], 'string', 'max' => 20],
            [['billing_period'], 'default', 'value' => self::PERIOD_MONTHLY],
            [['billing_period'], 'in', 'range' => [self::PERIOD_MONTHLY, self::PERIOD_YEARLY]],
            [['price'], 'number'],
            [['started_at', 'expires_at', 'trial_ends_at', 'cancelled_at'], 'safe'],
            [['value'], 'safe'],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            [['feature_id'], 'exist', 'targetClass' => SaasFeature::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => Yii::t('main', 'Организация'),
            'feature_id' => Yii::t('main', 'Функция/Аддон'),
            'status' => Yii::t('main', 'Статус'),
            'quantity' => Yii::t('main', 'Количество'),
            'value' => Yii::t('main', 'Значение'),
            'billing_period' => Yii::t('main', 'Период биллинга'),
            'price' => Yii::t('main', 'Цена'),
            'started_at' => Yii::t('main', 'Дата активации'),
            'expires_at' => Yii::t('main', 'Дата истечения'),
            'trial_ends_at' => Yii::t('main', 'Окончание trial'),
            'cancelled_at' => Yii::t('main', 'Дата отмены'),
            'created_by' => Yii::t('main', 'Создал'),
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
     * Связь с функцией/аддоном
     */
    public function getFeature()
    {
        return $this->hasOne(SaasFeature::class, ['id' => 'feature_id']);
    }

    /**
     * Связь с пользователем, создавшим аддон
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_TRIAL => Yii::t('main', 'Пробный период'),
            self::STATUS_ACTIVE => Yii::t('main', 'Активен'),
            self::STATUS_EXPIRED => Yii::t('main', 'Истёк'),
            self::STATUS_CANCELLED => Yii::t('main', 'Отменён'),
        ];
    }

    /**
     * Название статуса
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    /**
     * CSS класс для статуса (badge)
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_TRIAL => 'badge-warning',
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_EXPIRED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-gray',
            default => 'badge-gray',
        };
    }

    /**
     * Список периодов биллинга
     */
    public static function getBillingPeriodList(): array
    {
        return [
            self::PERIOD_MONTHLY => Yii::t('main', 'Ежемесячно'),
            self::PERIOD_YEARLY => Yii::t('main', 'Ежегодно'),
        ];
    }

    /**
     * Активен ли аддон
     */
    public function isActive(): bool
    {
        if (!in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE])) {
            return false;
        }

        // Проверяем дату истечения
        if ($this->expires_at && strtotime($this->expires_at) < time()) {
            return false;
        }

        // Для trial проверяем дату окончания trial
        if ($this->status === self::STATUS_TRIAL && $this->trial_ends_at) {
            if (strtotime($this->trial_ends_at) < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * На пробном периоде
     */
    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    /**
     * Истёк ли аддон
     */
    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->expires_at && strtotime($this->expires_at) < time()) {
            return true;
        }

        if ($this->status === self::STATUS_TRIAL && $this->trial_ends_at) {
            return strtotime($this->trial_ends_at) < time();
        }

        return false;
    }

    /**
     * Дней до окончания
     */
    public function getDaysRemaining(): ?int
    {
        $endDate = $this->status === self::STATUS_TRIAL
            ? $this->trial_ends_at
            : $this->expires_at;

        if (!$endDate) {
            return null;
        }

        $diff = strtotime($endDate) - time();
        return max(0, (int)floor($diff / 86400));
    }

    /**
     * Скоро истекает (менее 7 дней)
     */
    public function isExpiringSoon(): bool
    {
        $days = $this->getDaysRemaining();
        return $days !== null && $days <= 7 && $days > 0;
    }

    /**
     * Получить значение как массив
     */
    public function getValueArray(): array
    {
        if (empty($this->value)) {
            return [];
        }
        return is_array($this->value)
            ? $this->value
            : json_decode($this->value, true) ?? [];
    }

    /**
     * Получить конкретное значение из value
     */
    public function getValue(string $key, $default = null)
    {
        $values = $this->getValueArray();
        return $values[$key] ?? $default;
    }

    /**
     * Установить значение в value
     */
    public function setValue(string $key, $value): void
    {
        $values = $this->getValueArray();
        $values[$key] = $value;
        $this->value = $values;
    }

    /**
     * Получить бонус лимита (для аддонов типа limit)
     */
    public function getLimitBonus(string $field): int
    {
        if (!$this->feature || $this->feature->type !== SaasFeature::TYPE_LIMIT) {
            return 0;
        }

        // Проверяем, относится ли этот аддон к данному полю
        $config = $this->getValue('limit_field');
        if ($config !== $field) {
            return 0;
        }

        $limitValue = $this->getValue('limit_value', 0);
        return (int)$limitValue * $this->quantity;
    }

    /**
     * Предоставляет ли аддон функцию
     */
    public function grantsFeature(string $featureCode): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if (!$this->feature) {
            return false;
        }

        return $this->feature->code === $featureCode;
    }

    /**
     * Активировать аддон
     */
    public function activate(string $period = self::PERIOD_MONTHLY): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->billing_period = $period;
        $this->started_at = date('Y-m-d H:i:s');

        if ($period === self::PERIOD_YEARLY) {
            $this->expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        } else {
            $this->expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
        }

        return $this->save();
    }

    /**
     * Начать пробный период
     */
    public function startTrial(?int $days = null): bool
    {
        if ($days === null && $this->feature) {
            $days = $this->feature->trial_days ?: 7;
        }
        $days = $days ?: 7;

        $this->status = self::STATUS_TRIAL;
        $this->started_at = date('Y-m-d H:i:s');
        $this->trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $this->expires_at = $this->trial_ends_at;

        return $this->save();
    }

    /**
     * Продлить аддон
     */
    public function renew(): bool
    {
        if ($this->billing_period === self::PERIOD_YEARLY) {
            $this->expires_at = date('Y-m-d H:i:s', strtotime($this->expires_at . ' +1 year'));
        } else {
            $this->expires_at = date('Y-m-d H:i:s', strtotime($this->expires_at . ' +1 month'));
        }

        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Отменить аддон
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Пометить как истёкший
     */
    public function markExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    /**
     * Найти активные аддоны организации
     */
    public static function findActiveByOrganization(int $organizationId): array
    {
        $addons = static::find()
            ->with('feature')
            ->where(['organization_id' => $organizationId])
            ->andWhere(['in', 'status', [self::STATUS_TRIAL, self::STATUS_ACTIVE]])
            ->all();

        // Фильтруем только реально активные (проверяем даты)
        return array_filter($addons, fn($addon) => $addon->isActive());
    }

    /**
     * Найти аддон по коду функции
     */
    public static function findByFeatureCode(int $organizationId, string $featureCode): ?self
    {
        return static::find()
            ->alias('oa')
            ->innerJoin(['f' => SaasFeature::tableName()], 'oa.feature_id = f.id')
            ->where(['oa.organization_id' => $organizationId])
            ->andWhere(['f.code' => $featureCode])
            ->andWhere(['in', 'oa.status', [self::STATUS_TRIAL, self::STATUS_ACTIVE]])
            ->one();
    }

    /**
     * Проверить, есть ли активный аддон для функции
     */
    public static function hasActiveAddon(int $organizationId, string $featureCode): bool
    {
        $addon = static::findByFeatureCode($organizationId, $featureCode);
        return $addon !== null && $addon->isActive();
    }

    /**
     * Получить суммарный бонус лимита от всех аддонов
     */
    public static function getTotalLimitBonus(int $organizationId, string $field): int
    {
        $addons = static::findActiveByOrganization($organizationId);
        $bonus = 0;

        foreach ($addons as $addon) {
            $bonus += $addon->getLimitBonus($field);
        }

        return $bonus;
    }

    /**
     * Создать аддон для организации
     */
    public static function create(
        int $organizationId,
        int $featureId,
        int $quantity = 1,
        string $period = self::PERIOD_MONTHLY,
        ?float $price = null,
        ?array $value = null
    ): self {
        $addon = new self();
        $addon->organization_id = $organizationId;
        $addon->feature_id = $featureId;
        $addon->quantity = $quantity;
        $addon->billing_period = $period;
        $addon->status = self::STATUS_ACTIVE;
        $addon->started_at = date('Y-m-d H:i:s');
        $addon->created_by = Yii::$app->user->id ?? null;

        if ($price !== null) {
            $addon->price = $price;
        } else {
            $feature = SaasFeature::findOne($featureId);
            if ($feature) {
                $addon->price = $period === self::PERIOD_YEARLY
                    ? $feature->addon_price_yearly
                    : $feature->addon_price_monthly;
            }
        }

        if ($value !== null) {
            $addon->value = $value;
        }

        // Устанавливаем дату истечения
        if ($period === self::PERIOD_YEARLY) {
            $addon->expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        } else {
            $addon->expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
        }

        return $addon;
    }

    /**
     * Форматированная цена
     */
    public function getFormattedPrice(): string
    {
        if (!$this->price) {
            return '-';
        }
        $period = $this->billing_period === self::PERIOD_YEARLY ? '/год' : '/мес';
        return number_format($this->price, 0, '.', ' ') . ' KZT' . $period;
    }

    /**
     * Полное название аддона
     */
    public function getFullName(): string
    {
        $name = $this->feature->name ?? 'Аддон #' . $this->feature_id;
        if ($this->quantity > 1) {
            $name .= ' x' . $this->quantity;
        }
        return $name;
    }
}
