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
        $dataProvider = $searchModel->searchEmployer($this->request->queryParams);

        return $this->render('employer', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

}
