<?php

namespace app\models;

use app\helpers\Lists;
use app\traits\AttributesToInfoTrait;
use Yii;
use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
/**
 * This is the model class for table "tariff".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $status
 * @property int $duration
 * @property int|null $lesson_amount
 * @property int $type
 * @property int|null $price
 * @property string|null $description
 * @property string|null $info
 * @property int $organization_id [int(11)]
 * @property int $is_deleted [int(1)]
 */
class Tariff extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait, AttributesToInfoTrait;

    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tariff';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'info'], 'string'],
            [['status', 'duration', 'lesson_amount', 'type', 'price'], 'integer'],
            [['duration', 'type', 'name'], 'required'],
            [['lesson_amount'], 'integer', 'min' => 0, 'when' => function($model){
                return $model->duration === 3;
            }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'status' => 'Статус',
            'statusLabel' => 'Статус',
            'duration' => 'Продолжительность',
            'durationLabel' => 'Продолжительность',
            'lesson_amount' => 'Кол-во занятий',
            'type' => 'Тип тарифа',
            'typeLabel' => 'Тип тарифа',
            'price' => 'Стоимость',
            'description' => 'Описание',
            'info' => 'Info',
        ];
    }

    /**
     * @return array
     */
    public static function getStatusList(){
        return [
          self::STATUS_ACTIVE => Yii::t('main', 'Активный'),
          self::STATUS_ARCHIVE => Yii::t('main', 'Архивный'),
        ];
    }

    /**
     * @return mixed
     */
    public function getStatusLabel(){
        return self::getStatusList()[$this->status];
    }

    /**
     * @return mixed
     */
    public function getDurationLabel(){
        return Lists::getTariffDurations()[$this->duration];
    }

    /**
     *
     */
    public function getTypeLabel(){
        return Lists::getTariffTypes()[$this->type];
    }
}
