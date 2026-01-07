<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\Group;
use app\models\Pupil;
use app\models\relations\EducationGroup;
use app\models\relations\TeacherGroup;
use app\models\search\GroupSearch;
use app\services\SubscriptionLimitService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
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

    public function actions()
    {
        return [
            'modal-form' => [
                'class' => ModalFormAction::class,
                'getBody' => function ($action) {
                    return $this->render('@vendor/vsk/modal-form/src/test/views/modal-list');
                },
                'getTitle' => function() {
                    return 'Список тестовых данных';
                },
            ],
            'modal-form-update' => [
                'class' => ModalFormAction::class,
                'getBody' => function ($action) {
                    return $this->render('@vendor/vsk/modal-form/src/test/views/update', [
                        'model' => $action->getModel(),
                    ]);
                },
                'getTitle' => function() {
                    return 'title';
                },
                'getModel' => function () {
                    $id = \Yii::$app->request->get('id');
                    // Security: проверка organization_id
                    return Group::find()
                        ->where(['id' => $id])
                        ->byOrganization()
                        ->notDeleted()
                        ->one();
                },
                'submitForm' => function ($action) {
                    $model = $action->getModel();
                    if ($model === null) {
                        return false;
                    }
                    $model->load(\Yii::$app->request->post());
                    return $model->save();
                }
            ],
        ];
    }

    /**
     * Lists all Group models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Group model.
     * @param int $id ID
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
     * Displays a single Group model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionTeachers($id){
        $model = $this->findModel($id);
        $dataProvider = new ActiveDataProvider([
            'query' => TeacherGroup::find()->byOrganization()->andWhere(['target_id' => $model->id]),
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('teacher', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionPupils($id){
        $model = $this->findModel($id);
        $dataProvider = new ActiveDataProvider([
            'query' => Pupil::find()->innerJoinWith(['groups' => function($q) use ($model){
                $q->andWhere(['<>', 'education_group.is_deleted', 1]);
            }])->where(['education_group.group_id' => $model->id])
                ->andWhere(['<>', 'pupil.is_deleted', 1]),

            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('pupils', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        // Проверка лимита тарифного плана
        $limitService = SubscriptionLimitService::forCurrentOrganization();
        if ($limitService && !$limitService->canAddGroup()) {
            Yii::$app->session->setFlash('error', SubscriptionLimitService::getLimitErrorMessage('group'));
            return $this->redirect(['index']);
        }

        $model = new Group();

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
     * Updates an existing Group model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Group model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Проверка зависимостей - есть ли ученики в группе
        $pupilsCount = EducationGroup::find()
            ->where(['group_id' => $model->id])
            ->andWhere(['is_deleted' => 0])
            ->count();

        if ($pupilsCount > 0) {
            Yii::$app->session->setFlash('error',
                "Невозможно удалить группу с учениками ({$pupilsCount} учеников). Сначала переведите учеников в другую группу.");
            return $this->redirect(['view', 'id' => $id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Удаление связей с преподавателями
            TeacherGroup::deleteAll(['target_id' => $model->id]);

            $model->delete();
            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Группа успешно удалена');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка при удалении группы: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Deletes teacher from group.
     * If deletion is successful, the browser will be redirected to the 'teachers' page.
     * @param int $id TeacherGroup ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteTeacher($id)
    {
        // Security: проверка organization_id
        $model = TeacherGroup::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Связь преподавателя с группой не найдена');
        }

        $targetId = $model->target_id;
        $model->delete();

        return $this->redirect(OrganizationUrl::to(['group/teachers', 'id' => $targetId]));
    }

    /**
     * Adds teacher to group.
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCreateTeacher()
    {
        $groupId = Yii::$app->request->get('group_id');

        if (empty($groupId)) {
            throw new ForbiddenHttpException('Не указана группа');
        }

        // Security: проверка что группа существует и принадлежит организации
        $group = Group::find()
            ->where(['id' => $groupId])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if ($group === null) {
            throw new NotFoundHttpException('Группа не найдена');
        }

        $model = new TeacherGroup();
        $model->target_id = $groupId;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['group/teachers', 'id' => $model->target_id]));
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('teacher/form', [
                'model' => $model,
            ]);
        }

        return $this->render('teacher/form', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Group the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // Security: проверка organization_id и is_deleted
        $model = Group::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Группа не найдена');
    }
}
