<?php

namespace app\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\Group;
use app\models\Pupil;
use app\models\relations\EducationGroup;
use app\models\relations\TeacherGroup;
use app\models\search\GroupSearch;
use app\models\Subject;
use vsk\modalForm\actions\ModalFormAction;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::className(),
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
                    return Group::findOne($id);
                },
                'submitForm' => function ($action) {
                    $model = $action->getModel();
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
            'query' => TeacherGroup::find()->byOrganization(),
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Group model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteTeacher($id)
    {
        $model = TeacherGroup::findOne($id);
        $model->delete();

        return $this->redirect(OrganizationUrl::to(['group/teachers', 'id' => $model->target_id]));
    }

    /**
     * @throws ForbiddenHttpException
     */
    public function actionCreateTeacher(){
        $model = new TeacherGroup();
        if ($groupId =\Yii::$app->request->get('group_id')){
            $model->target_id = $groupId;
            if ($this->request->isPost) {
                if ($model->load($this->request->post()) && $model->save()) {
                    return $this->redirect(OrganizationUrl::to(['group/teachers', 'id' => $model->id]));
                }
            }

            if (\Yii::$app->request->isAjax){
                return $this->renderAjax('teacher/form', [
                    'model' => $model,
                ]);
            }


            return $this->render('teacher/form', [
                'model' => $model,
            ]);
        }else{
            throw new ForbiddenHttpException;
        }



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
        if (($model = Group::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
