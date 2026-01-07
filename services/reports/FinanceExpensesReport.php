<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Payment;
use Yii;

/**
 * Отчет о расходах
 *
 * Показывает платежи (TYPE_SPENDING) за выбранный период
 * с группировкой по датам и категориям
 */
class FinanceExpensesReport extends BaseReport
{
    public function getId(): string
    {
        return 'finance-expenses';
    }

    public function getTitle(): string
    {
        return 'Расходы';
    }

    public function getDescription(): string
    {
        return 'Анализ расходов за период';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_FINANCE;
    }

    public function getIcon(): string
    {
        return 'wallet';
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
            $query = Payment::find()
                ->select([
                    'payment.id',
                    'payment.date',
                    'payment.amount',
                    'payment.number',
                    'payment.comment',
                    'payment.method_id',
                    'pay_method.name as method_name',
                ])
                ->leftJoin('pay_method', 'pay_method.id = payment.method_id')
                ->andWhere(['payment.type' => Payment::TYPE_SPENDING])
                ->andWhere(['payment.is_deleted' => 0])
                ->andFilterWhere(['>=', 'payment.date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'payment.date', $filter->dateTo]);

            $this->applyOrganizationFilter($query, 'payment.organization_id');

            return $query
                ->orderBy(['payment.date' => SORT_DESC, 'payment.id' => SORT_DESC])
                ->asArray()
                ->all();
        });
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $query = Payment::find()
                ->andWhere(['type' => Payment::TYPE_SPENDING])
                ->andWhere(['is_deleted' => 0])
                ->andFilterWhere(['>=', 'date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'date', $filter->dateTo]);

            $this->applyOrganizationFilter($query);

            $total = (float)$query->sum('amount') ?? 0;
            $count = (int)$query->count();

            // Среднее за день
            $days = $filter->getDaysCount();
            $avgPerDay = $days > 0 ? $total / $days : 0;

            // Средний расход
            $avgExpense = $count > 0 ? $total / $count : 0;

            return [
                'total' => $total,
                'count' => $count,
                'avg_per_day' => $avgPerDay,
                'avg_expense' => $avgExpense,
            ];
        });
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total',
                'label' => 'Всего расходов',
                'icon' => 'wallet',
                'color' => 'danger',
                'format' => 'currency',
            ],
            [
                'key' => 'count',
                'label' => 'Операций',
                'icon' => 'receipt',
                'color' => 'gray',
                'format' => 'number',
            ],
            [
                'key' => 'avg_per_day',
                'label' => 'В среднем за день',
                'icon' => 'calendar',
                'color' => 'warning',
                'format' => 'currency',
            ],
            [
                'key' => 'avg_expense',
                'label' => 'Средний расход',
                'icon' => 'chart',
                'color' => 'info',
                'format' => 'currency',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_' . $this->getFilterHash($filter);

        return $this->cached($cacheKey, function() use ($filter) {
            $query = Payment::find()
                ->select([
                    'DATE(date) as date',
                    'SUM(amount) as total',
                ])
                ->andWhere(['type' => Payment::TYPE_SPENDING])
                ->andWhere(['is_deleted' => 0])
                ->andFilterWhere(['>=', 'date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'date', $filter->dateTo]);

            $this->applyOrganizationFilter($query);

            $data = $query
                ->groupBy(['DATE(date)'])
                ->orderBy(['date' => SORT_ASC])
                ->asArray()
                ->all();

            if (empty($data)) {
                return null;
            }

            // Форматируем даты
            $chartData = [];
            foreach ($data as $row) {
                $chartData[] = [
                    'date' => Yii::$app->formatter->asDate($row['date'], 'short'),
                    'value' => (float)$row['total'],
                ];
            }

            return $this->buildLineChart($chartData, 'Расходы', 'danger');
        });
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'date', 'label' => 'Дата', 'format' => 'date'],
            ['field' => 'comment', 'label' => 'Описание'],
            ['field' => 'amount', 'label' => 'Сумма', 'format' => 'currency'],
            ['field' => 'method_name', 'label' => 'Способ оплаты'],
            ['field' => 'number', 'label' => 'Номер'],
        ];
    }
}
