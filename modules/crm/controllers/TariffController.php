<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\forms\TariffForm;
use app\models\services\TariffService;
use app\models\Tariff;
use app\traits\FindModelTrait;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * TariffController implements the CRUD actions for Tariff model.
 */
class TariffController extends Controller
{
    use FindModelTrait;

    protected string $modelClass = Tariff::class;
    protected string $notFoundMessage = 'Тариф не найден';

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

    public function actionGetInfo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isPost) {
            return [];
        }

        $id = \Yii::$app->request->post('id');
        if (!$id) {
            return [];
        }

        $dateStart = \Yii::$app->request->post('date_start');
        $dateEnd = \Yii::$app->request->post('date_end');
        $sale = intval(\Yii::$app->request->post('sale', 0));

        return TariffService::calculatePricing($id, $dateStart, $dateEnd, $sale) ?: [];
    }

}
