<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationSubscriptionRequest;
use app\models\OrganizationActivityLog;
use app\models\SaasPlan;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * SubscriptionController - управление подписками организаций.
 */
class SubscriptionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'activate' => ['POST'],
                    'suspend' => ['POST'],
                    'cancel' => ['POST'],
                    'approve-request' => ['POST'],
                    'reject-request' => ['POST'],
                    'complete-request' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список подписок
     */
    public function actionIndex()
    {
        $query = OrganizationSubscription::find()
            ->with(['organization', 'saasPlan'])
            ->orderBy(['created_at' => SORT_DESC]);

        // Фильтры
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $expiring = Yii::$app->request->get('expiring');
        if ($expiring) {
            $query->andWhere(['in', 'status', ['trial', 'active']])
                ->andWhere(['<=', 'expires_at', date('Y-m-d H:i:s', strtotime('+7 days'))])
                ->andWhere(['>=', 'expires_at', date('Y-m-d H:i:s')]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр подписки
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Создание подписки для организации
     */
    public function actionCreate($organization_id = null)
    {
        $model = new OrganizationSubscription();
        $model->status = OrganizationSubscription::STATUS_TRIAL;
        $model->billing_period = OrganizationSubscription::PERIOD_MONTHLY;

        if ($organization_id) {
            $model->organization_id = $organization_id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $plan = SaasPlan::findOne($model->saas_plan_id);

            // Устанавливаем даты
            $model->started_at = date('Y-m-d H:i:s');
            if ($model->status === OrganizationSubscription::STATUS_TRIAL) {
                $model->trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$plan->trial_days} days"));
                $model->expires_at = $model->trial_ends_at;
            } else {
                $period = $model->billing_period === 'yearly' ? '+1 year' : '+1 month';
                $model->expires_at = date('Y-m-d H:i:s', strtotime($period));
            }

            if ($model->save()) {
                OrganizationActivityLog::log(
                    $model->organization_id,
                    OrganizationActivityLog::ACTION_SUBSCRIPTION_CREATED,
                    OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                    "Создана подписка: {$plan->name}"
                );

                Yii::$app->session->setFlash('success', 'Подписка создана.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $organizations = Organizations::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['or', ['parent_id' => null], ['type' => 'head']])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $plans = SaasPlan::find()->andWhere(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC])->all();

        return $this->render('create', [
            'model' => $model,
            'organizations' => $organizations,
            'plans' => $plans,
        ]);
    }

    /**
     * Активация подписки
     */
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        $model->activate($model->billing_period);

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_SUBSCRIPTION_ACTIVATED,
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Подписка активирована"
        );

        Yii::$app->session->setFlash('success', 'Подписка активирована.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Приостановка подписки
     */
    public function actionSuspend($id)
    {
        $model = $this->findModel($id);
        $model->suspend();

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_SUBSCRIPTION_EXPIRED,
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Подписка приостановлена"
        );

        Yii::$app->session->setFlash('warning', 'Подписка приостановлена.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Отмена подписки
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->cancel();

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_SUBSCRIPTION_CANCELLED,
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Подписка отменена"
        );

        Yii::$app->session->setFlash('danger', 'Подписка отменена.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Продление подписки
     */
    public function actionExtend($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $period = Yii::$app->request->post('period', 'monthly');
            $model->billing_period = $period;
            $model->status = OrganizationSubscription::STATUS_ACTIVE;

            $periodAdd = $period === 'yearly' ? '+1 year' : '+1 month';

            // Продлеваем от текущей даты окончания или от сегодня
            $startDate = $model->expires_at && strtotime($model->expires_at) > time()
                ? $model->expires_at
                : date('Y-m-d H:i:s');

            $model->expires_at = date('Y-m-d H:i:s', strtotime($periodAdd, strtotime($startDate)));
            $model->save();

            OrganizationActivityLog::log(
                $model->organization_id,
                OrganizationActivityLog::ACTION_SUBSCRIPTION_ACTIVATED,
                OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                "Подписка продлена до " . Yii::$app->formatter->asDate($model->expires_at, 'php:d.m.Y')
            );

            Yii::$app->session->setFlash('success', 'Подписка продлена.');
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('extend', [
            'model' => $model,
        ]);
    }

    /**
     * Список запросов на изменение подписки
     */
    public function actionRequests()
    {
        $query = OrganizationSubscriptionRequest::find()
            ->with(['organization', 'requestedPlan', 'currentPlan', 'processedByUser'])
            ->orderBy(['created_at' => SORT_DESC]);

        // Фильтр по статусу
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        // Количество ожидающих для бейджа
        $pendingCount = OrganizationSubscriptionRequest::getPendingCount();

        return $this->render('requests', [
            'dataProvider' => $dataProvider,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Просмотр запроса
     */
    public function actionViewRequest($id)
    {
        $model = $this->findRequest($id);

        return $this->render('view-request', [
            'model' => $model,
        ]);
    }

    /**
     * Одобрить запрос
     */
    public function actionApproveRequest($id)
    {
        $model = $this->findRequest($id);

        if (!$model->isPending()) {
            Yii::$app->session->setFlash('error', 'Запрос уже обработан.');
            return $this->redirect(['requests']);
        }

        $adminComment = Yii::$app->request->post('admin_comment');

        if ($model->approve($adminComment)) {
            OrganizationActivityLog::log(
                $model->organization_id,
                OrganizationActivityLog::ACTION_SUBSCRIPTION_ACTIVATED,
                OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                "Запрос на {$model->getTypeLabel()} одобрен"
            );

            Yii::$app->session->setFlash('success', 'Запрос одобрен. Не забудьте создать/продлить подписку.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при одобрении запроса.');
        }

        return $this->redirect(['view-request', 'id' => $id]);
    }

    /**
     * Отклонить запрос
     */
    public function actionRejectRequest($id)
    {
        $model = $this->findRequest($id);

        if (!$model->isPending()) {
            Yii::$app->session->setFlash('error', 'Запрос уже обработан.');
            return $this->redirect(['requests']);
        }

        $adminComment = Yii::$app->request->post('admin_comment');

        if (empty($adminComment)) {
            Yii::$app->session->setFlash('error', 'Укажите причину отклонения.');
            return $this->redirect(['view-request', 'id' => $id]);
        }

        if ($model->reject($adminComment)) {
            Yii::$app->session->setFlash('warning', 'Запрос отклонён.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при отклонении запроса.');
        }

        return $this->redirect(['requests']);
    }

    /**
     * Отметить запрос как выполненный
     */
    public function actionCompleteRequest($id)
    {
        $model = $this->findRequest($id);

        if ($model->status === OrganizationSubscriptionRequest::STATUS_COMPLETED) {
            Yii::$app->session->setFlash('info', 'Запрос уже выполнен.');
            return $this->redirect(['requests']);
        }

        $adminComment = Yii::$app->request->post('admin_comment');

        if ($model->complete($adminComment)) {
            Yii::$app->session->setFlash('success', 'Запрос отмечен как выполненный.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении запроса.');
        }

        return $this->redirect(['requests']);
    }

    protected function findModel($id)
    {
        if (($model = OrganizationSubscription::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Подписка не найдена.');
    }

    protected function findRequest($id)
    {
        if (($model = OrganizationSubscriptionRequest::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрос не найден.');
    }
}
