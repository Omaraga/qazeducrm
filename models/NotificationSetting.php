<?php

namespace app\models;

use app\components\ActiveRecord;
use Yii;
use yii\db\Expression;

/**
 * Модель настроек автоматических рассылок (авторассылки)
 *
 * @property int $id
 * @property int $organization_id
 * @property string $type
 * @property string $channel
 * @property bool $is_active
 * @property int|null $hours_before
 * @property string|null $frequency
 * @property int|null $template_id
 * @property string|null $last_run_at
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property SmsTemplate $template
 * @property Organizations $organization
 */
class NotificationSetting extends ActiveRecord
{
    // Типы авторассылок (триггеры)
    const TYPE_LESSON_REMINDER = 'lesson_reminder';       // Напоминание о занятии
    const TYPE_LESSON_CANCELLED = 'lesson_cancelled';     // Отмена занятия
    const TYPE_LESSON_RESCHEDULED = 'lesson_rescheduled'; // Перенос занятия
    const TYPE_PAYMENT_DUE = 'payment_due';               // Задолженность
    const TYPE_PAYMENT_RECEIVED = 'payment_received';     // Оплата получена
    const TYPE_BIRTHDAY = 'birthday';                     // День рождения

    // Каналы отправки
    const CHANNEL_SMS = 'sms';
    const CHANNEL_WHATSAPP = 'whatsapp';

