<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\services\DashboardService;
use yii\filters\AccessControl;

/**
 * Default controller for the CRM module - Dashboard
 */
class DefaultController extends CrmBaseController
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
     * Отображает разные данные в зависимости от роли пользователя
     */
    public function actionIndex()
    {
        $this->view->title = 'Dashboard';

        $service = new DashboardService();
        $stats = $service->getStatisticsForRole();

        return $this->render('index-tailwind', [
            'stats' => $stats,
        ]);
    }
}
