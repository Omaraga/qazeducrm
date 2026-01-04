<?php

namespace app\modules\crm\controllers;

use app\components\ActiveRecord;
use app\helpers\OrganizationRoles;
use app\models\Organizations;
use app\models\search\DateSearch;
use app\models\User;
use yii\base\BaseObject;

class ReportsController extends \yii\web\Controller
{
    /**
     * Главная страница отчётов - редирект на дневной отчёт
     */
    public function actionIndex()
    {
        return $this->redirect(['day']);
    }

    public function actionEmployer()
    {

        $searchModel = new DateSearch();
        $dateTeacherSalary = $searchModel->searchEmployer($this->request->queryParams);
        $teachers = User::find()->innerJoinWith(['currentUserOrganizations' => function($q){
            $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
        }])->all();
        return $this->render('employer', [
            'dateTeacherSalary' => $dateTeacherSalary,
            'searchModel' => $searchModel,
            'teachers' => $teachers,
        ]);
    }

    public function actionDay()
    {

        $searchModel = new DateSearch();
        $searchModel->type = \Yii::$app->request->get('type') ? : 1;
        $dataArray = $searchModel->searchDay($this->request->queryParams);

        return $this->render('day', [
            'dataArray' => $dataArray,
            'searchModel' => $searchModel,
            'type' => $searchModel->type,
        ]);
    }

    public function actionMonth(){
        $searchModel = new DateSearch();
        $searchModel->type = \Yii::$app->request->get('type') ? : DateSearch::TYPE_ATTENDANCE;
        $dataArray = $searchModel->searchMonth($this->request->queryParams);

        return $this->render('month', [
            'dataArray' => $dataArray,
            'searchModel' => $searchModel,
            'type' => $searchModel->type,
        ]);
    }


}
