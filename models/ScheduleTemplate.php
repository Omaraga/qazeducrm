<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "schedule_template".
 *
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property int $is_default
 * @property int $is_active
 * @property int $is_deleted
 * @property string|null $info
 * @property string $created_at
 * @property string $updated_at
 *
 * @property TypicalSchedule[] $typicalSchedules
 * @property int $lessonsCount
 */
class ScheduleTemplate extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule_template';
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
            [['organization_id', 'is_default', 'is_active', 'is_deleted'], 'integer'],
            [['description', 'info'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 7],
            [['is_default', 'is_active'], 'default', 'value' => 0],
            [['is_deleted'], 'default', 'value' => 0],
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
            'name' => Yii::t('main', 'Название'),
            'description' => Yii::t('main', 'Описание'),
            'color' => Yii::t('main', 'Цвет'),
            'is_default' => Yii::t('main', 'По умолчанию'),
            'is_active' => Yii::t('main', 'Активен'),
            'is_deleted' => Yii::t('main', 'Is Deleted'),
            'info' => Yii::t('main', 'Info'),
            'created_at' => Yii::t('main', 'Создано'),
            'updated_at' => Yii::t('main', 'Обновлено'),
        ];
    }

    /**
     * Перед сохранением: убираем is_default у других шаблонов
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Если этот шаблон устанавливается как дефолтный, убираем флаг у других
        if ($this->is_default) {
            self::updateAll(
                ['is_default' => 0],
                [
                    'and',
                    ['organization_id' => $this->organization_id],
                    ['!=', 'id', $this->id ?: 0]
                ]
            );
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypicalSchedules()
    {
        return $this->hasMany(TypicalSchedule::class, ['template_id' => 'id'])
            ->andWhere(['is_deleted' => 0]);
    }

    /**
     * Количество занятий в шаблоне
     * @return int
     */
    public function getLessonsCount()
    {
        return $this->getTypicalSchedules()->count();
    }

    /**
     * Получить шаблон по умолчанию для текущей организации
     * @return ScheduleTemplate|null
     */
    public static function getDefault()
    {
        return self::find()
            ->byOrganization()
            ->andWhere(['is_default' => 1, 'is_deleted' => 0])
            ->one();
    }

    /**
     * Получить список шаблонов для dropdown
     * @return array
     */
    public static function getList()
    {
        $templates = self::find()
            ->byOrganization()
            ->andWhere(['is_deleted' => 0, 'is_active' => 1])
            ->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])
            ->all();

        return ArrayHelper::map($templates, 'id', 'name');
    }

    /**
     * Получить все активные шаблоны для текущей организации
     * @return ScheduleTemplate[]
     */
    public static function getActiveTemplates()
    {
        return self::find()
            ->byOrganization()
            ->andWhere(['is_deleted' => 0, 'is_active' => 1])
            ->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])
            ->all();
    }

    /**
     * Дублировать шаблон со всеми занятиями
     * @param string|null $newName
     * @return ScheduleTemplate|null
     */
    public function duplicate($newName = null)
    {
        $newTemplate = new self();
        $newTemplate->attributes = $this->attributes;
        $newTemplate->id = null;
        $newTemplate->name = $newName ?: $this->name . ' (копия)';
        $newTemplate->is_default = 0;
        $newTemplate->created_at = null;
        $newTemplate->updated_at = null;

        if (!$newTemplate->save()) {
            return null;
        }

        // Копируем все занятия
        foreach ($this->typicalSchedules as $lesson) {
            $newLesson = new TypicalSchedule();
            $newLesson->attributes = $lesson->attributes;
            $newLesson->id = null;
            $newLesson->template_id = $newTemplate->id;
            $newLesson->created_at = null;
            $newLesson->updated_at = null;
            $newLesson->save(false);
        }

        return $newTemplate;
    }
}
