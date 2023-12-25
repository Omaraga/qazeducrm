<?php

namespace app\models\relations;

use app\models\Group;
use app\models\PupilEducation;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "education_group".
 *
 * @property int $id
 * @property int $education_id
 * @property int $group_id
 * @property int|null $subject_id
 * @property int|null $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 *
 * @property PupilEducation $education
 * @property Group $group
 * @property int $organization_id [int(11)]
 * @property int $pupil_id [int(11)]
 */
class EducationGroup extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'education_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['education_id', 'group_id'], 'required'],
            [['education_id', 'group_id', 'subject_id', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info'], 'string'],
            [['education_id'], 'exist', 'skipOnError' => true, 'targetClass' => PupilEducation::class, 'targetAttribute' => ['education_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'education_id' => Yii::t('main', 'Education ID'),
            'group_id' => Yii::t('main', 'Группа'),
            'subject_id' => Yii::t('main', 'Subject Io'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'info' => Yii::t('main', 'Info'),
        ];
    }

    /**
     * Gets query for [[Education]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEducation()
    {
        return $this->hasOne(PupilEducation::class, ['id' => 'education_id']);
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }
}
