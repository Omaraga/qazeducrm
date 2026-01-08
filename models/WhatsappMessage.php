<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель сообщения WhatsApp
 *
 * @property int $id
 * @property int $organization_id
 * @property int $session_id
 * @property int|null $lid_id
 * @property string $remote_jid
 * @property string|null $remote_phone
 * @property string|null $remote_name
 * @property string $direction
 * @property string $message_type
 * @property string|null $content
 * @property string|null $media_url
 * @property string|null $media_mimetype
 * @property string|null $media_filename
 * @property string $status
 * @property string|null $whatsapp_id
 * @property string|null $whatsapp_timestamp
 * @property bool $is_from_me
 * @property bool $is_read
 * @property string|null $read_at
 * @property int|null $read_by
 * @property array|null $info
 * @property int $is_deleted
 * @property string $created_at
 *
 * @property WhatsappSession $session
 * @property Lids $lid
 * @property User $readByUser
 */
class WhatsappMessage extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Направление сообщения
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    // Типы сообщений
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';
    const TYPE_STICKER = 'sticker';
    const TYPE_LOCATION = 'location';
    const TYPE_CONTACT = 'contact';

    // Статусы сообщений
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%whatsapp_message}}';
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
            [['organization_id', 'session_id', 'remote_jid', 'direction'], 'required'],
            [['organization_id', 'session_id', 'lid_id', 'read_by', 'is_deleted'], 'integer'],
            [['content', 'info'], 'safe'],
            [['whatsapp_timestamp', 'read_at'], 'safe'],
            [['is_from_me', 'is_read'], 'boolean'],
            [['remote_jid', 'whatsapp_id', 'media_mimetype'], 'string', 'max' => 100],
            [['remote_phone'], 'string', 'max' => 20],
            [['remote_name', 'media_filename'], 'string', 'max' => 255],
            [['direction'], 'string', 'max' => 10],
            [['message_type', 'status'], 'string', 'max' => 20],
            [['media_url'], 'string', 'max' => 500],
            [['message_type'], 'default', 'value' => self::TYPE_TEXT],
            [['status'], 'default', 'value' => self::STATUS_SENT],
            [['is_from_me'], 'default', 'value' => false],
            [['is_read'], 'default', 'value' => false],
            [['direction'], 'in', 'range' => [self::DIRECTION_INCOMING, self::DIRECTION_OUTGOING]],
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
            'session_id' => 'Сессия',
            'lid_id' => 'Лид',
            'remote_jid' => 'WhatsApp ID',
            'remote_phone' => 'Телефон',
            'remote_name' => 'Имя',
            'direction' => 'Направление',
            'message_type' => 'Тип',
            'content' => 'Сообщение',
            'status' => 'Статус',
            'is_from_me' => 'Исходящее',
            'is_read' => 'Прочитано',
            'created_at' => 'Дата',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSession()
    {
        return $this->hasOne(WhatsappSession::class, ['id' => 'session_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLid()
    {
        return $this->hasOne(Lids::class, ['id' => 'lid_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReadByUser()
    {
        return $this->hasOne(User::class, ['id' => 'read_by']);
    }

    /**
     * Создать сообщение из webhook данных
     * @param int $sessionId
     * @param array $data Данные от Evolution API
     * @return WhatsappMessage|null
     */
    public static function createFromWebhook(int $sessionId, array $data): ?WhatsappMessage
    {
        $session = WhatsappSession::findOne($sessionId);
        if (!$session) {
            return null;
        }

        $key = $data['key'] ?? [];
        $messageData = $data['message'] ?? [];

        $message = new self();
        $message->organization_id = $session->organization_id;
        $message->session_id = $sessionId;
        $message->remote_jid = $key['remoteJid'] ?? '';
        $message->remote_phone = self::extractPhoneFromJid($message->remote_jid);
        $message->remote_name = $data['pushName'] ?? null;
        $message->is_from_me = $key['fromMe'] ?? false;
        $message->direction = $message->is_from_me ? self::DIRECTION_OUTGOING : self::DIRECTION_INCOMING;
        $message->whatsapp_id = $key['id'] ?? null;
        $message->whatsapp_timestamp = isset($data['messageTimestamp'])
            ? date('Y-m-d H:i:s', $data['messageTimestamp'])
            : null;
        $message->info = json_encode($data);

        // Определяем тип и контент сообщения
        if (isset($messageData['conversation'])) {
            $message->message_type = self::TYPE_TEXT;
            $message->content = $messageData['conversation'];
        } elseif (isset($messageData['extendedTextMessage'])) {
            $message->message_type = self::TYPE_TEXT;
            $message->content = $messageData['extendedTextMessage']['text'] ?? '';
        } elseif (isset($messageData['imageMessage'])) {
            $message->message_type = self::TYPE_IMAGE;
            $message->content = $messageData['imageMessage']['caption'] ?? '';
            $message->media_mimetype = $messageData['imageMessage']['mimetype'] ?? null;
        } elseif (isset($messageData['videoMessage'])) {
            $message->message_type = self::TYPE_VIDEO;
            $message->content = $messageData['videoMessage']['caption'] ?? '';
            $message->media_mimetype = $messageData['videoMessage']['mimetype'] ?? null;
        } elseif (isset($messageData['audioMessage'])) {
            $message->message_type = self::TYPE_AUDIO;
            $message->media_mimetype = $messageData['audioMessage']['mimetype'] ?? null;
        } elseif (isset($messageData['documentMessage'])) {
            $message->message_type = self::TYPE_DOCUMENT;
            $message->media_filename = $messageData['documentMessage']['fileName'] ?? null;
            $message->media_mimetype = $messageData['documentMessage']['mimetype'] ?? null;
        } elseif (isset($messageData['stickerMessage'])) {
            $message->message_type = self::TYPE_STICKER;
        } else {
            $message->message_type = self::TYPE_TEXT;
            $message->content = '[Неподдерживаемый тип сообщения]';
        }

        // Пытаемся найти связанный лид по телефону
        if ($message->remote_phone) {
            $lid = self::findLidByPhone($message->remote_phone, $session->organization_id);
            if ($lid) {
                $message->lid_id = $lid->id;
            }
        }

        if ($message->save()) {
            return $message;
        }

        Yii::error('Failed to save WhatsApp message: ' . json_encode($message->errors), 'whatsapp');
        return null;
    }

    /**
     * Извлечь номер телефона из JID
     * @param string $jid
     * @return string|null
     */
    public static function extractPhoneFromJid(string $jid): ?string
    {
        // JID формат: 77001234567@s.whatsapp.net
        if (preg_match('/^(\d+)@/', $jid, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Найти лид по номеру телефона
     * @param string $phone
     * @param int $organizationId
     * @return Lids|null
     */
    public static function findLidByPhone(string $phone, int $organizationId): ?Lids
    {
        // Нормализуем номер для поиска
        $phone = preg_replace('/\D/', '', $phone);

        // Варианты форматов
        $phoneVariants = [
            $phone,
            '8' . substr($phone, 1), // 77001234567 -> 87001234567
            '+' . $phone,
        ];

        return Lids::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['is_deleted' => 0])
            ->andWhere([
                'or',
                ['in', 'phone', $phoneVariants],
                ['in', 'parent_phone', $phoneVariants],
                ['like', 'phone', $phone],
                ['like', 'parent_phone', $phone],
            ])
            ->one();
    }

    /**
     * Форматированная дата
     * @return string
     */
    public function getFormattedDate(): string
    {
        $timestamp = strtotime($this->created_at);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        if ($timestamp >= $today) {
            return 'Сегодня ' . date('H:i', $timestamp);
        } elseif ($timestamp >= $yesterday) {
            return 'Вчера ' . date('H:i', $timestamp);
        } else {
            return date('d.m.Y H:i', $timestamp);
        }
    }

    /**
     * Краткое описание сообщения для превью
     * @param int $maxLength
     * @return string
     */
    public function getPreview(int $maxLength = 50): string
    {
        if ($this->message_type !== self::TYPE_TEXT) {
            $typeLabels = [
                self::TYPE_IMAGE => 'Фото',
                self::TYPE_VIDEO => 'Видео',
                self::TYPE_AUDIO => 'Аудио',
                self::TYPE_DOCUMENT => 'Документ',
                self::TYPE_STICKER => 'Стикер',
                self::TYPE_LOCATION => 'Геолокация',
                self::TYPE_CONTACT => 'Контакт',
            ];
            return $typeLabels[$this->message_type] ?? 'Сообщение';
        }

        if (mb_strlen($this->content) > $maxLength) {
            return mb_substr($this->content, 0, $maxLength) . '...';
        }

        return $this->content ?? '';
    }

    /**
     * Пометить как прочитанное
     * @param int|null $userId
     * @return bool
     */
    public function markAsRead(?int $userId = null): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = new Expression('NOW()');
        $this->read_by = $userId ?? Yii::$app->user->id;

        return $this->save(false);
    }

    /**
     * Иконка типа сообщения
     * @return string
     */
    public function getTypeIcon(): string
    {
        $icons = [
            self::TYPE_TEXT => 'chat-bubble-left',
            self::TYPE_IMAGE => 'photo',
            self::TYPE_VIDEO => 'video-camera',
            self::TYPE_AUDIO => 'microphone',
            self::TYPE_DOCUMENT => 'document',
            self::TYPE_STICKER => 'face-smile',
            self::TYPE_LOCATION => 'map-pin',
            self::TYPE_CONTACT => 'user',
        ];

        return $icons[$this->message_type] ?? 'chat-bubble-left';
    }

    /**
     * Создать сообщение из webhook данных WAHA
     * @param int $sessionId
     * @param array $data Данные от WAHA
     * @return WhatsappMessage|null
     */
    public static function createFromWahaWebhook(int $sessionId, array $data): ?WhatsappMessage
    {
        $session = WhatsappSession::findOne($sessionId);
        if (!$session) {
            return null;
        }

        // Проверяем не дубликат ли
        $messageId = $data['id']['id'] ?? $data['id'] ?? null;
        if ($messageId) {
            $existing = self::find()
                ->where(['whatsapp_id' => $messageId, 'session_id' => $sessionId])
                ->one();
            if ($existing) {
                return null; // Уже есть
            }
        }

        $message = new self();
        $message->organization_id = $session->organization_id;
        $message->session_id = $sessionId;
        $message->remote_jid = $data['from'] ?? '';
        $message->remote_phone = self::extractPhoneFromJid($message->remote_jid);
        $message->remote_name = $data['_data']['notifyName'] ?? $data['notifyName'] ?? null;
        $message->is_from_me = $data['fromMe'] ?? false;
        $message->direction = $message->is_from_me ? self::DIRECTION_OUTGOING : self::DIRECTION_INCOMING;
        $message->whatsapp_id = $messageId;
        $message->whatsapp_timestamp = isset($data['timestamp'])
            ? date('Y-m-d H:i:s', $data['timestamp'])
            : null;
        $message->info = json_encode($data);

        // Определяем тип и контент сообщения
        if (isset($data['body'])) {
            $message->message_type = self::TYPE_TEXT;
            $message->content = $data['body'];
        } elseif (isset($data['_data']['body'])) {
            $message->message_type = self::TYPE_TEXT;
            $message->content = $data['_data']['body'];
        } elseif ($data['hasMedia'] ?? false) {
            $mediaType = $data['_data']['type'] ?? 'document';
            $message->message_type = match ($mediaType) {
                'image' => self::TYPE_IMAGE,
                'video' => self::TYPE_VIDEO,
                'audio', 'ptt' => self::TYPE_AUDIO,
                'sticker' => self::TYPE_STICKER,
                default => self::TYPE_DOCUMENT,
            };
            $message->content = $data['_data']['caption'] ?? '';
            $message->media_mimetype = $data['_data']['mimetype'] ?? null;
            $message->media_filename = $data['_data']['filename'] ?? null;
        } else {
            $message->message_type = self::TYPE_TEXT;
            $message->content = '[Сообщение]';
        }

        // Пытаемся найти связанный лид по телефону
        if ($message->remote_phone) {
            $lid = self::findLidByPhone($message->remote_phone, $session->organization_id);
            if ($lid) {
                $message->lid_id = $lid->id;
            }
        }

        if ($message->save()) {
            return $message;
        }

        Yii::error('Failed to save WhatsApp message from WAHA: ' . json_encode($message->errors), 'whatsapp');
        return null;
    }
}
