<?php

namespace app\modules\crm\controllers;

use app\helpers\ActivityLogger;
use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Lids;
use app\models\LidHistory;
use app\models\LidTag;
use app\models\Organizations;
use app\models\search\LidsSearch;
use app\models\services\LidService;
use app\models\SalesScript;
use app\models\SmsTemplate;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LidsController - управление лидами (воронка продаж)
 */
class LidsController extends Controller
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
                        'change-status' => ['POST'],
                        'add-interaction' => ['POST'],
                        'create-ajax' => ['POST'],
                        'update-ajax' => ['POST'],
                        'toggle-tag' => ['POST'],
                        'update-field' => ['POST'],
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
     * Kanban-доска лидов
     *
     * @return string
     */
    public function actionKanban()
    {
        // Собираем фильтры из GET-параметров
        $filters = [
            'search' => $this->request->get('search', ''),
            'source' => $this->request->get('source', ''),
            'manager_id' => $this->request->get('manager_id', ''),
            'class_id' => $this->request->get('class_id', ''),
            'overdue_only' => $this->request->get('overdue_only', ''),
            'date_from' => $this->request->get('date_from', ''),
            'date_to' => $this->request->get('date_to', ''),
            // Новые фильтры
            'my_leads_only' => $this->request->get('my_leads_only', ''),
            'contact_today' => $this->request->get('contact_today', ''),
            'stale_only' => $this->request->get('stale_only', ''),
            'tags' => $this->request->get('tags', []),
        ];

        // Убираем пустые значения (массивы проверяем отдельно)
        $filters = array_filter($filters, function($v) {
            if (is_array($v)) return !empty($v);
            return $v !== '' && $v !== null;
        });

        $columns = LidService::getKanbanData($filters);
        $funnelStats = Lids::getFunnelStats();

        return $this->render('kanban', [
            'columns' => $columns,
            'funnelStats' => $funnelStats,
            'filters' => $filters,
            'managers' => LidService::getManagersForDropdown(),
        ]);
    }

    /**
     * Страница аналитики
     *
     * @return string
     */
    public function actionAnalytics()
    {
        $dateFrom = $this->request->get('date_from', date('Y-m-01'));
        $dateTo = $this->request->get('date_to', date('Y-m-d'));
        $managerId = $this->request->get('manager_id');

        return $this->render('analytics', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'managerId' => $managerId,
            'funnelAnalytics' => LidService::getFunnelAnalytics($dateFrom, $dateTo, $managerId),
            'managerStats' => LidService::getManagerStats($dateFrom, $dateTo),
            'lostReasons' => LidService::getTopLostReasons(),
            'sourceStats' => LidService::getSourceStats(),
            'managers' => LidService::getManagersForDropdown(),
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
     * AJAX: Смена статуса (для Kanban drag & drop)
     *
     * @return array
     */
    public function actionChangeStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $newStatus = (int)$this->request->post('status');
        $comment = $this->request->post('comment');

        try {
            $lid = $this->findModel($id);

            if (LidService::changeStatus($lid, $newStatus, $comment)) {
                $response = [
                    'success' => true,
                    'message' => 'Статус изменён',
                ];

                // Если статус PAID и был создан ученик
                if ($newStatus == Lids::STATUS_PAID && $lid->pupil_id) {
                    $response['pupil_id'] = $lid->pupil_id;
                    $response['message'] = 'Лид конвертирован в ученика';
                }

                return $response;
            }

            return ['success' => false, 'message' => 'Невозможно изменить статус'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Добавление взаимодействия
     *
     * @return array
     */
    public function actionAddInteraction()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = $this->request->post('lid_id');
        $type = $this->request->post('type');
        $comment = $this->request->post('comment');
        $nextContactDate = $this->request->post('next_contact_date');
        $callDuration = $this->request->post('call_duration');

        try {
            $lid = $this->findModel($lidId);

            if (LidService::addInteraction($lid, $type, $comment, $nextContactDate, $callDuration)) {
                // Получаем последнюю запись истории для рендера
                $history = $lid->getHistories()->with('user')->one();

                return [
                    'success' => true,
                    'message' => 'Добавлено',
                    'history' => $this->renderPartial('_history-item', ['item' => $history]),
                ];
            }

            return ['success' => false, 'message' => 'Ошибка добавления'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Конверсия лида в ученика (ручная)
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionConvertToPupil($id)
    {
        $lid = $this->findModel($id);

        // Если уже конвертирован
        if ($lid->isConverted()) {
            Yii::$app->session->setFlash('info', 'Лид уже был конвертирован в ученика');
            return $this->redirect(['pupil/view', 'id' => $lid->pupil_id]);
        }

        if ($this->request->isPost) {
            $pupil = LidService::convertToPupil($lid);

            if ($pupil) {
                Yii::$app->session->setFlash('success', 'Ученик успешно создан');
                return $this->redirect(['pupil/update', 'id' => $pupil->id]);
            }

            Yii::$app->session->setFlash('error', 'Ошибка создания ученика');
        }

        return $this->render('convert-to-pupil', [
            'lid' => $lid,
        ]);
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
     * AJAX: Переключение тега
     *
     * @return array
     */
    public function actionToggleTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $tagId = (int) $this->request->post('tag_id');

        try {
            $model = $this->findModel($id);

            // Проверяем существование тега
            $tag = LidTag::find()
                ->byOrganization()
                ->notDeleted()
                ->andWhere(['id' => $tagId])
                ->one();

            if (!$tag) {
                return ['success' => false, 'message' => 'Тег не найден'];
            }

            $hadTag = $model->hasTag($tagId);

            if ($model->toggleTag($tagId)) {
                // Обновляем связи для получения актуальных данных
                $model->refresh();

                return [
                    'success' => true,
                    'tags' => $model->getTags(),
                    'hasTag' => !$hadTag,
                    'message' => !$hadTag ? 'Тег добавлен' : 'Тег удалён',
                ];
            }

            return ['success' => false, 'message' => 'Ошибка обновления тега'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Обновление отдельного поля (inline-edit)
     *
     * @return array
     */
    public function actionUpdateField()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $field = $this->request->post('field');
        $value = $this->request->post('value');

        try {
            $model = $this->findModel($id);
            return LidService::updateField($model, $field, $value);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
     * AJAX: Получить WhatsApp шаблоны
     *
     * @return array
     */
    public function actionGetWhatsappTemplates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $templates = SmsTemplate::findWhatsAppTemplates();

        $result = [];
        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->id,
                'code' => $template->code,
                'name' => $template->name,
                'content' => $template->content,
            ];
        }

        return [
            'success' => true,
            'templates' => $result,
        ];
    }

    /**
     * AJAX: Получить сформированное WhatsApp сообщение для лида
     *
     * @return array
     */
    public function actionRenderWhatsappMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = $this->request->get('lid_id');
        $templateId = $this->request->get('template_id');

        try {
            $lid = $this->findModel($lidId);
            $template = SmsTemplate::findOne($templateId);

            if (!$template) {
                return ['success' => false, 'message' => 'Шаблон не найден'];
            }

            // Получаем данные для подстановки
            $org = Organizations::findOne(Organizations::getCurrentOrganizationId());
            $manager = Yii::$app->user->identity;

            $data = [
                'name' => $lid->getContactName() ?: 'Клиент',
                'pupil_name' => $lid->fio ?: '',
                'manager' => $manager ? $manager->fio : '',
                'org_name' => $org ? $org->name : '',
                'date' => '{дата}',
                'time' => '{время}',
                'address' => $org ? ($org->address ?? '') : '',
                'subject' => '',
            ];

            $message = $template->render($data);

            // Формируем WhatsApp URL с текстом
            $phone = Lids::cleanPhone($lid->getContactPhone());
            if ($phone) {
                $phone = ltrim($phone, '+');
                if (strpos($phone, '8') === 0) {
                    $phone = '7' . substr($phone, 1);
                }
            }

            $whatsappUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

            return [
                'success' => true,
                'message' => $message,
                'whatsapp_url' => $whatsappUrl,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Проверка на дубликаты по телефону
     *
     * @return array
     */
    public function actionCheckDuplicates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $phone = $this->request->get('phone');
        $excludeId = $this->request->get('exclude_id');

        if (!$phone) {
            return ['success' => true, 'duplicates' => []];
        }

        $duplicates = Lids::findDuplicates($phone, $excludeId);

        $result = [];
        foreach ($duplicates as $lid) {
            $result[] = [
                'id' => $lid->id,
                'fio' => $lid->fio ?: 'Без имени',
                'phone' => $lid->phone,
                'parent_phone' => $lid->parent_phone,
                'status' => $lid->getStatusLabel(),
                'status_color' => $lid->getStatusColor(),
                'created_at' => Yii::$app->formatter->asDate($lid->created_at, 'php:d.m.Y'),
            ];
        }

        return [
            'success' => true,
            'duplicates' => $result,
            'has_duplicates' => count($result) > 0,
        ];
    }

    /**
     * Получить скрипт продаж для статуса (AJAX)
     *
     * @return array
     */
    public function actionGetSalesScript()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $status = $this->request->get('status');

        if (!$status) {
            return ['success' => false, 'error' => 'Статус не указан'];
        }

        $scripts = SalesScript::getForStatus($status);

        if (empty($scripts)) {
            // Если скриптов нет, создаём дефолтные
            SalesScript::createDefaults(Organizations::getCurrentOrganizationId());
            $scripts = SalesScript::getForStatus($status);
        }

        $result = [];
        foreach ($scripts as $script) {
            $result[] = $script->toApiArray();
        }

        return [
            'success' => true,
            'scripts' => $result,
            'status' => $status,
        ];
    }

    /**
     * Получить все скрипты продаж (AJAX)
     *
     * @return array
     */
    public function actionGetAllSalesScripts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $grouped = SalesScript::getAllGroupedByStatus();

        if (empty($grouped)) {
            // Если скриптов нет, создаём дефолтные
            SalesScript::createDefaults(Organizations::getCurrentOrganizationId());
            $grouped = SalesScript::getAllGroupedByStatus();
        }

        $result = [];
        foreach ($grouped as $status => $scripts) {
            $result[$status] = [];
            foreach ($scripts as $script) {
                $result[$status][] = $script->toArray();
            }
        }

        return [
            'success' => true,
            'scripts' => $result,
        ];
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
