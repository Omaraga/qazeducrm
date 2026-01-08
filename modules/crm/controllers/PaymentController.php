<?php

namespace app\modules\crm\controllers;

use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\Payment;
use app\models\PaymentChangeRequest;
use app\models\search\PaymentSearch;
use app\models\services\PupilService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
                        'approve-request' => ['POST'],
                        'reject-request' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // Редактирование - директора + админы с правами
                        [
                            'allow' => true,
                            'actions' => ['update'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::canEditPayments();
                            }
                        ],
                        // Удаление - директора + админы с правами
                        [
                            'allow' => true,
                            'actions' => ['delete'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::canDeletePayments();
                            }
                        ],
                        // Одобрение/отклонение запросов - только для директоров
                        [
                            'allow' => true,
                            'actions' => ['pending-requests', 'approve-request', 'reject-request', 'view-request'],
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::DIRECTOR,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        // Запросы на изменение/удаление - для админов без прямых прав
                        [
                            'allow' => true,
                            'actions' => ['request-delete', 'my-requests'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::needsPaymentDeleteRequest();
                            }
                        ],
                        [
                            'allow' => true,
                            'actions' => ['request-update', 'my-requests'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::needsPaymentChangeRequest();
                            }
                        ],
                        // Остальные действия - для всех админов и выше
                        [
                            'allow' => true,
                            'actions' => ['index', 'view', 'create', 'receipt'],
                            'roles' => RoleChecker::getRolesForAccess('admin'),
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
     * Запрос на удаление платежа (для Admin)
     * @param int $id ID платежа
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRequestDelete($id)
    {
        $model = $this->findModel($id);

        // Проверка - нет ли уже ожидающего запроса
        if (PaymentChangeRequest::hasPendingRequest($model->id)) {
            Yii::$app->session->setFlash('warning', 'Для этого платежа уже есть ожидающий запрос');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isPost) {
            $reason = $this->request->post('reason');

            if (empty($reason)) {
                Yii::$app->session->setFlash('error', 'Укажите причину удаления');
            } else {
                $request = PaymentChangeRequest::createDeleteRequest($model, $reason);
                if ($request) {
                    Yii::$app->session->setFlash('success', 'Запрос на удаление отправлен директору');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при создании запроса');
                }
            }
        }

        return $this->render('request-delete', [
            'model' => $model,
        ]);
    }

    /**
     * Запрос на изменение платежа (для Admin)
     * @param int $id ID платежа
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRequestUpdate($id)
    {
        $model = $this->findModel($id);

        // Проверка - нет ли уже ожидающего запроса
        if (PaymentChangeRequest::hasPendingRequest($model->id)) {
            Yii::$app->session->setFlash('warning', 'Для этого платежа уже есть ожидающий запрос');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isPost) {
            $reason = $this->request->post('reason');
            $newValues = [
                'amount' => $this->request->post('amount'),
                'date' => $this->request->post('date'),
                'method_id' => $this->request->post('method_id'),
                'purpose_id' => $this->request->post('purpose_id'),
                'comment' => $this->request->post('comment'),
            ];

            // Удаляем пустые значения
            $newValues = array_filter($newValues, function($v) { return $v !== '' && $v !== null; });

            if (empty($reason)) {
                Yii::$app->session->setFlash('error', 'Укажите причину изменения');
            } elseif (empty($newValues)) {
                Yii::$app->session->setFlash('error', 'Укажите хотя бы одно изменение');
            } else {
                $request = PaymentChangeRequest::createUpdateRequest($model, $newValues, $reason);
                if ($request) {
                    Yii::$app->session->setFlash('success', 'Запрос на изменение отправлен директору');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при создании запроса');
                }
            }
        }

        return $this->render('request-update', [
            'model' => $model,
        ]);
    }

    /**
     * Мои запросы на изменение платежей (для Admin)
     * @return string
     */
    public function actionMyRequests()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PaymentChangeRequest::find()
                ->byOrganization()
                ->notDeleted()
                ->andWhere(['requested_by' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        return $this->render('my-requests', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Ожидающие запросы на изменение платежей (для Director)
     * @return string
     */
    public function actionPendingRequests()
    {
        $status = $this->request->get('status', PaymentChangeRequest::STATUS_PENDING);

        $query = PaymentChangeRequest::find()
            ->byOrganization()
            ->notDeleted()
            ->with(['payment', 'requestedByUser'])
            ->orderBy(['created_at' => SORT_DESC]);

        if ($status !== 'all') {
            $query->andWhere(['status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        $pendingCount = PaymentChangeRequest::getPendingCount();

        return $this->render('pending-requests', [
            'dataProvider' => $dataProvider,
            'status' => $status,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Просмотр запроса на изменение (для Director)
     * @param int $id ID запроса
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewRequest($id)
    {
        $model = $this->findRequest($id);

        return $this->render('view-request', [
            'model' => $model,
        ]);
    }

    /**
     * Одобрить запрос на изменение платежа (для Director)
     * @param int $id ID запроса
     * @return \yii\web\Response
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    public function actionApproveRequest($id)
    {
        $model = $this->findRequest($id);

        if (!$model->isPending()) {
            Yii::$app->session->setFlash('warning', 'Этот запрос уже обработан');
            return $this->redirect(['pending-requests']);
        }

        $comment = $this->request->post('comment');

        if ($model->approve($comment)) {
            // Обновляем баланс ученика если платёж затронут
            $payment = $model->payment;
            if ($payment && $payment->pupil_id) {
                PupilService::updateBalance($payment->pupil_id);
            }

            Yii::$app->session->setFlash('success', 'Запрос одобрен, изменения применены');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при одобрении запроса');
        }

        return $this->redirect(['pending-requests']);
    }

    /**
     * Отклонить запрос на изменение платежа (для Director)
     * @param int $id ID запроса
     * @return \yii\web\Response
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    public function actionRejectRequest($id)
    {
        $model = $this->findRequest($id);

        if (!$model->isPending()) {
            Yii::$app->session->setFlash('warning', 'Этот запрос уже обработан');
            return $this->redirect(['pending-requests']);
        }

        $comment = $this->request->post('comment');

        if (empty($comment)) {
            Yii::$app->session->setFlash('error', 'Укажите причину отклонения');
            return $this->redirect(['view-request', 'id' => $id]);
        }

        if ($model->reject($comment)) {
            Yii::$app->session->setFlash('success', 'Запрос отклонён');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при отклонении запроса');
        }

        return $this->redirect(['pending-requests']);
    }

    /**
     * Печать квитанции платежа
     * @param int $id ID платежа
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionReceipt($id)
    {
        $model = Payment::find()
            ->with(['method', 'pupil', 'organization'])
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Платёж не найден.'));
        }

        // Квитанции только для оплат
        if ($model->type !== Payment::TYPE_PAY) {
            throw new ForbiddenHttpException('Квитанции доступны только для платежей');
        }

        return $this->renderPartial('_receipt', [
            'model' => $model,
        ]);
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

    /**
     * Finds the PaymentChangeRequest model
     * @param int $id ID
     * @return PaymentChangeRequest
     * @throws NotFoundHttpException
     */
    protected function findRequest($id)
    {
        $model = PaymentChangeRequest::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Запрос не найден');
        }

        return $model;
    }
}
