<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\enum\StatusEnum;
use app\models\Group;
use app\models\Homework;
use app\models\HomeworkSubmission;
use app\models\search\HomeworkSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * HomeworkController - управление домашними заданиями
 */
class HomeworkController extends CrmBaseController
{
    /**
     * {@inheritdoc}
     */
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
                            OrganizationRoles::TEACHER,
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'check' => ['POST'],
                    'return' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список домашних заданий
     */
    public function actionIndex()
    {
        $searchModel = new HomeworkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Для учителя - только его группы
        if (RoleChecker::isTeacherOnly()) {
            $dataProvider->query->innerJoin('teacher_group tg', 'tg.group_id = homework.group_id')
                ->andWhere(['tg.user_id' => Yii::$app->user->id]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр задания и ответов
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Получаем все ответы
        $submissions = HomeworkSubmission::find()
            ->with(['pupil'])
            ->where(['homework_id' => $id])
            ->orderBy(['submitted_at' => SORT_DESC])
            ->all();

        return $this->render('view', [
            'model' => $model,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Создание задания
     */
    public function actionCreate($group_id = null)
    {
        $model = new Homework();

        if ($group_id) {
            $model->group_id = $group_id;
        }

        if ($model->load(Yii::$app->request->post())) {
            // Обработка файлов
            $files = UploadedFile::getInstances($model, 'attachments');
            if ($files) {
                $attachments = [];
                foreach ($files as $file) {
                    $filename = Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
                    $path = 'uploads/homework/' . date('Y/m/');
                    @mkdir(Yii::getAlias('@webroot/' . $path), 0755, true);

                    if ($file->saveAs(Yii::getAlias('@webroot/' . $path . $filename))) {
                        $attachments[] = [
                            'name' => $file->baseName . '.' . $file->extension,
                            'path' => $path . $filename,
                            'uploaded_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
                $model->setAttachmentsList($attachments);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Домашнее задание создано'));
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Группы для выбора
        $groupsQuery = Group::find()
            ->where(['status' => StatusEnum::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC]);

        // Для учителя - только его группы
        if (RoleChecker::isTeacherOnly()) {
            $groupsQuery->innerJoin('teacher_group tg', 'tg.group_id = {{%group}}.id')
                ->andWhere(['tg.user_id' => Yii::$app->user->id]);
        }

        $groups = $groupsQuery->all();

        return $this->render('create', [
            'model' => $model,
            'groups' => $groups,
        ]);
    }

    /**
     * Редактирование задания
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Обработка новых файлов
            $files = UploadedFile::getInstances($model, 'attachments');
            if ($files) {
                $attachments = $model->getAttachmentsList();
                foreach ($files as $file) {
                    $filename = Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
                    $path = 'uploads/homework/' . date('Y/m/');
                    @mkdir(Yii::getAlias('@webroot/' . $path), 0755, true);

                    if ($file->saveAs(Yii::getAlias('@webroot/' . $path . $filename))) {
                        $attachments[] = [
                            'name' => $file->baseName . '.' . $file->extension,
                            'path' => $path . $filename,
                            'uploaded_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
                $model->setAttachmentsList($attachments);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Домашнее задание обновлено'));
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $groupsQuery = Group::find()
            ->where(['status' => StatusEnum::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC]);

        if (RoleChecker::isTeacherOnly()) {
            $groupsQuery->innerJoin('teacher_group tg', 'tg.group_id = {{%group}}.id')
                ->andWhere(['tg.user_id' => Yii::$app->user->id]);
        }

        $groups = $groupsQuery->all();

        return $this->render('update', [
            'model' => $model,
            'groups' => $groups,
        ]);
    }

    /**
     * Удаление задания
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Домашнее задание удалено'));
        return $this->redirect(['index']);
    }

    /**
     * Проверка ответа ученика
     */
    public function actionCheck($id)
    {
        $submission = HomeworkSubmission::findOne($id);
        if (!$submission) {
            throw new NotFoundHttpException(Yii::t('app', 'Ответ не найден'));
        }

        $grade = Yii::$app->request->post('grade');
        $comment = Yii::$app->request->post('comment');

        if ($grade && $submission->check((int) $grade, $comment)) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Работа проверена'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Ошибка при проверке'));
        }

        return $this->redirect(['view', 'id' => $submission->homework_id]);
    }

    /**
     * Вернуть на доработку
     */
    public function actionReturn($id)
    {
        $submission = HomeworkSubmission::findOne($id);
        if (!$submission) {
            throw new NotFoundHttpException(Yii::t('app', 'Ответ не найден'));
        }

        $comment = Yii::$app->request->post('comment', '');

        if ($submission->returnForRevision($comment)) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Работа возвращена на доработку'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Ошибка'));
        }

        return $this->redirect(['view', 'id' => $submission->homework_id]);
    }

    /**
     * Удалить вложение
     */
    public function actionDeleteAttachment($id, $index)
    {
        $model = $this->findModel($id);
        $attachments = $model->getAttachmentsList();

        if (isset($attachments[$index])) {
            // Удаляем файл
            $filePath = Yii::getAlias('@webroot/' . $attachments[$index]['path']);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            unset($attachments[$index]);
            $model->setAttachmentsList(array_values($attachments));
            $model->save(false);
        }

        return $this->redirect(['update', 'id' => $id]);
    }

    /**
     * Закрыть приём работ
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->status = Homework::STATUS_CLOSED;
        $model->save(false);

        Yii::$app->session->setFlash('info', Yii::t('app', 'Приём работ закрыт'));
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Открыть приём работ
     */
    public function actionReopen($id)
    {
        $model = $this->findModel($id);
        $model->status = Homework::STATUS_ACTIVE;
        $model->save(false);

        Yii::$app->session->setFlash('success', Yii::t('app', 'Приём работ открыт'));
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Просмотр ответа
     */
    public function actionSubmission($id)
    {
        $submission = HomeworkSubmission::find()
            ->with(['homework', 'pupil'])
            ->where(['id' => $id])
            ->one();

        if (!$submission) {
            throw new NotFoundHttpException(Yii::t('app', 'Ответ не найден'));
        }

        return $this->render('submission', [
            'submission' => $submission,
        ]);
    }

    /**
     * Найти модель по ID
     */
    protected function findModel($id)
    {
        if (($model = Homework::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'Домашнее задание не найдено'));
    }
}
