<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель ставок учителей
 *
 * @property int $id
 * @property int $organization_id
 * @property int $teacher_id
 * @property int|null $subject_id
 * @property int|null $group_id
 * @property int $rate_type
 * @property float $rate_value
 * @property bool $is_active
 * @property int $is_deleted
 * @property string|null $info
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $teacher
 * @property Subject $subject
 * @property Group $group
 */
class TeacherRate extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Типы ставок
    const RATE_PER_STUDENT = 1;  // За каждого ученика
    const RATE_PER_LESSON = 2;   // За урок (фиксированная)
    const RATE_PERCENT = 3;      // Процент от оплаты ученика

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teacher_rate}}';
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
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['teacher_id', 'rate_type', 'rate_value'], 'required'],
            [['organization_id', 'teacher_id', 'subject_id', 'group_id', 'rate_type', 'is_deleted'], 'integer'],
            [['rate_value'], 'number', 'min' => 0],
            [['is_active'], 'boolean'],
            [['info'], 'string'],
            [['teacher_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['subject_id'], 'exist', 'targetClass' => Subject::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['group_id'], 'exist', 'targetClass' => Group::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            ['rate_type', 'in', 'range' => [self::RATE_PER_STUDENT, self::RATE_PER_LESSON, self::RATE_PERCENT]],
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
            'teacher_id' => 'Преподаватель',
            'subject_id' => 'Предмет',
            'group_id' => 'Группа',
            'rate_type' => 'Тип ставки',
            'rate_value' => 'Значение ставки',
            'is_active' => 'Активна',
            'is_deleted' => 'Удалена',
            'info' => 'Доп. информация',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Связь с преподавателем
     */
    public function getTeacher()
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    /**
     * Связь с предметом
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    /**
     * Связь с группой
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Список типов ставок
     */
    public static function getRateTypeList()
    {
        return [
            self::RATE_PER_STUDENT => 'За ученика',
            self::RATE_PER_LESSON => 'За урок',
            self::RATE_PERCENT => 'Процент',
        ];
    }

    /**
     * Название типа ставки
     */
    public function getRateTypeLabel()
    {
        $list = self::getRateTypeList();
        return $list[$this->rate_type] ?? 'Неизвестно';
    }

    /**
     * Форматированное значение ставки
     */
    public function getFormattedRate()
    {
        if ($this->rate_type == self::RATE_PERCENT) {
            return $this->rate_value . '%';
        }
        return number_format($this->rate_value, 0, ',', ' ') . ' ₸';
    }

    /**
     * Описание области применения ставки
     */
    public function getScopeLabel()
    {
        $parts = [];
        if ($this->subject_id) {
            $parts[] = $this->subject ? $this->subject->name : 'Предмет #' . $this->subject_id;
        }
        if ($this->group_id) {
            $parts[] = $this->group ? $this->group->name : 'Группа #' . $this->group_id;
        }
        return $parts ? implode(', ', $parts) : 'Все предметы и группы';
    }

    /**
     * Найти ставку для учителя, предмета и группы
     * Возвращает наиболее специфичную ставку (группа > предмет > общая)
     */
    public static function findRate($teacherId, $subjectId = null, $groupId = null)
    {
        $organizationId = Organizations::getCurrentOrganizationId();

        // Сначала ищем ставку для конкретной группы
        if ($groupId) {
            $rate = self::find()
                ->byOrganization()
                ->andWhere([
                    'teacher_id' => $teacherId,
                    'group_id' => $groupId,
                    'is_active' => 1,
                ])
                ->notDeleted()
                ->one();
            if ($rate) return $rate;
        }

        // Затем для конкретного предмета
        if ($subjectId) {
            $rate = self::find()
                ->byOrganization()
                ->andWhere([
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'group_id' => null,
                    'is_active' => 1,
                ])
                ->notDeleted()
                ->one();
            if ($rate) return $rate;
        }

        // Общая ставка учителя
        return self::find()
            ->byOrganization()
            ->andWhere([
                'teacher_id' => $teacherId,
                'subject_id' => null,
                'group_id' => null,
                'is_active' => 1,
            ])
            ->notDeleted()
            ->one();
    }
}
