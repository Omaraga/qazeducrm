<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Room;
use app\traits\FindModelTrait;
use richardfan\sortable\SortableAction;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
/**
 * RoomController - управление кабинетами/аудиториями
 */
class RoomController extends CrmBaseController
{
    use FindModelTrait;

    protected string $modelClass = Room::class;
    protected string $notFoundMessage = 'Кабинет не найден';

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
                        [
                            'allow' => true,
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::GENERAL_DIRECTOR,
                                OrganizationRoles::ADMIN,
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
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'sort' => [
                'class' => SortableAction::class,
                'activeRecordClassName' => Room::class,
                'orderColumn' => 'sort_order',
            ],
        ];
    }

    /**
     * Lists all Room models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Room::find()->byOrganization()->notDeleted(),
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'sort_order' => SORT_ASC,
                    'name' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Room model.
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
     * Creates a new Room model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Room();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', 'Кабинет успешно создан');
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
            // Assign a random color from defaults
            $model->color = Room::DEFAULT_COLORS[array_rand(Room::DEFAULT_COLORS)];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Room model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', 'Кабинет успешно обновлен');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Room model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->session->setFlash('success', 'Кабинет успешно удален');

        return $this->redirect(['index']);
    }

}
