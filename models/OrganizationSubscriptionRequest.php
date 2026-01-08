<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель запросов на изменение подписки от организаций.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $request_type
 * @property int|null $current_plan_id
 * @property int|null $requested_plan_id
 * @property string|null $billing_period
 * @property int|null $addon_id
 * @property string|null $comment
 * @property string|null $contact_phone
 * @property string|null $contact_name
 * @property string $status
 * @property string|null $admin_comment
 * @property int|null $processed_by
 * @property string|null $processed_at
 * @property int|null $created_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property SaasPlan $currentPlan
 * @property SaasPlan $requestedPlan
 * @property User $processedByUser
 * @property User $createdByUser
 */
class OrganizationSubscriptionRequest extends \yii\db\ActiveRecord
{
    // Типы запросов
    const TYPE_RENEWAL = 'renewal';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_DOWNGRADE = 'downgrade';
    const TYPE_TRIAL_CONVERT = 'trial_convert';
    const TYPE_ADDON = 'addon';

    // Статусы
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_subscription_request}}';
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
            [['organization_id', 'request_type'], 'required'],
            [['organization_id', 'current_plan_id', 'requested_plan_id', 'addon_id', 'processed_by', 'created_by'], 'integer'],
            [['comment', 'admin_comment'], 'string'],
            [['processed_at', 'created_at', 'updated_at'], 'safe'],
            [['request_type'], 'string', 'max' => 20],
            [['request_type'], 'in', 'range' => [self::TYPE_RENEWAL, self::TYPE_UPGRADE, self::TYPE_DOWNGRADE, self::TYPE_TRIAL_CONVERT, self::TYPE_ADDON]],
            [['billing_period'], 'string', 'max' => 10],
            [['billing_period'], 'in', 'range' => ['monthly', 'yearly']],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_COMPLETED]],
            [['contact_phone'], 'string', 'max' => 20],
            [['contact_name'], 'string', 'max' => 100],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['organization_id' => 'id']],
            [['current_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => SaasPlan::class, 'targetAttribute' => ['current_plan_id' => 'id']],
            [['requested_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => SaasPlan::class, 'targetAttribute' => ['requested_plan_id' => 'id']],
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
            'request_type' => Yii::t('main', 'Тип запроса'),
            'current_plan_id' => Yii::t('main', 'Текущий план'),
            'requested_plan_id' => Yii::t('main', 'Запрашиваемый план'),
            'billing_period' => Yii::t('main', 'Период оплаты'),
            'addon_id' => Yii::t('main', 'Дополнение'),
            'comment' => Yii::t('main', 'Комментарий'),
            'contact_phone' => Yii::t('main', 'Контактный телефон'),
            'contact_name' => Yii::t('main', 'Контактное имя'),
            'status' => Yii::t('main', 'Статус'),
            'admin_comment' => Yii::t('main', 'Комментарий администратора'),
            'processed_by' => Yii::t('main', 'Обработал'),
            'processed_at' => Yii::t('main', 'Дата обработки'),
            'created_by' => Yii::t('main', 'Создал'),
            'created_at' => Yii::t('main', 'Дата создания'),
            'updated_at' => Yii::t('main', 'Дата обновления'),
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
     * Связь с текущим планом
     */
    public function getCurrentPlan()
    {
        return $this->hasOne(SaasPlan::class, ['id' => 'current_plan_id']);
    }

    /**
     * Связь с запрашиваемым планом
     */
    public function getRequestedPlan()
    {
        return $this->hasOne(SaasPlan::class, ['id' => 'requested_plan_id']);
    }

    /**
     * Связь с обработавшим пользователем
     */
    public function getProcessedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'processed_by']);
    }

    /**
     * Связь с создавшим пользователем
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Список типов запросов
     */
    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_RENEWAL => Yii::t('main', 'Продление'),
            self::TYPE_UPGRADE => Yii::t('main', 'Повышение тарифа'),
            self::TYPE_DOWNGRADE => Yii::t('main', 'Понижение тарифа'),
            self::TYPE_TRIAL_CONVERT => Yii::t('main', 'Конвертация trial'),
            self::TYPE_ADDON => Yii::t('main', 'Покупка дополнения'),
        ];
    }

    /**
     * Список статусов
     */
    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => Yii::t('main', 'Ожидает'),
            self::STATUS_APPROVED => Yii::t('main', 'Одобрен'),
            self::STATUS_REJECTED => Yii::t('main', 'Отклонён'),
            self::STATUS_COMPLETED => Yii::t('main', 'Выполнен'),
        ];
    }

    /**
     * Получить название типа запроса
     */
    public function getTypeLabel(): string
    {
        return self::getTypeLabels()[$this->request_type] ?? $this->request_type;
    }

    /**
     * Получить название статуса
     */
    public function getStatusLabel(): string
    {
        return self::getStatusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Получить CSS класс статуса для бейджа
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-info',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_COMPLETED => 'badge-success',
            default => 'badge-secondary',
        };
    }

    /**
     * Ожидает обработки?
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Одобрить запрос
     */
    public function approve(?string $adminComment = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->admin_comment = $adminComment;
        $this->processed_by = Yii::$app->has('user') ? Yii::$app->user->id : null;
        $this->processed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Отклонить запрос
     */
    public function reject(?string $adminComment = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->admin_comment = $adminComment;
        $this->processed_by = Yii::$app->has('user') ? Yii::$app->user->id : null;
        $this->processed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Отметить как выполненный
     */
    public function complete(?string $adminComment = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        if ($adminComment) {
            $this->admin_comment = $adminComment;
        }
        $this->processed_by = Yii::$app->has('user') ? Yii::$app->user->id : null;
        $this->processed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Создать запрос на продление
     */
    public static function createRenewalRequest(
        int $organizationId,
        ?int $planId = null,
        ?string $billingPeriod = null,
        ?string $comment = null,
        ?string $contactPhone = null,
        ?string $contactName = null
    ): self {
        $org = Organizations::findOne($organizationId);
        $subscription = ($org !== null ? $org->getActiveSubscription() : null);

        $request = new self();
        $request->organization_id = $organizationId;
        $request->request_type = self::TYPE_RENEWAL;
        $request->current_plan_id = ($subscription !== null ? $subscription->saas_plan_id : null);
        $request->requested_plan_id = $planId ?? ($subscription !== null ? $subscription->saas_plan_id : null);
        $request->billing_period = $billingPeriod ?? ($subscription !== null ? $subscription->billing_period : null);
        $request->comment = $comment;
        $request->contact_phone = $contactPhone;
        $request->contact_name = $contactName;
        $request->status = self::STATUS_PENDING;
        $request->created_by = Yii::$app->user->id ?? null;

        return $request;
    }

    /**
     * Создать запрос на конвертацию trial
     */
    public static function createTrialConvertRequest(
        int $organizationId,
        ?int $planId = null,
        ?string $billingPeriod = 'monthly',
        ?string $comment = null,
        ?string $contactPhone = null,
        ?string $contactName = null
    ): self {
        $org = Organizations::findOne($organizationId);
        $subscription = ($org !== null ? $org->getActiveSubscription() : null);

        $request = new self();
        $request->organization_id = $organizationId;
        $request->request_type = self::TYPE_TRIAL_CONVERT;
        $request->current_plan_id = ($subscription !== null ? $subscription->saas_plan_id : null);
        $request->requested_plan_id = $planId ?? ($subscription !== null ? $subscription->saas_plan_id : null);
        $request->billing_period = $billingPeriod;
        $request->comment = $comment;
        $request->contact_phone = $contactPhone;
        $request->contact_name = $contactName;
        $request->status = self::STATUS_PENDING;
        $request->created_by = Yii::$app->user->id ?? null;

        return $request;
    }

    /**
     * Создать запрос на апгрейд
     */
    public static function createUpgradeRequest(
        int $organizationId,
        int $requestedPlanId,
        ?string $billingPeriod = 'monthly',
        ?string $comment = null,
        ?string $contactPhone = null,
        ?string $contactName = null
    ): self {
        $org = Organizations::findOne($organizationId);
        $subscription = ($org !== null ? $org->getActiveSubscription() : null);

        $request = new self();
        $request->organization_id = $organizationId;
        $request->request_type = self::TYPE_UPGRADE;
        $request->current_plan_id = ($subscription !== null ? $subscription->saas_plan_id : null);
        $request->requested_plan_id = $requestedPlanId;
        $request->billing_period = $billingPeriod;
        $request->comment = $comment;
        $request->contact_phone = $contactPhone;
        $request->contact_name = $contactName;
        $request->status = self::STATUS_PENDING;
        $request->created_by = Yii::$app->user->id ?? null;

        return $request;
    }

    /**
     * Получить количество ожидающих запросов
     */
    public static function getPendingCount(): int
    {
        return self::find()->where(['status' => self::STATUS_PENDING])->count();
    }
}
