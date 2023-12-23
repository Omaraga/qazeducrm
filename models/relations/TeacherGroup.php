<?php

namespace app\models\relations;

use app\traits\AttributesToInfoTrait;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\models\User;
use app\models\Group;
use app\components\ActiveRecord;

/**
 * This is the model class for table "teacher_group".
 *
 * @property int $id
 * @property int|null $organization_id
 * @property int $related_id Учитель
 * @property int $target_id Группа
 * @property int|null $is_deleted
 * @property int|null $type
 * @property int|null $price
 * @property string|null $info
 *
 * @property User $teacher
 * @property Group $group
 */
class TeacherGroup extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait, AttributesToInfoTrait;

    const PRICE_TYPE_FIX = 1;
    const PRICE_TYPE_PERCENT = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'teacher_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'related_id', 'target_id', 'is_deleted', 'type', 'price'], 'integer'],
            [['related_id', 'target_id'], 'required'],
            [['info'], 'string'],
            [['related_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['related_id' => 'id']],
            [['target_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['target_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'organization_id' => Yii::t('app', 'Organization ID'),
            'related_id' => Yii::t('app', 'Преподаватель'),
            'target_id' => Yii::t('app', 'Группа'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'type' => Yii::t('app', 'Тип оплаты'),
            'typeLabel' => Yii::t('app', 'Тип оплаты'),
            'price' => Yii::t('app', 'Стоимость'),
            'info' => Yii::t('app', 'Info'),
        ];
    }

    /**
     * Gets query for [[Related]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(User::class, ['id' => 'related_id']);
    }

    /**
     * Gets query for [[Target]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'target_id']);
    }

    /**
     * @return array
     */
    public static function getPriceTypeList(){
        return [
            self::PRICE_TYPE_FIX => Yii::t('main', 'Фиксированная'),
            self::PRICE_TYPE_PERCENT => Yii::t('main', 'Процент'),
        ];
    }

    public function getTypeLabel(){
        return self::getPriceTypeList()[$this->type];
    }
}
