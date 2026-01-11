<?php

/**
 * Расширенная аналитика (KPI Dashboard)
 *
 * @var yii\web\View $this
 * @var array $metrics
 * @var array $managers
 * @var array $pupils
 */

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Аналитика');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Отчеты'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Подсказки для метрик
$tooltips = [
    'ltv' => Yii::t('app', 'LTV (Lifetime Value) — средняя сумма всех платежей от одного ученика за всё время обучения. Чем выше LTV, тем дольше и больше платят ваши клиенты.'),
    'conversion' => Yii::t('app', 'Конверсия — процент лидов, которые стали учениками. Показывает эффективность работы с заявками. Хороший показатель: 20-40%.'),
    'attendance' => Yii::t('app', 'Посещаемость — процент фактических посещений занятий за текущий месяц. Норма: 80%+. Низкая посещаемость может говорить о проблемах с мотивацией учеников.'),
    'churn' => Yii::t('app', 'Отток — процент учеников, не посещавших занятия более 30 дней. Показатель удержания клиентов. Норма: менее 10% в месяц.'),
    'debt' => Yii::t('app', 'Общая сумма задолженностей учеников с отрицательным балансом. Высокий долг может сигнализировать о проблемах с оплатой.'),
    'revenue' => Yii::t('app', 'Сумма всех поступлений за месяц. Сравнивайте с прошлым месяцем для оценки динамики.'),
    'new_pupils' => Yii::t('app', 'Количество учеников, добавленных в этом месяце. Показывает рост клиентской базы.'),
    'new_leads' => Yii::t('app', 'Количество новых заявок за месяц. Показывает эффективность маркетинга и рекламы.'),
];

// Подготовка данных для графиков
$leadConversion = $metrics['lead_conversion'];
$monthlyComparison = $metrics['monthly_comparison'];
$attendanceByGroup = $metrics['attendance_by_group'] ?? [];
$conversionBySource = $metrics['lead_conversion_by_source'] ?? [];

// JSON для графиков
$sourceLabels = json_encode(array_column($conversionBySource, 'source_label'));
$sourceValues = json_encode(array_column($conversionBySource, 'converted'));
$sourceTotals = json_encode(array_column($conversionBySource, 'total'));

