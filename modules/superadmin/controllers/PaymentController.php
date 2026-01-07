<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationPayment;
use app\models\OrganizationSubscription;
use app\models\OrganizationActivityLog;
use app\services\ManagerSalesService;
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
                    'pay-bonus' => ['POST'],
                    'cancel-bonus' => ['POST'],
                    'pay-all-bonuses' => ['POST'],
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
            ->with(['organization', 'subscription', 'manager'])
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

        $managerId = Yii::$app->request->get('manager_id');
        if ($managerId) {
            $query->andWhere(['manager_id' => $managerId]);
        }

        $bonusStatus = Yii::$app->request->get('bonus_status');
        if ($bonusStatus) {
            $query->andWhere(['manager_bonus_status' => $bonusStatus]);
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
        $model->manager_bonus_percent = 10; // Default 10%

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
                    $model->original_amount = $model->amount;
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

            // Сохраняем original_amount если не указана
            if (empty($model->original_amount)) {
                $model->original_amount = $model->amount;
            }

            // Рассчитываем скидку
            if ($model->original_amount > $model->amount) {
                $model->discount_amount = $model->original_amount - $model->amount;
            }

            if ($model->save()) {
                OrganizationActivityLog::log(
                    $model->organization_id,
                    OrganizationActivityLog::ACTION_PAYMENT_RECEIVED,
                    OrganizationActivityLog::CATEGORY_PAYMENT,
                    "Создан платёж на сумму {$model->amount} {$model->currency}" .
                    ($model->manager_id ? " (менеджер: {$model->manager->name})" : '')
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

        $managers = ManagerSalesService::getManagersList();

        return $this->render('create', [
            'model' => $model,
            'organizations' => $organizations,
            'subscriptions' => $subscriptions,
            'managers' => $managers,
        ]);
    }

    /**
     * Подтверждение платежа
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        // Используем метод confirm() который рассчитает бонус менеджера
        if ($model->confirm(Yii::$app->user->id)) {
            // Продлеваем подписку
            if ($model->subscription) {
                $subscription = $model->subscription;
                $subscription->status = OrganizationSubscription::STATUS_ACTIVE;
                $subscription->expires_at = $model->period_end;
                $subscription->save();
            }

            $message = 'Платёж подтверждён, подписка продлена.';
            if ($model->manager_bonus_amount > 0) {
                $message .= ' Бонус менеджера: ' . number_format($model->manager_bonus_amount, 0, '.', ' ') . ' KZT';
            }

            Yii::$app->session->setFlash('success', $message);
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка подтверждения платежа.');
        }

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

    /**
     * Выплатить бонус менеджеру
     */
    public function actionPayBonus($id)
    {
        $model = $this->findModel($id);

        if ($model->payBonus()) {
            OrganizationActivityLog::log(
                $model->organization_id,
                OrganizationActivityLog::ACTION_PAYMENT_RECEIVED,
                OrganizationActivityLog::CATEGORY_PAYMENT,
                "Выплачен бонус {$model->manager_bonus_amount} KZT менеджеру {$model->manager->name}"
            );
            Yii::$app->session->setFlash('success', 'Бонус выплачен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка выплаты бонуса.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Отменить бонус
     */
    public function actionCancelBonus($id)
    {
        $model = $this->findModel($id);

        if ($model->cancelBonus()) {
            Yii::$app->session->setFlash('warning', 'Бонус отменён.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка отмены бонуса.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Выплатить все ожидающие бонусы менеджера
     */
    public function actionPayAllBonuses($manager_id)
    {
        $service = new ManagerSalesService();
        $result = $service->payAllPendingBonuses($manager_id);

        if ($result['success'] > 0) {
            Yii::$app->session->setFlash('success',
                "Выплачено {$result['success']} бонусов на сумму " .
                number_format($result['total_amount'], 0, '.', ' ') . ' KZT'
            );
        }

        if ($result['failed'] > 0) {
            Yii::$app->session->setFlash('warning', "Не удалось выплатить {$result['failed']} бонусов");
        }

        return $this->redirect(['manager-sales']);
    }

    /**
     * Отчёт по продажам менеджеров
     */
    public function actionManagerSales()
    {
        $service = new ManagerSalesService();

        // Период фильтра
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));
        $dateFrom = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $dateTo = date('Y-m-t 23:59:59', strtotime($dateFrom));

        // Выбранный менеджер
        $managerId = Yii::$app->request->get('manager_id');

        $topManagers = $service->getTopManagers($dateFrom, $dateTo, 20);
        $pendingBonuses = $service->getPendingBonusesByManager();
        $totalPending = $service->getTotalPendingBonuses();
        $monthlyStats = $service->getMonthlyBonusStats($year, $month);

        $managerStats = null;
        $managerPayments = [];
        if ($managerId) {
            $managerStats = $service->getManagerStats($managerId, $dateFrom, $dateTo);
            $managerPayments = $service->getManagerPayments($managerId, $dateFrom, $dateTo);
        }

        return $this->render('manager-sales', [
            'topManagers' => $topManagers,
            'pendingBonuses' => $pendingBonuses,
            'totalPending' => $totalPending,
            'monthlyStats' => $monthlyStats,
            'managerStats' => $managerStats,
            'managerPayments' => $managerPayments,
            'managers' => ManagerSalesService::getManagersList(),
            'year' => $year,
            'month' => $month,
            'managerId' => $managerId,
        ]);
    }

    /**
     * Ожидающие бонусы (для быстрого доступа)
     */
    public function actionPendingBonuses()
    {
        $service = new ManagerSalesService();
        $pendingBonuses = $service->getPendingBonuses();

        return $this->render('pending-bonuses', [
            'pendingBonuses' => $pendingBonuses,
            'totalPending' => $service->getTotalPendingBonuses(),
        ]);
    }

    protected function findModel($id)
    {
        if (($model = OrganizationPayment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Платёж не найден.');
    }
}
