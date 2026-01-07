<?php

use app\components\reports\ReportInterface;
use app\components\reports\ReportFilterDTO;
use app\components\reports\ReportRegistry;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\DatePresetFilter;
use app\widgets\tailwind\EmptyState;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatCard;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var ReportInterface $report */
/** @var ReportFilterDTO $filter */
/** @var array $data */
/** @var array $summary */
/** @var array|null $chartData */
/** @var array $categories */

$this->title = $report->getTitle();
$this->params['breadcrumbs'][] = ['label' => 'Отчеты', 'url' => OrganizationUrl::to(['reports/index'])];

$categoryInfo = ReportRegistry::getCategoryInfo($report->getCategory());
if ($categoryInfo) {
    $this->params['breadcrumbs'][] = [
        'label' => $categoryInfo['label'],
        'url' => OrganizationUrl::to(['reports/category', 'category' => $report->getCategory()])
    ];
}
$this->params['breadcrumbs'][] = $this->title;

// Конфигурация метрик
$summaryConfig = $report->getSummaryConfig();
?>

<div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-gray-400 mb-1">
                    <?= Icon::show($report->getIcon(), 'sm') ?>
                    <span class="text-sm"><?= Html::encode($categoryInfo['label'] ?? '') ?></span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1"><?= Html::encode($filter->getPeriodLabel()) ?></p>
            </div>

            <!-- Export Buttons -->
            <?php if ($report->supportsExport()): ?>
            <div class="flex gap-2">
                <a href="<?= OrganizationUrl::to(array_merge(['reports/export', 'type' => $report->getId(), 'format' => 'xlsx'], $filter->toArray())) ?>"
                   class="btn btn-outline btn-sm" title="Экспорт в Excel">
                    <?= Icon::show('download', 'sm') ?>
                    Excel
                </a>
                <button onclick="window.print()" class="btn btn-outline btn-sm" title="Печать">
                    <?= Icon::show('print', 'sm') ?>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <?= DatePresetFilter::widget([
                    'filter' => $filter,
                    'action' => OrganizationUrl::to(['reports/view', 'type' => $report->getId()]),
                    'showQuarter' => true,
                    'showYear' => true,
                ]) ?>
            </div>
        </div>

        <!-- Summary Cards -->
        <?php if (!empty($summary) && !empty($summaryConfig)): ?>
        <div class="grid grid-cols-2 sm:grid-cols-<?= min(count($summaryConfig), 4) ?> gap-4">
            <?php foreach ($summaryConfig as $config): ?>
            <?php
            $key = $config['key'];
            $value = $summary[$key] ?? 0;

            // Форматирование значения
            if (isset($config['format'])) {
                switch ($config['format']) {
                    case 'currency':
                        $value = number_format($value, 0, '.', ' ') . ' ₸';
                        break;
                    case 'percent':
                        $value = number_format($value, 1) . '%';
                        break;
                    case 'number':
                        $value = number_format($value, 0, '.', ' ');
                        break;
                }
            }
            ?>
            <?= StatCard::widget([
                'label' => $config['label'],
                'value' => $value,
                'icon' => $config['icon'] ?? 'chart',
                'color' => $config['color'] ?? 'primary',
            ]) ?>
            <?php endforeach; ?>
        </div>
        <?php elseif (!empty($summary)): ?>
        <!-- Автоматическая генерация карточек из summary -->
        <div class="grid grid-cols-2 sm:grid-cols-<?= min(count($summary), 4) ?> gap-4">
            <?php foreach ($summary as $key => $value): ?>
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-2xl font-bold text-gray-900">
                        <?= is_numeric($value) ? number_format($value, 0, '.', ' ') : Html::encode($value) ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-1"><?= Html::encode(ucfirst(str_replace('_', ' ', $key))) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Chart -->
        <?php if ($chartData): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Динамика</h3>
            </div>
            <div class="card-body">
                <div class="h-64">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>
        </div>
        <?php
        $chartType = $chartData['type'] ?? 'line';
        $chartLabels = json_encode($chartData['labels'] ?? []);
        $chartDatasets = json_encode($chartData['datasets'] ?? []);
        $this->registerJs("
            if (typeof Chart !== 'undefined') {
                new Chart(document.getElementById('reportChart'), {
                    type: '$chartType',
                    data: {
                        labels: $chartLabels,
                        datasets: $chartDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        ");
        ?>
        <?php endif; ?>

        <!-- Data Table -->
        <?php if (!empty($data)): ?>
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Данные</h3>
                <span class="text-sm text-gray-500"><?= count($data) ?> записей</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <?php foreach ($report->getColumns() as $column): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?= Html::encode($column['label']) ?>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($data as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <?php foreach ($report->getColumns() as $column): ?>
                            <?php
                            $field = $column['field'];
                            $value = $row[$field] ?? '';
                            $format = $column['format'] ?? null;

                            // Форматирование
                            switch ($format) {
                                case 'currency':
                                    $value = number_format((float)$value, 0, '.', ' ') . ' ₸';
                                    break;
                                case 'percent':
                                    $value = number_format((float)$value, 1) . '%';
                                    break;
                                case 'date':
                                    $value = $value ? Yii::$app->formatter->asDate($value, 'short') : '-';
                                    break;
                                case 'datetime':
                                    $value = $value ? Yii::$app->formatter->asDatetime($value, 'short') : '-';
                                    break;
                                case 'number':
                                    $value = number_format((float)$value, 0, '.', ' ');
                                    break;
                            }
                            ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= Html::encode($value) ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <?= EmptyState::card(
            'chart',
            'Нет данных за выбранный период',
            'Попробуйте изменить параметры фильтра или выбрать другой период'
        ) ?>
        <?php endif; ?>
</div>
