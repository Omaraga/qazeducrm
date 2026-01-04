<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationPayment;
use app\models\OrganizationSubscription;
use app\models\OrganizationActivityLog;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * PaymentController - управление платежами организаций.
 */
class PaymentController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'complete' => ['POST'],
                    'fail' => ['POST'],
                    'refund' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список платежей
     */
    public function actionIndex()
    {
        $query = OrganizationPayment::find()
            ->with(['organization', 'subscription'])
            ->orderBy(['created_at' => SORT_DESC]);

        // Фильтры
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $organizationId = Yii::$app->request->get('organization_id');
        if ($organizationId) {
            $query->andWhere(['organization_id' => $organizationId]);
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
     * Просмотр платежа
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Создание платежа
     */
    public function actionCreate($organization_id = null, $subscription_id = null)
    {
        $model = new OrganizationPayment();
        $model->status = OrganizationPayment::STATUS_PENDING;
        $model->currency = 'KZT';

        if ($organization_id) {
            $model->organization_id = $organization_id;
        }
        if ($subscription_id) {
            $model->subscription_id = $subscription_id;
            $subscription = OrganizationSubscription::findOne($subscription_id);
            if ($subscription) {
                $model->organization_id = $subscription->organization_id;
                // Автозаполнение суммы
                if ($subscription->saasPlan) {
                    $model->amount = $subscription->billing_period === 'yearly'
                        ? $subscription->saasPlan->price_yearly
                        : $subscription->saasPlan->price_monthly;
                }
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // Устанавливаем период
            $subscription = OrganizationSubscription::findOne($model->subscription_id);
            if ($subscription) {
                $model->period_start = $subscription->expires_at ?: date('Y-m-d H:i:s');
                $periodAdd = $subscription->billing_period === 'yearly' ? '+1 year' : '+1 month';
                $model->period_end = date('Y-m-d H:i:s', strtotime($periodAdd, strtotime($model->period_start)));
            }

            if ($model->save()) {
                OrganizationActivityLog::log(
                    $model->organization_id,
                    OrganizationActivityLog::ACTION_PAYMENT_RECEIVED,
                    OrganizationActivityLog::CATEGORY_PAYMENT,
                    "Создан платёж на сумму {$model->amount} {$model->currency}"
                );

                Yii::$app->session->setFlash('success', 'Платёж создан.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $organizations = Organizations::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['or', ['parent_id' => null], ['type' => 'head']])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $subscriptions = OrganizationSubscription::find()
            ->with(['organization', 'saasPlan'])
            ->andWhere(['in', 'status', ['trial', 'active', 'expired']])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('create', [
            'model' => $model,
            'organizations' => $organizations,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Подтверждение платежа
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);
        $model->status = OrganizationPayment::STATUS_COMPLETED;
        $model->processed_at = date('Y-m-d H:i:s');
        $model->processed_by = Yii::$app->user->id;
        $model->save();

        // Продлеваем подписку
        if ($model->subscription) {
            $subscription = $model->subscription;
            $subscription->status = OrganizationSubscription::STATUS_ACTIVE;
            $subscription->expires_at = $model->period_end;
            $subscription->save();
        }

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_PAYMENT_RECEIVED,
            OrganizationActivityLog::CATEGORY_PAYMENT,
            "Платёж #{$model->id} подтверждён"
        );

        Yii::$app->session->setFlash('success', 'Платёж подтверждён, подписка продлена.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Пометить платёж как неудавшийся
     */
    public function actionFail($id)
    {
        $model = $this->findModel($id);
        $model->status = OrganizationPayment::STATUS_FAILED;
        $model->save();

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_PAYMENT_FAILED,
            OrganizationActivityLog::CATEGORY_PAYMENT,
            "Платёж #{$model->id} не прошёл"
        );

        Yii::$app->session->setFlash('warning', 'Платёж помечен как неудавшийся.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Возврат платежа
     */
    public function actionRefund($id)
    {
        $model = $this->findModel($id);
        $model->status = OrganizationPayment::STATUS_REFUNDED;
        $model->save();

        OrganizationActivityLog::log(
            $model->organization_id,
            OrganizationActivityLog::ACTION_PAYMENT_REFUNDED,
            OrganizationActivityLog::CATEGORY_PAYMENT,
            "Платёж #{$model->id} возвращён"
        );

        Yii::$app->session->setFlash('info', 'Платёж возвращён.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Удаление платежа
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Платёж удалён.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = OrganizationPayment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Платёж не найден.');
    }
}
