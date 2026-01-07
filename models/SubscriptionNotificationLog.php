<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель логов уведомлений о подписках
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $subscription_id
 * @property string $type Тип уведомления
 * @property string $channel Канал: email, sms, in_app
 * @property string|null $recipient Email или телефон
 * @property string|null $subject Тема письма
 * @property string|null $message Текст сообщения
 * @property array|null $metadata Дополнительные данные
 * @property string $status sent, failed, pending
 * @property string|null $error_message
 * @property string $sent_at
 * @property string $created_at
 *
 * @property Organizations $organization
 * @property OrganizationSubscription $subscription
 */
class SubscriptionNotificationLog extends ActiveRecord
{
    // Типы уведомлений
    const TYPE_SUBSCRIPTION_EXPIRING = 'subscription_expiring';     // Подписка скоро истекает
    const TYPE_SUBSCRIPTION_EXPIRED = 'subscription_expired';       // Подписка истекла
    const TYPE_TRIAL_ENDING = 'trial_ending';                       // Триал заканчивается
    const TYPE_TRIAL_ENDED = 'trial_ended';                         // Триал закончился
    const TYPE_LIMIT_WARNING = 'limit_warning';                     // Приближение к лимиту (80%)
    const TYPE_LIMIT_REACHED = 'limit_reached';                     // Лимит достигнут (100%)
    const TYPE_GRACE_PERIOD_START = 'grace_period_start';           // Начало grace периода
    const TYPE_GRACE_PERIOD_ENDING = 'grace_period_ending';         // Grace период заканчивается
    const TYPE_ACCESS_RESTRICTED = 'access_restricted';             // Доступ ограничен
    const TYPE_PAYMENT_REMINDER = 'payment_reminder';               // Напоминание об оплате
    const TYPE_PAYMENT_RECEIVED = 'payment_received';               // Платёж получен
    const TYPE_UPGRADE_SUGGESTION = 'upgrade_suggestion';           // Предложение апгрейда

    // Каналы
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_IN_APP = 'in_app';

    // Статусы
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%subscription_notification_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'type', 'channel'], 'required'],
            [['organization_id', 'subscription_id'], 'integer'],
            [['type', 'status'], 'string', 'max' => 50],
            [['channel'], 'string', 'max' => 20],
            [['recipient', 'subject'], 'string', 'max' => 255],
            [['message', 'error_message'], 'string'],
            [['metadata'], 'safe'],
            [['sent_at', 'created_at'], 'safe'],
            [['channel'], 'in', 'range' => [self::CHANNEL_EMAIL, self::CHANNEL_SMS, self::CHANNEL_IN_APP]],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_FAILED]],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'subscription_id' => 'Подписка',
            'type' => 'Тип',
            'channel' => 'Канал',
            'recipient' => 'Получатель',
            'subject' => 'Тема',
            'message' => 'Сообщение',
            'metadata' => 'Метаданные',
            'status' => 'Статус',
            'error_message' => 'Ошибка',
            'sent_at' => 'Отправлено',
            'created_at' => 'Создано',
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
     * Связь с подпиской
     */
    public function getSubscription()
    {
        return $this->hasOne(OrganizationSubscription::class, ['id' => 'subscription_id']);
    }

    /**
     * Список типов уведомлений
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_SUBSCRIPTION_EXPIRING => 'Подписка истекает',
            self::TYPE_SUBSCRIPTION_EXPIRED => 'Подписка истекла',
            self::TYPE_TRIAL_ENDING => 'Триал заканчивается',
            self::TYPE_TRIAL_ENDED => 'Триал закончился',
            self::TYPE_LIMIT_WARNING => 'Предупреждение о лимите',
            self::TYPE_LIMIT_REACHED => 'Лимит достигнут',
            self::TYPE_GRACE_PERIOD_START => 'Начало grace периода',
            self::TYPE_GRACE_PERIOD_ENDING => 'Grace период заканчивается',
            self::TYPE_ACCESS_RESTRICTED => 'Доступ ограничен',
            self::TYPE_PAYMENT_REMINDER => 'Напоминание об оплате',
            self::TYPE_PAYMENT_RECEIVED => 'Платёж получен',
            self::TYPE_UPGRADE_SUGGESTION => 'Предложение апгрейда',
        ];
    }

    /**
     * Название типа
     */
    public function getTypeLabel(): string
    {
        return self::getTypeList()[$this->type] ?? $this->type;
    }

    /**
     * Список каналов
     */
    public static function getChannelList(): array
    {
        return [
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_IN_APP => 'В приложении',
        ];
    }

    /**
     * Название канала
     */
    public function getChannelLabel(): string
    {
        return self::getChannelList()[$this->channel] ?? $this->channel;
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_SENT => 'Отправлено',
            self::STATUS_FAILED => 'Ошибка',
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
     * CSS класс статуса
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SENT => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            self::STATUS_PENDING => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Пометить как отправленное
     */
    public function markAsSent(): bool
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = date('Y-m-d H:i:s');
        return $this->save(false, ['status', 'sent_at']);
    }

    /**
     * Пометить как ошибка
     */
    public function markAsFailed(string $error): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $error;
        return $this->save(false, ['status', 'error_message']);
    }

    /**
     * Создать запись лога
     */
    public static function log(
        int $organizationId,
        string $type,
        string $channel,
        ?string $recipient = null,
        ?string $subject = null,
        ?string $message = null,
        ?array $metadata = null,
        ?int $subscriptionId = null
    ): self {
        $log = new self();
        $log->organization_id = $organizationId;
        $log->subscription_id = $subscriptionId;
        $log->type = $type;
        $log->channel = $channel;
        $log->recipient = $recipient;
        $log->subject = $subject;
        $log->message = $message;
        $log->metadata = $metadata;
        $log->status = self::STATUS_PENDING;
        $log->save();

        return $log;
    }

    /**
     * Проверить, было ли уведомление данного типа отправлено недавно
     */
    public static function wasRecentlySent(
        int $organizationId,
        string $type,
        int $withinHours = 24
    ): bool {
        $since = date('Y-m-d H:i:s', strtotime("-{$withinHours} hours"));

        return self::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['type' => $type])
            ->andWhere(['status' => self::STATUS_SENT])
            ->andWhere(['>=', 'sent_at', $since])
            ->exists();
    }

    /**
     * Получить последнее уведомление определённого типа
     */
    public static function getLastByType(int $organizationId, string $type): ?self
    {
        return self::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['type' => $type])
            ->orderBy(['sent_at' => SORT_DESC])
            ->one();
    }

    /**
     * Получить metadata как массив
     */
    public function getMetadataArray(): array
    {
        if (empty($this->metadata)) {
            return [];
        }
        return is_array($this->metadata)
            ? $this->metadata
            : json_decode($this->metadata, true) ?? [];
    }
}
