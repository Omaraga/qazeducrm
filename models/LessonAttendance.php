<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "lesson_attendance".
 *
 * @property int $id
 * @property int|null $organization_id
 * @property int $pupil_id
 * @property int $lesson_id
 * @property int $teacher_id
 * @property int|null $status
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 *
 * @property Lesson $lesson
 * @property Pupil $pupil
 */
class LessonAttendance extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    protected static $_multiple = false;

    const STATUS_VISIT = 1; // Посещение - ученик был на уроке, преподаватель получит оплату за ученика;
    const STATUS_MISS_WITH_PAY = 2; // Пропуск (с оплатой) - ученика не было на уроке, преподаватель получит оплату за ученика. Используется когда преподаватель явился ради ученика. В основном на индивиульных занятиях;
    const STATUS_MISS_WITHOUT_PAY = 3; // Пропуск (без оплаты) - ученик был на уроке, преподаватель не получит оплату за ученика. Используется в обычных групповых занятиях;
    const STATUS_MISS_VALID_REASON = 4; //Уваж. причина - пропуск урока по уважительной причине. Ученик не был на уроке, преподаватель не получит оплату за ученика. Урок ученика переносится (оплата не сгорает).

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
        return 'lesson_attendance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pupil_id', 'lesson_id', 'teacher_id', 'status'], 'integer'],
            [['pupil_id', 'lesson_id', 'teacher_id'], 'required'],
            [['lesson_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lesson::class, 'targetAttribute' => ['lesson_id' => 'id']],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
            [['pupil_id'], function() {
                if ($this->isNewRecord) {
                    if (static::find()
                        ->byOrganization()
                        ->andWhere([
                            "pupil_id" => $this->pupil_id,
                            "lesson_id" => $this->lesson_id
                        ])->exists()
                    ) {
                        $this->addError("pupil_id", \Yii::t("main", "Связь уже существует"));
                        return false;
                    }
                }
                return true;
            }, 'when' => function() {
                return !static::$_multiple;
            }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'organization_id' => Yii::t('main', 'Organization ID'),
            'pupil_id' => Yii::t('main', 'Pupil ID'),
            'lesson_id' => Yii::t('main', 'Lesson ID'),
            'teacher_id' => Yii::t('main', 'Teacher ID'),
            'status' => Yii::t('main', 'Status'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'info' => Yii::t('main', 'Info'),
        ];
    }

    /**
     * Gets query for [[Lesson]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    /**
     * Gets query for [[Pupil]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPupil()
    {
        return $this->hasOne(Pupil::class, ['id' => 'pupil_id']);
    }

    public function getStatusLabel(){
        if (!$this->status){
            return Yii::t('main', 'Не выставлена');
        }
        return self::getStatusList()[$this->status];
    }

    public static function getStatusList(){
        return [
            self::STATUS_VISIT => Yii::t('main', 'Посещение'),
            self::STATUS_MISS_WITH_PAY => Yii::t('main', 'Пропуск (c оплатой)'),
            self::STATUS_MISS_WITHOUT_PAY => Yii::t('main', 'Пропуск (без оплаты)'),
            self::STATUS_MISS_VALID_REASON => Yii::t('main', 'Уваж. причина'),
        ];
    }
}
