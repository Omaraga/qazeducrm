<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use app\traits\HasTypeTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель уведомлений и напоминаний
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property int $type
 * @property string $title
 * @property string $message
 * @property string $entity_type
 * @property int $entity_id
 * @property string $link
 * @property bool $is_read
 * @property string $scheduled_at
 * @property string $sent_at
 * @property string $info
 * @property int $is_deleted
 * @property string $created_at
 *
 * @property User $user
 */
class Notification extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;
    use HasTypeTrait;

    // Типы уведомлений
    const TYPE_INFO = 1;           // Информационное
    const TYPE_WARNING = 2;        // Предупреждение
    const TYPE_SUCCESS = 3;        // Успех
    const TYPE_DANGER = 4;         // Важное/Ошибка
    const TYPE_REMINDER = 5;       // Напоминание

    // Типы сущностей
    const ENTITY_LID = 'lid';
    const ENTITY_PUPIL = 'pupil';
    const ENTITY_PAYMENT = 'payment';
    const ENTITY_LESSON = 'lesson';
    const ENTITY_GROUP = 'group';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification}}';
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
            [['user_id', 'title'], 'required'],
            [['organization_id', 'user_id', 'type', 'entity_id', 'is_deleted'], 'integer'],
            [['message', 'info'], 'string'],
            [['is_read'], 'boolean'],
            [['scheduled_at', 'sent_at', 'created_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['entity_type'], 'string', 'max' => 50],
            [['link'], 'string', 'max' => 500],
            ['type', 'default', 'value' => self::TYPE_INFO],
            ['is_read', 'default', 'value' => false],
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
            'user_id' => 'Пользователь',
            'type' => 'Тип',
            'title' => 'Заголовок',
            'message' => 'Сообщение',
            'entity_type' => 'Тип сущности',
            'entity_id' => 'ID сущности',
            'link' => 'Ссылка',
            'is_read' => 'Прочитано',
            'scheduled_at' => 'Запланировано на',
            'sent_at' => 'Отправлено',
            'is_deleted' => 'Удалено',
            'created_at' => 'Создано',
        ];
    }

    /**
     * Связь с пользователем
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Список типов уведомлений
     * @see HasTypeTrait
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_INFO => 'Информация',
            self::TYPE_WARNING => 'Предупреждение',
            self::TYPE_SUCCESS => 'Успех',
            self::TYPE_DANGER => 'Важное',
            self::TYPE_REMINDER => 'Напоминание',
        ];
    }

    /**
     * Иконки типов
     * @see HasTypeTrait::getTypeIcon()
     */
    public static function getTypeIcons(): array
    {
        return [
            self::TYPE_INFO => 'information-circle',
            self::TYPE_WARNING => 'exclamation-triangle',
            self::TYPE_SUCCESS => 'check-circle',
            self::TYPE_DANGER => 'exclamation-circle',
            self::TYPE_REMINDER => 'bell',
        ];
    }

    /**
     * Цвета типов
     * @see HasTypeTrait::getTypeColor()
     */
    public static function getTypeColors(): array
    {
        return [
            self::TYPE_INFO => 'blue',
            self::TYPE_WARNING => 'amber',
            self::TYPE_SUCCESS => 'green',
            self::TYPE_DANGER => 'red',
            self::TYPE_REMINDER => 'purple',
        ];
    }

    /**
     * CSS класс для типа (Tailwind text color)
     */
    public function getTypeClass(): string
    {
        return 'text-' . $this->getTypeColor() . '-500';
    }

    // getTypeLabel(), getTypeIcon(), getTypeColor() предоставляются HasTypeTrait

    /**
     * Отметить как прочитанное
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }
        $this->is_read = true;
        return $this->save(false);
    }

    /**
     * Создать уведомление
     */
    public static function create(
        int $userId,
        string $title,
        ?string $message = null,
        int $type = self::TYPE_INFO,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $link = null,
        ?string $scheduledAt = null
    ): ?self {
        $notification = new self();
        $notification->organization_id = Organizations::getCurrentOrganizationId();
        $notification->user_id = $userId;
        $notification->type = $type;
        $notification->title = $title;
        $notification->message = $message;
        $notification->entity_type = $entityType;
        $notification->entity_id = $entityId;
        $notification->link = $link;
        $notification->scheduled_at = $scheduledAt;

        return $notification->save() ? $notification : null;
    }

    /**
     * Создать напоминание о лиде
     */
    public static function createLidReminder(Lids $lid, int $userId, string $scheduledAt, ?string $message = null): ?self
    {
        $title = 'Напоминание: ' . ($lid->fio ?: 'Лид #' . $lid->id);
        $message = $message ?: 'Запланирован контакт с лидом';

        return self::create(
            $userId,
            $title,
            $message,
            self::TYPE_REMINDER,
            self::ENTITY_LID,
            $lid->id,
            "/{$lid->organization_id}/lids/view?id={$lid->id}",
            $scheduledAt
        );
    }

    /**
     * Создать уведомление о просроченном контакте
     */
    public static function createOverdueReminder(Lids $lid): ?self
    {
        if (!$lid->manager_id) {
            return null;
        }

        $title = 'Просрочен контакт: ' . ($lid->fio ?: 'Лид');
        $message = 'Дата контакта была ' . Yii::$app->formatter->asDate($lid->next_contact_date, 'php:d.m.Y');

        return self::create(
            $lid->manager_id,
            $title,
            $message,
            self::TYPE_WARNING,
            self::ENTITY_LID,
            $lid->id,
            "/{$lid->organization_id}/lids/view?id={$lid->id}"
        );
    }

    /**
     * Создать уведомление о контактах на сегодня
     */
    public static function createTodayContactsReminder(int $userId, int $count): ?self
    {
        $title = "На сегодня: {$count} контакт" . self::pluralize($count, '', 'а', 'ов');
        $message = 'Не забудьте связаться с лидами';

        return self::create(
            $userId,
            $title,
            $message,
            self::TYPE_INFO,
            null,
            null,
            null
        );
    }

    /**
     * Получить непрочитанные уведомления пользователя
     */
    public static function getUnreadForUser(int $userId, int $limit = 10): array
    {
        return self::find()
            ->andWhere(['user_id' => $userId, 'is_read' => false, 'is_deleted' => 0])
            ->andWhere([
                'or',
                ['scheduled_at' => null],
                ['<=', 'scheduled_at', new Expression('NOW()')]
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Получить количество непрочитанных
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        return (int)self::find()
            ->andWhere(['user_id' => $userId, 'is_read' => false, 'is_deleted' => 0])
            ->andWhere([
                'or',
                ['scheduled_at' => null],
                ['<=', 'scheduled_at', new Expression('NOW()')]
            ])
            ->count();
    }

    /**
     * Получить все уведомления пользователя
     */
    public static function getAllForUser(int $userId, int $limit = 50): array
    {
        return self::find()
            ->andWhere(['user_id' => $userId, 'is_deleted' => 0])
            ->andWhere([
                'or',
                ['scheduled_at' => null],
                ['<=', 'scheduled_at', new Expression('NOW()')]
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Отметить все как прочитанные
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::updateAll(
            ['is_read' => true],
            ['user_id' => $userId, 'is_read' => false, 'is_deleted' => 0]
        );
    }

    /**
     * Получить напоминания для отправки
     * (запланированные на текущее время и ещё не отправленные)
     */
    public static function getPendingReminders(): array
    {
        return self::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['sent_at' => null])
            ->andWhere(['<=', 'scheduled_at', new Expression('NOW()')])
            ->andWhere(['is not', 'scheduled_at', null])
            ->orderBy(['scheduled_at' => SORT_ASC])
            ->all();
    }

    /**
     * Отметить как отправленное
     */
    public function markAsSent(): bool
    {
        $this->sent_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }

    /**
     * Форматированное время
     */
    public function getTimeAgo(): string
    {
        $time = strtotime($this->created_at);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Только что';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' мин. назад';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' ч. назад';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' дн. назад';
        } else {
            return Yii::$app->formatter->asDate($this->created_at, 'php:d.m.Y');
        }
    }

    /**
     * Склонение числительных
     */
    private static function pluralize(int $number, string $one, string $few, string $many): string
    {
        $n = abs($number) % 100;
        if ($n >= 5 && $n <= 20) {
            return $many;
        }
        $n = $n % 10;
        if ($n === 1) {
            return $one;
        }
        if ($n >= 2 && $n <= 4) {
            return $few;
        }
        return $many;
    }
}
