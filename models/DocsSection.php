<?php

namespace app\models;

use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "docs_section".
 *
 * @property int $id
 * @property int $chapter_id
 * @property string $slug
 * @property string $title
 * @property string|null $content
 * @property string|null $excerpt
 * @property array|null $screenshots
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DocsChapter $chapter
 */
class DocsSection extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'docs_section';
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
            [['chapter_id', 'slug', 'title'], 'required'],
            [['chapter_id', 'sort_order', 'is_active', 'is_deleted'], 'integer'],
            [['content', 'excerpt'], 'string'],
            [['screenshots'], 'safe'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 255],
            [['excerpt'], 'string', 'max' => 500],
            [['slug'], 'unique', 'targetAttribute' => ['chapter_id', 'slug'], 'message' => 'Этот URL-ключ уже используется в данной главе'],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocsChapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
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
            'chapter_id' => 'Глава',
            'slug' => 'URL-ключ',
            'title' => 'Заголовок',
            'content' => 'Содержимое',
            'excerpt' => 'Краткое описание',
            'screenshots' => 'Скриншоты',
            'sort_order' => 'Порядок сортировки',
            'is_active' => 'Активна',
            'is_deleted' => 'Удалена',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter()
    {
        return $this->hasOne(DocsChapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Find section by slug within a chapter
     *
     * @param string $slug
     * @param int $chapterId
     * @return static|null
     */
    public static function findBySlug($slug, $chapterId)
    {
        return static::find()
            ->where([
                'slug' => $slug,
                'chapter_id' => $chapterId,
                'is_active' => 1,
                'is_deleted' => 0
            ])
            ->one();
    }

    /**
     * Find section by chapter slug and section slug
     *
     * @param string $chapterSlug
     * @param string $sectionSlug
     * @return static|null
     */
    public static function findBySlugs($chapterSlug, $sectionSlug)
    {
        return static::find()
            ->alias('s')
            ->innerJoin(['c' => DocsChapter::tableName()], 's.chapter_id = c.id')
            ->where([
                'c.slug' => $chapterSlug,
                's.slug' => $sectionSlug,
                'c.is_active' => 1,
                'c.is_deleted' => 0,
                's.is_active' => 1,
                's.is_deleted' => 0
            ])
            ->one();
    }

    /**
     * Search sections by keyword using FULLTEXT
     *
     * @param string $keyword
     * @param int $limit
     * @return static[]
     */
    public static function search($keyword, $limit = 20)
    {
        if (empty(trim($keyword))) {
            return [];
        }

        $keyword = trim($keyword);

        return static::find()
            ->alias('s')
            ->innerJoin(['c' => DocsChapter::tableName()], 's.chapter_id = c.id')
            ->where([
                'c.is_active' => 1,
                'c.is_deleted' => 0,
                's.is_active' => 1,
                's.is_deleted' => 0
            ])
            ->andWhere(['or',
                ['like', 's.title', $keyword],
                ['like', 's.content', $keyword],
                ['like', 's.excerpt', $keyword],
            ])
            ->with(['chapter'])
            ->orderBy(['s.chapter_id' => SORT_ASC, 's.sort_order' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    /**
     * Get previous and next sections for navigation
     *
     * @return array ['prev' => DocsSection|null, 'next' => DocsSection|null]
     */
    public function getPrevNextSections()
    {
        $chapters = DocsChapter::getActiveChaptersWithSections();

        $allSections = [];
        foreach ($chapters as $chapter) {
            foreach ($chapter->activeSections as $section) {
                $allSections[] = $section;
            }
        }

        $currentIndex = null;
        foreach ($allSections as $index => $section) {
            if ($section->id === $this->id) {
                $currentIndex = $index;
                break;
            }
        }

        return [
            'prev' => $currentIndex !== null && $currentIndex > 0 ? $allSections[$currentIndex - 1] : null,
            'next' => $currentIndex !== null && $currentIndex < count($allSections) - 1 ? $allSections[$currentIndex + 1] : null,
        ];
    }

    /**
     * Extract headings (h2, h3) from content for Table of Contents
     *
     * @return array [['id' => 'anchor', 'text' => 'Title', 'level' => 2], ...]
     */
    public function getHeadings()
    {
        if (empty($this->content)) {
            return [];
        }

        $headings = [];
        preg_match_all('/<h([23])[^>]*(?:id=["\']([^"\']*)["\'])?[^>]*>([^<]*)<\/h\1>/i', $this->content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = (int)$match[1];
            $id = !empty($match[2]) ? $match[2] : $this->generateAnchor($match[3]);
            $text = strip_tags($match[3]);

            $headings[] = [
                'id' => $id,
                'text' => $text,
                'level' => $level,
            ];
        }

        return $headings;
    }

    /**
     * Generate anchor from text
     *
     * @param string $text
     * @return string
     */
    protected function generateAnchor($text)
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s]+/', '-', $text);
        return $text ?: 'section';
    }

    /**
     * Get URL to this section
     *
     * @return string
     */
    public function getUrl()
    {
        return \yii\helpers\Url::to(['/docs/section', 'chapter' => $this->chapter->slug, 'slug' => $this->slug]);
    }

    /**
     * Get excerpt or generate from content
     *
     * @param int $length
     * @return string
     */
    public function getExcerptText($length = 150)
    {
        if (!empty($this->excerpt)) {
            return $this->excerpt;
        }

        if (empty($this->content)) {
            return '';
        }

        $text = strip_tags($this->content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }

    /**
     * Highlight search query in text
     *
     * @param string $text
     * @param string $query
     * @return string
     */
    public static function highlightSearchQuery($text, $query)
    {
        if (empty($query)) {
            return $text;
        }

        $query = preg_quote($query, '/');
        return preg_replace('/(' . $query . ')/iu', '<mark class="bg-yellow-200">$1</mark>', $text);
    }
}
