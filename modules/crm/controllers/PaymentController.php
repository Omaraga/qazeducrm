<?php

namespace app\modules\crm\controllers;

use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Payment;
use app\models\search\PaymentSearch;
use app\models\services\PupilService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
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

    /**
     * Lists all Payment models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PaymentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payment model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Eager loading для оптимизации
        $model = Payment::find()
            ->with(['method', 'pupil'])
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Платёж не найден.'));
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Payment();

        // Установить тип из GET параметра
        $type = (int)Yii::$app->request->get('type', Payment::TYPE_PAY);
        if (in_array($type, [Payment::TYPE_PAY, Payment::TYPE_REFUND, Payment::TYPE_SPENDING])) {
            $model->type = $type;
        }

        // Установить дату по умолчанию
        $model->date = date('Y-m-d H:i:s');

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {
                    // Конвертируем дату из datetime-local формата
                    if ($model->date && strpos($model->date, 'T') !== false) {
                        $model->date = str_replace('T', ' ', $model->date) . ':00';
                    }

                    if ($model->save()) {
                        // Обновляем баланс ученика если есть
                        if ($model->pupil_id) {
                            PupilService::updateBalance($model->pupil_id);
                        }
                        ActivityLogger::logPaymentCreated($model);
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
                $transaction->rollBack();
                // Показываем ошибки валидации
                if ($model->hasErrors()) {
                    $errors = [];
                    foreach ($model->getErrors() as $attribute => $messages) {
                        $errors[] = $model->getAttributeLabel($attribute) . ': ' . implode(', ', $messages);
                    }
                    Yii::$app->session->setFlash('error', implode('<br>', $errors));
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Ошибка при создании платежа: ' . $e->getMessage());
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldPupilId = $model->pupil_id;

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post()) && $model->save()) {
                    // Обновляем баланс старого ученика если он изменился
                    if ($oldPupilId && $oldPupilId !== $model->pupil_id) {
                        PupilService::updateBalance($oldPupilId);
                    }
                    // Обновляем баланс текущего ученика
                    if ($model->pupil_id) {
                        PupilService::updateBalance($model->pupil_id);
                    }
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                $transaction->rollBack();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении платежа: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $pupilId = $model->pupil_id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->delete();
            ActivityLogger::logPaymentDeleted($model);

            // Обновляем баланс ученика после удаления платежа
            if ($pupilId) {
                PupilService::updateBalance($pupilId);
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Платёж успешно удалён');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Ошибка при удалении платежа: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Payment::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Платёж не найден.'));
        }

        return $model;
    }
}
