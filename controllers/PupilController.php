<?php

namespace app\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\forms\EducationForm;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\search\PupilSearch;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PupilController implements the CRUD actions for Pupil model.
 */
class PupilController extends Controller
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
                        'delete-edu' => ['POST']
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
     * Lists all Pupil models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PupilSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pupil model.
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
     * Creates a new Pupil model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Pupil();

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
     * Updates an existing Pupil model.
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
     * Deletes an existing Pupil model.
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

    public function actionEdu($id){
        return $this->render('edu/index', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreateEdu($pupil_id){
        $model = new EducationForm();
        $model->scenario = EducationForm::TYPE_ADD;
        $model->loadDefaultValues();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id]));
            }
        }

        return $this->render('edu/form', [
            'model' => $model,
        ]);
    }

    public function actionUpdateEdu(){
        $model = new EducationForm();
        $model->scenario = EducationForm::TYPE_EDIT;
        $model->loadDefaultValues();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id]));
            }
        }

        return $this->render('edu/form', [
            'model' => $model,
        ]);
    }

    public function actionCopyEdu(){
        $model = new EducationForm();
        $model->scenario = EducationForm::TYPE_COPY;
        $model->loadDefaultValues();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id]));
            }
        }

        return $this->render('edu/form', [
            'model' => $model,
        ]);
    }

    public function actionDeleteEdu($id)
    {
        $model = PupilEducation::findOne($id);
        $model->delete();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Pupil model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pupil the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pupil::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
