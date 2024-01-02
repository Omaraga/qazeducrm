<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "typical_schedule".
 *
 * @property int $id
 * @property int|null $organization_id
 * @property int|null $group_id
 * @property int|null $teacher_id
 * @property int|null $week
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $date
 * @property int|null $is_deleted
 * @property string|null $info
 * @property string $created_at
 *
 * @property Group $group
 * @property User $teacher
 *
 */
class TypicalSchedule extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'typical_schedule';
    }

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
    public function rules()
    {
        return [
            [['group_id', 'teacher_id', 'week'], 'integer'],
            [['group_id', 'teacher_id', 'week', 'start_time', 'end_time'], 'required'],
            [['start_time', 'end_time', 'date', 'created_at'], 'safe'],
            [['start_time', 'end_time'], 'time','format' => 'php:H:i'],
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->validate()){
            return false;
        }
        $this->date = $this->getDate();

        return parent::save($runValidation, $attributeNames); // TODO: Change the autogenerated stub
    }

    private function getDate(){
        return '2024-01-0'.$this->week;
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'organization_id' => Yii::t('main', 'Organization ID'),
            'group_id' => Yii::t('main', 'Группа'),
            'teacher_id' => Yii::t('main', 'Преподаватель'),
            'week' => Yii::t('main', 'День недели'),
            'start_time' => Yii::t('main', ' Время начала'),
            'end_time' => Yii::t('main', ' Время окончания'),
            'date' => Yii::t('main', 'Дата'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'info' => Yii::t('main', 'Info'),
            'created_at' => Yii::t('main', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup(){
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacher(){
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }
}
