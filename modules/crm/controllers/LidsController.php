<?php

namespace app\modules\crm\controllers;

use app\components\ActiveRecord;
use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\Lids;
use app\models\LidHistory;
use app\models\search\LidsSearch;
use app\models\services\LidService;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
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
                        'link-to-pupil' => ['POST'],
                        'mark-not-target' => ['POST'],
                        'mark-in-training' => ['POST'],
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

        // Менеджеры для фильтра
        $managers = User::find()
            ->innerJoinWith(['currentUserOrganizations' => function($q) {
                $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED]);
            }])
            ->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'funnelStats' => Lids::getFunnelStats(),
            'managers' => ArrayHelper::map($managers, 'id', 'fio'),
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

            // Получаем данные WhatsApp
            $lastWhatsappMessage = $model->getLastIncomingWhatsappMessage();
            $whatsappData = null;
            if ($lastWhatsappMessage) {
                $whatsappData = [
                    'content' => mb_substr($lastWhatsappMessage->content ?? '', 0, 100, 'UTF-8'),
                    'time' => Yii::$app->formatter->asRelativeTime($lastWhatsappMessage->created_at),
                    'full_time' => Yii::$app->formatter->asDatetime($lastWhatsappMessage->created_at, 'php:d.m.Y H:i'),
                ];
            }

            // Получаем потенциального связанного ученика
            $potentialPupil = $model->findPotentialPupil();
            $potentialPupilData = null;
            if ($potentialPupil) {
                $potentialPupilData = [
                    'id' => $potentialPupil->id,
                    'fio' => $potentialPupil->fio,
                ];
            }

            // Связанный ученик (если уже привязан)
            $linkedPupilData = null;
            if ($model->pupil) {
                $linkedPupilData = [
                    'id' => $model->pupil->id,
                    'fio' => $model->pupil->fio,
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
                    'status_color' => $model->getStatusColor(),
                    'is_final_status' => $model->isFinalStatus(),
                    'is_not_target' => $model->isNotTarget(),
                    'is_in_training' => $model->isInTraining(),
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
                    'first_response_at' => $model->first_response_at ? Yii::$app->formatter->asDatetime($model->first_response_at, 'php:d.m.Y H:i') : null,
                    // WhatsApp данные
                    'whatsapp_profile_picture' => $model->getWhatsappProfilePicture(),
                    'has_whatsapp_chat' => $model->hasWhatsappChat(),
                    'whatsapp_unread_count' => $model->getWhatsappUnreadCount(),
                    'last_whatsapp_message' => $whatsappData,
                    'initials' => $model->getInitials(),
                    // Связи с учениками
                    'pupil_id' => $model->pupil_id,
                    'pupil' => $linkedPupilData,
                    'potential_pupil' => $potentialPupilData,
                    'related_lids_count' => $model->getRelatedLidsCount(),
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
     * AJAX: Связать лида с учеником
     *
     * @return array
     */
    public function actionLinkToPupil()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $lidId = $this->request->post('lid_id');
            $pupilId = $this->request->post('pupil_id');

            if (!$lidId || !$pupilId) {
                return ['success' => false, 'message' => 'Не указан ID лида или ученика'];
            }

            $model = $this->findModel($lidId);

            if ($model->linkToPupil($pupilId)) {
                $model->refresh();
                return [
                    'success' => true,
                    'message' => 'Лид связан с учеником',
                    'pupil' => [
                        'id' => $model->pupil->id,
                        'fio' => $model->pupil->fio,
                    ],
                ];
            }

            return ['success' => false, 'message' => 'Ученик не найден'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Перевести лида в статус "Не целевой"
     *
     * @return array
     */
    public function actionMarkNotTarget()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $lidId = $this->request->post('lid_id');
            $reason = $this->request->post('reason', 'other');

            if (!$lidId) {
                return ['success' => false, 'message' => 'Не указан ID лида'];
            }

            $model = $this->findModel($lidId);

            // Формируем комментарий с причиной
            $reasonLabels = Lids::getNotTargetReasonList();
            $reasonLabel = $reasonLabels[$reason] ?? $reason;
            $comment = "Причина: {$reasonLabel}";

            $result = LidService::changeStatus($model, Lids::STATUS_NOT_TARGET, $comment);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Лид отмечен как нецелевой',
                    'status' => Lids::STATUS_NOT_TARGET,
                    'status_label' => 'Не целевой',
                ];
            }

            return ['success' => false, 'message' => $result['message']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Перевести лида в статус "В обучении"
     *
     * @return array
     */
    public function actionMarkInTraining()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $lidId = $this->request->post('lid_id');
            $pupilId = $this->request->post('pupil_id');

            if (!$lidId) {
                return ['success' => false, 'message' => 'Не указан ID лида'];
            }

            $model = $this->findModel($lidId);

            // Если указан ученик - связываем
            if ($pupilId) {
                $model->linkToPupil($pupilId);
            }

            $result = LidService::changeStatus($model, Lids::STATUS_IN_TRAINING, 'Перевод в "В обучении"');

            if ($result['success']) {
                $model->refresh();
                return [
                    'success' => true,
                    'message' => 'Лид переведён в статус "В обучении"',
                    'status' => Lids::STATUS_IN_TRAINING,
                    'status_label' => 'В обучении',
                    'pupil' => $model->pupil ? [
                        'id' => $model->pupil->id,
                        'fio' => $model->pupil->fio,
                    ] : null,
                ];
            }

            return ['success' => false, 'message' => $result['message']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
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
