<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property int|null $subject_id
 * @property string $code
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $type
 * @property string|null $color
 * @property int|null $is_deleted
 * @property string|null $info
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group';
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
            [['subject_id', 'category_id', 'type', 'is_deleted'], 'integer'],
            [['code'], 'required'],
            [['info'], 'string'],
            [['code', 'name', 'color'], 'string', 'max' => 255],
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subject_id' => 'Subject ID',
            'code' => 'Code',
            'name' => 'Name',
            'category_id' => 'Category ID',
            'type' => 'Type',
            'color' => 'Color',
            'is_deleted' => 'Is Deleted',
            'info' => 'Info',
        ];
    }
}
