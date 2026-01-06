<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель истории взаимодействий с лидами
 *
 * @property int $id
 * @property int $lid_id
 * @property int $organization_id
 * @property int|null $user_id
 * @property string $type
 * @property int|null $status_from
 * @property int|null $status_to
 * @property string|null $comment
 * @property int|null $call_duration
 * @property string|null $next_contact_date
 * @property int $is_deleted
 * @property string $created_at
 *
 * @property Lids $lid
 * @property User $user
 */
class LidHistory extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Типы взаимодействий
    const TYPE_CREATED = 'created';           // Лид создан
    const TYPE_CALL = 'call';                 // Звонок
    const TYPE_MESSAGE = 'message';           // Сообщение
    const TYPE_WHATSAPP = 'whatsapp';         // WhatsApp
    const TYPE_NOTE = 'note';                 // Заметка
    const TYPE_STATUS_CHANGE = 'status_change'; // Смена статуса
    const TYPE_MEETING = 'meeting';           // Встреча
    const TYPE_CONVERTED = 'converted';       // Конвертирован в ученика
    const TYPE_FIELD_CHANGED = 'field_changed'; // Изменено поле
    const TYPE_MANAGER_CHANGED = 'manager_changed'; // Смена менеджера

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lid_history';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
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
            [['lid_id', 'type'], 'required'],
            [['lid_id', 'organization_id', 'user_id', 'status_from', 'status_to', 'call_duration', 'is_deleted'], 'integer'],
            ['type', 'string', 'max' => 50],
            ['type', 'in', 'range' => array_keys(self::getTypeList())],
            ['comment', 'string'],
            ['next_contact_date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lid_id' => 'Лид',
            'organization_id' => 'Организация',
            'user_id' => 'Пользователь',
            'type' => 'Тип',
            'status_from' => 'Статус до',
            'status_to' => 'Статус после',
            'comment' => 'Комментарий',
            'call_duration' => 'Длительность звонка',
            'next_contact_date' => 'Следующий контакт',
            'is_deleted' => 'Удалён',
            'created_at' => 'Дата',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            // Автоматически заполняем organization_id и user_id
            if (!$this->organization_id) {
                $this->organization_id = Organizations::getCurrentOrganizationId();
            }
            if (!$this->user_id && !Yii::$app->user->isGuest) {
                $this->user_id = Yii::$app->user->id;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * Связь с лидом
     */
    public function getLid()
    {
        return $this->hasOne(Lids::class, ['id' => 'lid_id']);
    }

    /**
     * Связь с пользователем
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Список типов взаимодействий
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_CREATED => 'Создан',
            self::TYPE_CALL => 'Звонок',
            self::TYPE_MESSAGE => 'Сообщение',
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_NOTE => 'Заметка',
            self::TYPE_STATUS_CHANGE => 'Смена статуса',
            self::TYPE_MEETING => 'Встреча',
            self::TYPE_CONVERTED => 'Конвертирован',
            self::TYPE_FIELD_CHANGED => 'Изменено поле',
            self::TYPE_MANAGER_CHANGED => 'Смена менеджера',
        ];
    }

    /**
     * Название типа
     */
    public function getTypeLabel()
    {
        $list = self::getTypeList();
        return $list[$this->type] ?? $this->type;
    }

    /**
     * Иконка типа (HeroIcons)
     */
    public function getTypeIcon()
    {
        $icons = [
            self::TYPE_CREATED => 'plus-circle',
            self::TYPE_CALL => 'phone',
            self::TYPE_MESSAGE => 'chat-bubble-left',
            self::TYPE_WHATSAPP => 'chat-bubble-left-right',
            self::TYPE_NOTE => 'document-text',
            self::TYPE_STATUS_CHANGE => 'arrow-path',
            self::TYPE_MEETING => 'user-group',
            self::TYPE_CONVERTED => 'check-circle',
            self::TYPE_FIELD_CHANGED => 'pencil',
            self::TYPE_MANAGER_CHANGED => 'user',
        ];
        return $icons[$this->type] ?? 'information-circle';
    }

    /**
     * Цвет типа для Tailwind
     */
    public function getTypeColor()
    {
        $colors = [
            self::TYPE_CREATED => 'blue',
            self::TYPE_CALL => 'green',
            self::TYPE_MESSAGE => 'purple',
            self::TYPE_WHATSAPP => 'emerald',
            self::TYPE_NOTE => 'gray',
            self::TYPE_STATUS_CHANGE => 'amber',
            self::TYPE_MEETING => 'indigo',
            self::TYPE_CONVERTED => 'green',
            self::TYPE_FIELD_CHANGED => 'sky',
            self::TYPE_MANAGER_CHANGED => 'violet',
        ];
        return $colors[$this->type] ?? 'gray';
    }

    /**
     * Создать запись о смене статуса
     */
    public static function createStatusChange(Lids $lid, $oldStatus, $newStatus, $comment = null)
    {
        $history = new self();
        $history->lid_id = $lid->id;
        $history->type = self::TYPE_STATUS_CHANGE;
        $history->status_from = $oldStatus;
        $history->status_to = $newStatus;
        $history->comment = $comment;
        return $history->save();
    }

    /**
     * Создать запись о создании лида
     */
    public static function createLidCreated(Lids $lid)
    {
        $history = new self();
        $history->lid_id = $lid->id;
        $history->type = self::TYPE_CREATED;
        $history->comment = 'Лид создан';
        return $history->save();
    }

    /**
     * Создать запись о конверсии
     */
    public static function createConverted(Lids $lid, Pupil $pupil)
    {
        $history = new self();
        $history->lid_id = $lid->id;
        $history->type = self::TYPE_CONVERTED;
        $history->comment = 'Конвертирован в ученика #' . $pupil->id;
        return $history->save();
    }

    /**
     * Создать запись об изменении поля
     *
     * @param Lids $lid
     * @param string $fieldName Название поля
     * @param mixed $oldValue Старое значение
     * @param mixed $newValue Новое значение
     * @return bool
     */
    public static function createFieldChanged(Lids $lid, string $fieldName, $oldValue, $newValue)
    {
        $fieldLabels = [
            'next_contact_date' => 'Дата контакта',
            'phone' => 'Телефон',
            'parent_phone' => 'Телефон родителя',
            'comment' => 'Комментарий',
            'source' => 'Источник',
            'lost_reason' => 'Причина отказа',
        ];

        $fieldLabel = $fieldLabels[$fieldName] ?? $fieldName;

        // Форматируем даты
        if ($fieldName === 'next_contact_date') {
            $oldValue = $oldValue ? date('d.m.Y', strtotime($oldValue)) : 'не указана';
            $newValue = $newValue ? date('d.m.Y', strtotime($newValue)) : 'не указана';
        }

        $history = new self();
        $history->lid_id = $lid->id;
        $history->type = self::TYPE_FIELD_CHANGED;
        $history->comment = "{$fieldLabel}: {$oldValue} → {$newValue}";
        return $history->save();
    }

    /**
     * Создать запись о смене менеджера
     *
     * @param Lids $lid
     * @param User|null $oldManager
     * @param User|null $newManager
     * @return bool
     */
    public static function createManagerChanged(Lids $lid, ?User $oldManager, ?User $newManager)
    {
        $oldName = $oldManager ? $oldManager->fio : 'не назначен';
        $newName = $newManager ? $newManager->fio : 'не назначен';

        $history = new self();
        $history->lid_id = $lid->id;
        $history->type = self::TYPE_MANAGER_CHANGED;
        $history->comment = "Менеджер: {$oldName} → {$newName}";
        return $history->save();
    }

    /**
     * Получить описание смены статуса
     */
    public function getStatusChangeDescription()
    {
        if ($this->type !== self::TYPE_STATUS_CHANGE) {
            return null;
        }

        $statuses = Lids::getStatusList();
        $from = $statuses[$this->status_from] ?? 'Неизвестно';
        $to = $statuses[$this->status_to] ?? 'Неизвестно';

        return "{$from} → {$to}";
    }

    /**
     * Форматирование длительности звонка
     */
    public function getFormattedCallDuration()
    {
        if (!$this->call_duration) {
            return null;
        }

        $minutes = floor($this->call_duration / 60);
        $seconds = $this->call_duration % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $seconds);
        }

        return "{$seconds} сек";
    }

    /**
     * Форматирование даты для отображения
     */
    public function getFormattedDate()
    {
        if (!$this->created_at) {
            return '';
        }

        $date = strtotime($this->created_at);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        if ($date >= $today) {
            return 'Сегодня, ' . date('H:i', $date);
        } elseif ($date >= $yesterday) {
            return 'Вчера, ' . date('H:i', $date);
        } else {
            return date('d.m.Y H:i', $date);
        }
    }
}
