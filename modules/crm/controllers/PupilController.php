<?php

namespace app\modules\crm\controllers;

use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\forms\EducationForm;
use app\models\forms\PaymentForm;
use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\search\PupilSearch;
use app\models\services\PupilService;
use app\services\SubscriptionLimitService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'delete-edu' => ['POST']
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
        PupilService::updateBalance($id);
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
        // Проверка лимита тарифного плана
        $limitService = SubscriptionLimitService::forCurrentOrganization();
        if ($limitService && !$limitService->canAddPupil()) {
            Yii::$app->session->setFlash('error', SubscriptionLimitService::getLimitErrorMessage('pupil'));
            return $this->redirect(['index']);
        }

        $model = new Pupil();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                ActivityLogger::logPupilCreated($model);
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

        PupilService::updateBalance($id);
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
        $model = $this->findModel($id);
        ActivityLogger::logPupilDeleted($model);
        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionEdu($id){
        PupilService::updateBalance($id);
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

    /**
     * @return string|\yii\web\Response
     */
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

    /**
     * Удаление обучения ученика
     *
     * @param int $id ID обучения
     * @return \yii\web\Response
     */
    public function actionDeleteEdu($id)
    {
        // Security: проверяем organization_id
        $model = PupilEducation::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->one();

        if ($model === null) {
            \Yii::$app->session->setFlash('error', \Yii::t('main', 'Обучение не найдено'));
            return $this->redirect(['index']);
        }

        // Проверяем права на удаление
        if (!$model->canDelete()) {
            \Yii::$app->session->setFlash('error', \Yii::t('main', 'Нет прав на удаление этого обучения'));
            return $this->redirect(['edu', 'id' => $model->pupil_id]);
        }

        $pupilId = $model->pupil_id;
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            // Batch delete - удаляем все связи с группами одним запросом
            \app\models\relations\EducationGroup::deleteAll(['education_id' => $model->id]);

            // Удаляем само обучение
            if (!$model->delete()) {
                throw new \Exception('Ошибка при удалении обучения');
            }

            // Пересчитываем баланс
            PupilService::updateBalance($pupilId);

            $transaction->commit();
            \Yii::$app->session->setFlash('success', \Yii::t('main', 'Обучение удалено'));

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', \Yii::t('main', 'Ошибка при удалении'));
            \Yii::error('Error deleting education: ' . $e->getMessage(), 'application');
        }

        return $this->redirect(['edu', 'id' => $pupilId]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPayment($id){
        $model = $this->findModel($id);
        PupilService::updateBalance($id);
        $dataProvider = new ActiveDataProvider([
            'query' => Payment::find()->andWhere(['pupil_id' => $model->id])->byOrganization()->orderBy('date DESC'),
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('payment/index', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreatePayment($pupil_id)
    {
        $pupil = Pupil::findOne($pupil_id);

        // Проверка null
        if ($pupil === null) {
            throw new NotFoundHttpException(\Yii::t('main', 'Ученик не найден'));
        }

        $model = new PaymentForm();
        $model->loadDefaultValues();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]));
            }
        }

        return $this->render('payment/form', [
            'model' => $model,
            'pupil' => $pupil,
        ]);
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
