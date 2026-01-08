<?php

namespace app\modules\crm\controllers;

use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\Lids;
use app\models\LidHistory;
use app\models\search\LidsSearch;
use app\models\services\LidService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LidsController - CRUD операции с лидами
 *
 * Отвечает за:
 * - Список лидов (табличный вид)
 * - Просмотр/создание/редактирование/удаление лидов
 * - AJAX операции CRUD
 *
 * @see LidsFunnelController для канбан-доски и аналитики
 * @see LidsInteractionController для взаимодействий и WhatsApp
 */
class LidsController extends CrmBaseController
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
                        'create-ajax' => ['POST'],
                        'update-ajax' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // Удаление - по настройкам организации
                        [
                            'allow' => true,
                            'actions' => ['delete'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::canDeleteLids();
                            }
                        ],
                        // Остальные действия - для админов и выше
                        [
                            'allow' => true,
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
     * Список всех лидов (табличный вид)
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LidsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'funnelStats' => Lids::getFunnelStats(),
        ]);
    }

    /**
     * Просмотр лида
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'histories' => $model->getHistories()->with('user')->all(),
        ]);
    }

    /**
     * Создание нового лида
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Lids();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                // Записываем в историю создание лида
                LidHistory::createLidCreated($model);
                ActivityLogger::logLidCreated($model);

                Yii::$app->session->setFlash('success', 'Лид успешно создан');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            $model->date = date('Y-m-d');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование лида
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldStatus = $model->status;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $newStatus = $model->status;

            // Если статус изменился - используем сервис для записи в историю
            if ($oldStatus != $newStatus) {
                if (LidService::changeStatus($model, $newStatus)) {
                    Yii::$app->session->setFlash('success', 'Лид успешно обновлён');

                    // Если статус стал PAID и был создан ученик - редирект на ученика
                    if ($newStatus == Lids::STATUS_PAID && $model->pupil_id) {
                        Yii::$app->session->setFlash('info', 'Ученик автоматически создан. Заполните недостающие данные.');
                        return $this->redirect(['pupil/update', 'id' => $model->pupil_id]);
                    }

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Лид успешно обновлён');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление лида
     *
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Лид удалён');
        return $this->redirect(['index']);
    }

    /**
     * AJAX: Создание лида
     *
     * @return array
     */
    public function actionCreateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Lids();
        $model->date = date('Y-m-d');

        if ($model->load($this->request->post()) && $model->save()) {
            LidHistory::createLidCreated($model);
            ActivityLogger::logLidCreated($model);

            return [
                'success' => true,
                'message' => 'Лид создан',
                'id' => $model->id,
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка создания',
            'errors' => $model->errors,
        ];
    }

    /**
     * AJAX: Обновление лида
     *
     * @param int $id
     * @return array
     */
    public function actionUpdateAjax($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = $this->findModel($id);
            $oldStatus = $model->status;

            if ($model->load($this->request->post())) {
                $newStatus = $model->status;

                if ($oldStatus != $newStatus) {
                    if (LidService::changeStatus($model, $newStatus)) {
                        return [
                            'success' => true,
                            'message' => 'Лид обновлён',
                            'pupil_id' => $model->pupil_id,
                        ];
                    }
                } else {
                    if ($model->save()) {
                        return [
                            'success' => true,
                            'message' => 'Лид обновлён',
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => 'Ошибка сохранения',
                'errors' => $model->errors,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * AJAX: Получение данных лида
     *
     * @param int $id
     * @return array
     */
    public function actionGetLid($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = $this->findModel($id);

            // Получаем историю взаимодействий
            $historyItems = LidHistory::find()
                ->where(['lid_id' => $id])
                ->andWhere(['is_deleted' => 0])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(15)
                ->with('user')
                ->all();

            $history = [];
            foreach ($historyItems as $h) {
                $history[] = [
                    'id' => $h->id,
                    'type' => $h->type,
                    'type_label' => $h->getTypeLabel(),
                    'type_icon' => $h->getTypeIcon(),
                    'type_color' => $h->getTypeColor(),
                    'comment' => $h->comment,
                    'user_name' => $h->user->fio ?? 'Система',
                    'created_at' => $h->getFormattedDate(),
                    'status_from' => $h->status_from,
                    'status_to' => $h->status_to,
                    'status_change' => $h->getStatusChangeDescription(),
                    'call_duration' => $h->getFormattedCallDuration(),
                ];
            }

            return [
                'success' => true,
                'lid' => [
                    'id' => $model->id,
                    'fio' => $model->fio,
                    'phone' => $model->phone,
                    'parent_fio' => $model->parent_fio,
                    'parent_phone' => $model->parent_phone,
                    'contact_person' => $model->contact_person,
                    'contact_phone' => $model->getContactPhone(),
                    'school' => $model->school,
                    'class_id' => $model->class_id,
                    'status' => $model->status,
                    'status_label' => $model->getStatusLabel(),
                    'source' => $model->source,
                    'source_label' => $model->getSourceLabel(),
                    'manager_id' => $model->manager_id,
                    'manager_name' => $model->manager ? $model->manager->fio : null,
                    'next_contact_date' => $model->next_contact_date,
                    'next_contact_date_formatted' => $model->next_contact_date ? date('d.m.Y', strtotime($model->next_contact_date)) : null,
                    'comment' => $model->comment,
                    'lost_reason' => $model->lost_reason,
                    'tags' => $model->getTags(),
                    'days_in_status' => $model->getDaysInStatus(),
                    'is_overdue' => $model->isOverdue(),
                    'is_stale' => $model->isStaleInStatus(),
                    'created_at' => $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') : null,
                    'history' => $history,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Поиск модели лида по ID
     *
     * @param int $id ID
     * @return Lids
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Lids::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Лид не найден.'));
        }

        return $model;
    }
}
