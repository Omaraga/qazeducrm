<?php

namespace app\services\reports;

use app\components\reports\BaseReport;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationRoles;
use app\models\Pupil;
use Yii;

/**
 * Отчет о задолженностях учеников
 *
 * Показывает учеников с отрицательным балансом,
 * группирует по срокам задолженности
 */
class FinanceDebtsReport extends BaseReport
{
    public function getId(): string
    {
        return 'finance-debts';
    }

    public function getTitle(): string
    {
        return 'Задолженности';
    }

    public function getDescription(): string
    {
        return 'Ученики с отрицательным балансом';
    }

    public function getCategory(): string
    {
        return ReportRegistry::CATEGORY_FINANCE;
    }

    public function getIcon(): string
    {
        return 'exclamation';
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
        return []; // Без фильтра по датам - показывает текущее состояние
    }

    public function getData(ReportFilterDTO $filter): array
    {
        $cacheKey = 'data_debts';

        return $this->cached($cacheKey, function() {
            $query = Pupil::find()
                ->select([
                    'pupil.id',
                    'pupil.fio',
                    'pupil.phone',
                    'pupil.parent_phone',
                    'pupil.balance',
                    'pupil.class_id',
                ])
                ->andWhere(['pupil.status' => Pupil::STATUS_ACTIVE])
                ->andWhere(['pupil.is_deleted' => 0])
                ->andWhere(['<', 'pupil.balance', 0]);

            $this->applyOrganizationFilter($query, 'pupil.organization_id');

            return $query
                ->orderBy(['pupil.balance' => SORT_ASC]) // Самые большие долги сверху
                ->asArray()
                ->all();
        }, 60); // Кэш на 1 минуту - данные часто меняются
    }

    public function getSummary(ReportFilterDTO $filter): array
    {
        $cacheKey = 'summary_debts';

        return $this->cached($cacheKey, function() {
            // Общая задолженность
            $debtQuery = Pupil::find()
                ->andWhere(['status' => Pupil::STATUS_ACTIVE])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['<', 'balance', 0]);
            $this->applyOrganizationFilter($debtQuery);

            $totalDebt = (float)$debtQuery->sum('ABS(balance)') ?? 0;

            // Количество должников
            $debtorsQuery = Pupil::find()
                ->andWhere(['status' => Pupil::STATUS_ACTIVE])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['<', 'balance', 0]);
            $this->applyOrganizationFilter($debtorsQuery);

            $debtorsCount = (int)$debtorsQuery->count();

            // Всего активных учеников
            $pupilsQuery = Pupil::find()
                ->andWhere(['status' => Pupil::STATUS_ACTIVE])
                ->andWhere(['is_deleted' => 0]);
            $this->applyOrganizationFilter($pupilsQuery);

            $totalPupils = (int)$pupilsQuery->count();

            // Процент должников
            $debtorsPercent = $totalPupils > 0 ? ($debtorsCount / $totalPupils) * 100 : 0;

            // Средний долг
            $avgDebt = $debtorsCount > 0 ? $totalDebt / $debtorsCount : 0;

            return [
                'total_debt' => $totalDebt,
                'debtors_count' => $debtorsCount,
                'debtors_percent' => $debtorsPercent,
                'avg_debt' => $avgDebt,
            ];
        }, 60);
    }

    public function getSummaryConfig(): array
    {
        return [
            [
                'key' => 'total_debt',
                'label' => 'Общая задолженность',
                'icon' => 'exclamation',
                'color' => 'danger',
                'format' => 'currency',
            ],
            [
                'key' => 'debtors_count',
                'label' => 'Должников',
                'icon' => 'users',
                'color' => 'warning',
                'format' => 'number',
            ],
            [
                'key' => 'debtors_percent',
                'label' => '% от всех учеников',
                'icon' => 'chart-pie',
                'color' => 'gray',
                'format' => 'percent',
            ],
            [
                'key' => 'avg_debt',
                'label' => 'Средний долг',
                'icon' => 'calculator',
                'color' => 'info',
                'format' => 'currency',
            ],
        ];
    }

    public function getChartData(ReportFilterDTO $filter): ?array
    {
        $cacheKey = 'chart_debts';

        return $this->cached($cacheKey, function() {
            // Группировка по размеру долга
            $ranges = [
                ['min' => 0, 'max' => 10000, 'label' => 'До 10 000 ₸'],
                ['min' => 10000, 'max' => 30000, 'label' => '10-30 000 ₸'],
                ['min' => 30000, 'max' => 50000, 'label' => '30-50 000 ₸'],
                ['min' => 50000, 'max' => 100000, 'label' => '50-100 000 ₸'],
                ['min' => 100000, 'max' => PHP_INT_MAX, 'label' => 'Более 100 000 ₸'],
            ];

            $chartData = [];
            foreach ($ranges as $range) {
                $rangeQuery = Pupil::find()
                    ->andWhere(['status' => Pupil::STATUS_ACTIVE])
                    ->andWhere(['is_deleted' => 0])
                    ->andWhere(['<', 'balance', -$range['min']])
                    ->andWhere(['>=', 'balance', -$range['max']]);
                $this->applyOrganizationFilter($rangeQuery);

                $count = (int)$rangeQuery->count();

                if ($count > 0) {
                    $chartData[] = [
                        'label' => $range['label'],
                        'value' => $count,
                    ];
                }
            }

            if (empty($chartData)) {
                return null;
            }

            return $this->buildPieChart($chartData);
        }, 60);
    }

    public function getColumns(): array
    {
        return [
            ['field' => 'fio', 'label' => 'ФИО ученика'],
            ['field' => 'balance', 'label' => 'Задолженность', 'format' => 'currency'],
            ['field' => 'phone', 'label' => 'Телефон'],
            ['field' => 'parent_phone', 'label' => 'Телефон родителя'],
            ['field' => 'class_id', 'label' => 'Класс'],
        ];
    }

    /**
     * Переопределяем форматирование для отрицательного баланса
     */
    public function formatBalance(float $balance): string
    {
        // Показываем как положительное число с минусом
        return number_format(abs($balance), 0, '.', ' ') . ' ₸';
    }
}
