<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "docs_chapter".
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property string|null $icon
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DocsSection[] $sections
 * @property DocsSection[] $activeSections
 * @property int $sectionCount
 */
class DocsChapter extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'docs_chapter';
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
            [['slug', 'title'], 'required'],
            [['description'], 'string'],
            [['sort_order', 'is_active', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 255],
            [['icon'], 'string', 'max' => 50],
            [['slug'], 'unique'],
            [['is_active'], 'default', 'value' => 1],
            [['is_deleted'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
            [['icon'], 'default', 'value' => 'book'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug' => 'URL-ключ',
            'title' => 'Название',
            'description' => 'Описание',
            'icon' => 'Иконка',
            'sort_order' => 'Порядок сортировки',
            'is_active' => 'Активна',
            'is_deleted' => 'Удалена',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasMany(DocsSection::class, ['chapter_id' => 'id']);
    }

    /**
     * Gets query for active [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActiveSections()
    {
        return $this->hasMany(DocsSection::class, ['chapter_id' => 'id'])
            ->andWhere(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return int
     */
    public function getSectionCount()
    {
        return $this->getActiveSections()->count();
    }

    /**
     * Find chapter by slug
     *
     * @param string $slug
     * @return static|null
     */
    public static function findBySlug($slug)
    {
        return static::find()
            ->where(['slug' => $slug, 'is_active' => 1, 'is_deleted' => 0])
            ->one();
    }

    /**
     * Get all active chapters
     *
     * @return static[]
     */
    public static function getActiveChapters()
    {
        return static::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Get all active chapters with their sections (eager loading)
     *
     * @return static[]
     */
    public static function getActiveChaptersWithSections()
    {
        return static::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->with(['activeSections'])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Get list for dropdown
     *
     * @return array
     */
    public static function getList()
    {
        return static::find()
            ->select(['title', 'id'])
            ->where(['is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Get first section of the chapter
     *
     * @return DocsSection|null
     */
    public function getFirstSection()
    {
        return $this->getActiveSections()->one();
    }

    /**
     * Get URL to this chapter
     *
     * @return string
     */
    public function getUrl()
    {
        return \yii\helpers\Url::to(['/docs/chapter', 'slug' => $this->slug]);
    }
}
