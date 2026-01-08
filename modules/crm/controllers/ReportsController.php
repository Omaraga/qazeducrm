<?php

namespace app\modules\crm\controllers;

use app\components\ActiveRecord;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\Organizations;
use app\models\search\DateSearch;
use app\models\User;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

/**
 * ReportsController - отчёты CRM
 *
 * Поддерживает как старые отчеты (day, month, employer),
 * так и новую систему отчетов
 */
class ReportsController extends Controller
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
                    // Новая система отчетов - доступ проверяется в action
                    [
                        'actions' => ['index', 'view', 'category', 'export', 'test'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // Старые отчеты - только директора и админы
                    [
                        'actions' => ['day', 'month', 'employer'],
                        'allow' => true,
                        'roles' => [
                            SystemRoles::SUPER,
                            OrganizationRoles::ADMIN,
                            OrganizationRoles::DIRECTOR,
                            OrganizationRoles::GENERAL_DIRECTOR,
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
     * Главная страница отчётов - новая версия с категориями
     */
    public function actionIndex()
    {
        $categories = ReportRegistry::getAccessibleCategories();
        $popularReports = ReportRegistry::getPopularReports();
        $reportsByCategory = ReportRegistry::getAccessibleReports();

        return $this->render('index-new', [
            'categories' => $categories,
            'popularReports' => $popularReports,
            'reportsByCategory' => $reportsByCategory,
        ]);
    }

    /**
     * Страница категории отчетов
     */
    public function actionCategory(string $category)
    {
        $categoryInfo = ReportRegistry::getCategoryInfo($category);
        if (!$categoryInfo) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        // Проверка доступа к категории
        $hasAccess = RoleChecker::isSuper();
        if (!$hasAccess) {
            $currentRole = RoleChecker::getCurrentRole();
            $hasAccess = $currentRole && in_array($currentRole, $categoryInfo['roles']);
        }
        if (!$hasAccess) {
            throw new ForbiddenHttpException('Доступ запрещен');
        }

        $reports = ReportRegistry::getReportsByCategory($category);
        $categories = ReportRegistry::getAccessibleCategories();

        return $this->render('category', [
            'category' => $category,
            'categoryInfo' => $categoryInfo,
            'reports' => $reports,
            'categories' => $categories,
        ]);
    }

    /**
     * Просмотр конкретного отчета
     */
    public function actionView(string $type)
    {
        $report = ReportRegistry::getReport($type);
        if (!$report) {
            throw new NotFoundHttpException('Отчет не найден');
        }

        if (!$report->checkAccess()) {
            throw new ForbiddenHttpException('Доступ запрещен');
        }

        $filter = ReportFilterDTO::fromRequest();
        $categories = ReportRegistry::getAccessibleCategories();

        // Получаем данные отчета
        $data = $report->getData($filter);
        $summary = $report->getSummary($filter);
        $chartData = $report->getChartData($filter);

        return $this->render('view', [
            'report' => $report,
            'filter' => $filter,
            'data' => $data,
            'summary' => $summary,
            'chartData' => $chartData,
            'categories' => $categories,
        ]);
    }

    /**
     * Экспорт отчета в Excel
     */
    public function actionExport(string $type, string $format = 'xlsx')
    {
        $report = ReportRegistry::getReport($type);
        if (!$report) {
            throw new NotFoundHttpException('Отчет не найден');
        }

        if (!$report->checkAccess()) {
            throw new ForbiddenHttpException('Доступ запрещен');
        }

        if (!$report->supportsExport()) {
            throw new NotFoundHttpException('Экспорт не поддерживается');
        }

        $filter = ReportFilterDTO::fromRequest();

        // TODO: Реализовать ReportExportService в спринте 2
        // $exportService = new \app\services\reports\ReportExportService();
        // return $exportService->export($report, $filter, $format);

        throw new NotFoundHttpException('Экспорт будет доступен в следующем обновлении');
    }

    // ========================================
    // СТАРЫЕ МЕТОДЫ (для обратной совместимости)
    // ========================================

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

    /**
     * Тестирование всех отчетов (только для разработки)
     */
    public function actionTest()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $orgId = \app\models\Organizations::getCurrentOrganizationId();
        $userId = \Yii::$app->user->id ?? null;
        $activeOrgId = \Yii::$app->user->identity->active_organization_id ?? null;

        $filter = new ReportFilterDTO();
        $filter->dateFrom = date('Y-01-01');
        $filter->dateTo = date('Y-m-d');

        $reportTypes = [
            'finance-income',
            'finance-expenses',
            'finance-debts',
            'leads-funnel',
            'leads-sources',
            'leads-managers',
            'pupils-attendance',
            'teachers-salary',
            'operations-groups',
        ];

        $results = [];

        foreach ($reportTypes as $type) {
            try {
                $report = ReportRegistry::getReport($type);
                if (!$report) {
                    $results[$type] = ['status' => 'ERROR', 'message' => 'Report not found'];
                    continue;
                }

                $data = $report->getData($filter);
                $summary = $report->getSummary($filter);

                $results[$type] = [
                    'status' => 'OK',
                    'title' => $report->getTitle(),
                    'data_count' => count($data),
                    'summary' => $summary,
                    'sample' => array_slice($data, 0, 2),
                ];
            } catch (\Exception $e) {
                $results[$type] = [
                    'status' => 'ERROR',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                ];
            }
        }

        return [
            '_debug' => [
                'organization_id' => $orgId,
                'user_id' => $userId,
                'active_organization_id' => $activeOrgId,
                'date_from' => $filter->dateFrom,
                'date_to' => $filter->dateTo,
            ],
            'reports' => $results,
        ];
    }
}
