<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pupil_education".
 *
 * @property int $id
 * @property int $pupil_id
 * @property int $tariff_id
 * @property int|null $sale
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $comment
 * @property float|null $tariff_price
 * @property float|null $total_price
 * @property int|null $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $create_user
 * @property int|null $update_user
 * @property string|null $info
 *
 * @property Pupil $pupil
 * @property Tariff $tariff
 */
class PupilEducation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pupil_education';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pupil_id', 'tariff_id'], 'required'],
            [['pupil_id', 'tariff_id', 'sale', 'is_deleted', 'create_user', 'update_user'], 'integer'],
            [['date_start', 'date_end', 'created_at', 'updated_at'], 'safe'],
            [['comment', 'info'], 'string'],
            [['tariff_price', 'total_price'], 'number'],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
            [['tariff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tariff::class, 'targetAttribute' => ['tariff_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'pupil_id' => Yii::t('main', 'Pupil ID'),
            'tariff_id' => Yii::t('main', 'Tariff ID'),
            'sale' => Yii::t('main', 'Sale'),
            'date_start' => Yii::t('main', 'Date Start'),
            'date_end' => Yii::t('main', 'Date End'),
            'comment' => Yii::t('main', 'Comment'),
            'tariff_price' => Yii::t('main', 'Tariff Price'),
            'total_price' => Yii::t('main', 'Total Price'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'created_at' => Yii::t('main', 'Created At'),
            'updated_at' => Yii::t('main', 'Updated At'),
            'create_user' => Yii::t('main', 'Create User'),
            'update_user' => Yii::t('main', 'Update User'),
            'info' => Yii::t('main', 'Info'),
        ];
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
     * Gets query for [[Tariff]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }
}
