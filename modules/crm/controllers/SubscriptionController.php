<?php

namespace app\modules\crm\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\SaasPlan;
use app\services\SubscriptionAccessService;
use app\services\SubscriptionLimitService;
use app\services\AddonTrialService;
use app\models\SaasFeature;
use app\models\OrganizationSubscriptionRequest;

/**
 * Контроллер управления подпиской для пользователей
 */
class SubscriptionController extends Controller
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Главная страница подписки
     */
    public function actionIndex()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = $organization->getActiveSubscription();
        $accessService = SubscriptionAccessService::forOrganization($organization);
        $limitService = SubscriptionLimitService::forOrganization($organization);

        // Получаем все тарифные планы
        $plans = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'organization' => $organization,
            'subscription' => $subscription,
            'accessService' => $accessService,
            'limitService' => $limitService,
            'plans' => $plans,
        ]);
    }

    /**
     * Страница выбора тарифа
     */
    public function actionPlans()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = $organization->getActiveSubscription();

        $plans = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('plans', [
            'organization' => $organization,
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }

    /**
     * Страница продления подписки
     */
    public function actionRenew()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = $organization->getActiveSubscription()
            ?? OrganizationSubscription::findByOrganization($organization->id);

        $plans = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('renew', [
            'organization' => $organization,
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }

    /**
     * Страница апгрейда тарифа
     */
    public function actionUpgrade()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = $organization->getActiveSubscription();
        $currentPlan = $subscription ? $subscription->saasPlan : null;

        // Получаем планы выше текущего
        $query = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC]);

        if ($currentPlan) {
            $query->andWhere(['>', 'sort_order', $currentPlan->sort_order]);
        }

        $plans = $query->all();

        return $this->render('upgrade', [
            'organization' => $organization,
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'plans' => $plans,
        ]);
    }

    /**
     * История платежей
     */
    public function actionPayments()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $payments = \app\models\OrganizationPayment::find()
            ->where(['organization_id' => $organization->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('payments', [
            'organization' => $organization,
            'payments' => $payments,
        ]);
    }

    /**
     * Страница использования лимитов
     */
    public function actionUsage()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = $organization->getActiveSubscription();
        $limitService = SubscriptionLimitService::forOrganization($organization);

        return $this->render('usage', [
            'organization' => $organization,
            'subscription' => $subscription,
            'limitService' => $limitService,
        ]);
    }

    /**
     * Страница заблокированного доступа
     */
    public function actionBlocked()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $subscription = OrganizationSubscription::findByOrganization($organization->id);
        $accessService = SubscriptionAccessService::forOrganization($organization);

        // Если доступ не заблокирован - редирект на главную
        if ($accessService->getAccessMode() !== SubscriptionAccessService::MODE_BLOCKED) {
            return $this->redirect(['index']);
        }

        $plans = SaasPlan::find()
            ->where(['is_active' => 1, 'is_deleted' => 0])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('blocked', [
            'organization' => $organization,
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }

    /**
     * Запрос на продление (заявка)
     */
    public function actionRequestRenewal()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        if (Yii::$app->request->isPost) {
            $planId = Yii::$app->request->post('plan_id');
            $period = Yii::$app->request->post('period', 'monthly');
            $comment = Yii::$app->request->post('comment', '');
            $contactPhone = Yii::$app->request->post('contact_phone');
            $contactName = Yii::$app->request->post('contact_name');

            // Создаём запрос на продление
            $request = OrganizationSubscriptionRequest::createRenewalRequest(
                $organization->id,
                $planId ? (int)$planId : null,
                $period,
                $comment ?: null,
                $contactPhone ?: null,
                $contactName ?: null
            );

            if ($request->save()) {
                Yii::$app->session->setFlash('success',
                    'Заявка на продление подписки отправлена. ' .
                    'Наш менеджер свяжется с вами в ближайшее время.'
                );
            } else {
                Yii::$app->session->setFlash('error',
                    'Не удалось создать заявку. Попробуйте позже.'
                );
            }

            return $this->redirect(['index']);
        }

        return $this->redirect(['renew']);
    }

    /**
     * Начать пробный период для функции/аддона
     */
    public function actionStartTrial()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $featureCode = Yii::$app->request->post('feature') ?? Yii::$app->request->get('feature');
        if (!$featureCode) {
            Yii::$app->session->setFlash('error', 'Не указана функция для пробного периода');
            return $this->redirect(['upgrade']);
        }

        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            Yii::$app->session->setFlash('error', 'Ошибка инициализации сервиса');
            return $this->redirect(['upgrade']);
        }

        // Проверяем возможность начать trial
        $trialInfo = $trialService->getTrialInfo($featureCode);

        if (!$trialInfo['available']) {
            $errorMessage = match ($trialInfo['reason'] ?? 'unknown') {
                'feature_not_found' => 'Функция не найдена',
                'trial_active' => 'Пробный период уже активен',
                'already_active' => 'Функция уже активна в вашем тарифе',
                'trial_used' => 'Вы уже использовали пробный период для этой функции',
                'trial_not_available' => 'Пробный период недоступен для этой функции',
                'dependencies_not_met' => 'Для этой функции требуются другие функции',
                default => 'Не удалось начать пробный период',
            };

            Yii::$app->session->setFlash('error', $errorMessage);
            return $this->redirect(['upgrade']);
        }

        // Запускаем trial
        $addon = $trialService->startTrial($featureCode);

        if ($addon) {
            $feature = $trialInfo['feature'];
            Yii::$app->session->setFlash('success', sprintf(
                'Пробный период для "%s" активирован на %d дней. Наслаждайтесь!',
                $feature->name,
                $trialInfo['trial_days']
            ));

            return $this->redirect(['trials']);
        }

        Yii::$app->session->setFlash('error', 'Не удалось активировать пробный период. Попробуйте позже.');
        return $this->redirect(['upgrade']);
    }

    /**
     * Страница активных пробных периодов
     */
    public function actionTrials()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            return $this->redirect(['index']);
        }

        $statistics = $trialService->getTrialStatistics();

        return $this->render('trials', [
            'organization' => $organization,
            'activeTrials' => $statistics['active_trials'],
            'availableTrials' => $statistics['available_trials'],
            'statistics' => $statistics,
        ]);
    }

    /**
     * Отмена пробного периода
     */
    public function actionCancelTrial()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $featureCode = Yii::$app->request->post('feature') ?? Yii::$app->request->get('feature');
        if (!$featureCode) {
            Yii::$app->session->setFlash('error', 'Не указана функция');
            return $this->redirect(['trials']);
        }

        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            Yii::$app->session->setFlash('error', 'Ошибка инициализации сервиса');
            return $this->redirect(['trials']);
        }

        if ($trialService->cancelTrial($featureCode)) {
            $feature = SaasFeature::findByCode($featureCode);
            Yii::$app->session->setFlash('success', sprintf(
                'Пробный период для "%s" отменён',
                $feature ? $feature->name : $featureCode
            ));
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось отменить пробный период');
        }

        return $this->redirect(['trials']);
    }

    /**
     * Конвертация trial в платную подписку (заявка)
     */
    public function actionConvertTrial()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        if (!Yii::$app->request->isPost) {
            return $this->redirect(['trials']);
        }

        $featureCode = Yii::$app->request->post('feature');
        $period = Yii::$app->request->post('period', 'monthly');
        $planId = Yii::$app->request->post('plan_id');
        $contactPhone = Yii::$app->request->post('contact_phone');
        $contactName = Yii::$app->request->post('contact_name');
        $comment = Yii::$app->request->post('comment');

        if (!$featureCode && !$planId) {
            Yii::$app->session->setFlash('error', 'Не указана функция или план');
            return $this->redirect(['trials']);
        }

        // Создаём запрос на конвертацию trial
        $request = OrganizationSubscriptionRequest::createTrialConvertRequest(
            $organization->id,
            $planId ? (int)$planId : null,
            $period,
            $comment ?: ($featureCode ? "Запрос на покупку: " . $featureCode : null),
            $contactPhone ?: null,
            $contactName ?: null
        );

        if ($request->save()) {
            $feature = $featureCode ? SaasFeature::findByCode($featureCode) : null;
            Yii::$app->session->setFlash('success', sprintf(
                'Заявка на подключение%s отправлена. Наш менеджер свяжется с вами в ближайшее время.',
                $feature ? ' "' . $feature->name . '"' : ''
            ));
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось создать заявку. Попробуйте позже.');
        }

        return $this->redirect(['trials']);
    }

    /**
     * Запрос на апгрейд тарифа
     */
    public function actionRequestUpgrade()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        if (!Yii::$app->request->isPost) {
            return $this->redirect(['upgrade']);
        }

        $planId = Yii::$app->request->post('plan_id');
        $period = Yii::$app->request->post('period', 'monthly');
        $comment = Yii::$app->request->post('comment');
        $contactPhone = Yii::$app->request->post('contact_phone');
        $contactName = Yii::$app->request->post('contact_name');

        if (!$planId) {
            Yii::$app->session->setFlash('error', 'Не указан тарифный план');
            return $this->redirect(['upgrade']);
        }

        // Создаём запрос на апгрейд
        $request = OrganizationSubscriptionRequest::createUpgradeRequest(
            $organization->id,
            (int)$planId,
            $period,
            $comment ?: null,
            $contactPhone ?: null,
            $contactName ?: null
        );

        if ($request->save()) {
            $plan = SaasPlan::findOne($planId);
            Yii::$app->session->setFlash('success', sprintf(
                'Заявка на переход на тариф "%s" отправлена. Наш менеджер свяжется с вами в ближайшее время.',
                $plan ? $plan->name : 'новый'
            ));
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось создать заявку. Попробуйте позже.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Мои заявки на изменение подписки
     */
    public function actionRequests()
    {
        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена');
        }

        $requests = OrganizationSubscriptionRequest::find()
            ->where(['organization_id' => $organization->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('requests', [
            'organization' => $organization,
            'requests' => $requests,
        ]);
    }
}
