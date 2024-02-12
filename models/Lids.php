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
 * @property string|null $comment
 * @property int|null $sale
 * @property int|null $total_sum
 * @property int|null $total_point
 * @property int|null $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $info
 * @property int $organization_id [int(11)]
 */
class Lids extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

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
            [['fio', 'comment', 'info'], 'string'],
            [['class_id', 'sale', 'total_sum', 'total_point', 'is_deleted'], 'integer'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['phone', 'school', 'manager_name'], 'string', 'max' => 255],
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
            'comment' => Yii::t('main', 'Коментарии'),
            'sale' => Yii::t('main', 'Скидка'),
            'total_sum' => Yii::t('main', 'Итоговая цена'),
            'total_point' => Yii::t('main', 'Итоговые баллы'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'info' => Yii::t('main', 'Info'),
        ];
    }
}
