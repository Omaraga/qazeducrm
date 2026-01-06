<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Модель связи лидов и тегов
 *
 * @property int $id
 * @property int $lid_id
 * @property int $tag_id
 * @property string $created_at
 *
 * @property Lids $lid
 * @property LidTag $tag
 */
class LidTagRelation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lid_tag_relations}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lid_id', 'tag_id'], 'required'],
            [['lid_id', 'tag_id'], 'integer'],
            [['lid_id', 'tag_id'], 'unique', 'targetAttribute' => ['lid_id', 'tag_id'], 'message' => 'Этот тег уже добавлен'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lid_id' => 'Лид',
            'tag_id' => 'Тег',
            'created_at' => 'Добавлен',
        ];
    }

    /**
     * Связь с лидом
     */
    public function getLid()
    {
        return $this->hasOne(Lids::class, ['id' => 'lid_id']);
    }

    /**
     * Связь с тегом
     */
    public function getTag()
    {
        return $this->hasOne(LidTag::class, ['id' => 'tag_id']);
    }
}
