<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Payment;
use app\models\PayMethod;
use Yii;

/**
 * Отчет о доходах
 *
 * Показывает платежи (TYPE_PAY) за выбранный период
 * с группировкой по датам и методам оплаты
 */
class FinanceIncomeReport extends BaseReport
{
    public function getId(): string
    {
        return 'finance-income';
    }

    public function getTitle(): string
    {
        return 'Доходы';
    }

    public function getDescription(): string
    {
        return 'Анализ поступлений за период';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_FINANCE;
    }

    public function getIcon(): string
    {
        return 'payment';
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
        return ['date_range', 'pay_method'];
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
                    'payment.pupil_id',
                    'pupil.fio as pupil_fio',
                    'pay_method.name as method_name',
                ])
                ->leftJoin('pupil', 'pupil.id = payment.pupil_id')
                ->leftJoin('pay_method', 'pay_method.id = payment.method_id')
                ->andWhere(['payment.type' => Payment::TYPE_PAY])
                ->andWhere(['payment.is_deleted' => 0]);

            $this->applyOrganizationFilter($query, 'payment.organization_id');

            $query
                ->andFilterWhere(['>=', 'payment.date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'payment.date', $filter->dateTo]);

            if ($filter->payMethodId) {
                $query->andWhere(['payment.method_id' => $filter->payMethodId]);
            }

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
                ->andWhere(['type' => Payment::TYPE_PAY])
                ->andWhere(['is_deleted' => 0])
                ->andFilterWhere(['>=', 'date', $filter->dateFrom])
                ->andFilterWhere(['<=', 'date', $filter->dateTo]);

            $this->applyOrganizationFilter($query);

            if ($filter->payMethodId) {
                $query->andWhere(['method_id' => $filter->payMethodId]);
            }

            $total = (float)$query->sum('amount') ?? 0;
            $count = (int)$query->count();

            // Среднее за день
            $days = $filter->getDaysCount();
            $avgPerDay = $days > 0 ? $total / $days : 0;

            // Средний чек
            $avgCheck = $count > 0 ? $total / $count : 0;

            return [
                'total' => $total,
                'count' => $count,
                'avg_per_day' => $avgPerDay,
                'avg_check' => $avgCheck,
            ];
        });
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total',
                'label' => 'Всего доход',
                'icon' => 'payment',
                'color' => 'success',
                'format' => 'currency',
            ],
            [
                'key' => 'count',
                'label' => 'Платежей',
                'icon' => 'receipt',
                'color' => 'primary',
                'format' => 'number',
            ],
            [
                'key' => 'avg_per_day',
                'label' => 'В среднем за день',
                'icon' => 'calendar',
                'color' => 'gray',
                'format' => 'currency',
            ],
            [
                'key' => 'avg_check',
                'label' => 'Средний чек',
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
                ->andWhere(['type' => Payment::TYPE_PAY])
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

            return $this->buildLineChart($chartData, 'Доход', 'success');
        });
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'date', 'label' => 'Дата', 'format' => 'date'],
            ['field' => 'pupil_fio', 'label' => 'Ученик'],
            ['field' => 'amount', 'label' => 'Сумма', 'format' => 'currency'],
            ['field' => 'method_name', 'label' => 'Способ оплаты'],
            ['field' => 'number', 'label' => 'Номер'],
            ['field' => 'comment', 'label' => 'Комментарий'],
        ];
    }
}
