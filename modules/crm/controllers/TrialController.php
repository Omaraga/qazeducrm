<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\enum\StatusEnum;
use app\models\Group;
use app\models\Lids;
use app\models\TrialLesson;
use app\models\search\TrialLessonSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контроллер управления пробными занятиями
 */
class TrialController extends CrmBaseController
{
    public function behaviors(): array
    {
        return [
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
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'complete' => ['POST'],
                    'no-show' => ['POST'],
                    'cancel' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список пробных занятий
     */
    public function actionIndex(): string
    {
        $searchModel = new TrialLessonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => TrialLesson::getStatistics(date('Y-m-01'), date('Y-m-t')),
            'todayTrials' => TrialLesson::getTodayTrials(),
        ]);
    }

    /**
     * Просмотр пробного занятия
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Создание пробного занятия
     */
    public function actionCreate(?int $lid_id = null): string|Response
    {
        $model = new TrialLesson();

        if ($lid_id && ($lid = Lids::findOne($lid_id))) {
            $model->lid_id = $lid_id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->updateLidStatus($model);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Пробное занятие создано'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'lids' => $this->getAvailableLids(),
            'groups' => $this->getActiveGroups(),
        ]);
    }

    /**
     * Редактирование пробного занятия
     */
    public function actionUpdate(int $id): string|Response
    {
        $model = $this->findModel($id);

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Нельзя редактировать завершённое пробное занятие'));
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Пробное занятие обновлено'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'groups' => $this->getActiveGroups(),
        ]);
    }

    /**
     * Удаление пробного занятия
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Пробное занятие удалено'));
        return $this->redirect(['index']);
    }

    /**
     * Отметить как проведённое
     */
    public function actionComplete(int $id): Response
    {
        $model = $this->findModel($id);
        $request = Yii::$app->request;

        $success = $model->markAsCompleted(
            $request->post('feedback'),
            $request->post('rating') ? (int)$request->post('rating') : null
        );

        Yii::$app->session->setFlash(
            $success ? 'success' : 'error',
            $success ? Yii::t('app', 'Пробное занятие отмечено как проведённое') : Yii::t('app', 'Ошибка сохранения')
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Отметить как "не пришёл"
     */
    public function actionNoShow(int $id): Response
    {
        $model = $this->findModel($id);
        $success = $model->markAsNoShow();

        Yii::$app->session->setFlash(
            $success ? 'warning' : 'error',
            $success ? Yii::t('app', 'Лид не пришёл на пробное занятие') : Yii::t('app', 'Ошибка сохранения')
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Отменить пробное занятие
     */
    public function actionCancel(int $id): Response
    {
        $model = $this->findModel($id);
        $success = $model->markAsCancelled(Yii::$app->request->post('reason'));

        Yii::$app->session->setFlash(
            $success ? 'info' : 'error',
            $success ? Yii::t('app', 'Пробное занятие отменено') : Yii::t('app', 'Ошибка сохранения')
        );

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Конвертировать в ученика
     */
    public function actionConvert(int $id): Response
    {
        $model = $this->findModel($id);

        if (!$model->lid) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Лид не найден'));
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->redirect(['/crm/pupil/create-from-lid', 'lid_id' => $model->lid_id, 'trial_id' => $model->id]);
    }

    /**
     * Календарь пробных занятий (AJAX)
     */
    public function actionCalendar(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $trials = TrialLesson::find()
            ->with(['lid', 'group'])
            ->where(['>=', 'date', $request->get('start')])
            ->andWhere(['<=', 'date', $request->get('end')])
            ->all();

        $colors = [
            TrialLesson::STATUS_PLANNED => '#3B82F6',
            TrialLesson::STATUS_CONFIRMED => '#F59E0B',
            TrialLesson::STATUS_COMPLETED => '#10B981',
            TrialLesson::STATUS_NO_SHOW => '#EF4444',
            TrialLesson::STATUS_CANCELLED => '#6B7280',
            TrialLesson::STATUS_CONVERTED => '#8B5CF6',
        ];

        return array_map(fn($trial) => [
            'id' => $trial->id,
            'title' => $trial->getLidName(),
            'start' => $trial->date . 'T' . $trial->time,
            'backgroundColor' => $colors[$trial->status] ?? '#6B7280',
            'borderColor' => $colors[$trial->status] ?? '#6B7280',
            'extendedProps' => [
                'status' => $trial->status,
                'statusLabel' => $trial->getStatusLabel(),
                'group' => $trial->group?->name ?? '',
                'phone' => $trial->getLidPhone(),
            ],
        ], $trials);
    }

    /**
     * Статистика пробных (AJAX)
     */
    public function actionStats(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return TrialLesson::getStatistics(
            Yii::$app->request->get('date_from', date('Y-m-01')),
            Yii::$app->request->get('date_to', date('Y-m-t'))
        );
    }

    /**
     * Поиск модели по ID
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): TrialLesson
    {
        if ($model = TrialLesson::findOne($id)) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'Пробное занятие не найдено'));
    }

    /**
     * Обновление статуса лида после создания пробного
     */
    private function updateLidStatus(TrialLesson $model): void
    {
        if ($model->lid && $model->lid->status < Lids::STATUS_TRIAL) {
            $model->lid->status = Lids::STATUS_TRIAL;
            $model->lid->save(false);
        }
    }

    /**
     * Получить список доступных лидов для записи на пробное
     */
    private function getAvailableLids(): array
    {
        return Lids::find()
            ->select(['id', 'fio', 'parent_fio', 'phone'])
            ->where(['not in', 'status', [Lids::STATUS_LOST, Lids::STATUS_NOT_TARGET, Lids::STATUS_IN_TRAINING]])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(100)
            ->all();
    }

    /**
     * Получить список активных групп
     */
    private function getActiveGroups(): array
    {
        return Group::find()
            ->where(['status' => StatusEnum::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }
}
