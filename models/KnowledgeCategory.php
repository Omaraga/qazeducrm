<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "knowledge_category".
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property KnowledgeArticle[] $articles
 * @property KnowledgeArticle[] $activeArticles
 */
class KnowledgeCategory extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge_category';
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
            [['slug', 'name'], 'required'],
            [['description'], 'string'],
            [['sort_order', 'is_active', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 255],
            [['icon'], 'string', 'max' => 50],
            [['slug'], 'unique'],
            [['is_active'], 'default', 'value' => 1],
            [['is_deleted'], 'default', 'value' => 0],
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
            'slug' => 'URL-ключ',
            'name' => 'Название',
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
     * Gets query for [[Articles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(KnowledgeArticle::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for active [[Articles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActiveArticles()
    {
        return $this->hasMany(KnowledgeArticle::class, ['category_id' => 'id'])
            ->andWhere(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return int
     */
    public function getArticleCount()
    {
        return $this->getActiveArticles()->count();
    }

    /**
     * Find category by slug
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
     * Get all active categories
     *
     * @return static[]
     */
    public static function getActiveCategories()
    {
        return static::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
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
            ->select(['name', 'id'])
            ->where(['is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
