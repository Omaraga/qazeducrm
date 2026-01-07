<?php

use app\components\reports\ReportRegistry;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatCard;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $categories */
/** @var array $popularReports */
/** @var array $reportsByCategory */

$this->title = 'Отчеты';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Аналитика и статистика вашей организации</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= OrganizationUrl::to(['reports/day']) ?>" class="btn btn-outline">
                    <?= Icon::show('calendar', 'sm') ?>
                    Дневной отчет
                </a>
                <a href="<?= OrganizationUrl::to(['reports/month']) ?>" class="btn btn-outline">
                    <?= Icon::show('chart', 'sm') ?>
                    Месячный отчет
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php if (!empty($popularReports)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            // Показываем карточки для популярных отчетов
            $icons = ['payment' => 'primary', 'check' => 'success', 'funnel' => 'warning', 'wallet' => 'info'];
            foreach ($popularReports as $reportId => $report):
                $color = $icons[$report->getIcon()] ?? 'primary';
            ?>
            <a href="<?= OrganizationUrl::to(['reports/view', 'type' => $reportId]) ?>"
               class="card hover:shadow-lg transition-shadow cursor-pointer">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-<?= $color ?>-100 text-<?= $color ?>-600">
                            <?= Icon::show($report->getIcon(), 'md') ?>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900"><?= Html::encode($report->getTitle()) ?></div>
                            <div class="text-xs text-gray-500"><?= Html::encode($report->getDescription()) ?></div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Categories -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Категории отчетов</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($categories as $categoryId => $category): ?>
                <a href="<?= OrganizationUrl::to(['reports/category', 'category' => $categoryId]) ?>"
                   class="card hover:shadow-lg transition-shadow group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-lg bg-primary-100 text-primary-600 group-hover:bg-primary-200 transition-colors">
                                <?= Icon::show($category['icon'], 'lg') ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                                    <?= Html::encode($category['label']) ?>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1"><?= Html::encode($category['description']) ?></p>

                                <?php
                                // Количество отчетов в категории
                                $reportsCount = isset($reportsByCategory[$categoryId]) ? count($reportsByCategory[$categoryId]) : 0;
                                ?>
                                <div class="text-xs text-gray-400 mt-2">
                                    <?= $reportsCount ?> <?= Yii::t('main', '{n, plural, =0{отчетов} =1{отчет} one{отчет} few{отчета} many{отчетов} other{отчетов}}', ['n' => $reportsCount]) ?>
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
        </div>

        <!-- All Reports by Category -->
        <?php foreach ($reportsByCategory as $categoryId => $reports): ?>
        <?php if (empty($reports)) continue; ?>
        <?php $categoryInfo = ReportRegistry::getCategoryInfo($categoryId); ?>
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-gray-400"><?= Icon::show($categoryInfo['icon'] ?? 'folder', 'sm') ?></span>
                <h3 class="text-base font-semibold text-gray-700"><?= Html::encode($categoryInfo['label'] ?? $categoryId) ?></h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($reports as $reportId => $report): ?>
                <a href="<?= OrganizationUrl::to(['reports/view', 'type' => $reportId]) ?>"
                   class="card card-hover">
                    <div class="card-body py-3">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400"><?= Icon::show($report->getIcon(), 'sm') ?></span>
                            <span class="text-sm font-medium text-gray-700 hover:text-primary-600">
                                <?= Html::encode($report->getTitle()) ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Empty State if no reports -->
        <?php if (empty($reportsByCategory)): ?>
        <div class="card">
            <div class="card-body text-center py-12">
                <div class="text-gray-400 mb-4">
                    <?= Icon::show('chart', 'xl') ?>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Нет доступных отчетов</h3>
                <p class="text-gray-500 mt-1">Отчеты будут доступны после настройки системы</p>
            </div>
        </div>
        <?php endif; ?>
</div>
