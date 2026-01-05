<?php

namespace app\modules\crm\controllers;

use app\models\KnowledgeArticle;
use app\models\KnowledgeCategory;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * KnowledgeController - база знаний для пользователей
 */
class KnowledgeController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'], // Доступ всем авторизованным
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Главная страница базы знаний
     *
     * @return string
     */
    public function actionIndex()
    {
        $categories = KnowledgeCategory::getActiveCategories();
        $featured = KnowledgeArticle::getFeatured(6);

        return $this->render('index', [
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }

    /**
     * Просмотр категории со списком статей
     *
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategory($slug)
    {
        $category = KnowledgeCategory::findBySlug($slug);

        if ($category === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Категория не найдена.'));
        }

        $articles = $category->getActiveArticles()->all();

        return $this->render('category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    /**
     * Просмотр статьи
     *
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($slug)
    {
        $article = KnowledgeArticle::findBySlug($slug);

        if ($article === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Статья не найдена.'));
        }

        // Увеличиваем счётчик просмотров
        $article->incrementViews();

        $relatedArticles = $article->getRelatedArticles(3);

        return $this->render('view', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    /**
     * Поиск по базе знаний
     *
     * @return string
     */
    public function actionSearch()
    {
        $query = Yii::$app->request->get('q', '');
        $articles = [];

        if (strlen($query) >= 2) {
            $articles = KnowledgeArticle::search($query);
        }

        return $this->render('search', [
            'query' => $query,
            'articles' => $articles,
        ]);
    }
}