    // Частота для payment_due
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification_setting}}';
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
            [['organization_id', 'type', 'channel'], 'required'],
            [['organization_id', 'hours_before', 'template_id'], 'integer'],
            [['is_active'], 'boolean'],
            [['last_run_at'], 'safe'],
            [['type', 'frequency'], 'string', 'max' => 50],
            [['channel'], 'string', 'max' => 20],
            [['type'], 'in', 'range' => array_keys(self::getTypeList())],
            [['channel'], 'in', 'range' => [self::CHANNEL_SMS, self::CHANNEL_WHATSAPP]],
            [['frequency'], 'in', 'range' => [self::FREQUENCY_DAILY, self::FREQUENCY_WEEKLY]],
            [['is_active'], 'default', 'value' => false],
            [['channel'], 'default', 'value' => self::CHANNEL_WHATSAPP],
            [['organization_id', 'type'], 'unique', 'targetAttribute' => ['organization_id', 'type']],
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
            'type' => 'Тип уведомления',
            'channel' => 'Канал отправки',
            'is_active' => 'Активно',
            'hours_before' => 'За сколько часов',
            'frequency' => 'Частота',
            'template_id' => 'Шаблон',
            'last_run_at' => 'Последний запуск',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(SmsTemplate::class, ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Список типов уведомлений (простой)
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_LESSON_REMINDER => 'Напоминание о занятии',
            self::TYPE_LESSON_CANCELLED => 'Отмена занятия',
            self::TYPE_LESSON_RESCHEDULED => 'Перенос занятия',
            self::TYPE_PAYMENT_DUE => 'Уведомление о задолженности',
            self::TYPE_PAYMENT_RECEIVED => 'Оплата получена',
            self::TYPE_BIRTHDAY => 'Поздравление с днём рождения',
        ];
    }

    /**
     * Полная информация о типах триггеров для UI
     */
    public static function getTypesMeta(): array
    {
        return [
            self::TYPE_LESSON_REMINDER => [
                'name' => 'Напоминание о занятии',
                'description' => 'Автоматическое напоминание ученикам/родителям о предстоящем занятии',
                'icon' => 'calendar',
                'color' => 'primary',
                'hasHoursBefore' => true,
                'category' => 'lessons',
            ],
            self::TYPE_LESSON_CANCELLED => [
                'name' => 'Отмена занятия',
                'description' => 'Уведомление при отмене занятия преподавателем или администратором',
                'icon' => 'x-circle',
                'color' => 'danger',
                'hasHoursBefore' => false,
                'category' => 'lessons',
            ],
            self::TYPE_LESSON_RESCHEDULED => [
                'name' => 'Перенос занятия',
                'description' => 'Уведомление при изменении времени или даты занятия',
                'icon' => 'arrow-path',
                'color' => 'warning',
                'hasHoursBefore' => false,
                'category' => 'lessons',
            ],
            self::TYPE_PAYMENT_DUE => [
                'name' => 'Задолженность',
                'description' => 'Напоминание о необходимости оплаты при отрицательном балансе',
                'icon' => 'banknotes',
                'color' => 'warning',
                'hasHoursBefore' => false,
                'hasFrequency' => true,
                'category' => 'payments',
            ],
            self::TYPE_PAYMENT_RECEIVED => [
                'name' => 'Оплата получена',
                'description' => 'Подтверждение получения оплаты',
                'icon' => 'check-circle',
                'color' => 'success',
                'hasHoursBefore' => false,
                'category' => 'payments',
            ],
            self::TYPE_BIRTHDAY => [
                'name' => 'День рождения',
                'description' => 'Автоматическое поздравление с днём рождения',
                'icon' => 'cake',
                'color' => 'pink',
                'hasHoursBefore' => false,
                'category' => 'other',
            ],
        ];
    }

    /**
     * Получить метаданные для типа
     */
    public function getTypeMeta(): array
    {
        $metas = self::getTypesMeta();
        return $metas[$this->type] ?? [
            'name' => $this->type,
            'description' => '',
            'icon' => 'bell',
            'color' => 'gray',
        ];
    }

    /**
     * Список каналов
     */
    public static function getChannelList(): array
    {
        return [
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_SMS => 'SMS',
        ];
    }

    /**
     * Список вариантов "за сколько часов"
     */
    public static function getHoursBeforeList(): array
    {
        return [
            1 => '1 час',
            2 => '2 часа',
            3 => '3 часа',
            6 => '6 часов',
            12 => '12 часов',
            24 => '24 часа (за день)',
        ];
    }

    /**
     * Список частоты для payment_due
     */
    public static function getFrequencyList(): array
    {
        return [
            self::FREQUENCY_DAILY => 'Ежедневно',
            self::FREQUENCY_WEEKLY => 'Раз в неделю',
        ];
    }

    /**
     * Получить настройку для организации по типу
     * Создаёт если не существует
     */
    public static function getOrCreate(int $organizationId, string $type): self
    {
        $setting = self::find()
            ->where(['organization_id' => $organizationId, 'type' => $type])
            ->one();

        if (!$setting) {
            $setting = new self();
            $setting->organization_id = $organizationId;
            $setting->type = $type;
            $setting->is_active = false;

            // Дефолтные значения по типу
            if ($type === self::TYPE_LESSON_REMINDER) {
                $setting->hours_before = 2;
            } elseif ($type === self::TYPE_PAYMENT_DUE) {
                $setting->frequency = self::FREQUENCY_WEEKLY;
            }

            $setting->save(false);
        }

        return $setting;
    }

    /**
     * Получить все настройки для организации (для страницы авторассылок)
     */
    public static function getAllForOrganization(int $organizationId): array
    {
        $settings = [];
        foreach (array_keys(self::getTypeList()) as $type) {
            $settings[$type] = self::getOrCreate($organizationId, $type);
        }
        return $settings;
    }

    /**
     * Получить все активные настройки для типа (для cron)
     */
    public static function getActiveByType(string $type): array
    {
        return self::find()
            ->where(['type' => $type, 'is_active' => true])
            ->with(['template', 'organization'])
            ->all();
    }

    /**
     * Обновить время последнего запуска
     */
    public function updateLastRun(): bool
    {
        $this->last_run_at = new Expression('NOW()');
        return $this->save(false);
    }

    /**
     * Название типа уведомления
     */
    public function getTypeLabel(): string
    {
        return self::getTypeList()[$this->type] ?? $this->type;
    }

    /**
     * Название канала
     */
    public function getChannelLabel(): string
    {
        return self::getChannelList()[$this->channel] ?? $this->channel;
    }

    /**
     * Проверить, требует ли этот тип настройку hours_before
     */
    public function hasHoursBefore(): bool
    {
        return $this->type === self::TYPE_LESSON_REMINDER;
    }

    /**
     * Проверить, требует ли этот тип настройку frequency
     */
    public function hasFrequency(): bool
    {
        return $this->type === self::TYPE_PAYMENT_DUE;
    }

    /**
     * Описание текущей настройки для отображения
     */
    public function getSettingDescription(): string
    {
        $parts = [];

        if ($this->hasHoursBefore() && $this->hours_before) {
            $hoursList = self::getHoursBeforeList();
            $parts[] = 'за ' . ($hoursList[$this->hours_before] ?? $this->hours_before . ' ч.');
        }

        if ($this->hasFrequency() && $this->frequency) {
            $freqList = self::getFrequencyList();
            $parts[] = mb_strtolower($freqList[$this->frequency] ?? $this->frequency);
        }

        return implode(', ', $parts);
    }
}
