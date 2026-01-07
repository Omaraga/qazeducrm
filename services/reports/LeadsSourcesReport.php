<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Lids;
use Yii;

/**
 * Отчет по источникам лидов
 *
 * Показывает эффективность различных каналов привлечения:
 * Instagram, WhatsApp, 2GIS, сайт, рекомендации и т.д.
 */
class LeadsSourcesReport extends BaseReport
{
    public function getId(): string
    {
        return 'leads-sources';
    }

    public function getTitle(): string
    {
        return 'Источники лидов';
    }

    public function getDescription(): string
    {
        return 'Эффективность каналов привлечения';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_LEADS;
    }

    public function getIcon(): string
    {
        return 'link';
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
        return ['date_range'];
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $query = Lids::find()
                ->select([
                    'source',
                    'COUNT(*) as total',
                    'SUM(CASE WHEN status = ' . Lids::STATUS_PAID . ' THEN 1 ELSE 0 END) as converted',
                    'SUM(CASE WHEN status = ' . Lids::STATUS_LOST . ' THEN 1 ELSE 0 END) as lost',
                    'SUM(CASE WHEN status NOT IN (' . Lids::STATUS_PAID . ', ' . Lids::STATUS_LOST . ') THEN 1 ELSE 0 END) as active',
                ])
                ->byOrganization()
                ->notDeleted()
                ->andWhere(['is not', 'source', null])
                ->groupBy('source')
                ->orderBy(['total' => SORT_DESC]);

            if ($filter->dateFrom) {
                $query->andWhere(['>=', 'created_at', $filter->dateFrom . ' 00:00:00']);
            }
            if ($filter->dateTo) {
                $query->andWhere(['<=', 'created_at', $filter->dateTo . ' 23:59:59']);
            }

            $results = $query->asArray()->all();
            $sourceLabels = Lids::getSourceList();

            foreach ($results as &$row) {
                $row['source_label'] = $sourceLabels[$row['source']] ?? $row['source'];
                $row['conversion_rate'] = $row['total'] > 0
                    ? round($row['converted'] / $row['total'] * 100, 1)
                    : 0;
                $row['loss_rate'] = $row['total'] > 0
                    ? round($row['lost'] / $row['total'] * 100, 1)
                    : 0;
            }

            return $results;
        }, 120);
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $query = Lids::find()
                ->byOrganization()
                ->notDeleted()
                ->andWhere(['is not', 'source', null]);

            if ($filter->dateFrom) {
                $query->andWhere(['>=', 'created_at', $filter->dateFrom . ' 00:00:00']);
            }
            if ($filter->dateTo) {
                $query->andWhere(['<=', 'created_at', $filter->dateTo . ' 23:59:59']);
            }

            $total = (clone $query)->count();
            $converted = (clone $query)->andWhere(['status' => Lids::STATUS_PAID])->count();

            // Лучший источник по конверсии
            $bestSource = (clone $query)
                ->select([
                    'source',
                    'COUNT(*) as cnt',
                    'SUM(CASE WHEN status = ' . Lids::STATUS_PAID . ' THEN 1 ELSE 0 END) as conv',
                ])
                ->groupBy('source')
                ->having(['>', 'COUNT(*)', 5]) // Минимум 5 лидов для статистики
                ->orderBy([new \yii\db\Expression('SUM(CASE WHEN status = ' . Lids::STATUS_PAID . ' THEN 1 ELSE 0 END) / COUNT(*) DESC')])
                ->asArray()
                ->one();

            $sourceLabels = Lids::getSourceList();
            $bestSourceLabel = $bestSource ? ($sourceLabels[$bestSource['source']] ?? $bestSource['source']) : '-';

            // Количество уникальных источников
            $sourcesCount = (clone $query)
                ->select('source')
                ->distinct()
                ->count();

            return [
                'total' => (int)$total,
                'converted' => (int)$converted,
                'sources_count' => (int)$sourcesCount,
                'best_source' => $bestSourceLabel,
            ];
        }, 120);
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
                'label' => 'Конверсии',
                'icon' => 'check-circle',
                'color' => 'success',
                'format' => 'number',
            ],
            [
                'key' => 'sources_count',
                'label' => 'Источников',
                'icon' => 'link',
                'color' => 'info',
                'format' => 'number',
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

            // Берём топ-6 источников для читаемости графика
            $topData = array_slice($data, 0, 6);

            $chartData = [];
            $colors = [
                Lids::SOURCE_INSTAGRAM => '#E4405F',
                Lids::SOURCE_WHATSAPP => '#25D366',
                Lids::SOURCE_2GIS => '#6DB33F',
                Lids::SOURCE_WEBSITE => '#3B82F6',
                Lids::SOURCE_REFERRAL => '#8B5CF6',
                Lids::SOURCE_WALK_IN => '#F59E0B',
                Lids::SOURCE_PHONE => '#6B7280',
                Lids::SOURCE_OTHER => '#9CA3AF',
            ];

            foreach ($topData as $row) {
                $chartData[] = [
                    'label' => $row['source_label'],
                    'value' => $row['total'],
                    'color' => $colors[$row['source']] ?? '#6B7280',
                ];
            }

            return $this->buildPieChart($chartData);
        }, 120);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'source_label', 'label' => 'Источник'],
            ['field' => 'total', 'label' => 'Всего лидов', 'format' => 'number'],
            ['field' => 'converted', 'label' => 'Оплатили', 'format' => 'number'],
            ['field' => 'active', 'label' => 'В работе', 'format' => 'number'],
            ['field' => 'lost', 'label' => 'Потеряно', 'format' => 'number'],
            ['field' => 'conversion_rate', 'label' => 'Конверсия', 'format' => 'percent'],
        ];
    }
}
