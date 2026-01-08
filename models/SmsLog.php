<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\HasStatusTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель логов SMS
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $template_id
 * @property string $phone
 * @property string $message
 * @property int $status
 * @property string|null $provider_id
 * @property string|null $provider_response
 * @property string|null $error_message
 * @property string|null $recipient_type
 * @property int|null $recipient_id
 * @property string|null $sent_at
 * @property string|null $delivered_at
 * @property string $created_at
 *
 * @property SmsTemplate $template
 */
class SmsLog extends ActiveRecord
{
    use HasStatusTrait;
    // Статусы
    const STATUS_PENDING = 1;    // В очереди
    const STATUS_SENT = 2;       // Отправлено
    const STATUS_DELIVERED = 3;  // Доставлено
    const STATUS_FAILED = 4;     // Ошибка

    // Типы получателей
    const RECIPIENT_PUPIL = 'pupil';
    const RECIPIENT_PARENT = 'parent';
    const RECIPIENT_TEACHER = 'teacher';
    const RECIPIENT_LEAD = 'lead';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone', 'message'], 'required'],
            [['organization_id', 'template_id', 'status', 'recipient_id'], 'integer'],
            [['message', 'provider_response'], 'string'],
            [['phone'], 'string', 'max' => 20],
            [['provider_id'], 'string', 'max' => 100],
            [['error_message'], 'string', 'max' => 255],
            [['recipient_type'], 'string', 'max' => 20],
            [['sent_at', 'delivered_at'], 'safe'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_FAILED]],
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
            'template_id' => 'Шаблон',
            'phone' => 'Телефон',
            'message' => 'Сообщение',
            'status' => 'Статус',
            'provider_id' => 'ID провайдера',
            'provider_response' => 'Ответ провайдера',
            'error_message' => 'Ошибка',
            'recipient_type' => 'Тип получателя',
            'recipient_id' => 'ID получателя',
            'sent_at' => 'Отправлено',
            'delivered_at' => 'Доставлено',
            'created_at' => 'Создано',
        ];
    }

    /**
     * Связь с шаблоном
     */
    public function getTemplate()
    {
        return $this->hasOne(SmsTemplate::class, ['id' => 'template_id']);
    }

    /**
     * Список статусов
     * @see HasStatusTrait
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'В очереди',
            self::STATUS_SENT => 'Отправлено',
            self::STATUS_DELIVERED => 'Доставлено',
            self::STATUS_FAILED => 'Ошибка',
        ];
    }

    /**
     * Цвета статусов
     * @see HasStatusTrait::getStatusColor()
     */
    public static function getStatusColors(): array
    {
        return [
            self::STATUS_PENDING => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_FAILED => 'red',
        ];
    }

    /**
     * CSS класс для бейджа (Tailwind)
     */
    public function getStatusBadgeClass(): string
    {
        $classes = [
            self::STATUS_PENDING => 'badge badge-secondary',
            self::STATUS_SENT => 'badge badge-info',
            self::STATUS_DELIVERED => 'badge badge-success',
            self::STATUS_FAILED => 'badge badge-danger',
        ];
        return $classes[$this->status] ?? 'badge badge-secondary';
    }

    // getStatusLabel(), getStatusColor() предоставляются HasStatusTrait

    /**
     * Отметить как отправленное
     */
    public function markAsSent($providerId = null, $response = null)
    {
        $this->status = self::STATUS_SENT;
        $this->provider_id = $providerId;
        $this->provider_response = $response;
        $this->sent_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Отметить как доставленное
     */
    public function markAsDelivered()
    {
        $this->status = self::STATUS_DELIVERED;
        $this->delivered_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Отметить как ошибку
     */
    public function markAsFailed($errorMessage, $response = null)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->provider_response = $response;
        return $this->save(false);
    }

    /**
     * Создать запись в логе
     */
    public static function log($phone, $message, $templateId = null, $recipientType = null, $recipientId = null)
    {
        $log = new self();
        $log->organization_id = Organizations::getCurrentOrganizationId();
        $log->template_id = $templateId;
        $log->phone = $phone;
        $log->message = $message;
        $log->recipient_type = $recipientType;
        $log->recipient_id = $recipientId;
        $log->status = self::STATUS_PENDING;
        $log->save();
        return $log;
    }
}
