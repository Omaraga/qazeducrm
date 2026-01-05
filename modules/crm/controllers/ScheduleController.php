<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\forms\TypicalLessonForm;
use app\models\Lesson;
use app\models\Organizations;
use app\models\Room;
use app\models\services\ScheduleConflictService;
use app\models\services\ScheduleService;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ScheduleController implements the CRUD actions for Lesson model.
 */
class ScheduleController extends Controller
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
                                OrganizationRoles::TEACHER,
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
     * Lists all TypicalSchedule models.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', []);
    }

    /**
     * AJAX: Получить события с фильтрами
     * POST: start, end, groups[], teachers[]
     */
    public function actionEvents()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax) {
            return [];
        }

        $request = \Yii::$app->request;

        // Получаем данные из JSON body или POST
        $bodyParams = $request->getBodyParams();
        $start = $bodyParams['start'] ?? $request->post('start');
        $end = $bodyParams['end'] ?? $request->post('end');
        $groupIds = $bodyParams['groups'] ?? $request->post('groups', []);
        $teacherIds = $bodyParams['teachers'] ?? $request->post('teachers', []);

        // Если переданы timestamps, конвертируем в даты
        if (is_numeric($start)) {
            $start = date('Y-m-d', $start);
        }
        if (is_numeric($end)) {
            $end = date('Y-m-d', $end);
        }

        // Если даты не переданы, используем текущую неделю
        if (!$start || !$end) {
            $today = new \DateTime();
            $start = $today->modify('monday this week')->format('Y-m-d');
            $end = $today->modify('sunday this week')->format('Y-m-d');
        }

        return ScheduleService::getLessonEventsFiltered($start, $end, $groupIds, $teacherIds);
    }

    /**
     * AJAX: Получить данные для фильтров (группы, учителя, кабинеты)
     */
    public function actionFilters()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'groups' => ScheduleService::getGroupsForFilter(),
            'teachers' => ScheduleService::getTeachersForFilter(),
            'rooms' => ScheduleService::getRoomsForFilter(),
        ];
    }

    /**
     * AJAX: Получить детали урока для модального просмотра
     */
    public function actionDetails($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $details = ScheduleService::getLessonDetails($id);

        if (!$details) {
            return ['success' => false, 'message' => 'Урок не найден'];
        }

        return ['success' => true, 'data' => $details];
    }

    /**
     * AJAX: Создать урок (модальная форма)
     */
    public function actionAjaxCreate()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $model = new Lesson();

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => 'Занятие успешно создано',
                'id' => $model->id,
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при создании занятия',
            'errors' => $model->errors,
        ];
    }

    /**
     * AJAX: Обновить урок (модальная форма)
     */
    public function actionAjaxUpdate($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => 'Занятие успешно обновлено',
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при обновлении занятия',
            'errors' => $model->errors,
        ];
    }

    /**
     * AJAX: Удалить урок
     */
    public function actionAjaxDelete($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        try {
            $this->findModel($id)->delete();
            return ['success' => true, 'message' => 'Занятие удалено'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка при удалении'];
        }
    }

    /**
     * AJAX: Переместить урок (drag & drop)
     * POST: id, newDate, newStartTime
     */
    public function actionMove()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $id = \Yii::$app->request->post('id');
        $newDate = \Yii::$app->request->post('newDate');
        $newStartTime = \Yii::$app->request->post('newStartTime');

        if (!$id || !$newDate || !$newStartTime) {
            return ['success' => false, 'message' => 'Недостаточно данных'];
        }

        if (ScheduleService::moveLesson($id, $newDate, $newStartTime)) {
            return ['success' => true, 'message' => 'Занятие перемещено'];
        }

        return ['success' => false, 'message' => 'Ошибка при перемещении'];
    }

    public function actionTeachers()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return [];
        }

        $groupId = \Yii::$app->request->post('id');
        if (!$groupId) {
            return [];
        }

        return ScheduleService::getTeachersForGroup($groupId);
    }

    /**
     * AJAX: Получить список кабинетов
     */
    public function actionRooms()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return Room::getList();
    }

    /**
     * AJAX: Проверить конфликты расписания
     * POST: teacher_id, group_id, room_id, date, start_time, end_time, exclude_id
     */
    public function actionCheckConflicts()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'conflicts' => []];
        }

        $request = \Yii::$app->request;

        $teacherId = $request->post('teacher_id');
        $groupId = $request->post('group_id');
        $roomId = $request->post('room_id');
        $date = $request->post('date');
        $startTime = $request->post('start_time');
        $endTime = $request->post('end_time');
        $excludeId = $request->post('exclude_id');

        if (!$date || !$startTime || !$endTime) {
            return ['success' => false, 'conflicts' => [], 'message' => 'Недостаточно данных'];
        }

        $conflicts = ScheduleConflictService::checkAllConflicts(
            $teacherId ? (int) $teacherId : null,
            $groupId ? (int) $groupId : null,
            $roomId ? (int) $roomId : null,
            $date,
            $startTime,
            $endTime,
            $excludeId ? (int) $excludeId : null
        );

        return [
            'success' => true,
            'conflicts' => $conflicts,
            'hasConflicts' => count($conflicts) > 0,
        ];
    }

    /**
     * Displays a single TypicalSchedule model.
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

    public function actionTypicalSchedule(){
        $model = new TypicalLessonForm();
        $model->loadDefault();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', 'Расписание успешно создано');
                return $this->redirect(OrganizationUrl::to(['schedule/index']));
            }
        }
        return $this->render('typical-schedule', [
            'model' => $model,
        ]);
    }

    /**
     * AJAX: Получить события типового расписания для календаря
     */
    public function actionTypicalEvents()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return ScheduleService::getTypicalScheduleEventsForCalendar();
    }

    /**
     * AJAX: Получить предпросмотр генерации из типового расписания
     * POST: date_start, date_end
     */
    public function actionTypicalPreview()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $dateStart = \Yii::$app->request->post('date_start');
        $dateEnd = \Yii::$app->request->post('date_end');

        if (!$dateStart || !$dateEnd) {
            return ['success' => false, 'message' => 'Укажите даты'];
        }

        return ScheduleService::getTypicalSchedulePreview($dateStart, $dateEnd);
    }

    /**
     * AJAX: Создать расписание из типового
     * POST: date_start, date_end, skip_conflicts
     */
    public function actionTypicalGenerate()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isAjax || !\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $dateStart = \Yii::$app->request->post('date_start');
        $dateEnd = \Yii::$app->request->post('date_end');
        $skipConflicts = \Yii::$app->request->post('skip_conflicts', false);

        if (!$dateStart || !$dateEnd) {
            return ['success' => false, 'message' => 'Укажите даты'];
        }

        $result = ScheduleService::generateFromTypicalSchedule($dateStart, $dateEnd, $skipConflicts);

        return $result;
    }

    /**
     * Creates a new TypicalSchedule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Lesson();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['schedule/index']));
            }
        } else {
            $model->loadDefaultValues();
        }
        if (\Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TypicalSchedule model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Lesson::findOne($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(OrganizationUrl::to(['schedule/index']));
        }else{
            $model->date = $model->date ? date('d.m.Y', strtotime($model->date)) : date('d.m.Y');
        }
        if (\Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TypicalSchedule model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(OrganizationUrl::to(['schedule/index']));
    }

    /**
     * AJAX: Получить настройки календаря для организации
     */
    public function actionSettings()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $org = Organizations::getCurrentOrganization();

        return [
            'grid_interval' => $org ? (int)($org->schedule_grid_interval ?? 60) : 60,
            'view_mode' => $org ? ($org->schedule_view_mode ?? 'week') : 'week',
        ];
    }

    /**
     * AJAX: Сохранить настройки календаря для организации
     */
    public function actionSaveSettings()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!\Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $org = Organizations::getCurrentOrganization();
        if (!$org) {
            return ['success' => false, 'message' => 'Организация не найдена'];
        }

        // Получаем данные из JSON body или POST
        $bodyParams = \Yii::$app->request->getBodyParams();

        // Сохраняем grid_interval если передан
        if (isset($bodyParams['grid_interval'])) {
            $gridInterval = (int)$bodyParams['grid_interval'];
            if (in_array($gridInterval, [10, 15, 30, 60])) {
                $org->schedule_grid_interval = $gridInterval;
            }
        }

        // Сохраняем view_mode если передан
        if (isset($bodyParams['view_mode'])) {
            $viewMode = $bodyParams['view_mode'];
            if (in_array($viewMode, ['day', 'week', 'month'])) {
                $org->schedule_view_mode = $viewMode;
            }
        }

        if ($org->save(false)) {
            return ['success' => true, 'message' => 'Настройки сохранены'];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Finds the Lesson model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Lesson the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Lesson::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(\Yii::t('main', 'Урок не найден.'));
        }

        return $model;
    }
}
