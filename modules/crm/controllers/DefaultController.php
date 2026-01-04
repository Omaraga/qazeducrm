<?php

namespace app\modules\crm\controllers;

use app\models\search\DateSearch;
use yii\web\Controller;

/**
 * Default controller for the CRM module - Dashboard
 */
class DefaultController extends Controller
{
    /**
     * Dashboard - главная страница CRM
     */
    public function actionIndex()
    {
        $this->view->title = 'Dashboard';

        $search = new DateSearch();

        return $this->render('index', [
            'data' => $search->getWeekPayments(),
            'week' => $search->getWeeks(true),
        ]);
    }
}
