<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Коды верификации для авторизации в кабинете
 * Привязаны к организации - код работает только для той организации, где запрошен
 *
 * @property int $id
 * @property int $organization_id ID организации
 * @property string $phone Нормализованный номер телефона
 * @property string $code 6-значный код
 * @property string $status Статус: pending, sent, verified, expired
 * @property int $attempts Количество попыток ввода
 * @property string $expires_at Время истечения
 * @property string|null $ip_address IP адрес запроса
 * @property string $created_at
 *
 * @property Organizations $organization
 */
class VerificationCode extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_VERIFIED = 'verified';
    const STATUS_EXPIRED = 'expired';

    const CODE_LIFETIME = 300; // 5 минут
    const MAX_ATTEMPTS = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%verification_code}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'phone', 'code', 'expires_at'], 'required'],
            [['organization_id', 'attempts'], 'integer'],
            [['expires_at', 'created_at'], 'safe'],
            [['phone'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 6],
            [['status'], 'string', 'max' => 20],
            [['ip_address'], 'string', 'max' => 45],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_VERIFIED, self::STATUS_EXPIRED]],
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
            'organization_id' => Yii::t('app', 'Организация'),
            'phone' => Yii::t('app', 'Телефон'),
            'code' => Yii::t('app', 'Код'),
            'status' => Yii::t('app', 'Статус'),
            'attempts' => Yii::t('app', 'Попытки'),
            'expires_at' => Yii::t('app', 'Истекает'),
            'ip_address' => 'IP',
            'created_at' => Yii::t('app', 'Создан'),
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
     * Генерация нового кода верификации
     */
    public static function generate(int $orgId, string $phone): self
    {
        $phone = TelegramUser::normalizePhone($phone);

        // Истекаем старые коды для этого телефона
        self::updateAll(
            ['status' => self::STATUS_EXPIRED],
            [
                'and',
                ['phone' => $phone],
                ['organization_id' => $orgId],
                ['in', 'status', [self::STATUS_PENDING, self::STATUS_SENT]],
            ]
        );

        $code = new self();
        $code->organization_id = $orgId;
        $code->phone = $phone;
        $code->code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $code->status = self::STATUS_PENDING;
        $code->attempts = 0;
        $code->expires_at = date('Y-m-d H:i:s', time() + self::CODE_LIFETIME);
        $code->ip_address = Yii::$app->request->userIP ?? null;
        $code->save(false);

        return $code;
    }

    /**
     * Проверить, действителен ли код
     */
    public function isValid(): bool
    {
        if ($this->status === self::STATUS_VERIFIED || $this->status === self::STATUS_EXPIRED) {
            return false;
        }

        if (strtotime($this->expires_at) < time()) {
            $this->status = self::STATUS_EXPIRED;
            $this->save(false);
            return false;
        }

        if ($this->attempts >= self::MAX_ATTEMPTS) {
            $this->status = self::STATUS_EXPIRED;
            $this->save(false);
            return false;
        }

        return true;
    }

    /**
     * Проверить введённый код
     * @return bool true если код верный
     */
    public function verify(string $inputCode): bool
    {
        $this->attempts++;

        if (!$this->isValid()) {
            $this->save(false);
            return false;
        }

        if ($this->code === $inputCode) {
            $this->status = self::STATUS_VERIFIED;
            $this->save(false);
            return true;
        }

        // Если превысили лимит попыток - истекаем код
        if ($this->attempts >= self::MAX_ATTEMPTS) {
            $this->status = self::STATUS_EXPIRED;
        }

        $this->save(false);
        return false;
    }

    /**
     * Пометить как отправленный
     */
    public function markAsSent(): void
    {
        $this->status = self::STATUS_SENT;
        $this->save(false);
    }
}
