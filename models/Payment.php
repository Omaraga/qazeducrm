<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property int|null $organization_id
 * @property int $pupil_id
 * @property int|null $purpose_id
 * @property int|null $method_id
 * @property int $type
 * @property string|null $number
 * @property float|null $amount
 * @property string $date
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $comment
 *
 * @property PayMethod $method
 * @property Pupil $pupil
 * @property Organizations $organization
 */
class Payment extends ActiveRecord
{
    const TYPE_PAY = 1;
    const TYPE_REFUND = 2;

    const PURPOSE_EDUCATION = 1;
    const PURPOSE_MATERIAL = 2;
    const PURPOSE_DEFAULT = 3;


    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
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
            [['organization_id', 'pupil_id', 'purpose_id', 'method_id', 'type', 'is_deleted'], 'integer'],
            [['pupil_id', 'type', 'date'], 'required'],
            [['amount'], 'number'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['comment'], 'string'],
            [['number'], 'string', 'max' => 255],
            [['method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PayMethod::class, 'targetAttribute' => ['method_id' => 'id']],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
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
            'purpose_id' => Yii::t('main', 'Назначение'),
            'method_id' => Yii::t('main', 'Метод оплаты'),
            'number' => Yii::t('main', '№ кватанции'),
            'date' => Yii::t('main', 'Дата'),
            'amount' => Yii::t('main', 'Сумма'),
            'type' => Yii::t('main', 'Тип'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'comment' => Yii::t('main', 'Примечание'),
        ];
    }

    /**
     * Gets query for [[Method]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMethod()
    {
        return $this->hasOne(PayMethod::class, ['id' => 'method_id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization(){
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * @return array
     */
    public static function getPurposeList(){
        return [
            self::PURPOSE_EDUCATION => Yii::t('main', 'Оплата за обучение'),
            self::PURPOSE_MATERIAL => Yii::t('main', 'Оплата за материалы'),
            self::PURPOSE_DEFAULT => Yii::t('main', 'Простой платеж'),
        ];
    }

    /**
     * @return mixed
     */
    public function getPurposeLabel(){
        return self::getPurposeList()[$this->purpose_id];
    }

    /**
     * @return mixed
     */
    public function getTypeLabel(){
        return self::getTypeList()[$this->type];
    }

    /**
     * @return array
     */
    public static function getTypeList(){
        return [
            self::TYPE_PAY => Yii::t('main', 'Платеж'),
            self::TYPE_REFUND => Yii::t('main', 'Возврат'),
        ];
    }
}
