<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\SystemRoles;
use app\models\ScheduleTemplate;
use app\models\services\ScheduleTemplateService;
use app\models\TypicalSchedule;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ScheduleTemplateController - контроллер для шаблонов расписания
 */
class ScheduleTemplateController extends CrmBaseController
{
    /**
     * @inheritDoc
     */
    protected array $postActions = ['delete', 'create', 'update', 'duplicate', 'add-lesson', 'update-lesson', 'delete-lesson', 'generate', 'create-from-schedule'];

    protected array $allowedRoles = [
        SystemRoles::SUPER,
        OrganizationRoles::ADMIN,
        OrganizationRoles::DIRECTOR,
        OrganizationRoles::GENERAL_DIRECTOR,
        OrganizationRoles::TEACHER,
    ];

    /**
     * Список шаблонов
     */
    public function actionIndex()
    {
        $templates = ScheduleTemplateService::getTemplatesWithCounts();

        return $this->render('index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Просмотр шаблона (календарь + генерация)
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $template = $this->findModel($id);
        $formData = ScheduleTemplateService::getFormData();

        return $this->render('view', [
            'model' => $template,
            'formData' => $formData,
        ]);
    }

    /**
     * Создание шаблона (AJAX)
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new ScheduleTemplate();
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Шаблон создан',
                'id' => $model->id,
                'redirect' => OrganizationUrl::to(['schedule-template/view', 'id' => $model->id]),
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка создания шаблона',
            'errors' => $model->getErrors(),
        ];
    }

    /**
     * Обновление шаблона (AJAX)
     *
     * @param int $id
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Шаблон обновлен',
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка обновления',
            'errors' => $model->getErrors(),
        ];
    }

    /**
     * Удаление шаблона (AJAX)
     *
     * @param int $id
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        // Удаляем все занятия шаблона
        TypicalSchedule::updateAll(
            ['is_deleted' => 1],
            ['template_id' => $model->id]
        );

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Шаблон удален',
                'redirect' => OrganizationUrl::to(['schedule-template/index']),
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка удаления',
        ];
    }

    /**
     * Дублирование шаблона (AJAX)
     *
     * @param int $id
     */
    public function actionDuplicate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $newName = Yii::$app->request->post('name');

        $newTemplate = $model->duplicate($newName);

        if ($newTemplate) {
            return [
                'success' => true,
                'message' => 'Шаблон скопирован',
                'id' => $newTemplate->id,
                'redirect' => OrganizationUrl::to(['schedule-template/view', 'id' => $newTemplate->id]),
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка копирования',
        ];
    }

    /**
     * Получить события шаблона для календаря (AJAX)
     *
     * @param int $id
     */
    public function actionEvents($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $template = $this->findModel($id);
        $events = ScheduleTemplateService::getTemplateEvents($template->id);

        return [
            'success' => true,
            'events' => $events,
        ];
    }

    /**
     * Добавить занятие в шаблон (AJAX)
     *
     * @param int $id
     */
    public function actionAddLesson($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->post();
        return ScheduleTemplateService::addLesson($id, $data);
    }

    /**
     * Обновить занятие (AJAX)
     *
     * @param int $lessonId
     */
    public function actionUpdateLesson($lessonId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->post();
        return ScheduleTemplateService::updateLesson($lessonId, $data);
    }

    /**
     * Удалить занятие (AJAX)
     *
     * @param int $lessonId
     */
    public function actionDeleteLesson($lessonId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ScheduleTemplateService::deleteLesson($lessonId);
    }

    /**
     * Предпросмотр генерации (AJAX)
     *
     * @param int $id
     */
    public function actionPreview($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dateStart = Yii::$app->request->get('date_start') ?: Yii::$app->request->post('date_start');
        $dateEnd = Yii::$app->request->get('date_end') ?: Yii::$app->request->post('date_end');
        $dayMappingJson = Yii::$app->request->post('day_mapping');

        if (!$dateStart || !$dateEnd) {
            return ['success' => false, 'message' => 'Укажите период'];
        }

        // Парсим маппинг дней если передан
        $dayMapping = null;
        if ($dayMappingJson) {
            $dayMapping = json_decode($dayMappingJson, true);
        }

        return ScheduleTemplateService::getPreview($id, $dateStart, $dateEnd, $dayMapping);
    }

    /**
     * Генерация расписания из шаблона (AJAX)
     *
     * Принимает либо:
     * - lessons: JSON массив занятий (новый wizard интерфейс)
     * - date_start, date_end, skip_conflicts: старый интерфейс (для совместимости)
     *
     * @param int $id
     */
    public function actionGenerate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Новый способ - получаем готовый массив занятий
        $lessonsJson = Yii::$app->request->post('lessons');
        if ($lessonsJson) {
            $lessons = json_decode($lessonsJson, true);
            if (is_array($lessons) && !empty($lessons)) {
                return ScheduleTemplateService::generateFromLessons($lessons);
            }
            return ['success' => false, 'message' => 'Неверный формат данных'];
        }

        // Старый способ - для обратной совместимости
        $dateStart = Yii::$app->request->post('date_start');
        $dateEnd = Yii::$app->request->post('date_end');
        $skipConflicts = (bool)Yii::$app->request->post('skip_conflicts', false);

        if (!$dateStart || !$dateEnd) {
            return ['success' => false, 'message' => 'Укажите период'];
        }

        return ScheduleTemplateService::generateFromTemplate($id, $dateStart, $dateEnd, $skipConflicts);
    }

    /**
     * Получить данные для формы добавления занятия (AJAX)
     */
    public function actionFormData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => true,
            'data' => ScheduleTemplateService::getFormData(),
        ];
    }

    /**
     * Создать шаблон из существующего расписания (AJAX)
     *
     * Получает все занятия за указанный период и создает шаблон
     */
    public function actionCreateFromSchedule()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = Yii::$app->request->post('name');
        $description = Yii::$app->request->post('description', '');
        $dateStart = Yii::$app->request->post('date_start');
        $dateEnd = Yii::$app->request->post('date_end');

        if (!$name || !$dateStart || !$dateEnd) {
            return [
                'success' => false,
                'message' => 'Укажите название и период'
            ];
        }

        $result = ScheduleTemplateService::createFromSchedule($name, $description, $dateStart, $dateEnd);

        if ($result['success']) {
            $result['redirect'] = OrganizationUrl::to(['schedule-template/view', 'id' => $result['id']]);
        }

        return $result;
    }

    /**
     * Найти модель по ID
     *
     * @param int $id
     * @return ScheduleTemplate
     * @throws NotFoundHttpException
     */
    protected function findModel($id): ScheduleTemplate
    {
        $model = ScheduleTemplate::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->andWhere(['is_deleted' => 0])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Шаблон не найден');
        }

        return $model;
    }
}
