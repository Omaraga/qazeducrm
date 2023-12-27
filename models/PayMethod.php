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
 * @property string|null $name
 * @property int $is_deleted
 * @property int|null $order_col
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 * @property int $organization_id [int(11)]
 * @property Organizations $organization
 */
class PayMethod extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_method';
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
            [['is_deleted', 'organization_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['info'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization(){
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
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
            'organization_id' => 'Соответствует организации',
            'info' => 'Info',
        ];
    }
}
