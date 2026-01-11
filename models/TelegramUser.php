<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Глобальная модель пользователей Telegram (один бот на платформу)
 * Хранит связь chat_id ↔ phone для отправки кодов верификации
 *
 * @property int $id
 * @property string $chat_id Telegram chat ID
 * @property string $phone Нормализованный номер телефона (10 цифр)
 * @property string|null $username Telegram username
 * @property string|null $first_name Имя в Telegram
 * @property string|null $last_name Фамилия в Telegram
 * @property int $is_active Активен ли пользователь
 * @property string $created_at
 * @property string|null $updated_at
 */
class TelegramUser extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%telegram_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'phone'], 'required'],
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['chat_id'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 20],
            [['username', 'first_name', 'last_name'], 'string', 'max' => 100],
            [['chat_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Telegram Chat ID',
            'phone' => Yii::t('app', 'Телефон'),
            'username' => 'Username',
            'first_name' => Yii::t('app', 'Имя'),
            'last_name' => Yii::t('app', 'Фамилия'),
            'is_active' => Yii::t('app', 'Активен'),
            'created_at' => Yii::t('app', 'Создан'),
            'updated_at' => Yii::t('app', 'Обновлен'),
        ];
    }

    /**
     * Найти пользователя по номеру телефона
     */
    public static function findByPhone(string $phone): ?self
    {
        $phone = self::normalizePhone($phone);
        return self::find()
            ->where(['phone' => $phone])
            ->andWhere(['is_active' => 1])
            ->one();
    }

    /**
     * Найти пользователя по Telegram chat_id
     */
    public static function findByChatId(string $chatId): ?self
    {
        return self::find()
            ->where(['chat_id' => $chatId])
            ->andWhere(['is_active' => 1])
            ->one();
    }

    /**
     * Создать или обновить пользователя
     */
    public static function createOrUpdate(string $chatId, string $phone, array $userData): self
    {
        $phone = self::normalizePhone($phone);

        // Ищем по chat_id
        $user = self::find()->where(['chat_id' => $chatId])->one();

        if (!$user) {
            // Ищем по телефону (возможно сменил Telegram аккаунт)
            $user = self::find()->where(['phone' => $phone])->one();
        }

        if (!$user) {
            $user = new self();
        }

        $user->chat_id = $chatId;
        $user->phone = $phone;
        $user->username = $userData['username'] ?? null;
        $user->first_name = $userData['first_name'] ?? null;
        $user->last_name = $userData['last_name'] ?? null;
        $user->is_active = 1;
        $user->updated_at = date('Y-m-d H:i:s');
        $user->save(false);

        return $user;
    }

    /**
     * Нормализация номера телефона
     * Убирает всё кроме цифр и убирает 7/8 в начале
     */
    public static function normalizePhone(string $phone): string
    {
        // Убираем всё кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Убираем 8 или 7 в начале если есть
        if (strlen($phone) === 11 && ($phone[0] === '8' || $phone[0] === '7')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Полное имя пользователя
     */
    public function getFullName(): string
    {
        $parts = array_filter([$this->first_name, $this->last_name]);
        return implode(' ', $parts) ?: $this->username ?: $this->phone;
    }
}
