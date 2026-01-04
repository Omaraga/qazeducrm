<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationActivityLog;
use app\models\SaasPlan;
use app\modules\superadmin\models\search\OrganizationSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * OrganizationController - CRUD организаций и филиалов.
 */
class OrganizationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'activate' => ['POST'],
                    'suspend' => ['POST'],
                    'block' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список организаций
     */
    public function actionIndex()
    {
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр организации
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Филиалы
        $branches = Organizations::find()
            ->andWhere(['parent_id' => $id, 'is_deleted' => 0])
            ->all();

        // Активная подписка
        $subscription = $model->getActiveSubscription();

        // Логи активности
        $activityLogs = OrganizationActivityLog::find()
            ->andWhere(['organization_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->all();

        return $this->render('view', [
            'model' => $model,
            'branches' => $branches,
            'subscription' => $subscription,
            'activityLogs' => $activityLogs,
        ]);
    }

    /**
     * Создание организации
     */
    public function actionCreate()
    {
        $model = new Organizations();
        $model->status = Organizations::STATUS_ACTIVE;
        $model->type = Organizations::TYPE_HEAD;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Создаём пробную подписку
            $freePlan = SaasPlan::findByCode(SaasPlan::CODE_FREE);
            if ($freePlan) {
                $subscription = OrganizationSubscription::createTrial($model->id, $freePlan->id);
                $subscription->save();
            }

            // Логируем
            OrganizationActivityLog::log(
                $model->id,
                OrganizationActivityLog::ACTION_REGISTERED,
                OrganizationActivityLog::CATEGORY_GENERAL,
                'Организация создана через супер-админку'
            );

            Yii::$app->session->setFlash('success', 'Организация успешно создана.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Создание филиала
     */
    public function actionCreateBranch($parent_id)
    {
        $parent = $this->findModel($parent_id);

        $model = new Organizations();
        $model->parent_id = $parent_id;
        $model->type = Organizations::TYPE_BRANCH;
        $model->status = Organizations::STATUS_ACTIVE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            OrganizationActivityLog::log(
                $parent_id,
                OrganizationActivityLog::ACTION_BRANCH_CREATED,
                OrganizationActivityLog::CATEGORY_GENERAL,
                "Создан филиал: {$model->name}"
            );

            Yii::$app->session->setFlash('success', 'Филиал успешно создан.');
            return $this->redirect(['view', 'id' => $parent_id]);
        }

        return $this->render('create-branch', [
            'model' => $model,
            'parent' => $parent,
        ]);
    }

    /**
     * Редактирование организации
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Логируем изменение статуса
            if ($oldStatus !== $model->status) {
                OrganizationActivityLog::log(
                    $model->id,
                    OrganizationActivityLog::ACTION_STATUS_CHANGED,
                    OrganizationActivityLog::CATEGORY_STATUS,
                    "Статус изменён с {$oldStatus} на {$model->status}",
                    $oldStatus,
                    $model->status
                );
            }

            Yii::$app->session->setFlash('success', 'Организация успешно обновлена.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление организации (soft delete)
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->is_deleted = 1;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Организация удалена.');
        return $this->redirect(['index']);
    }

    /**
     * Активация организации
     */
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;
        $model->status = Organizations::STATUS_ACTIVE;
        $model->save(false);

        OrganizationActivityLog::log(
            $model->id,
            OrganizationActivityLog::ACTION_STATUS_CHANGED,
            OrganizationActivityLog::CATEGORY_STATUS,
            "Организация активирована",
            $oldStatus,
            $model->status
        );

        Yii::$app->session->setFlash('success', 'Организация активирована.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Приостановка организации
     */
    public function actionSuspend($id)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;
        $model->status = Organizations::STATUS_SUSPENDED;
        $model->save(false);

        OrganizationActivityLog::log(
            $model->id,
            OrganizationActivityLog::ACTION_STATUS_CHANGED,
            OrganizationActivityLog::CATEGORY_STATUS,
            "Организация приостановлена",
            $oldStatus,
            $model->status
        );

        Yii::$app->session->setFlash('warning', 'Организация приостановлена.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Блокировка организации
     */
    public function actionBlock($id)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;
        $model->status = Organizations::STATUS_BLOCKED;
        $model->save(false);

        OrganizationActivityLog::log(
            $model->id,
            OrganizationActivityLog::ACTION_STATUS_CHANGED,
            OrganizationActivityLog::CATEGORY_STATUS,
            "Организация заблокирована",
            $oldStatus,
            $model->status
        );

        Yii::$app->session->setFlash('danger', 'Организация заблокирована.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Поиск модели по ID
     */
    protected function findModel($id)
    {
        if (($model = Organizations::find()->andWhere(['id' => $id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Организация не найдена.');
    }
}
