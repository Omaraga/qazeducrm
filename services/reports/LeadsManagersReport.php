<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Lids;
use app\models\User;
use app\models\services\LidService;
use Yii;

/**
 * Отчет по эффективности менеджеров
 *
 * Показывает рейтинг менеджеров по конверсии,
 * количеству обработанных лидов и активности
 */
class LeadsManagersReport extends BaseReport
{
    public function getId(): string
    {
        return 'leads-managers';
    }

    public function getTitle(): string
    {
        return 'Рейтинг менеджеров';
    }

    public function getDescription(): string
    {
        return 'Эффективность работы с лидами';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_LEADS;
    }

    public function getIcon(): string
    {
        return 'user-group';
    }

    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
        ];
    }

    public function getAvailableFilters(): array
    {
        return ['date_range'];
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            return LidService::getManagerStats($filter->dateFrom, $filter->dateTo);
        }, 120);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $data = $this->getData($filter);

            if (empty($data)) {
                return [
                    'managers_count' => 0,
                    'total_leads' => 0,
                    'avg_conversion' => 0,
                    'best_manager' => '-',
                ];
            }

            $totalLeads = array_sum(array_column($data, 'total'));
            $totalConverted = array_sum(array_column($data, 'converted'));
            $avgConversion = $totalLeads > 0 ? round($totalConverted / $totalLeads * 100, 1) : 0;

            // Лучший менеджер по конверсии (с минимум 5 лидами)
            $bestManager = '-';
            foreach ($data as $row) {
                if ($row['total'] >= 5) {
                    $bestManager = $row['manager_name'];
                    break; // Данные уже отсортированы по конверсии
                }
            }

            return [
                'managers_count' => count($data),
                'total_leads' => $totalLeads,
                'avg_conversion' => $avgConversion,
                'best_manager' => $bestManager,
            ];
        }, 120);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'managers_count',
                'label' => 'Менеджеров',
                'icon' => 'user-group',
                'color' => 'primary',
                'format' => 'number',
            ],
            [
                'key' => 'total_leads',
                'label' => 'Всего лидов',
                'icon' => 'users',
                'color' => 'info',
                'format' => 'number',
            ],
            [
                'key' => 'avg_conversion',
                'label' => 'Средняя конверсия',
                'icon' => 'trending-up',
                'color' => 'warning',
                'format' => 'percent',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $data = $this->getData($filter);

            if (empty($data)) {
                return null;
            }

            // Топ-5 менеджеров
            $topData = array_slice($data, 0, 5);

            $labels = [];
            $converted = [];
            $active = [];
            $lost = [];

            foreach ($topData as $row) {
                $labels[] = $row['manager_name'];
                $converted[] = (int)$row['converted'];
                $active[] = (int)$row['active'];
                $lost[] = (int)$row['lost'];
            }

            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Оплатили',
                        'data' => $converted,
                        'backgroundColor' => '#22c55e',
                    ],
                    [
                        'label' => 'В работе',
                        'data' => $active,
                        'backgroundColor' => '#3b82f6',
                    ],
                    [
                        'label' => 'Потеряно',
                        'data' => $lost,
                        'backgroundColor' => '#ef4444',
                    ],
                ],
            ];
        }, 120);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'manager_name', 'label' => 'Менеджер'],
            ['field' => 'total', 'label' => 'Всего лидов', 'format' => 'number'],
            ['field' => 'converted', 'label' => 'Оплатили', 'format' => 'number'],
            ['field' => 'active', 'label' => 'В работе', 'format' => 'number'],
            ['field' => 'lost', 'label' => 'Потеряно', 'format' => 'number'],
            ['field' => 'conversion_rate', 'label' => 'Конверсия', 'format' => 'percent'],
        ];
    }
}
