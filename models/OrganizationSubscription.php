<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель подписок организаций.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $saas_plan_id
 * @property string $status
 * @property string $billing_period
 * @property string|null $started_at
 * @property string|null $expires_at
 * @property string|null $trial_ends_at
 * @property string|null $cancelled_at
 * @property array|null $custom_limits
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property SaasPlan $saasPlan
 * @property OrganizationPayment[] $payments
 */
class OrganizationSubscription extends ActiveRecord
{
    // Статусы подписки
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';

    // Периоды биллинга
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_subscription}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'saas_plan_id'], 'required'],
            [['organization_id', 'saas_plan_id'], 'integer'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_TRIAL, self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_SUSPENDED, self::STATUS_CANCELLED]],
            [['billing_period'], 'string', 'max' => 20],
            [['billing_period'], 'in', 'range' => [self::PERIOD_MONTHLY, self::PERIOD_YEARLY]],
            [['started_at', 'expires_at', 'trial_ends_at', 'cancelled_at'], 'safe'],
            [['custom_limits'], 'safe'],
            [['notes'], 'string'],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            [['saas_plan_id'], 'exist', 'targetClass' => SaasPlan::class, 'targetAttribute' => 'id'],
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
            'saas_plan_id' => Yii::t('main', 'Тарифный план'),
            'status' => Yii::t('main', 'Статус'),
            'billing_period' => Yii::t('main', 'Период биллинга'),
            'started_at' => Yii::t('main', 'Начало подписки'),
            'expires_at' => Yii::t('main', 'Окончание подписки'),
            'trial_ends_at' => Yii::t('main', 'Окончание пробного периода'),
            'cancelled_at' => Yii::t('main', 'Дата отмены'),
            'custom_limits' => Yii::t('main', 'Кастомные лимиты'),
            'notes' => Yii::t('main', 'Заметки'),
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
     * Связь с тарифным планом
     */
    public function getSaasPlan()
    {
        return $this->hasOne(SaasPlan::class, ['id' => 'saas_plan_id']);
    }

    /**
     * Связь с платежами
     */
    public function getPayments()
    {
        return $this->hasMany(OrganizationPayment::class, ['subscription_id' => 'id']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_TRIAL => Yii::t('main', 'Пробный период'),
            self::STATUS_ACTIVE => Yii::t('main', 'Активна'),
            self::STATUS_EXPIRED => Yii::t('main', 'Истекла'),
            self::STATUS_SUSPENDED => Yii::t('main', 'Приостановлена'),
            self::STATUS_CANCELLED => Yii::t('main', 'Отменена'),
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
     * Активна ли подписка
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE]);
    }

    /**
     * На пробном периоде
     */
    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    /**
     * Истекла ли подписка
     */
    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->expires_at && strtotime($this->expires_at) < time()) {
            return true;
        }

        return false;
    }

    /**
     * Дней до окончания
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        $diff = strtotime($this->expires_at) - time();
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
     * Получить эффективный лимит (кастомный или из плана)
     */
    public function getLimit(string $field): int
    {
        $customLimits = $this->getCustomLimitsArray();

        if (isset($customLimits[$field])) {
            return (int)$customLimits[$field];
        }

        return $this->saasPlan ? (int)$this->saasPlan->$field : 0;
    }

    /**
     * Получить массив кастомных лимитов
     */
    public function getCustomLimitsArray(): array
    {
        if (empty($this->custom_limits)) {
            return [];
        }
        return is_array($this->custom_limits) ? $this->custom_limits : json_decode($this->custom_limits, true) ?? [];
    }

    /**
     * Создать пробную подписку для организации
     */
    public static function createTrial(int $organizationId, int $planId): self
    {
        $plan = SaasPlan::findOne($planId);

        $subscription = new self();
        $subscription->organization_id = $organizationId;
        $subscription->saas_plan_id = $planId;
        $subscription->status = self::STATUS_TRIAL;
        $subscription->billing_period = self::PERIOD_MONTHLY;
        $subscription->started_at = date('Y-m-d H:i:s');
        $subscription->trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$plan->trial_days} days"));
        $subscription->expires_at = $subscription->trial_ends_at;

        return $subscription;
    }

    /**
     * Активировать подписку после оплаты
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
     * Приостановить подписку
     */
    public function suspend(): bool
    {
        $this->status = self::STATUS_SUSPENDED;
        return $this->save();
    }

    /**
     * Отменить подписку
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Найти активную подписку организации
     */
    public static function findActiveByOrganization(int $organizationId): ?self
    {
        return static::find()
            ->andWhere(['organization_id' => $organizationId])
            ->andWhere(['in', 'status', [self::STATUS_TRIAL, self::STATUS_ACTIVE]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}
