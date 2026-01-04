<?php

namespace app\models;

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
            [['class_id', 'sale', 'total_sum', 'total_point', 'is_deleted', 'status', 'manager_id'], 'integer'],
            [['date', 'created_at', 'updated_at', 'next_contact_date'], 'safe'],
            [['phone', 'school', 'manager_name', 'source'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['status', 'in', 'range' => array_keys(self::getStatusList())],
            ['source', 'in', 'range' => array_keys(self::getSourceList())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'fio' => Yii::t('main', 'ФИО'),
            'phone' => Yii::t('main', 'Телефон'),
            'class_id' => Yii::t('main', 'Класс'),
            'school' => Yii::t('main', 'Школа'),
            'date' => Yii::t('main', 'Дата'),
            'manager_name' => Yii::t('main', 'Менеджер'),
            'manager_id' => Yii::t('main', 'Ответственный'),
            'comment' => Yii::t('main', 'Коментарии'),
            'status' => Yii::t('main', 'Статус'),
            'source' => Yii::t('main', 'Источник'),
            'next_contact_date' => Yii::t('main', 'Следующий контакт'),
            'lost_reason' => Yii::t('main', 'Причина потери'),
            'sale' => Yii::t('main', 'Скидка'),
            'total_sum' => Yii::t('main', 'Итоговая цена'),
            'total_point' => Yii::t('main', 'Итоговые баллы'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'info' => Yii::t('main', 'Info'),
        ];
    }

    /**
     * Связь с менеджером
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
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
     * Можно ли перевести в следующий статус
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
     * Лиды требующие внимания (дата контакта сегодня или просрочена)
     */
    public static function findNeedingAttention()
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['not in', 'status', [self::STATUS_PAID, self::STATUS_LOST]])
            ->andWhere(['<=', 'next_contact_date', date('Y-m-d')])
            ->orderBy(['next_contact_date' => SORT_ASC]);
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
}
