<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\models\forms\TariffForm;
use app\models\Organizations;
use app\models\Tariff;
use app\helpers\SystemRoles;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TariffController implements the CRUD actions for Tariff model.
 */
class TariffController extends Controller
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
                            'actions' => [
                                'delete',
                                'create',
                                'update'
                            ],
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        [
                            'allow' => false,
                            'actions' => [
                                'delete'
                            ],
                            'roles' => ['@', '?']
                        ],
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
     * Lists all Tariff models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Tariff::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tariff model.
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
     * Creates a new Tariff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TariffForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['tariff/index']));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Tariff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {

        $model = new TariffForm();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(OrganizationUrl::to(['tariff/index']));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Tariff model.
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

    public function actionGetInfo(){
        if (\Yii::$app->request->isPost){
            $id = \Yii::$app->request->post('id');
            $dateStart = \Yii::$app->request->post('date_start') ? : date('d.m.Y');
            $dateEnd = \Yii::$app->request->post('date_end') ? : date('d.m.Y', time() + (30*24*60*60));
            $sale = \Yii::$app->request->post('sale') ? intval(\Yii::$app->request->post('sale')) : 0;
            $result = [];
            if ($id){
                $tariff = Tariff::findOne($id);
                if ($tariff->type == 2){
                    $pricePerDay = $tariff->price / 31;
                    $days = (strtotime($dateEnd) - strtotime($dateStart)) / (24 * 60 * 60);
                    $periodPrice = intval(($days + 1) * $pricePerDay);

                }else{
                    $periodPrice = $tariff->price;
                }
                if ($sale > 0){
                    $salePrice = intval($periodPrice * $sale / 100);
                }else{
                    $salePrice = 0;
                }
                $totalPrice = $periodPrice - $salePrice;

                $infoText = 'Стоимость по тарифу '.$tariff->price.'тг. ';
                if ($periodPrice != $tariff->price){
                    $infoText .= 'Стоимость за выбранный период '.$periodPrice.'тг. ';
                }
                if ($sale > 0){
                    $infoText .= 'Скидка '.$sale.'% составляет '.$salePrice.'тг.';
                }
                $infoText .= '<br><b>Итого к оплате '.$totalPrice.'тг. </b>';
                $subjects = [];
                foreach ($tariff->subjectsRelation as $subject){
                    $subjects[] = $subject->subject_id;
                }
                $result = [
                    'id' => $tariff->id,
                    'name' => $tariff->name,
                    'info_text' => $infoText,
                    'price' => $tariff->price,
                    'sale' => $sale,
                    'period_price' => $periodPrice,
                    'sale_price' => $salePrice,
                    'total_price' => $totalPrice,
                    'type' => $tariff->type,
                    'duration' => $tariff->duration,
                    'subjects' => $subjects
                ];
            }

            return json_encode($result, true);

        }
    }

    /**
     * Finds the Tariff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Tariff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Tariff::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(\Yii::t('main', 'Тариф не найден.'));
        }

        return $model;
    }
}
