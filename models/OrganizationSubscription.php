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
 * @property string|null $grace_period_ends_at
 * @property string $access_mode
 * @property string|null $trial_ends_at
 * @property string|null $cancelled_at
 * @property array|null $custom_limits
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property int|null $parent_subscription_id
 *
 * @property Organizations $organization
 * @property SaasPlan $plan
 * @property SaasPlan $saasPlan
 * @property OrganizationPayment[] $payments
 * @property OrganizationSubscription|null $parentSubscription
 * @property OrganizationSubscription[] $childSubscriptions
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

    // Режимы доступа
    const ACCESS_FULL = 'full';
    const ACCESS_LIMITED = 'limited';
    const ACCESS_READ_ONLY = 'read_only';
    const ACCESS_BLOCKED = 'blocked';

    // Grace period (дней)
    const GRACE_PERIOD_DAYS = 3;
    const READ_ONLY_PERIOD_DAYS = 7;

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
            [['organization_id', 'saas_plan_id', 'parent_subscription_id'], 'integer'],
            [['parent_subscription_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_TRIAL, self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_SUSPENDED, self::STATUS_CANCELLED]],
            [['billing_period'], 'string', 'max' => 20],
            [['billing_period'], 'in', 'range' => [self::PERIOD_MONTHLY, self::PERIOD_YEARLY]],
            [['access_mode'], 'string', 'max' => 20],
            [['access_mode'], 'in', 'range' => [self::ACCESS_FULL, self::ACCESS_LIMITED, self::ACCESS_READ_ONLY, self::ACCESS_BLOCKED]],
            [['access_mode'], 'default', 'value' => self::ACCESS_FULL],
            [['started_at', 'expires_at', 'trial_ends_at', 'cancelled_at', 'grace_period_ends_at'], 'safe'],
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
            'grace_period_ends_at' => Yii::t('main', 'Окончание grace периода'),
            'access_mode' => Yii::t('main', 'Режим доступа'),
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
     * Связь с родительской подпиской (для филиалов)
     */
    public function getParentSubscription()
    {
        return $this->hasOne(self::class, ['id' => 'parent_subscription_id']);
    }

    /**
     * Связь с дочерними подписками (подписки филиалов)
     */
    public function getChildSubscriptions()
    {
        return $this->hasMany(self::class, ['parent_subscription_id' => 'id']);
    }

    /**
     * Является ли подписка родительской (есть дочерние)
     */
    public function isParentSubscription(): bool
    {
        return $this->getChildSubscriptions()->exists();
    }

    /**
     * Является ли подписка дочерней (филиала)
     */
    public function isBranchSubscription(): bool
    {
        return $this->parent_subscription_id !== null;
    }

    /**
     * Создать подписку для филиала
     * Полная цена плана, скидка применяется при оплате
     */
    public static function createForBranch(
        int $branchId,
        int $planId,
        ?int $parentSubscriptionId = null
    ): self {
        $plan = SaasPlan::findOne($planId);

        $subscription = new self();
        $subscription->organization_id = $branchId;
        $subscription->saas_plan_id = $planId;
        $subscription->parent_subscription_id = $parentSubscriptionId;
        $subscription->status = self::STATUS_TRIAL;
        $subscription->billing_period = self::PERIOD_MONTHLY;
        $subscription->started_at = date('Y-m-d H:i:s');

        // Если есть триальный период, устанавливаем его
        if ($plan && $plan->trial_days > 0) {
            $subscription->trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$plan->trial_days} days"));
            $subscription->expires_at = $subscription->trial_ends_at;
        } else {
            // Без триала - активируем сразу, но срок истекает через месяц
            $subscription->status = self::STATUS_ACTIVE;
            $subscription->expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
        }

        return $subscription;
    }

    /**
     * Найти все активные подписки филиалов для HEAD организации
     */
    public static function findBranchSubscriptions(int $headSubscriptionId): array
    {
        return static::find()
            ->andWhere(['parent_subscription_id' => $headSubscriptionId])
            ->andWhere(['in', 'status', [self::STATUS_TRIAL, self::STATUS_ACTIVE]])
            ->all();
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

    /**
     * Алиас для saasPlan для удобства
     */
    public function getPlan()
    {
        return $this->getSaasPlan();
    }

    /**
     * Получить месячную цену подписки
     */
    public function getMonthlyPrice(): float
    {
        if (!$this->saasPlan) {
            return 0;
        }

        if ($this->billing_period === self::PERIOD_YEARLY) {
            return round($this->saasPlan->price_yearly / 12, 2);
        }

        return (float)$this->saasPlan->price_monthly;
    }

    // ==================== Grace Period методы ====================

    /**
     * Список режимов доступа
     */
    public static function getAccessModeList(): array
    {
        return [
            self::ACCESS_FULL => 'Полный доступ',
            self::ACCESS_LIMITED => 'Ограниченный',
            self::ACCESS_READ_ONLY => 'Только чтение',
            self::ACCESS_BLOCKED => 'Заблокирован',
        ];
    }

    /**
     * Название режима доступа
     */
    public function getAccessModeLabel(): string
    {
        return self::getAccessModeList()[$this->access_mode] ?? $this->access_mode;
    }

    /**
     * CSS класс для режима доступа
     */
    public function getAccessModeBadgeClass(): string
    {
        return match ($this->access_mode) {
            self::ACCESS_FULL => 'badge-success',
            self::ACCESS_LIMITED => 'badge-warning',
            self::ACCESS_READ_ONLY => 'badge-secondary',
            self::ACCESS_BLOCKED => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Находится ли в grace периоде
     */
    public function isInGracePeriod(): bool
    {
        if ($this->status !== self::STATUS_EXPIRED) {
            return false;
        }

        if ($this->grace_period_ends_at) {
            return strtotime($this->grace_period_ends_at) > time();
        }

        // Если grace_period_ends_at не установлен, считаем по expires_at
        if ($this->expires_at) {
            $daysSinceExpired = $this->getDaysSinceExpired();
            return $daysSinceExpired <= self::GRACE_PERIOD_DAYS;
        }

        return false;
    }

    /**
     * Дней с момента истечения
     */
    public function getDaysSinceExpired(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        $expiresAt = strtotime($this->expires_at);
        $now = time();

        if ($now <= $expiresAt) {
            return 0;
        }

        return (int)floor(($now - $expiresAt) / 86400);
    }

    /**
     * Дней до окончания grace периода
     */
    public function getDaysUntilGraceEnds(): ?int
    {
        if (!$this->isInGracePeriod()) {
            return null;
        }

        if ($this->grace_period_ends_at) {
            $diff = strtotime($this->grace_period_ends_at) - time();
            return max(0, (int)ceil($diff / 86400));
        }

        $daysSinceExpired = $this->getDaysSinceExpired();
        return max(0, self::GRACE_PERIOD_DAYS - $daysSinceExpired);
    }

    /**
     * Запустить grace период
     */
    public function startGracePeriod(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        $this->access_mode = self::ACCESS_LIMITED;
        $this->grace_period_ends_at = date('Y-m-d H:i:s', strtotime('+' . self::GRACE_PERIOD_DAYS . ' days'));

        return $this->save(false, ['status', 'access_mode', 'grace_period_ends_at']);
    }

    /**
     * Перевести в режим read_only
     */
    public function setReadOnlyMode(): bool
    {
        $this->access_mode = self::ACCESS_READ_ONLY;
        return $this->save(false, ['access_mode']);
    }

    /**
     * Заблокировать доступ
     */
    public function block(): bool
    {
        $this->access_mode = self::ACCESS_BLOCKED;
        return $this->save(false, ['access_mode']);
    }

    /**
     * Восстановить полный доступ (после оплаты)
     */
    public function restoreFullAccess(): bool
    {
        $this->access_mode = self::ACCESS_FULL;
        $this->grace_period_ends_at = null;
        return $this->save(false, ['access_mode', 'grace_period_ends_at']);
    }

    /**
     * Можно ли создавать записи
     */
    public function canCreate(): bool
    {
        return $this->access_mode === self::ACCESS_FULL;
    }

    /**
     * Можно ли редактировать
     */
    public function canUpdate(): bool
    {
        return in_array($this->access_mode, [self::ACCESS_FULL, self::ACCESS_LIMITED]);
    }

    /**
     * Можно ли просматривать
     */
    public function canView(): bool
    {
        return $this->access_mode !== self::ACCESS_BLOCKED;
    }

    /**
     * Обновить режим доступа на основе текущего состояния
     */
    public function updateAccessMode(): bool
    {
        $newMode = $this->calculateAccessMode();

        if ($this->access_mode !== $newMode) {
            $this->access_mode = $newMode;
            return $this->save(false, ['access_mode']);
        }

        return true;
    }

    /**
     * Рассчитать режим доступа
     */
    public function calculateAccessMode(): string
    {
        // Активная или триал подписка
        if (in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL])) {
            if (!$this->isExpired()) {
                return self::ACCESS_FULL;
            }
        }

        // Отменённая подписка
        if ($this->status === self::STATUS_CANCELLED) {
            return self::ACCESS_READ_ONLY;
        }

        // Истёкшая подписка - проверяем grace период
        $daysSinceExpired = $this->getDaysSinceExpired();

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS) {
            return self::ACCESS_LIMITED;
        }

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS + self::READ_ONLY_PERIOD_DAYS) {
            return self::ACCESS_READ_ONLY;
        }

        return self::ACCESS_BLOCKED;
    }

    /**
     * Скоро истекает (с указанием дней)
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        $remaining = $this->getDaysRemaining();
        return $remaining !== null && $remaining <= $days && $remaining > 0;
    }

    /**
     * Продлить подписку
     */
    public function renew(string $period = null): bool
    {
        $period = $period ?? $this->billing_period;

        $this->status = self::STATUS_ACTIVE;
        $this->billing_period = $period;
        $this->access_mode = self::ACCESS_FULL;
        $this->grace_period_ends_at = null;

        // Если подписка ещё активна, продлеваем от текущей даты окончания
        $baseDate = ($this->expires_at && strtotime($this->expires_at) > time())
            ? $this->expires_at
            : date('Y-m-d H:i:s');

        if ($period === self::PERIOD_YEARLY) {
            $this->expires_at = date('Y-m-d H:i:s', strtotime($baseDate . ' +1 year'));
        } else {
            $this->expires_at = date('Y-m-d H:i:s', strtotime($baseDate . ' +1 month'));
        }

        return $this->save();
    }

    /**
     * Найти подписку организации (активную или истёкшую)
     */
    public static function findByOrganization(int $organizationId): ?self
    {
        return static::find()
            ->andWhere(['organization_id' => $organizationId])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    /**
     * Получить статистику по подпискам для SuperAdmin
     */
    public static function getStatistics(): array
    {
        return [
            'total' => static::find()->count(),
            'active' => static::find()->where(['status' => self::STATUS_ACTIVE])->count(),
            'trial' => static::find()->where(['status' => self::STATUS_TRIAL])->count(),
            'expired' => static::find()->where(['status' => self::STATUS_EXPIRED])->count(),
            'cancelled' => static::find()->where(['status' => self::STATUS_CANCELLED])->count(),
            'expiring_soon' => static::find()
                ->where(['status' => self::STATUS_ACTIVE])
                ->andWhere(['between', 'expires_at', date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))])
                ->count(),
            'by_access_mode' => [
                self::ACCESS_FULL => static::find()->where(['access_mode' => self::ACCESS_FULL])->count(),
                self::ACCESS_LIMITED => static::find()->where(['access_mode' => self::ACCESS_LIMITED])->count(),
                self::ACCESS_READ_ONLY => static::find()->where(['access_mode' => self::ACCESS_READ_ONLY])->count(),
                self::ACCESS_BLOCKED => static::find()->where(['access_mode' => self::ACCESS_BLOCKED])->count(),
            ],
        ];
    }
}
