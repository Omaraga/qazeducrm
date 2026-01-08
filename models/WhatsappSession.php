<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель сессии WhatsApp (подключение организации к WhatsApp)
 *
 * @property int $id
 * @property int $organization_id
 * @property string $instance_name
 * @property string|null $phone_number
 * @property string $status
 * @property string|null $qr_code
 * @property string|null $qr_code_updated_at
 * @property string|null $connected_at
 * @property string|null $disconnected_at
 * @property string|null $webhook_url
 * @property array|null $info
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property WhatsappMessage[] $messages
 * @property WhatsappChat[] $chats
 */
class WhatsappSession extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Статусы подключения
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_CONNECTING = 'connecting';
    const STATUS_CONNECTED = 'connected';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%whatsapp_session}}';
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
                'updatedAtAttribute' => 'updated_at',
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
            [['organization_id', 'instance_name'], 'required'],
            [['organization_id', 'is_deleted'], 'integer'],
            [['qr_code', 'info'], 'safe'],
            [['qr_code_updated_at', 'connected_at', 'disconnected_at'], 'safe'],
            [['instance_name'], 'string', 'max' => 100],
            [['phone_number'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 20],
            [['webhook_url'], 'string', 'max' => 500],
            [['instance_name'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_DISCONNECTED],
            [['status'], 'in', 'range' => [self::STATUS_DISCONNECTED, self::STATUS_CONNECTING, self::STATUS_CONNECTED]],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['organization_id' => 'id']],
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
            'instance_name' => 'Имя инстанса',
            'phone_number' => 'Номер телефона',
            'status' => 'Статус',
            'qr_code' => 'QR код',
            'connected_at' => 'Подключен',
            'disconnected_at' => 'Отключен',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(WhatsappMessage::class, ['session_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChats()
    {
        return $this->hasMany(WhatsappChat::class, ['session_id' => 'id']);
    }

    /**
     * Проверка подключена ли сессия
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    /**
     * Генерация уникального имени инстанса для организации
     * @param int $organizationId
     * @return string
     */
    public static function generateInstanceName(int $organizationId): string
    {
        return 'org_' . $organizationId . '_' . time();
    }

    /**
     * Получить сессию текущей организации
     * @return WhatsappSession|null
     */
    public static function getCurrentSession(): ?WhatsappSession
    {
        $orgId = Organizations::getCurrentOrganizationId();
        if (!$orgId) {
            return null;
        }

        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->one();
    }

    /**
     * Обновить статус подключения
     * @param string $status
     * @param array $data Дополнительные данные
     * @return bool
     */
    public function updateConnectionStatus(string $status, array $data = []): bool
    {
        $this->status = $status;

        if ($status === self::STATUS_CONNECTED) {
            $this->connected_at = new Expression('NOW()');
            $this->disconnected_at = null;
            if (isset($data['phone_number'])) {
                $this->phone_number = $data['phone_number'];
            }
        } elseif ($status === self::STATUS_DISCONNECTED) {
            $this->disconnected_at = new Expression('NOW()');
        }

        if (isset($data['qr_code'])) {
            $this->qr_code = $data['qr_code'];
            $this->qr_code_updated_at = new Expression('NOW()');
        }

        if (isset($data['info'])) {
            $this->info = json_encode($data['info']);
        }

        return $this->save(false);
    }

    /**
     * Получить метку статуса
     * @return string
     */
    public function getStatusLabel(): string
    {
        $labels = [
            self::STATUS_DISCONNECTED => 'Отключен',
            self::STATUS_CONNECTING => 'Подключение...',
            self::STATUS_CONNECTED => 'Подключен',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Получить цвет статуса для UI
     * @return string
     */
    public function getStatusColor(): string
    {
        $colors = [
            self::STATUS_DISCONNECTED => 'gray',
            self::STATUS_CONNECTING => 'yellow',
            self::STATUS_CONNECTED => 'green',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Количество непрочитанных сообщений
     * @return int
     */
    public function getUnreadCount(): int
    {
        return WhatsappChat::find()
            ->where(['session_id' => $this->id])
            ->andWhere(['>', 'unread_count', 0])
            ->sum('unread_count') ?? 0;
    }
}
