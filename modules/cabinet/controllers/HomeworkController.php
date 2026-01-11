<?php

namespace app\modules\cabinet\controllers;

use app\models\relations\EducationGroup;
use app\models\Homework;
use app\models\HomeworkSubmission;
use app\modules\cabinet\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * HomeworkController - домашние задания в личном кабинете
 */
class HomeworkController extends CabinetBaseController
{
    /**
     * Список домашних заданий для ученика
     */
    public function actionIndex($pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Выбираем ученика
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        // Получаем группы ученика
        $pupilIds = $selectedPupil ? [$selectedPupil->id] : array_map(fn($p) => $p->id, $pupils);

        $groupIds = EducationGroup::findWithDeleted()
            ->alias('eg')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['pe.pupil_id' => $pupilIds])
            ->andWhere(['pe.is_deleted' => 0])
            ->andWhere(['eg.is_deleted' => 0])
            ->select('eg.group_id')
            ->column();

        // Активные задания
        $dataProvider = new ActiveDataProvider([
            'query' => Homework::find()
                ->where(['group_id' => $groupIds])
                ->andWhere(['in', 'status', [Homework::STATUS_ACTIVE, Homework::STATUS_CLOSED]])
                ->orderBy(['due_date' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
        ]);
    }

    /**
     * Просмотр задания
     */
    public function actionView($id, $pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];
        $selectedPupil = $pupil_id ? $this->getPupilById($pupil_id) : (count($pupils) === 1 ? $pupils[0] : null);

        // Получаем группы учеников для проверки доступа
        $pupilIds = $selectedPupil ? [$selectedPupil->id] : array_map(fn($p) => $p->id, $pupils);
        $groupIds = $this->getPupilGroupIds($pupilIds);

        // Загружаем homework только если принадлежит группе ученика
        $homework = Homework::find()
            ->where(['id' => $id])
            ->andWhere(['group_id' => $groupIds])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$homework) {
            throw new NotFoundHttpException(Yii::t('app', 'Задание не найдено'));
        }

        // Получаем ответ ученика (если есть)
        $submission = null;
        if ($selectedPupil) {
            $submission = $homework->getSubmissionByPupil($selectedPupil->id);
        }

        return $this->render('view', [
            'homework' => $homework,
            'submission' => $submission,
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
        ]);
    }

    /**
     * Сдать домашнее задание
     */
    public function actionSubmit($id, $pupil_id)
    {
        $pupil = $this->getPupilById($pupil_id);
        if (!$pupil) {
            throw new NotFoundHttpException(Yii::t('app', 'Ученик не найден'));
        }

        // Проверяем что homework принадлежит группе ученика
        $groupIds = $this->getPupilGroupIds([$pupil->id]);

        $homework = Homework::find()
            ->where(['id' => $id])
            ->andWhere(['group_id' => $groupIds])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$homework || !$homework->canSubmit()) {
            throw new NotFoundHttpException(Yii::t('app', 'Задание не найдено или приём закрыт'));
        }

        // Получаем или создаём ответ
        $submission = HomeworkSubmission::getOrCreate($homework->id, $pupil->id);

        if (Yii::$app->request->isPost) {
            $answer = Yii::$app->request->post('answer');

            // Обработка файлов
            $uploadedFiles = UploadedFile::getInstancesByName('files');
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    $filename = Yii::$app->security->generateRandomString(8) . '.' . $file->extension;
                    $path = 'uploads/homework-submissions/' . date('Y/m/');
                    @mkdir(Yii::getAlias('@webroot/' . $path), 0755, true);

                    if ($file->saveAs(Yii::getAlias('@webroot/' . $path . $filename))) {
                        $submission->addFile($file->baseName . '.' . $file->extension, $path . $filename);
                    }
                }
            }

            if ($submission->submit($answer)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Домашнее задание сдано'));
                return $this->redirect(['view', 'id' => $homework->id, 'pupil_id' => $pupil->id]);
            }
        }

        return $this->render('submit', [
            'homework' => $homework,
            'submission' => $submission,
            'pupil' => $pupil,
        ]);
    }
}
