<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Lids;
use app\models\services\LidService;

/**
 * Отчет о воронке продаж
 *
 * Показывает статистику лидов по этапам воронки,
 * конверсию между этапами и общую эффективность
 */
class LeadsFunnelReport extends BaseReport
{
    public function getId(): string
    {
        return 'leads-funnel';
    }

    public function getTitle(): string
    {
        return 'Воронка продаж';
    }

    public function getDescription(): string
    {
        return 'Конверсия лидов по этапам';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_LEADS;
    }

    public function getIcon(): string
    {
        return 'funnel';
    }

    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
            OrganizationRoles::ADMIN,
        ];
    }

    public function getAvailableFilters(): array
    {
        return ['date_range', 'manager'];
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            // Получаем аналитику воронки
            $analytics = LidService::getFunnelAnalytics(
                $filter->dateFrom,
                $filter->dateTo,
                $filter->managerId ?? null
            );

            $data = [];
            $statuses = Lids::getStatusList();
            // Цвета статусов
            $colors = [
                Lids::STATUS_NEW => 'sky',
                Lids::STATUS_CONTACTED => 'blue',
                Lids::STATUS_TRIAL => 'amber',
                Lids::STATUS_THINKING => 'gray',
                Lids::STATUS_ENROLLED => 'indigo',
                Lids::STATUS_PAID => 'green',
                Lids::STATUS_LOST => 'red',
            ];

            // Формируем данные по каждому статусу
            foreach ($analytics['funnel'] as $status => $stats) {
                $data[] = [
                    'status' => $status,
                    'status_label' => $statuses[$status] ?? $stats['label'],
                    'count' => $stats['count'],
                    'total_conversion' => $stats['total_conversion'],
                    'step_conversion' => $stats['step_conversion'],
                    'color' => $colors[$status] ?? 'gray',
                ];
            }

            return $data;
        }, 60);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $analytics = LidService::getFunnelAnalytics(
                $filter->dateFrom,
                $filter->dateTo,
                $filter->managerId ?? null
            );

            // Средняя длительность воронки (от создания до оплаты)
            $avgDays = $this->getAverageFunnelDuration($filter);

            return [
                'total' => $analytics['total'],
                'converted' => $analytics['converted'],
                'lost' => $analytics['lost'],
                'conversion_rate' => $analytics['conversion_rate'],
                'avg_days' => $avgDays,
            ];
        }, 60);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total',
                'label' => 'Всего лидов',
                'icon' => 'users',
                'color' => 'primary',
                'format' => 'number',
            ],
            [
                'key' => 'converted',
                'label' => 'Оплатили',
                'icon' => 'check-circle',
                'color' => 'success',
                'format' => 'number',
            ],
            [
                'key' => 'conversion_rate',
                'label' => 'Конверсия',
                'icon' => 'trending-up',
                'color' => 'info',
                'format' => 'percent',
            ],
            [
                'key' => 'lost',
                'label' => 'Потеряно',
                'icon' => 'x-circle',
                'color' => 'danger',
                'format' => 'number',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $analytics = LidService::getFunnelAnalytics(
                $filter->dateFrom,
                $filter->dateTo,
                $filter->managerId ?? null
            );

            $chartData = [];
            $colors = [
                Lids::STATUS_NEW => '#0ea5e9',       // sky-500
                Lids::STATUS_CONTACTED => '#3b82f6', // blue-500
                Lids::STATUS_TRIAL => '#f59e0b',     // amber-500
                Lids::STATUS_THINKING => '#6b7280', // gray-500
                Lids::STATUS_ENROLLED => '#6366f1', // indigo-500
                Lids::STATUS_PAID => '#22c55e',     // green-500
                Lids::STATUS_LOST => '#ef4444',     // red-500
            ];

            foreach ($analytics['funnel'] as $status => $stats) {
                if ($status == Lids::STATUS_LOST) {
                    continue; // Потерянных не показываем в воронке
                }

                $chartData[] = [
                    'label' => $stats['label'],
                    'value' => $stats['count'],
                    'color' => $colors[$status] ?? '#6b7280',
                ];
            }

            if (empty($chartData)) {
                return null;
            }

            // Для воронки используем bar chart
            return [
                'type' => 'bar',
                'labels' => array_column($chartData, 'label'),
                'datasets' => [
                    [
                        'label' => 'Количество лидов',
                        'data' => array_column($chartData, 'value'),
                        'backgroundColor' => array_column($chartData, 'color'),
                        'borderColor' => array_column($chartData, 'color'),
                        'borderWidth' => 1,
                    ],
                ],
            ];
        }, 60);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'status_label', 'label' => 'Этап'],
            ['field' => 'count', 'label' => 'Количество', 'format' => 'number'],
            ['field' => 'step_conversion', 'label' => 'Конверсия этапа', 'format' => 'percent'],
            ['field' => 'total_conversion', 'label' => 'От общего', 'format' => 'percent'],
        ];
    }

    /**
     * Средняя длительность прохождения воронки (в днях)
     */
    private function getAverageFunnelDuration(ReportFilterDTO $filter): float
    {
        $query = Lids::find()
            ->select(['AVG(DATEDIFF(status_changed_at, created_at)) as avg_days'])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['status' => Lids::STATUS_PAID])
            ->andFilterWhere(['>=', 'created_at', $filter->dateFrom ? $filter->dateFrom . ' 00:00:00' : null])
            ->andFilterWhere(['<=', 'created_at', $filter->dateTo ? $filter->dateTo . ' 23:59:59' : null]);

        if ($filter->managerId) {
            $query->andWhere(['manager_id' => $filter->managerId]);
        }

        $result = $query->asArray()->one();

        return round((float)($result['avg_days'] ?? 0), 1);
    }
}
