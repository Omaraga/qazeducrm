<?php

namespace app\modules\superadmin\controllers;

use app\models\KnowledgeArticle;
use app\models\KnowledgeCategory;
use app\modules\superadmin\models\search\KnowledgeArticleSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * KnowledgeController - управление базой знаний в супер-админке.
 */
class KnowledgeController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-category' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список статей
     */
    public function actionIndex()
    {
        $searchModel = new KnowledgeArticleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => KnowledgeCategory::getList(),
        ]);
    }

    /**
     * Просмотр статьи
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Создание статьи
     */
    public function actionCreate()
    {
        $model = new KnowledgeArticle();
        $model->is_active = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Статья успешно создана.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => KnowledgeCategory::getList(),
        ]);
    }

    /**
     * Редактирование статьи
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Статья успешно обновлена.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => KnowledgeCategory::getList(),
        ]);
    }

    /**
     * Удаление статьи (soft delete)
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Статья удалена.');
        return $this->redirect(['index']);
    }

    /**
     * Список категорий
     */
    public function actionCategories()
    {
        $categories = KnowledgeCategory::find()
            ->where(['is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Создание категории
     */
    public function actionCreateCategory()
    {
        $model = new KnowledgeCategory();
        $model->is_active = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Категория успешно создана.');
            return $this->redirect(['categories']);
        }

        return $this->render('category-form', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование категории
     */
    public function actionUpdateCategory($id)
    {
        $model = $this->findCategory($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Категория успешно обновлена.');
            return $this->redirect(['categories']);
        }

        return $this->render('category-form', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление категории (soft delete)
     */
    public function actionDeleteCategory($id)
    {
        $model = $this->findCategory($id);

        // Проверяем наличие статей
        $articleCount = KnowledgeArticle::find()
            ->where(['category_id' => $id, 'is_deleted' => 0])
            ->count();

        if ($articleCount > 0) {
            Yii::$app->session->setFlash('error', "Невозможно удалить категорию. В ней есть {$articleCount} статей.");
            return $this->redirect(['categories']);
        }

        $model->delete();

        Yii::$app->session->setFlash('success', 'Категория удалена.');
        return $this->redirect(['categories']);
    }

    /**
     * Поиск статьи по ID
     */
    protected function findModel($id)
    {
        if (($model = KnowledgeArticle::find()->andWhere(['id' => $id, 'is_deleted' => 0])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Статья не найдена.');
    }

    /**
     * Поиск категории по ID
     */
    protected function findCategory($id)
    {
        if (($model = KnowledgeCategory::find()->andWhere(['id' => $id, 'is_deleted' => 0])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Категория не найдена.');
    }
}
