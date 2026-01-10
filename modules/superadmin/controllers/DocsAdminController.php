<?php

namespace app\modules\superadmin\controllers;

use app\helpers\SystemRoles;
use app\models\DocsChapter;
use app\models\DocsSection;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * DocsAdminController - администрирование документации
 */
class DocsAdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest &&
                                Yii::$app->user->identity->system_role === SystemRoles::SUPER;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Список глав и секций
     *
     * @return string
     */
    public function actionIndex()
    {
        $chapters = DocsChapter::find()
            ->where(['is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->with(['sections' => function ($query) {
                $query->andWhere(['is_deleted' => 0])->orderBy(['sort_order' => SORT_ASC]);
            }])
            ->all();

        return $this->render('index', [
            'chapters' => $chapters,
        ]);
    }

    /**
     * Создание главы
     *
     * @return string|\yii\web\Response
     */
    public function actionCreateChapter()
    {
        $model = new DocsChapter();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Глава создана');
            return $this->redirect(['index']);
        }

        return $this->render('chapter-form', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование главы
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateChapter($id)
    {
        $model = $this->findChapter($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Глава обновлена');
            return $this->redirect(['index']);
        }

        return $this->render('chapter-form', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление главы
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteChapter($id)
    {
        $model = $this->findChapter($id);
        $model->is_deleted = 1;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Глава удалена');
        return $this->redirect(['index']);
    }

    /**
     * Создание секции
     *
     * @param int $chapter_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreateSection($chapter_id)
    {
        $chapter = $this->findChapter($chapter_id);
        $model = new DocsSection();
        $model->chapter_id = $chapter_id;

        // Установить следующий sort_order
        $maxOrder = DocsSection::find()
            ->where(['chapter_id' => $chapter_id, 'is_deleted' => 0])
            ->max('sort_order');
        $model->sort_order = ($maxOrder ?? 0) + 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Раздел создан');
            return $this->redirect(['index']);
        }

        return $this->render('section-form', [
            'model' => $model,
            'chapter' => $chapter,
        ]);
    }

    /**
     * Редактирование секции
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateSection($id)
    {
        $model = $this->findSection($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Раздел обновлён');
            return $this->redirect(['index']);
        }

        return $this->render('section-form', [
            'model' => $model,
            'chapter' => $model->chapter,
        ]);
    }

    /**
     * Удаление секции
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteSection($id)
    {
        $model = $this->findSection($id);
        $model->is_deleted = 1;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Раздел удалён');
        return $this->redirect(['index']);
    }

    /**
     * Загрузка изображения для редактора
     *
     * @return array
     */
    public function actionUploadImage()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            return ['error' => 'Файл не загружен'];
        }

        // Создаём директорию если не существует
        $uploadDir = Yii::getAlias('@webroot/images/docs/uploads');
        FileHelper::createDirectory($uploadDir);

        // Генерируем уникальное имя файла
        $fileName = time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
        $filePath = $uploadDir . '/' . $fileName;

        if ($file->saveAs($filePath)) {
            return [
                'location' => '/images/docs/uploads/' . $fileName,
            ];
        }

        return ['error' => 'Ошибка сохранения файла'];
    }

    /**
     * Обновление порядка сортировки (AJAX)
     *
     * @return array
     */
    public function actionReorder()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $type = Yii::$app->request->post('type');
        $items = Yii::$app->request->post('items', []);

        if ($type === 'chapters') {
            foreach ($items as $order => $id) {
                DocsChapter::updateAll(['sort_order' => $order], ['id' => $id]);
            }
        } elseif ($type === 'sections') {
            foreach ($items as $order => $id) {
                DocsSection::updateAll(['sort_order' => $order], ['id' => $id]);
            }
        }

        return ['success' => true];
    }

    /**
     * Предпросмотр секции
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPreview($id)
    {
        $model = $this->findSection($id);
        return $this->render('preview', ['model' => $model]);
    }

    /**
     * Поиск главы по ID
     *
     * @param int $id
     * @return DocsChapter
     * @throws NotFoundHttpException
     */
    protected function findChapter($id)
    {
        $model = DocsChapter::findOne(['id' => $id, 'is_deleted' => 0]);
        if ($model === null) {
            throw new NotFoundHttpException('Глава не найдена');
        }
        return $model;
    }

    /**
     * Поиск секции по ID
     *
     * @param int $id
     * @return DocsSection
     * @throws NotFoundHttpException
     */
    protected function findSection($id)
    {
        $model = DocsSection::findOne(['id' => $id, 'is_deleted' => 0]);
        if ($model === null) {
            throw new NotFoundHttpException('Раздел не найден');
        }
        return $model;
    }
}
