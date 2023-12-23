<?php

namespace app\models\relations;

use app\models\Subject;
use Yii;

/**
 * This is the model class for table "tariff_subject".
 *
 * @property int $id
 * @property int|null $tariff_id
 * @property int|null $subject_id
 * @property int|null $lesson_amount
 * @property int|null $is_deleted
 * @property string|null $info
 * @property Subject $subject
 */
class TariffSubject extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tariff_subject';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tariff_id', 'subject_id', 'lesson_amount', 'is_deleted'], 'integer'],
            [['info'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tariff_id' => Yii::t('app', 'Tariff ID'),
            'subject_id' => Yii::t('app', 'Subject ID'),
            'lesson_amount' => Yii::t('app', 'Lesson Amount'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'info' => Yii::t('app', 'Info'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject(){
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    /**
     * @return string|null
     */
    public function getSubjectName(){
        if ($this->subject_id === -1){
            return Yii::t('main', 'Любой предмет');
        }else{
            return $this->subject->name;
        }
    }

}
