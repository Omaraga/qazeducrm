<?php

namespace app\models;

use app\helpers\Lists;
use app\models\relations\TariffSubject;
use app\traits\AttributesToInfoTrait;
use Yii;
use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use yii\helpers\ArrayHelper;

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
 * @property string $durationLabel
 * @property string $subjectsLabel
 * @property string $nameFull
 * @property int $organization_id [int(11)]
 * @property int $is_deleted [int(1)]
 * @property TariffSubject[] $subjectsRelation
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
            'subjectsLabel' => 'Предмет',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectsRelation(){
        return $this->hasMany(TariffSubject::class, ['tariff_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getSubjectsLabel(){
        $subjects = TariffSubject::find()->where(['tariff_id' => $this->id])->all();
        $result = [];
        foreach ($subjects as $subject){
            $result[] = $subject->getSubjectName().'('.self::getAmounts()[$subject->lesson_amount].')';
        }
        return implode(', ', $result);
    }

    /**
     * @return array
     */
    public static function getSubjectsMap(){
        return ArrayHelper::merge([-1 => \Yii::t('main', 'Любой предмет')],ArrayHelper::map(Subject::find()->byOrganization()->all(), 'id', 'name'));
    }

    /**
     * @return string
     */
    public function getNameFull(){
        return $this->name.' ('.$this->durationLabel.'); '.$this->price.'; Предмет: '.$this->subjectsLabel;;
    }

    /**
     * @return array
     */
    public static function getAmounts(){
        return [
            1  => \Yii::t('main', '1 раз в неделю'),
            2  => \Yii::t('main', '2 раза в неделю'),
            3  => \Yii::t('main', '3 раза в неделю'),
            4  => \Yii::t('main', '4 раза в неделю'),
            5  => \Yii::t('main', '5 раз в неделю'),
            6  => \Yii::t('main', '6 раз в неделю'),
            7  => \Yii::t('main', '7 раз в неделю'),
            8  => \Yii::t('main', '8 раз в неделю'),
            9  => \Yii::t('main', '9 раз в неделю'),
            10  => \Yii::t('main', '10 раз в неделю'),
            11  => \Yii::t('main', '11 раз в неделю'),
            12  => \Yii::t('main', '12 раз в неделю'),
        ];

    }
}
