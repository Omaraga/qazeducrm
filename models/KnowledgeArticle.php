<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "knowledge_article".
 *
 * @property int $id
 * @property int $category_id
 * @property string $slug
 * @property string $title
 * @property string|null $content
 * @property string|null $excerpt
 * @property string|null $icon
 * @property int $sort_order
 * @property int $views
 * @property int $is_featured
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property KnowledgeCategory $category
 */
class KnowledgeArticle extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge_article';
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
            [['category_id', 'slug', 'title'], 'required'],
            [['category_id', 'sort_order', 'views', 'is_featured', 'is_active', 'is_deleted'], 'integer'],
            [['content', 'excerpt'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug'], 'string', 'max' => 200],
            [['title'], 'string', 'max' => 500],
            [['icon'], 'string', 'max' => 50],
            [['slug'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => KnowledgeCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['is_active'], 'default', 'value' => 1],
            [['is_deleted'], 'default', 'value' => 0],
            [['is_featured'], 'default', 'value' => 0],
            [['views'], 'default', 'value' => 0],
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
            'category_id' => 'Категория',
            'slug' => 'URL-ключ',
            'title' => 'Заголовок',
            'content' => 'Содержимое',
            'excerpt' => 'Краткое описание',
            'icon' => 'Иконка',
            'sort_order' => 'Порядок сортировки',
            'views' => 'Просмотры',
            'is_featured' => 'Избранная',
            'is_active' => 'Активна',
            'is_deleted' => 'Удалена',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(KnowledgeCategory::class, ['id' => 'category_id']);
    }

    /**
     * Find article by slug
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
     * Increment view counter
     */
    public function incrementViews()
    {
        $this->updateCounters(['views' => 1]);
    }

    /**
     * Get featured articles
     *
     * @param int $limit
     * @return static[]
     */
    public static function getFeatured($limit = 5)
    {
        return static::find()
            ->where(['is_featured' => 1, 'is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    /**
     * Search articles by keyword
     *
     * @param string $keyword
     * @param int $limit
     * @return static[]
     */
    public static function search($keyword, $limit = 20)
    {
        return static::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->andWhere(['or',
                ['like', 'title', $keyword],
                ['like', 'content', $keyword],
                ['like', 'excerpt', $keyword],
            ])
            ->orderBy(['is_featured' => SORT_DESC, 'views' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Get related articles from same category
     *
     * @param int $limit
     * @return static[]
     */
    public function getRelatedArticles($limit = 3)
    {
        return static::find()
            ->where(['category_id' => $this->category_id, 'is_active' => 1, 'is_deleted' => 0])
            ->andWhere(['<>', 'id', $this->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->limit($limit)
            ->all();
    }
}
