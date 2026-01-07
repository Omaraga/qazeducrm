<?php

use app\components\reports\ReportRegistry;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $category */
/** @var array $categoryInfo */
/** @var array $reports */
/** @var array $categories */

$this->title = $categoryInfo['label'];
$this->params['breadcrumbs'][] = ['label' => 'Отчеты', 'url' => OrganizationUrl::to(['reports/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-gray-400 mb-1">
                    <a href="<?= OrganizationUrl::to(['reports/index']) ?>" class="hover:text-primary-600">
                        <?= Icon::show('arrow-left', 'sm') ?>
                        Все отчеты
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-100 text-primary-600">
                        <?= Icon::show($categoryInfo['icon'], 'lg') ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                        <p class="text-gray-500"><?= Html::encode($categoryInfo['description']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Grid -->
        <?php if (!empty($reports)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($reports as $reportId => $report): ?>
            <a href="<?= OrganizationUrl::to(['reports/view', 'type' => $reportId]) ?>"
               class="card hover:shadow-lg transition-shadow group">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="p-2 rounded-lg bg-gray-100 text-gray-600 group-hover:bg-primary-100 group-hover:text-primary-600 transition-colors">
                            <?= Icon::show($report->getIcon(), 'md') ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                <?= Html::encode($report->getTitle()) ?>
                            </h3>
                            <?php if ($report->getDescription()): ?>
                            <p class="text-sm text-gray-500 mt-1"><?= Html::encode($report->getDescription()) ?></p>
                            <?php endif; ?>

                            <!-- Features -->
                            <div class="flex flex-wrap gap-2 mt-3">
                                <?php if ($report->supportsExport()): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-success-100 text-success-700">
                                    <?= Icon::show('download', 'xs') ?>
                                    Excel
                                </span>
                                <?php endif; ?>
                                <?php if ($report->getChartData(new \app\components\reports\ReportFilterDTO())): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">
                                    <?= Icon::show('chart', 'xs') ?>
                                    График
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-gray-400 group-hover:text-primary-600 transition-colors">
                            <?= Icon::show('chevron-right', 'sm') ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-12">
                <div class="text-gray-400 mb-4">
                    <?= Icon::show('clock', 'xl') ?>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Отчеты скоро появятся</h3>
                <p class="text-gray-500 mt-1">Мы работаем над добавлением отчетов в эту категорию</p>
                <a href="<?= OrganizationUrl::to(['reports/index']) ?>" class="btn btn-primary mt-4">
                    <?= Icon::show('arrow-left', 'sm') ?>
                    Вернуться к списку
                </a>
            </div>
        </div>
        <?php endif; ?>
</div>
