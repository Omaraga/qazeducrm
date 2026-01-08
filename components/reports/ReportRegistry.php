<?php

namespace app\components\reports;

use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use Yii;

/**
 * Реестр всех отчетов
 *
 * Хранит конфигурацию категорий и отчетов,
 * предоставляет фабрику для создания экземпляров
 */
class ReportRegistry
{
    // Категории отчетов
    const CATEGORY_OVERVIEW = 'overview';
    const CATEGORY_FINANCE = 'finance';
    const CATEGORY_LEADS = 'leads';
    const CATEGORY_PUPILS = 'pupils';
    const CATEGORY_TEACHERS = 'teachers';
    const CATEGORY_OPERATIONS = 'operations';
    const CATEGORY_TEACHER_PERSONAL = 'teacher-personal';

    /**
     * Конфигурация категорий
     */
    const CATEGORIES = [
        self::CATEGORY_OVERVIEW => [
            'label' => 'Обзор',
            'icon' => 'home',
            'description' => 'Сводные показатели',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR, OrganizationRoles::ADMIN],
        ],
        self::CATEGORY_FINANCE => [
            'label' => 'Финансы',
            'icon' => 'payment',
            'description' => 'Доходы, расходы, задолженности',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR],
        ],
        self::CATEGORY_LEADS => [
            'label' => 'Продажи',
            'icon' => 'funnel',
            'description' => 'Воронка лидов, источники, менеджеры',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR, OrganizationRoles::ADMIN],
        ],
        self::CATEGORY_PUPILS => [
            'label' => 'Ученики',
            'icon' => 'users',
            'description' => 'Посещаемость, набор, отток',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR, OrganizationRoles::ADMIN, OrganizationRoles::TEACHER],
        ],
        self::CATEGORY_TEACHERS => [
            'label' => 'Учителя',
            'icon' => 'user',
            'description' => 'Нагрузка, зарплаты',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR],
        ],
        self::CATEGORY_OPERATIONS => [
            'label' => 'Операции',
            'icon' => 'chart',
            'description' => 'Группы, расписание',
            'roles' => [SystemRoles::SUPER, OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR, OrganizationRoles::ADMIN],
        ],
        self::CATEGORY_TEACHER_PERSONAL => [
            'label' => 'Мои отчеты',
            'icon' => 'user-circle',
            'description' => 'Личная статистика',
            'roles' => [OrganizationRoles::TEACHER],
        ],
    ];

    /**
     * Маппинг идентификаторов отчетов на классы
     */
    private static array $reportClasses = [
        // Финансы
        'finance-income' => \app\services\reports\FinanceIncomeReport::class,
        'finance-expenses' => \app\services\reports\FinanceExpensesReport::class,
        'finance-debts' => \app\services\reports\FinanceDebtsReport::class,

        // Продажи
        'leads-funnel' => \app\services\reports\LeadsFunnelReport::class,
        'leads-sources' => \app\services\reports\LeadsSourcesReport::class,
        'leads-managers' => \app\services\reports\LeadsManagersReport::class,

        // Ученики
        'pupils-attendance' => \app\services\reports\PupilsAttendanceReport::class,
        'pupils-enrollment' => \app\services\reports\PupilsEnrollmentReport::class,

        // Учителя
        'teachers-salary' => \app\services\reports\TeacherSalaryReport::class,
        'teachers-workload' => \app\services\reports\TeacherWorkloadReport::class,

        // Операции
        'operations-groups' => \app\services\reports\GroupsCapacityReport::class,

        // Личные отчеты учителя
        'teacher-dashboard' => \app\services\reports\teacher\TeacherDashboardReport::class,
        'teacher-schedule' => \app\services\reports\teacher\TeacherScheduleReport::class,
        'teacher-attendance' => \app\services\reports\teacher\TeacherAttendanceReport::class,
        'teacher-groups' => \app\services\reports\teacher\TeacherGroupsReport::class,
        'teacher-earnings' => \app\services\reports\teacher\TeacherEarningsReport::class,
        'teacher-lessons' => \app\services\reports\teacher\TeacherLessonsReport::class,
        'teacher-pupils' => \app\services\reports\teacher\TeacherPupilProgressReport::class,
    ];

    /**
     * Получить экземпляр отчета по ID
     */
    public static function getReport(string $reportId): ?ReportInterface
    {
        if (!isset(self::$reportClasses[$reportId])) {
            return null;
        }

        $class = self::$reportClasses[$reportId];

        if (!class_exists($class)) {
            Yii::warning("Report class not found: $class");
            return null;
        }

        return new $class();
    }

    /**
     * Получить все отчеты категории
     */
    public static function getReportsByCategory(string $category): array
    {
        $reports = [];

        foreach (self::$reportClasses as $reportId => $class) {
            if (!class_exists($class)) {
                continue;
            }

            $report = new $class();
            if ($report->getCategory() === $category) {
                $reports[$reportId] = $report;
            }
        }

        return $reports;
    }

    /**
     * Получить все доступные категории для пользователя
     */
    public static function getAccessibleCategories(): array
    {
        $result = [];

        // SUPER имеет доступ ко всем категориям
        if (RoleChecker::isSuper()) {
            return self::CATEGORIES;
        }

        $currentRole = RoleChecker::getCurrentRole();
        if (!$currentRole) {
            return $result;
        }

        foreach (self::CATEGORIES as $categoryId => $category) {
            if (in_array($currentRole, $category['roles'])) {
                $result[$categoryId] = $category;
            }
        }

        return $result;
    }

    /**
     * Получить все доступные отчеты для пользователя
     */
    public static function getAccessibleReports(): array
    {
        $result = [];

        foreach (self::$reportClasses as $reportId => $class) {
            if (!class_exists($class)) {
                continue;
            }

            $report = new $class();
            if ($report->checkAccess()) {
                $category = $report->getCategory();
                if (!isset($result[$category])) {
                    $result[$category] = [];
                }
                $result[$category][$reportId] = $report;
            }
        }

        return $result;
    }

    /**
     * Получить информацию о категории
     */
    public static function getCategoryInfo(string $categoryId): ?array
    {
        return self::CATEGORIES[$categoryId] ?? null;
    }

    /**
     * Проверить существование отчета
     */
    public static function reportExists(string $reportId): bool
    {
        return isset(self::$reportClasses[$reportId]) && class_exists(self::$reportClasses[$reportId]);
    }

    /**
     * Зарегистрировать новый отчет
     */
    public static function registerReport(string $reportId, string $className): void
    {
        self::$reportClasses[$reportId] = $className;
    }

    /**
     * Получить все зарегистрированные отчеты
     */
    public static function getAllReports(): array
    {
        $result = [];

        foreach (self::$reportClasses as $reportId => $class) {
            if (class_exists($class)) {
                $result[$reportId] = new $class();
            }
        }

        return $result;
    }

    /**
     * Получить популярные отчеты (для главной страницы)
     */
    public static function getPopularReports(): array
    {
        $popularIds = [
            'finance-income',
            'pupils-attendance',
            'leads-funnel',
            'teachers-salary',
        ];

        $result = [];
        foreach ($popularIds as $reportId) {
            if (self::reportExists($reportId)) {
                $report = self::getReport($reportId);
                if ($report && $report->checkAccess()) {
                    $result[$reportId] = $report;
                }
            }
        }

        return $result;
    }
}
