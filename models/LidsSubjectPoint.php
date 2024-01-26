<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
/**
 * This is the model class for table "lids_subject_point".
 *
 * @property int $id
 * @property int|null $lid_id
 * @property int|null $subject_id
 * @property int|null $point
 * @property int|null $is_deleted
 */
class LidsSubjectPoint extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lids_subject_point';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lid_id', 'subject_id', 'point', 'is_deleted'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'lid_id' => Yii::t('main', 'Lid ID'),
            'subject_id' => Yii::t('main', 'Subject ID'),
            'point' => Yii::t('main', 'Point'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
        ];
    }
}
