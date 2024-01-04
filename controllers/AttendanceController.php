<?php

namespace app\controllers;

use app\models\forms\AttendancesForm;
use app\models\Lesson;

class AttendanceController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLesson($id){
        $lesson = Lesson::findOne($id);
        $model = new AttendancesForm();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['schedule/index']);
            }
        }
        return $this->render('lesson', [
            'lesson' => $lesson,
            'model' => $model
        ]);
    }

}
