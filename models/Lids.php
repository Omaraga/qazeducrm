<?php

namespace app\models;

use app\helpers\DateHelper;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "lids".
 *
 * @property int $id
 * @property string|null $fio
 * @property string|null $phone
 * @property int|null $class_id
 * @property string|null $school
 * @property string|null $parent_fio
 * @property string|null $parent_phone
 * @property string $contact_person
 * @property int|null $pupil_id
 * @property string|null $converted_at
 * @property string|null $status_changed_at
 * @property string|null $date
 * @property string|null $manager_name
 * @property int|null $manager_id
 * @property string|null $comment
 * @property int $status
 * @property string|null $source
 * @property string|null $next_contact_date
 * @property string|null $lost_reason
 * @property int|null $sale
 * @property int|null $total_sum
 * @property int|null $total_point
 * @property int|null $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 * @property int $organization_id [int(11)]
 *
 * @property User $manager
 * @property Pupil $pupil
 * @property LidHistory[] $histories
 */
class Lids extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Статусы воронки
    const STATUS_NEW = 1;           // Новый
    const STATUS_CONTACTED = 2;     // Связались
    const STATUS_TRIAL = 3;         // На пробном занятии
    const STATUS_THINKING = 4;      // Думает
    const STATUS_ENROLLED = 5;      // Записан
    const STATUS_PAID = 6;          // Оплатил
    const STATUS_LOST = 7;          // Потерян

    // Источники лидов
    const SOURCE_INSTAGRAM = 'instagram';
    const SOURCE_WHATSAPP = 'whatsapp';
    const SOURCE_2GIS = '2gis';
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_WALK_IN = 'walk_in';
    const SOURCE_PHONE = 'phone';
    const SOURCE_OTHER = 'other';

    // Контактное лицо
    const CONTACT_PARENT = 'parent';
    const CONTACT_PUPIL = 'pupil';

    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lids';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fio', 'comment', 'info', 'lost_reason'], 'string'],
            [['class_id', 'sale', 'total_sum', 'total_point', 'is_deleted', 'status', 'manager_id', 'pupil_id'], 'integer'],
            [['date', 'created_at', 'updated_at', 'next_contact_date', 'converted_at', 'status_changed_at'], 'safe'],
            [['phone', 'school', 'manager_name', 'source', 'parent_fio', 'parent_phone'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::getStatusList())],
            ['source', 'in', 'range' => array_keys(self::getSourceList())],
            ['contact_person', 'default', 'value' => self::CONTACT_PARENT],
            ['contact_person', 'in', 'range' => [self::CONTACT_PARENT, self::CONTACT_PUPIL]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'fio' => Yii::t('main', 'ФИО ребёнка'),
            'phone' => Yii::t('main', 'Телефон ребёнка'),
            'class_id' => Yii::t('main', 'Класс'),
            'school' => Yii::t('main', 'Школа'),
            'parent_fio' => Yii::t('main', 'ФИО родителя'),
            'parent_phone' => Yii::t('main', 'Телефон родителя'),
            'contact_person' => Yii::t('main', 'Контактное лицо'),
            'pupil_id' => Yii::t('main', 'Ученик'),
            'converted_at' => Yii::t('main', 'Дата конверсии'),
            'status_changed_at' => Yii::t('main', 'Статус изменён'),
            'date' => Yii::t('main', 'Дата обращения'),
            'manager_name' => Yii::t('main', 'Менеджер'),
            'manager_id' => Yii::t('main', 'Ответственный'),
            'comment' => Yii::t('main', 'Комментарий'),
            'status' => Yii::t('main', 'Статус'),
            'source' => Yii::t('main', 'Источник'),
            'next_contact_date' => Yii::t('main', 'Следующий контакт'),
            'lost_reason' => Yii::t('main', 'Причина потери'),
            'sale' => Yii::t('main', 'Скидка'),
            'total_sum' => Yii::t('main', 'Итоговая цена'),
            'total_point' => Yii::t('main', 'Итоговые баллы'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Создан'),
            'updated_at' => Yii::t('main', 'Обновлён'),
            'info' => Yii::t('main', 'Info'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        // Автоматическое обновление status_changed_at при смене статуса
        if (!$insert && $this->isAttributeChanged('status')) {
            $this->status_changed_at = DateHelper::now();
        }

        // При создании устанавливаем status_changed_at
        if ($insert) {
            $this->status_changed_at = DateHelper::now();
        }

        return parent::beforeSave($insert);
    }

    /**
     * Связь с менеджером
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * Связь с учеником (после конверсии)
     */
    public function getPupil()
    {
        return $this->hasOne(Pupil::class, ['id' => 'pupil_id']);
    }

    /**
     * История взаимодействий
     */
    public function getHistories()
    {
        return $this->hasMany(LidHistory::class, ['lid_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Связанный чат WhatsApp
     */
    public function getWhatsappChat()
    {
        return $this->hasOne(WhatsappChat::class, ['lid_id' => 'id']);
    }

    /**
     * Получить URL аватарки из WhatsApp
     * @return string|null
     */
    public function getWhatsappProfilePicture(): ?string
    {
        return $this->whatsappChat?->profile_picture_url;
    }

    // ===================== ТЕГИ (через LidTag) =====================

    /**
     * Связь с тегами через pivot таблицу
     */
    public function getLidTagRelations()
    {
        return $this->hasMany(LidTagRelation::class, ['lid_id' => 'id']);
    }

    /**
     * Связь с тегами (LidTag модели)
     */
    public function getLidTags()
    {
        return $this->hasMany(LidTag::class, ['id' => 'tag_id'])
            ->via('lidTagRelations');
    }

    /**
     * Получить массив тегов для API/JSON
     * @return array
     */
    public function getTags(): array
    {
        $relations = $this->lidTagRelations;
        $tags = [];
        foreach ($relations as $rel) {
            if ($rel->tag) {
                $tags[] = $rel->tag->toArray();
            }
        }
        return $tags;
    }

    /**
     * Получить массив ID тегов
     * @return int[]
     */
    public function getTagIds(): array
    {
        return array_column($this->lidTagRelations, 'tag_id');
    }

    /**
     * Добавить тег по ID
     * @param int $tagId
     * @return bool
     */
    public function addTag(int $tagId): bool
    {
        if (LidTagRelation::findOne(['lid_id' => $this->id, 'tag_id' => $tagId])) {
            return true; // Уже есть
        }

        $rel = new LidTagRelation();
        $rel->lid_id = $this->id;
        $rel->tag_id = $tagId;
        return $rel->save();
    }

    /**
     * Удалить тег по ID
     * @param int $tagId
     * @return bool
     */
    public function removeTag(int $tagId): bool
    {
        return LidTagRelation::deleteAll(['lid_id' => $this->id, 'tag_id' => $tagId]) > 0;
    }

    /**
     * Проверить наличие тега по ID
     * @param int $tagId
     * @return bool
     */
    public function hasTag(int $tagId): bool
    {
        return LidTagRelation::findOne(['lid_id' => $this->id, 'tag_id' => $tagId]) !== null;
    }

    /**
     * Переключить тег (добавить/удалить)
     * @param int $tagId
     * @return bool
     */
    public function toggleTag(int $tagId): bool
    {
        if ($this->hasTag($tagId)) {
            return $this->removeTag($tagId);
        }
        return $this->addTag($tagId);
    }

    /**
     * Проверить наличие тега по имени
     * @param string $tagName
     * @return bool
     */
    public function hasTagByName(string $tagName): bool
    {
        $tag = LidTag::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['name' => $tagName])
            ->one();

        return $tag && $this->hasTag($tag->id);
    }

    /**
     * Список статусов
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_CONTACTED => 'Связались',
            self::STATUS_TRIAL => 'Пробное занятие',
            self::STATUS_THINKING => 'Думает',
            self::STATUS_ENROLLED => 'Записан',
            self::STATUS_PAID => 'Оплатил',
            self::STATUS_LOST => 'Потерян',
        ];
    }

    /**
     * Статусы для Kanban (без финальных)
     */
    public static function getKanbanStatusList()
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_CONTACTED => 'Связались',
            self::STATUS_TRIAL => 'Пробное занятие',
            self::STATUS_THINKING => 'Думает',
            self::STATUS_ENROLLED => 'Записан',
        ];
    }

    /**
     * Название статуса
     */
    public function getStatusLabel()
    {
        $list = self::getStatusList();
        return $list[$this->status] ?? 'Неизвестно';
    }

    /**
     * CSS класс для бейджа статуса
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_NEW => 'bg-info',
            self::STATUS_CONTACTED => 'bg-primary',
            self::STATUS_TRIAL => 'bg-warning',
            self::STATUS_THINKING => 'bg-secondary',
            self::STATUS_ENROLLED => 'bg-success',
            self::STATUS_PAID => 'bg-success',
            self::STATUS_LOST => 'bg-danger',
        ];
        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Цвет статуса для Tailwind
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_NEW => 'sky',
            self::STATUS_CONTACTED => 'blue',
            self::STATUS_TRIAL => 'amber',
            self::STATUS_THINKING => 'gray',
            self::STATUS_ENROLLED => 'indigo',
            self::STATUS_PAID => 'green',
            self::STATUS_LOST => 'red',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Список источников
     */
    public static function getSourceList()
    {
        return [
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_WHATSAPP => 'WhatsApp',
            self::SOURCE_2GIS => '2GIS',
            self::SOURCE_WEBSITE => 'Сайт',
            self::SOURCE_REFERRAL => 'Рекомендация',
            self::SOURCE_WALK_IN => 'Пришёл сам',
            self::SOURCE_PHONE => 'Звонок',
            self::SOURCE_OTHER => 'Другое',
        ];
    }

    /**
     * Название источника
     */
    public function getSourceLabel()
    {
        $list = self::getSourceList();
        return $list[$this->source] ?? $this->source;
    }

    /**
     * Иконка источника
     */
    public function getSourceIcon()
    {
        $icons = [
            self::SOURCE_INSTAGRAM => 'fab fa-instagram',
            self::SOURCE_WHATSAPP => 'fab fa-whatsapp',
            self::SOURCE_2GIS => 'fas fa-map-marker-alt',
            self::SOURCE_WEBSITE => 'fas fa-globe',
            self::SOURCE_REFERRAL => 'fas fa-user-friends',
            self::SOURCE_WALK_IN => 'fas fa-walking',
            self::SOURCE_PHONE => 'fas fa-phone',
            self::SOURCE_OTHER => 'fas fa-question',
        ];
        return $icons[$this->source] ?? 'fas fa-question';
    }

    /**
     * Список контактных лиц
     */
    public static function getContactPersonList()
    {
        return [
            self::CONTACT_PARENT => 'Родитель',
            self::CONTACT_PUPIL => 'Ребёнок',
        ];
    }

    /**
     * Можно ли перевести в указанный статус
     */
    public function canMoveToStatus($newStatus)
    {
        // Из LOST и PAID нельзя переходить
        if ($this->status == self::STATUS_LOST || $this->status == self::STATUS_PAID) {
            return false;
        }
        return true;
    }

    /**
     * Можно ли конвертировать в ученика
     * Разрешено при статусах ENROLLED (Записан) и PAID (Оплатил)
     */
    public function canConvertToPupil(): bool
    {
        // Можно конвертировать если статус ENROLLED или PAID и ещё не конвертирован
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_PAID])
               && $this->pupil_id === null;
    }

    /**
     * Уже конвертирован в ученика
     */
    public function isConverted()
    {
        return $this->pupil_id !== null;
    }

    /**
     * Получить основной контактный телефон
     */
    public function getContactPhone()
    {
        if ($this->contact_person === self::CONTACT_PARENT) {
            return $this->parent_phone ?: $this->phone;
        }
        return $this->phone ?: $this->parent_phone;
    }

    /**
     * Получить ФИО контактного лица
     */
    public function getContactName()
    {
        if ($this->contact_person === self::CONTACT_PARENT) {
            return $this->parent_fio ?: $this->fio;
        }
        return $this->fio ?: $this->parent_fio;
    }

    /**
     * Количество дней в текущем статусе
     */
    public function getDaysInStatus()
    {
        $date = $this->status_changed_at ?: $this->created_at;
        if (!$date) {
            return 0;
        }
        return (int)((time() - strtotime($date)) / 86400);
    }

    /**
     * Просрочен ли контакт
     */
    public function isOverdue()
    {
        if (!$this->next_contact_date) {
            return false;
        }
        return strtotime($this->next_contact_date) < strtotime('today');
    }

    /**
     * Контакт сегодня
     */
    public function isContactToday()
    {
        if (!$this->next_contact_date) {
            return false;
        }
        return $this->next_contact_date === DateHelper::today();
    }

    /**
     * Лиды требующие внимания (дата контакта сегодня или просрочена)
     */
    public static function findNeedingAttention()
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['not in', 'status', [self::STATUS_PAID, self::STATUS_LOST]])
            ->andWhere(['<=', 'next_contact_date', DateHelper::today()])
            ->orderBy(['next_contact_date' => SORT_ASC]);
    }

    /**
     * Активные лиды (не в финальных статусах)
     */
    public static function findActive()
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['not in', 'status', [self::STATUS_PAID, self::STATUS_LOST]])
            ->orderBy(['next_contact_date' => SORT_ASC, 'created_at' => SORT_DESC]);
    }

    /**
     * Статистика по воронке
     */
    public static function getFunnelStats()
    {
        $stats = [];
        foreach (self::getStatusList() as $status => $label) {
            $stats[$status] = [
                'label' => $label,
                'count' => self::find()
                    ->byOrganization()
                    ->notDeleted()
                    ->andWhere(['status' => $status])
                    ->count(),
            ];
        }
        return $stats;
    }

    /**
     * Очистить телефон от форматирования
     */
    public static function cleanPhone($phone)
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Получить WhatsApp ссылку
     */
    public function getWhatsAppUrl()
    {
        $phone = self::cleanPhone($this->getContactPhone());
        if (!$phone) {
            return null;
        }
        // Убираем + и заменяем 8 на 7 в начале
        $phone = ltrim($phone, '+');
        if (strpos($phone, '8') === 0) {
            $phone = '7' . substr($phone, 1);
        }
        return 'https://wa.me/' . $phone;
    }

    /**
     * Долго в статусе (> 7 дней)
     */
    public function isStaleInStatus(): bool
    {
        return $this->getDaysInStatus() > 7;
    }

    /**
     * Является ли лид горячим (тег "Горячий")
     */
    public function isHot(): bool
    {
        return $this->hasTagByName('Горячий');
    }

    /**
     * Является ли лид VIP (тег "VIP")
     */
    public function isVip(): bool
    {
        return $this->hasTagByName('VIP');
    }

    // ===================== ДУБЛИКАТЫ =====================

    /**
     * Найти возможные дубликаты по телефону
     *
     * @param string $phone Телефон для поиска
     * @param int|null $excludeId ID лида для исключения (при редактировании)
     * @return Lids[] Массив найденных дубликатов
     */
    public static function findDuplicates($phone, $excludeId = null): array
    {
        if (empty($phone)) {
            return [];
        }

        // Очищаем телефон от форматирования
        $cleanPhone = self::cleanPhone($phone);

        // Если телефон слишком короткий - не ищем
        if (strlen($cleanPhone) < 10) {
            return [];
        }

        // Создаём паттерн для поиска (последние 10 цифр)
        $phonePattern = '%' . substr($cleanPhone, -10);

        $query = self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere([
                'or',
                ['like', 'phone', $phonePattern, false],
                ['like', 'parent_phone', $phonePattern, false],
            ]);

        // Исключаем текущий лид при редактировании
        if ($excludeId) {
            $query->andWhere(['!=', 'id', $excludeId]);
        }

        return $query->orderBy(['created_at' => SORT_DESC])->limit(5)->all();
    }

    /**
     * Проверить, есть ли дубликаты для этого лида
     */
    public function hasDuplicates(): bool
    {
        $phone = $this->getContactPhone();
        if (!$phone) {
            return false;
        }

        return count(self::findDuplicates($phone, $this->id)) > 0;
    }
}
