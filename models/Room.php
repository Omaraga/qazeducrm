<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;
use app\components\ActiveRecord;

/**
 * This is the model class for table "room".
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string|null $code
 * @property int|null $capacity
 * @property string|null $color
 * @property int|null $sort_order
 * @property int|null $is_deleted
 * @property string|null $info
 * @property string $created_at
 * @property string $updated_at
 */
class Room extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * Default room colors
     */
    const DEFAULT_COLORS = [
        '#6366f1', // indigo
        '#8b5cf6', // violet
        '#ec4899', // pink
        '#f43f5e', // rose
        '#f97316', // orange
        '#eab308', // yellow
        '#22c55e', // green
        '#14b8a6', // teal
        '#06b6d4', // cyan
        '#3b82f6', // blue
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room';
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
            [['name'], 'required'],
            [['capacity', 'sort_order', 'is_deleted'], 'integer'],
            [['info'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 20],
            [['color'], 'string', 'max' => 7],
            [['color'], 'default', 'value' => '#6366f1'],
            [['capacity'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => Yii::t('main', 'Организация'),
            'name' => Yii::t('main', 'Название'),
            'code' => Yii::t('main', 'Код/Номер'),
            'capacity' => Yii::t('main', 'Вместимость'),
            'color' => Yii::t('main', 'Цвет'),
            'sort_order' => Yii::t('main', 'Порядок'),
            'is_deleted' => 'Is Deleted',
            'info' => 'Info',
            'created_at' => Yii::t('main', 'Создано'),
            'updated_at' => Yii::t('main', 'Обновлено'),
        ];
    }

    /**
     * Get full display name with code
     * @return string
     */
    public function getFullName()
    {
        return $this->code ? $this->code . ' - ' . $this->name : $this->name;
    }

    /**
     * Get rooms list for dropdown
     * @return array
     */
    public static function getList()
    {
        return self::find()
            ->select(['id', 'name', 'code'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Get rooms as id => name array for dropdown
     * @return array
     */
    public static function getDropdownList()
    {
        $rooms = self::getList();
        $result = [];
        foreach ($rooms as $room) {
            $name = $room['code'] ? $room['code'] . ' - ' . $room['name'] : $room['name'];
            $result[$room['id']] = $name;
        }
        return $result;
    }
}
