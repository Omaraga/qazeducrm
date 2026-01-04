<?php

namespace app\models;

use app\components\ActiveRecord;
use Yii;
use yii\db\Expression;

/**
 * Модель детализации зарплаты учителя
 *
 * @property int $id
 * @property int $salary_id
 * @property int $lesson_id
 * @property int|null $attendance_id
 * @property int|null $group_id
 * @property int|null $subject_id
 * @property int $students_paid
 * @property int $rate_type
 * @property float $rate_value
 * @property float $amount
 * @property string $lesson_date
 * @property string $created_at
 *
 * @property TeacherSalary $salary
 * @property Lesson $lesson
 * @property Group $group
 */
class TeacherSalaryDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teacher_salary_detail}}';
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
            [['salary_id', 'lesson_id', 'rate_type', 'rate_value', 'amount', 'lesson_date'], 'required'],
            [['salary_id', 'lesson_id', 'attendance_id', 'group_id', 'subject_id', 'students_paid', 'rate_type'], 'integer'],
            [['rate_value', 'amount'], 'number'],
            [['lesson_date', 'created_at'], 'safe'],
            [['salary_id'], 'exist', 'targetClass' => TeacherSalary::class, 'targetAttribute' => 'id'],
            [['lesson_id'], 'exist', 'targetClass' => Lesson::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'salary_id' => 'Зарплата',
            'lesson_id' => 'Урок',
            'attendance_id' => 'Посещение',
            'group_id' => 'Группа',
            'subject_id' => 'Предмет',
            'students_paid' => 'Учеников с оплатой',
            'rate_type' => 'Тип ставки',
            'rate_value' => 'Ставка',
            'amount' => 'Сумма',
            'lesson_date' => 'Дата урока',
            'created_at' => 'Создано',
        ];
    }

    /**
     * Связь с зарплатой
     */
    public function getSalary()
    {
        return $this->hasOne(TeacherSalary::class, ['id' => 'salary_id']);
    }

    /**
     * Связь с уроком
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    /**
     * Связь с группой
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Название типа ставки
     */
    public function getRateTypeLabel()
    {
        $list = TeacherRate::getRateTypeList();
        return $list[$this->rate_type] ?? 'Неизвестно';
    }

    /**
     * Форматированная сумма
     */
    public function getFormattedAmount()
    {
        return number_format($this->amount, 0, ',', ' ') . ' ₸';
    }

    /**
     * Форматированная ставка
     */
    public function getFormattedRate()
    {
        if ($this->rate_type == TeacherRate::RATE_PERCENT) {
            return $this->rate_value . '%';
        }
        return number_format($this->rate_value, 0, ',', ' ') . ' ₸';
    }
}
