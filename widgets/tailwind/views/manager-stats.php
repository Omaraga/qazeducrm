<?php
/**
 * View для виджета личной статистики менеджера
 *
 * @var yii\web\View $this
 * @var array $stats
 * @var array $attentionLeads
 * @var bool $compact
 */

use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$statusLabels = Lids::getStatusLabels();
?>

<?php if ($compact): ?>
    <!-- Компактная версия для сайдбара -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Моя статистика</h3>

        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-2 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-primary-600"><?= $stats['today_contacts'] ?></div>
                <div class="text-xs text-gray-500">Сегодня</div>
            </div>
            <div class="text-center p-2 <?= $stats['overdue_contacts'] > 0 ? 'bg-danger-50' : 'bg-gray-50' ?> rounded-lg">
                <div class="text-2xl font-bold <?= $stats['overdue_contacts'] > 0 ? 'text-danger-600' : 'text-gray-400' ?>">
                    <?= $stats['overdue_contacts'] ?>
                </div>
                <div class="text-xs text-gray-500">Просрочено</div>
            </div>
        </div>

        <?php if ($stats['overdue_contacts'] > 0): ?>
            <a href="<?= OrganizationUrl::to(['lids-funnel/kanban', 'overdue_only' => 1]) ?>"
               class="mt-3 block text-center text-xs text-danger-600 hover:underline">
                Обработать просроченных →
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Полная версия для дашборда -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Моя статистика по лидам</h3>
        </div>

        <div class="p-6">
            <!-- Основные метрики -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Контакты сегодня -->
                <div class="bg-primary-50 rounded-xl p-4 text-center">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('phone', 'w-5 h-5 text-primary-600') ?>
                    </div>
                    <div class="text-3xl font-bold text-primary-600"><?= $stats['today_contacts'] ?></div>
                    <div class="text-sm text-primary-700">Контактов сегодня</div>
                </div>

                <!-- Просроченные -->
                <div class="<?= $stats['overdue_contacts'] > 0 ? 'bg-danger-50' : 'bg-gray-50' ?> rounded-xl p-4 text-center">
                    <div class="w-10 h-10 rounded-full <?= $stats['overdue_contacts'] > 0 ? 'bg-danger-100' : 'bg-gray-100' ?> flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('clock', 'w-5 h-5 ' . ($stats['overdue_contacts'] > 0 ? 'text-danger-600' : 'text-gray-400')) ?>
                    </div>
                    <div class="text-3xl font-bold <?= $stats['overdue_contacts'] > 0 ? 'text-danger-600' : 'text-gray-400' ?>">
                        <?= $stats['overdue_contacts'] ?>
                    </div>
                    <div class="text-sm <?= $stats['overdue_contacts'] > 0 ? 'text-danger-700' : 'text-gray-500' ?>">Просрочено</div>
                </div>

                <!-- Активные лиды -->
                <div class="bg-amber-50 rounded-xl p-4 text-center">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('users', 'w-5 h-5 text-amber-600') ?>
                    </div>
                    <div class="text-3xl font-bold text-amber-600"><?= $stats['active_leads'] ?></div>
                    <div class="text-sm text-amber-700">В работе</div>
                </div>

                <!-- Конверсия за месяц -->
                <div class="bg-green-50 rounded-xl p-4 text-center">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('chart-bar', 'w-5 h-5 text-green-600') ?>
                    </div>
                    <div class="text-3xl font-bold text-green-600"><?= $stats['month_conversions'] ?></div>
                    <div class="text-sm text-green-700">Оплат за месяц</div>
                    <?php if ($stats['conversion_change'] != 0): ?>
                        <div class="text-xs mt-1 <?= $stats['conversion_change'] > 0 ? 'text-green-600' : 'text-danger-600' ?>">
                            <?= $stats['conversion_change'] > 0 ? '+' : '' ?><?= $stats['conversion_change'] ?>% к прошлому
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Дополнительные метрики -->
            <div class="grid grid-cols-3 gap-4 mb-6 py-4 border-y border-gray-100">
                <div class="text-center">
                    <div class="text-xl font-semibold text-gray-900"><?= $stats['week_conversions'] ?></div>
                    <div class="text-xs text-gray-500">Оплат за неделю</div>
                </div>
                <div class="text-center border-x border-gray-100">
                    <div class="text-xl font-semibold text-gray-900"><?= $stats['month_new_leads'] ?></div>
                    <div class="text-xs text-gray-500">Новых за месяц</div>
                </div>
                <div class="text-center">
                    <div class="text-xl font-semibold text-gray-900"><?= round($stats['conversion_rate']) ?>%</div>
                    <div class="text-xs text-gray-500">Конверсия</div>
                </div>
            </div>

            <!-- Требуют внимания -->
            <?php if (!empty($attentionLeads)): ?>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <?= Icon::show('exclamation-circle', 'w-4 h-4 text-amber-500') ?>
                        Требуют внимания
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($attentionLeads as $lead): ?>
                            <a href="<?= OrganizationUrl::to(['lids-funnel/kanban']) ?>#lid-<?= $lead['id'] ?>"
                               class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-<?= $lead['priority_color'] ?>-100 flex items-center justify-center">
                                        <?= Icon::show($lead['priority_icon'], 'w-4 h-4 text-' . $lead['priority_color'] . '-600') ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= Html::encode($lead['name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= Html::encode($lead['reason']) ?></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <?= Html::encode($statusLabels[$lead['status']] ?? $lead['status']) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-gray-500">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                        <?= Icon::show('check', 'w-6 h-6 text-green-600') ?>
                    </div>
                    <div class="text-sm">Все лиды обработаны!</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
