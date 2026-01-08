<?php

namespace app\models;

use app\helpers\Lists;
use app\models\enum\StatusEnum;
use app\models\relations\EducationGroup;
use app\models\relations\TeacherGroup;
use app\services\SubscriptionLimitService;
use Yii;
use yii\db\Expression;
use app\components\ActiveRecord;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property int|null $subject_id
 * @property string $code
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $type
 * @property string|null $color
 * @property int|null $is_deleted
 * @property string|null $info
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 * @property Subject $subject
 * @property int $status [int(1)]
 * @property int $organization_id [int(11)]
 * @property int $editor_id [int(11)]
 */
class Group extends ActiveRecord
{
    const TYPE_GROUP = 1;
    const TYPE_INDIVIDUAL = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group';
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
            [['subject_id', 'category_id', 'type', 'is_deleted'], 'integer'],
            [['code'], 'required'],
            [['info'], 'string'],
            [['code', 'name', 'color'], 'string', 'max' => 255],
            ['editor_id', 'default', 'value' => function() {
                return Yii::$app->user && !Yii::$app->user->isGuest
                    ? Yii::$app->user->identity->id
                    : null;
            }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        // Проверка лимита только при создании новой группы
        if ($insert) {
            $limitService = SubscriptionLimitService::forCurrentOrganization();
            if ($limitService && !$limitService->canAddGroup()) {
                $this->addError('id', SubscriptionLimitService::getLimitErrorMessage('group'));
                return false;
            }
        }
        return parent::beforeSave($insert);
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subject_id' => Yii::t('main', 'Предмет'),
            'subjectLabel' => Yii::t('main', 'Предмет'),
            'code' => Yii::t('main', 'Код группы'),
            'name' => Yii::t('main', 'Наименование'),
            'category_id' => Yii::t('main', 'Категория группы'),
            'categoryLabel' => Yii::t('main', 'Категория группы'),
            'type' => Yii::t('main', 'Тип занятий'),
            'color' => Yii::t('main', 'Цвет'),
            'status' => Yii::t('main', 'Статус'),
            'statusLabel' => Yii::t('main', 'Статус'),
            'is_deleted' => 'Is Deleted',
            'info' => 'Info',
        ];
    }

    public static function getTypeList(){
        return [
            self::TYPE_GROUP => Yii::t('main', 'Групповые'),
            self::TYPE_INDIVIDUAL => Yii::t('main', 'Индивидуальные'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject(){
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
    }

    /**
     * @return string
     */
    public function getStatusLabel(): string
    {
        $statuses = StatusEnum::getStatusList();
        return $statuses[$this->status] ?? 'Не указан';
    }

    /**
     * @return string
     */
    public function getSubjectLabel(): string
    {
        return $this->subject?->name ?? 'Не указан';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeacherRelations(){
        return $this->hasMany(TeacherGroup::class, ['target_id' => 'id']);
    }


    /**
     * @return string
     */
    public function getCategoryLabel(): string
    {
        if ($this->category_id === null) {
            return 'Не указана';
        }
        $categories = Lists::getGroupCategories();
        return $categories[$this->category_id] ?? 'Не указана';
    }

    /**
     * @return string
     */
    public function getNameFull(){
        return $this->code.' - '.$this->name;
    }

    /**
     * Связь с записями учеников в группе
     * @return \yii\db\ActiveQuery
     */
    public function getEducationGroups()
    {
        return $this->hasMany(EducationGroup::class, ['group_id' => 'id'])
            ->andWhere(['education_group.is_deleted' => 0]);
    }

    /**
     * Количество учеников в группе
     * @return int
     */
    public function getPupilsCount(): int
    {
        return (int) EducationGroup::find()
            ->where(['group_id' => $this->id])
            ->andWhere(['is_deleted' => 0])
            ->count();
    }
}