$groupLabels = json_encode(array_slice(array_column($attendanceByGroup, 'group_name'), 0, 10));
$groupRates = json_encode(array_slice(array_column($attendanceByGroup, 'rate'), 0, 10));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Yii::t('app', 'KPI и ключевые метрики организации') ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['reports/index']) ?>" class="btn btn-outline">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <?= Yii::t('app', 'Все отчеты') ?>
            </a>
        </div>
    </div>

    <!-- Сравнение с прошлым месяцем -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Доход -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Доход за месяц') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="revenue" title="<?= Html::encode($tooltips['revenue']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <?php
                $revenueChange = $monthlyComparison['revenue']['change'];
                $changeClass = $revenueChange >= 0 ? 'text-green-600' : 'text-red-600';
                $changeIcon = $revenueChange >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3';
                ?>
                <span class="flex items-center <?= $changeClass ?> text-xs">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $changeIcon ?>"/>
                    </svg>
                    <?= $revenueChange >= 0 ? '+' : '' ?><?= $revenueChange ?>%
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-900">
                <?= number_format($monthlyComparison['revenue']['this_month'], 0, '', ' ') ?>
                <span class="text-sm font-normal text-gray-500">&#8376;</span>
            </div>
            <div class="text-xs text-gray-400 mt-1">
                <?= Yii::t('app', 'Прошлый месяц') ?>: <?= number_format($monthlyComparison['revenue']['last_month'], 0, '', ' ') ?> &#8376;
            </div>
        </div>

        <!-- Новые ученики -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Новые ученики') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="new_pupils" title="<?= Html::encode($tooltips['new_pupils']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <?php
                $pupilsChange = $monthlyComparison['pupils']['change'];
                $changeClass = $pupilsChange >= 0 ? 'text-green-600' : 'text-red-600';
                ?>
                <span class="flex items-center <?= $changeClass ?> text-xs">
                    <?= $pupilsChange >= 0 ? '+' : '' ?><?= $pupilsChange ?>%
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $monthlyComparison['pupils']['this_month'] ?></div>
            <div class="text-xs text-gray-400 mt-1">
                <?= Yii::t('app', 'Прошлый месяц') ?>: <?= $monthlyComparison['pupils']['last_month'] ?>
            </div>
        </div>

        <!-- Новые лиды -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Новые лиды') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="new_leads" title="<?= Html::encode($tooltips['new_leads']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <?php
                $lidsChange = $monthlyComparison['lids']['change'];
                $changeClass = $lidsChange >= 0 ? 'text-green-600' : 'text-red-600';
                ?>
                <span class="flex items-center <?= $changeClass ?> text-xs">
                    <?= $lidsChange >= 0 ? '+' : '' ?><?= $lidsChange ?>%
                </span>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $monthlyComparison['lids']['this_month'] ?></div>
            <div class="text-xs text-gray-400 mt-1">
                <?= Yii::t('app', 'Прошлый месяц') ?>: <?= $monthlyComparison['lids']['last_month'] ?>
            </div>
        </div>
    </div>

    <!-- Ключевые метрики -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- LTV -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Средний LTV') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="ltv" title="<?= Html::encode($tooltips['ltv']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900">
                <?= number_format($metrics['ltv']['average'], 0, '', ' ') ?>
                <span class="text-sm font-normal text-gray-500">&#8376;</span>
            </div>
        </div>

        <!-- Конверсия лидов -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Конверсия') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="conversion" title="<?= Html::encode($tooltips['conversion']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $leadConversion['rate'] ?>%</div>
            <div class="text-xs text-gray-400 mt-1"><?= $leadConversion['converted'] ?> / <?= $leadConversion['total'] ?></div>
        </div>

        <!-- Посещаемость -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Посещаемость') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="attendance" title="<?= Html::encode($tooltips['attendance']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <?php
            $attendanceRate = $metrics['attendance_rate']['rate'];
            $attendanceColor = $attendanceRate >= 80 ? 'text-green-600' : ($attendanceRate >= 60 ? 'text-yellow-600' : 'text-red-600');
            ?>
            <div class="text-2xl font-semibold <?= $attendanceColor ?>"><?= $attendanceRate ?>%</div>
            <div class="text-xs text-gray-400 mt-1"><?= Yii::t('app', 'за текущий месяц') ?></div>
        </div>

        <!-- Отток -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Отток') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="churn" title="<?= Html::encode($tooltips['churn']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <?php
            $churnRate = $metrics['churn_rate']['rate'];
            $churnColor = $churnRate <= 10 ? 'text-green-600' : ($churnRate <= 20 ? 'text-yellow-600' : 'text-red-600');
            ?>
            <div class="text-2xl font-semibold <?= $churnColor ?>"><?= $churnRate ?>%</div>
            <div class="text-xs text-gray-400 mt-1"><?= $metrics['churn_rate']['churned'] ?> / <?= $metrics['churn_rate']['total_active'] ?></div>
        </div>

        <!-- Задолженность -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <?= Yii::t('app', 'Долги') ?>
                    <button type="button" class="tooltip-trigger ml-1" data-tooltip="debt" title="<?= Html::encode($tooltips['debt']) ?>">
                        <svg class="w-3.5 h-3.5 text-gray-400 hover:text-blue-500 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </span>
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-orange-600">
                <?= number_format($metrics['total_debt']['total'], 0, '', ' ') ?>
                <span class="text-sm font-normal text-gray-500">&#8376;</span>
            </div>
            <div class="text-xs text-gray-400 mt-1"><?= $metrics['total_debt']['count'] ?> <?= Yii::t('app', 'должников') ?></div>
        </div>
    </div>

    <!-- Графики и детали -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Конверсия по источникам -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Конверсия по источникам') ?></h2>
            </div>
            <div class="p-5">
                <div class="h-64">
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Посещаемость по группам -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Посещаемость по группам') ?></h2>
            </div>
            <div class="p-5">
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Детальные таблицы -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Топ учеников по LTV -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Топ-10 учеников по LTV') ?></h2>
            </div>
            <?php if (empty($metrics['ltv']['top_pupils'])): ?>
                <div class="px-5 py-12 text-center text-gray-500"><?= Yii::t('app', 'Нет данных') ?></div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($metrics['ltv']['top_pupils'] as $i => $pupil): ?>
                        <?php
                        $pupilInfo = $pupils[$pupil['pupil_id']] ?? null;
                        $pupilName = $pupilInfo ? ($pupilInfo['fio'] ?: $pupilInfo['first_name'] . ' ' . $pupilInfo['last_name']) : '—';
                        ?>
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-medium">
                                    <?= $i + 1 ?>
                                </span>
                                <a href="<?= OrganizationUrl::to(['/crm/pupil/view', 'id' => $pupil['pupil_id']]) ?>"
                                   class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                    <?= Html::encode($pupilName) ?>
                                </a>
                            </div>
                            <div class="text-sm font-medium text-green-600">
                                <?= number_format($pupil['total_paid'], 0, '', ' ') ?> &#8376;
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Конверсия по менеджерам -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Конверсия по менеджерам') ?></h2>
            </div>
            <?php if (empty($metrics['lead_conversion_by_manager'])): ?>
                <div class="px-5 py-12 text-center text-gray-500"><?= Yii::t('app', 'Нет данных') ?></div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($metrics['lead_conversion_by_manager'] as $manager): ?>
                        <?php
                        $managerInfo = $managers[$manager['manager_id']] ?? null;
                        $managerName = $managerInfo ? ($managerInfo['first_name'] . ' ' . $managerInfo['last_name']) : '—';
                        ?>
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-medium uppercase">
                                    <?= mb_substr($managerInfo['first_name'] ?? '?', 0, 1) ?>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?= Html::encode($managerName) ?></div>
                                    <div class="text-xs text-gray-500"><?= $manager['converted'] ?> / <?= $manager['total'] ?> <?= Yii::t('app', 'лидов') ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php
                                $rate = $manager['rate'];
                                $rateColor = $rate >= 30 ? 'bg-green-100 text-green-700' : ($rate >= 15 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $rateColor ?>">
                                    <?= $rate ?>%
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Загруженность кабинетов и Доход по группам -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Загруженность кабинетов -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Загруженность кабинетов') ?></h2>
            </div>
            <?php if (empty($metrics['room_utilization'])): ?>
                <div class="px-5 py-12 text-center text-gray-500"><?= Yii::t('app', 'Нет данных') ?></div>
            <?php else: ?>
                <div class="p-5 space-y-4">
                    <?php foreach ($metrics['room_utilization'] as $room): ?>
                        <?php
                        $util = $room['utilization'];
                        $barColor = $util >= 70 ? 'bg-green-500' : ($util >= 40 ? 'bg-yellow-500' : 'bg-red-500');
                        ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700"><?= Html::encode($room['room_name']) ?></span>
                                <span class="text-sm text-gray-500"><?= $room['hours_used'] ?>h / <?= $util ?>%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="<?= $barColor ?> h-2 rounded-full transition-all" style="width: <?= $util ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Доход по группам -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Топ групп по доходу') ?></h2>
            </div>
            <?php if (empty($metrics['revenue_by_group'])): ?>
                <div class="px-5 py-12 text-center text-gray-500"><?= Yii::t('app', 'Нет данных') ?></div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach (array_slice($metrics['revenue_by_group'], 0, 8) as $group): ?>
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                            <div>
                                <a href="<?= OrganizationUrl::to(['/crm/group/view', 'id' => $group['group_id']]) ?>"
                                   class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                    <?= Html::encode($group['group_name']) ?>
                                </a>
                                <div class="text-xs text-gray-500"><?= $group['students'] ?> <?= Yii::t('app', 'учеников') ?></div>
                            </div>
                            <div class="text-sm font-medium text-green-600">
                                <?= number_format($group['revenue'], 0, '', ' ') ?> &#8376;
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Должники -->
    <?php if (!empty($metrics['total_debt']['debtors'])): ?>
    <div class="bg-white rounded-xl border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Должники (топ-10)') ?></h2>
            <span class="text-xs text-gray-500"><?= Yii::t('app', 'Общий долг') ?>: <?= number_format($metrics['total_debt']['total'], 0, '', ' ') ?> &#8376;</span>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($metrics['total_debt']['debtors'] as $debtor): ?>
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                    <a href="<?= OrganizationUrl::to(['/crm/pupil/view', 'id' => $debtor['pupil_id']]) ?>"
                       class="text-sm font-medium text-gray-900 hover:text-blue-600">
                        <?= Html::encode($debtor['pupil_name']) ?>
                    </a>
                    <span class="text-sm font-medium text-red-600">
                        <?= number_format($debtor['balance'], 0, '', ' ') ?> &#8376;
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // График конверсии по источникам
    const sourceCtx = document.getElementById('sourceChart');
    if (sourceCtx) {
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: <?= $sourceLabels ?>,
                datasets: [
                    {
                        label: '<?= Yii::t('app', 'Конвертировано') ?>',
                        data: <?= $sourceValues ?>,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderRadius: 4,
                    },
                    {
                        label: '<?= Yii::t('app', 'Всего') ?>',
                        data: <?= $sourceTotals ?>,
                        backgroundColor: 'rgba(209, 213, 219, 0.8)',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 15 }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F3F4F6', drawBorder: false }
                    }
                }
            }
        });
    }

    // График посещаемости по группам
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: <?= $groupLabels ?>,
                datasets: [{
                    data: <?= $groupRates ?>,
                    backgroundColor: function(context) {
                        const value = context.dataset.data[context.dataIndex];
                        if (value >= 80) return 'rgba(34, 197, 94, 0.8)';
                        if (value >= 60) return 'rgba(234, 179, 8, 0.8)';
                        return 'rgba(239, 68, 68, 0.8)';
                    },
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#F3F4F6', drawBorder: false },
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }
});

// Tooltip modal functionality
document.querySelectorAll('.tooltip-trigger').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const tooltip = this.getAttribute('title');
        if (!tooltip) return;

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black/30" onclick="this.parentElement.remove()"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-5 animate-fade-in">
                <button onclick="this.closest('.fixed').remove()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1"><?= Yii::t('app', 'Справка') ?></h3>
                        <p class="text-sm text-gray-600 leading-relaxed">${tooltip}</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Close on Escape
        const closeOnEsc = function(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', closeOnEsc);
            }
        };
        document.addEventListener('keydown', closeOnEsc);
    });
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
    animation: fade-in 0.15s ease-out;
}
</style>
