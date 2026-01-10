<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\forms\AttendancesForm;
use app\models\Lesson;
use app\models\LessonAttendance;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AttendanceController - управление посещаемостью занятий
 */
class AttendanceController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'save-status' => ['POST'],
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
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Посещаемость урока
     * Учитель может работать только со своими занятиями
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    public function actionLesson($id)
    {
        $lesson = $this->findLesson($id);

        // Проверка доступа учителя - может работать только со своими занятиями
        $this->checkTeacherAccess($lesson);

        $model = new AttendancesForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['schedule/index']));
            }
        }

        return $this->render('lesson', [
            'lesson' => $lesson,
            'model' => $model
        ]);
    }

    /**
     * Проверка доступа учителя к занятию
     * Учитель может работать только со своими занятиями
     *
     * @param Lesson $lesson
     * @throws ForbiddenHttpException
     */
    protected function checkTeacherAccess(Lesson $lesson): void
    {
        // Не учитель - доступ разрешен
        if (!RoleChecker::isTeacherOnly()) {
            return;
        }

        // Учитель - проверяем, что это его занятие
        $teacherId = RoleChecker::getCurrentTeacherId();
        if ($lesson->teacher_id !== $teacherId) {
            throw new ForbiddenHttpException('Вы не являетесь преподавателем этого занятия');
        }
    }

    /**
     * Найти урок по ID с проверкой организации
     * @param int $id
     * @return Lesson
     * @throws NotFoundHttpException
     */
    protected function findLesson($id)
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

    /**
     * AJAX: Сохранить статус посещаемости для одного ученика
     *
     * @return array
     */
    public function actionSaveStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lessonId = Yii::$app->request->post('lesson_id');
        $pupilId = Yii::$app->request->post('pupil_id');
        $status = Yii::$app->request->post('status');

        if (!$lessonId || !$pupilId || !$status) {
            return ['success' => false, 'message' => 'Не указаны обязательные параметры'];
        }

        try {
            $lesson = $this->findLesson($lessonId);
            $this->checkTeacherAccess($lesson);

            // Ищем или создаем запись посещаемости
            $attendance = LessonAttendance::find()
                ->where(['lesson_id' => $lessonId, 'pupil_id' => $pupilId])
                ->byOrganization()
                ->notDeleted()
                ->one();

            if (!$attendance) {
                $attendance = new LessonAttendance();
                $attendance->lesson_id = $lessonId;
                $attendance->pupil_id = $pupilId;
                $attendance->teacher_id = $lesson->teacher_id;
            }

            $attendance->status = (int)$status;

            if ($attendance->save()) {
                return [
                    'success' => true,
                    'status' => $attendance->status,
                    'status_label' => $attendance->getStatusLabel(),
                ];
            }

            return ['success' => false, 'message' => 'Ошибка сохранения'];

        } catch (ForbiddenHttpException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (NotFoundHttpException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}
