<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель чата WhatsApp (группировка сообщений по собеседнику)
 *
 * @property int $id
 * @property int $organization_id
 * @property int $session_id
 * @property int|null $lid_id
 * @property string $remote_jid
 * @property string|null $remote_phone
 * @property string|null $remote_name
 * @property string|null $profile_picture_url
 * @property int|null $last_message_id
 * @property string|null $last_message_at
 * @property int $unread_count
 * @property bool $is_archived
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property WhatsappSession $session
 * @property Lids $lid
 * @property WhatsappMessage $lastMessage
 * @property WhatsappMessage[] $messages
 */
class WhatsappChat extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%whatsapp_chat}}';
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
            [['organization_id', 'session_id', 'remote_jid'], 'required'],
            [['organization_id', 'session_id', 'lid_id', 'last_message_id', 'unread_count', 'is_deleted'], 'integer'],
            [['last_message_at'], 'safe'],
            [['is_archived'], 'boolean'],
            [['remote_jid'], 'string', 'max' => 100],
            [['remote_phone'], 'string', 'max' => 20],
            [['remote_name'], 'string', 'max' => 255],
            [['profile_picture_url'], 'string', 'max' => 512],
            [['unread_count'], 'default', 'value' => 0],
            [['is_archived'], 'default', 'value' => false],
            [['session_id', 'remote_jid'], 'unique', 'targetAttribute' => ['session_id', 'remote_jid']],
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
            'unread_count' => 'Непрочитанных',
            'is_archived' => 'В архиве',
            'last_message_at' => 'Последнее сообщение',
            'created_at' => 'Создан',
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
    public function getLastMessage()
    {
        return $this->hasOne(WhatsappMessage::class, ['id' => 'last_message_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(WhatsappMessage::class, ['remote_jid' => 'remote_jid', 'session_id' => 'session_id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Найти или создать чат для сообщения
     * @param WhatsappMessage $message
     * @return WhatsappChat
     */
    public static function findOrCreateForMessage(WhatsappMessage $message): WhatsappChat
    {
        $chat = self::find()
            ->where([
                'session_id' => $message->session_id,
                'remote_jid' => $message->remote_jid,
            ])
            ->one();

        if (!$chat) {
            $chat = new self();
            $chat->organization_id = $message->organization_id;
            $chat->session_id = $message->session_id;
            $chat->remote_jid = $message->remote_jid;
            $chat->remote_phone = $message->remote_phone;
            $chat->remote_name = $message->remote_name;
            $chat->lid_id = $message->lid_id;
        }

        // Обновляем данные чата
        // ВАЖНО: обновляем remote_name только от входящих сообщений,
        // т.к. для исходящих remote_name будет null или содержать неправильное имя
        if ($message->remote_name && !$chat->remote_name && !$message->is_from_me) {
            $chat->remote_name = $message->remote_name;
        }

        // Привязываем лид если нашли
        if ($message->lid_id && !$chat->lid_id) {
            $chat->lid_id = $message->lid_id;
        }

        $chat->last_message_id = $message->id;
        $chat->last_message_at = $message->created_at;

        // Увеличиваем счётчик непрочитанных для входящих
        if ($message->direction === WhatsappMessage::DIRECTION_INCOMING) {
            $chat->unread_count++;
        }

        $chat->save(false);

        return $chat;
    }

    /**
     * Пометить все сообщения чата как прочитанные
     * @param int|null $userId
     * @return int Количество помеченных сообщений
     */
    public function markAllAsRead(?int $userId = null): int
    {
        $userId = $userId ?? Yii::$app->user->id;

        $count = WhatsappMessage::updateAll(
            [
                'is_read' => true,
                'read_at' => new Expression('NOW()'),
                'read_by' => $userId,
            ],
            [
                'and',
                ['session_id' => $this->session_id],
                ['remote_jid' => $this->remote_jid],
                ['is_read' => false],
                ['direction' => WhatsappMessage::DIRECTION_INCOMING],
            ]
        );

        $this->unread_count = 0;
        $this->save(false);

        return $count;
    }

    /**
     * Получить отображаемое имя контакта
     * @return string
     */
    public function getDisplayName(): string
    {
        // Сначала пробуем имя из лида
        if ($this->lid) {
            return $this->lid->fio ?: $this->lid->parent_fio ?: $this->remote_name ?: $this->getFormattedPhone();
        }

        // Затем имя из WhatsApp
        if ($this->remote_name) {
            return $this->remote_name;
        }

        // Иначе номер телефона
        return $this->getFormattedPhone();
    }

    /**
     * Форматированный номер телефона
     * @return string
     */
    public function getFormattedPhone(): string
    {
        $phone = $this->remote_phone;
        if (!$phone) {
            return 'Неизвестный';
        }

        // Форматируем как +7 (700) 123-45-67
        if (strlen($phone) === 11 && $phone[0] === '7') {
            return '+' . $phone[0] . ' (' . substr($phone, 1, 3) . ') ' .
                substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
        }

        return '+' . $phone;
    }

    /**
     * Форматированное время последнего сообщения
     * @return string
     */
    public function getLastMessageTime(): string
    {
        if (!$this->last_message_at) {
            return '';
        }

        $timestamp = strtotime($this->last_message_at);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');
        $weekAgo = strtotime('-7 days');

        if ($timestamp >= $today) {
            return date('H:i', $timestamp);
        } elseif ($timestamp >= $yesterday) {
            return 'Вчера';
        } elseif ($timestamp >= $weekAgo) {
            $days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
            return $days[date('w', $timestamp)];
        } else {
            return date('d.m.Y', $timestamp);
        }
    }

    /**
     * Привязать чат к лиду
     * @param int $lidId
     * @return bool
     */
    public function linkToLid(int $lidId): bool
    {
        $this->lid_id = $lidId;

        // Также обновляем lid_id во всех сообщениях чата
        WhatsappMessage::updateAll(
            ['lid_id' => $lidId],
            [
                'session_id' => $this->session_id,
                'remote_jid' => $this->remote_jid,
            ]
        );

        return $this->save(false);
    }

    /**
     * Проверить, есть ли входящие сообщения от контакта
     * @param string $phone Номер телефона (без +)
     * @param int $sessionId ID сессии
     * @return bool
     */
    public static function hasIncomingFrom(string $phone, int $sessionId): bool
    {
        // Формируем JID для поиска
        $jid = $phone . '@s.whatsapp.net';

        return WhatsappMessage::find()
            ->where(['session_id' => $sessionId])
            ->andWhere([
                'or',
                ['remote_jid' => $jid],
                ['remote_phone' => $phone],
            ])
            ->andWhere(['direction' => WhatsappMessage::DIRECTION_INCOMING])
            ->exists();
    }

    /**
     * Создать лид из чата
     * @return Lids|null
     */
    public function createLid(): ?Lids
    {
        if ($this->lid_id) {
            return $this->lid;
        }

        $lid = new Lids();
        $lid->organization_id = $this->organization_id;
        $lid->fio = $this->remote_name ?: 'WhatsApp ' . $this->getFormattedPhone();
        $lid->phone = $this->remote_phone;
        $lid->source = Lids::SOURCE_WHATSAPP;
        $lid->status = Lids::STATUS_NEW;
        $lid->date = date('Y-m-d');
        $lid->contact_person = Lids::CONTACT_PUPIL;

        if ($lid->save()) {
            // Создаём запись в истории
            LidHistory::createLidCreated($lid);

            // Привязываем чат к лиду
            $this->linkToLid($lid->id);

            return $lid;
        }

        return null;
    }
}
