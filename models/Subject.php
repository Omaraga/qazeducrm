<?php

namespace app\models;

use app\traits\AttributesToInfoTrait;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;
use app\components\ActiveRecord;

/**
 * This is the model class for table "subject".
 *
 * @property int $id
 * @property int|null $organization_id
 * @property string|null $name
 * @property int $is_deleted
 * @property int|null $order_col
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 */
class Subject extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subject';
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
            [['is_deleted', 'order_col', 'organization_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'is_deleted' => 'Is Deleted',
            'order_col' => 'Order Col',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата редактирования',
            'info' => 'Info',
        ];
    }
}
