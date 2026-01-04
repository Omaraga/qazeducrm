<?php

namespace app\modules\superadmin\controllers;

use app\models\SaasPlan;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * PlanController - CRUD тарифных планов.
 */
class PlanController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список тарифных планов
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SaasPlan::find()->orderBy(['sort_order' => SORT_ASC]),
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр плана
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Статистика использования
        $activeSubscriptions = $model->getSubscriptions()
            ->andWhere(['in', 'status', ['trial', 'active']])
            ->count();

        return $this->render('view', [
            'model' => $model,
            'activeSubscriptions' => $activeSubscriptions,
        ]);
    }

    /**
     * Создание плана
     */
    public function actionCreate()
    {
        $model = new SaasPlan();
        $model->is_active = 1;
        $model->trial_days = 14;

        if ($model->load(Yii::$app->request->post())) {
            // Обработка features JSON
            $features = Yii::$app->request->post('features', []);
            $model->features = json_encode(array_filter($features));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Тарифный план создан.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование плана
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $features = Yii::$app->request->post('features', []);
            $model->features = json_encode(array_filter($features));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Тарифный план обновлён.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление плана
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Проверяем, есть ли активные подписки
        $activeCount = $model->getSubscriptions()
            ->andWhere(['in', 'status', ['trial', 'active']])
            ->count();

        if ($activeCount > 0) {
            Yii::$app->session->setFlash('error', "Невозможно удалить план: есть {$activeCount} активных подписок.");
            return $this->redirect(['index']);
        }

        $model->is_deleted = 1;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Тарифный план удалён.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = SaasPlan::findWithDeleted()->andWhere(['id' => $id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Тарифный план не найден.');
    }
}
