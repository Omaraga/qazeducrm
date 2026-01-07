<?php

namespace app\modules\superadmin\controllers;

use app\helpers\OrganizationRoles;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationActivityLog;
use app\models\relations\UserOrganization;
use app\models\SaasPlan;
use app\models\User;
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
                    'impersonate' => ['POST'],
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

        // Сотрудники по ролям
        $staffByRole = $this->getStaffByRole($id);

        return $this->render('view', [
            'model' => $model,
            'branches' => $branches,
            'subscription' => $subscription,
            'activityLogs' => $activityLogs,
            'staffByRole' => $staffByRole,
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
        $parentSubscription = $parent->getActiveSubscription();

        $model = new Organizations();
        $model->parent_id = $parent_id;
        $model->type = Organizations::TYPE_BRANCH;
        $model->status = Organizations::STATUS_ACTIVE;
        $model->billing_mode = Organizations::BILLING_ISOLATED; // По умолчанию отдельная подписка

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Если isolated режим - создаём подписку для филиала
            if ($model->billing_mode === Organizations::BILLING_ISOLATED) {
                $planId = Yii::$app->request->post('saas_plan_id');

                // Если план не выбран - используем план родителя
                if (!$planId && $parentSubscription) {
                    $planId = $parentSubscription->saas_plan_id;
                }

                if ($planId) {
                    $branchSubscription = OrganizationSubscription::createForBranch(
                        $model->id,
                        $planId,
                        $parentSubscription?->id
                    );

                    if ($branchSubscription->save()) {
                        OrganizationActivityLog::log(
                            $model->id,
                            OrganizationActivityLog::ACTION_SUBSCRIPTION_CHANGED,
                            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                            "Создана подписка филиала: " . ($branchSubscription->saasPlan->name ?? 'План ID: ' . $planId)
                        );
                    }
                }
            }

            OrganizationActivityLog::log(
                $parent_id,
                OrganizationActivityLog::ACTION_BRANCH_CREATED,
                OrganizationActivityLog::CATEGORY_GENERAL,
                "Создан филиал: {$model->name} (режим: {$model->billing_mode})"
            );

            Yii::$app->session->setFlash('success', 'Филиал успешно создан.');
            return $this->redirect(['view', 'id' => $parent_id]);
        }

        // Получаем список планов для формы
        $plans = SaasPlan::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('create-branch', [
            'model' => $model,
            'parent' => $parent,
            'parentSubscription' => $parentSubscription,
            'plans' => $plans,
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
     * Войти под пользователем (impersonate)
     *
     * @param int $user_id ID пользователя
     * @param int $organization_id ID организации
     */
    public function actionImpersonate($user_id, $organization_id)
    {
        $user = User::findOne($user_id);
        $organization = $this->findModel($organization_id);

        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        if (Yii::$app->impersonate->start($user_id, $organization_id)) {
            Yii::$app->session->setFlash('warning',
                'Вы вошли как ' . $user->fio . '. Используйте панель вверху страницы для возврата.'
            );
            return $this->redirect(['/crm']);
        }

        Yii::$app->session->setFlash('error', 'Не удалось войти под пользователем.');
        return $this->redirect(['view', 'id' => $organization_id]);
    }

    /**
     * Получить сотрудников организации, сгруппированных по ролям
     *
     * @param int $organizationId ID организации
     * @return array ['role_name' => UserOrganization[], ...]
     */
    protected function getStaffByRole($organizationId)
    {
        $roles = [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
            OrganizationRoles::ADMIN,
            OrganizationRoles::TEACHER,
        ];

        $result = [];

        foreach ($roles as $role) {
            $users = UserOrganization::find()
                ->joinWith('user')
                ->where([
                    'user_organization.target_id' => $organizationId,
                    'user_organization.role' => $role,
                    'user_organization.is_deleted' => 0,
                ])
                ->andWhere(['user.status' => User::STATUS_ACTIVE])
                ->all();

            if (!empty($users)) {
                $result[$role] = $users;
            }
        }

        return $result;
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
