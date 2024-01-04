<?php

namespace app\controllers;

use app\components\ActiveRecord;
use app\helpers\OrganizationRoles;
use app\models\Organizations;
use app\models\search\DateSearch;
use app\models\User;
use yii\base\BaseObject;

class ReportsController extends \yii\web\Controller
{
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

}
