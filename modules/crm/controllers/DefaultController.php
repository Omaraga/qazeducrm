<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\services\DashboardService;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Default controller for the CRM module - Dashboard
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
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

    /**
     * Dashboard - главная страница CRM
     */
    public function actionIndex()
    {
        $this->view->title = 'Dashboard';

        $service = new DashboardService();
        $stats = $service->getStatistics();

        return $this->render('index-tailwind', [
            'stats' => $stats,
        ]);
    }
}
