<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\forms\TeacherForm;
use app\models\search\UserSearch;
use app\models\User;
use app\services\SubscriptionLimitService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController - управление сотрудниками организации
 */
class UserController extends CrmBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // Сброс пароля - только для директоров и выше
                        [
                            'allow' => true,
                            'actions' => ['reset-password'],
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::DIRECTOR,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        // Остальные действия - для всех админов и выше
                        [
                            'allow' => true,
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::ADMIN,
                                OrganizationRoles::DIRECTOR,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        [
                            'allow' => false,
                            'roles' => ['?']
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        // Проверка лимита учителей тарифного плана
        $limitService = SubscriptionLimitService::forCurrentOrganization();
        if ($limitService && !$limitService->canAddTeacher()) {
            Yii::$app->session->setFlash('error', SubscriptionLimitService::getLimitErrorMessage('teacher'));
            return $this->redirect(['index']);
        }

        $model = new TeacherForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);

            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = new TeacherForm();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Сброс пароля сотрудника
     * Доступно только для SUPER, GENERAL_DIRECTOR, DIRECTOR
     * Нельзя сбросить пароль SUPER админу
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);

        // Нельзя сбросить пароль SUPER админу
        if (Yii::$app->authManager->checkAccess($model->id, SystemRoles::SUPER)) {
            throw new ForbiddenHttpException('Нельзя сбросить пароль суперадминистратору');
        }

        // Нельзя сбросить пароль самому себе через эту форму
        if ($model->id === Yii::$app->user->id) {
            throw new ForbiddenHttpException('Используйте профиль для изменения своего пароля');
        }

        if ($this->request->isPost) {
            $newPassword = $this->request->post('new_password');
            $confirmPassword = $this->request->post('confirm_password');

            if (empty($newPassword)) {
                Yii::$app->session->setFlash('error', 'Введите новый пароль');
            } elseif (strlen($newPassword) < 6) {
                Yii::$app->session->setFlash('error', 'Пароль должен быть не менее 6 символов');
            } elseif ($newPassword !== $confirmPassword) {
                Yii::$app->session->setFlash('error', 'Пароли не совпадают');
            } else {
                $model->setPassword($newPassword);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Пароль успешно изменён');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при сохранении пароля');
                }
            }
        }

        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $organizationId = \app\models\Organizations::getCurrentOrganizationId();

        // User не имеет organization_id, связь через user_organization
        $model = User::find()
            ->innerJoin('user_organization', 'user_organization.related_id = user.id')
            ->andWhere(['user_organization.target_id' => $organizationId])
            ->andWhere(['user.id' => $id])
            ->andWhere(['!=', 'user.status', User::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(\Yii::t('main', 'Пользователь не найден.'));
        }

        return $model;
    }
}
