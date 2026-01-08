<?php

/**
 * Dashboard - главная страница CRM
 * Отображает разные данные в зависимости от роли пользователя
 *
 * @var yii\web\View $this
 * @var array $stats
 */

use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;

$this->title = 'Dashboard';

// Определяем тип дашборда
$isTeacherDashboard = $stats['is_teacher_dashboard'] ?? false;
$isAdminDashboard = $stats['is_admin_dashboard'] ?? false;
$hasFinanceAccess = RoleChecker::hasFinanceAccess();

// Данные для графика (только для полного дашборда)
$weekPaymentsJson = json_encode($stats['week_payments'] ?? []);
$weekLabelsJson = json_encode($stats['week_labels'] ?? []);

// Если дашборд учителя - рендерим отдельный шаблон
if ($isTeacherDashboard):
?>
<!-- Teacher Dashboard -->
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Мои группы -->
        <a href="<?= OrganizationUrl::to(['/crm/group/my-groups']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Мои группы</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= number_format($stats['my_groups'] ?? 0) ?></div>
        </a>

        <!-- Занятия сегодня -->
        <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Занятия сегодня</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $stats['my_lessons_today'] ?? 0 ?></div>
        </a>

        <!-- Мои ученики -->
        <div class="bg-white rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Мои ученики</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= number_format($stats['my_students'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Today's Lessons (Full Width on Mobile) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900">Мои занятия сегодня</h2>
                <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>" class="text-xs text-blue-600 hover:text-blue-700">
                    Расписание →
                </a>
            </div>
            <?php if (empty($stats['today_lessons'])): ?>
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нет занятий на сегодня</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($stats['today_lessons'] as $lesson): ?>
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50">
                            <div class="flex-shrink-0 w-12 text-center">
                                <div class="text-sm font-medium text-gray-900"><?= $lesson['time'] ?></div>
                                <div class="text-xs text-gray-400"><?= $lesson['end_time'] ?></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900 truncate"><?= $lesson['group'] ?></div>
                            </div>
                            <?php if ($lesson['status'] == \app\models\Lesson::STATUS_FINISHED): ?>
                                <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    Завершено
                                </span>
                            <?php else: ?>
                                <a href="<?= OrganizationUrl::to(['/crm/schedule/view', 'id' => $lesson['id']]) ?>"
                                   class="flex-shrink-0 text-xs text-blue-600 hover:text-blue-700">
                                    Открыть
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Groups -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900">Мои группы</h2>
                <a href="<?= OrganizationUrl::to(['/crm/group/my-groups']) ?>" class="text-xs text-blue-600 hover:text-blue-700">
                    Все группы →
                </a>
            </div>
            <?php if (empty($stats['my_groups_list'])): ?>
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нет групп</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($stats['my_groups_list'] as $group): ?>
                        <a href="<?= OrganizationUrl::to(['/crm/group/view', 'id' => $group['id']]) ?>"
                           class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                            <div class="text-sm font-medium text-gray-900"><?= $group['name'] ?></div>
                            <div class="text-xs text-gray-500"><?= $group['students_count'] ?> уч.</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
// Конец дашборда учителя
return;
endif;
?>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 <?= $isAdminDashboard ? 'lg:grid-cols-4' : 'lg:grid-cols-5' ?> gap-4">
        <!-- Ученики -->
        <a href="<?= OrganizationUrl::to(['/crm/pupil']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ученики</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= number_format($stats['pupils']) ?></div>
        </a>

        <!-- Группы -->
        <a href="<?= OrganizationUrl::to(['/crm/group']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Группы</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= number_format($stats['groups']) ?></div>
        </a>

        <?php if (!$isAdminDashboard): ?>
        <!-- Доход (только для Director+) -->
        <a href="<?= OrganizationUrl::to(['/crm/payment']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Доход</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= number_format($stats['revenue'], 0, '', ' ') ?> <span class="text-sm font-normal text-gray-500">₸</span></div>
        </a>
        <?php endif; ?>

        <!-- Занятия сегодня -->
        <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Сегодня</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $stats['lessons_today'] ?> <span class="text-sm font-normal text-gray-500">занятий</span></div>
        </a>

        <!-- Новые лиды -->
        <a href="<?= OrganizationUrl::to(['/crm/lids']) ?>"
           class="bg-white rounded-xl p-5 border border-gray-100 hover:border-gray-200 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Лиды</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div class="text-2xl font-semibold text-gray-900"><?= $stats['new_lids'] ?> <span class="text-sm font-normal text-gray-500">новых</span></div>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?php if (!$isAdminDashboard): ?>
        <!-- Chart (только для Director+) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-medium text-gray-900">Платежи за неделю</h2>
                <span class="text-xs text-gray-500"><?= date('d.m') ?> — <?= date('d.m', strtotime('+6 days')) ?></span>
            </div>
            <div class="h-48">
                <canvas id="weeklyPaymentsChart"></canvas>
            </div>
        </div>
        <?php else: ?>
        <!-- Для админа - занятия на сегодня вместо графика -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900">Занятия сегодня</h2>
                <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>" class="text-xs text-blue-600 hover:text-blue-700">
                    Расписание →
                </a>
            </div>
            <?php if (empty($stats['today_lessons'])): ?>
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нет занятий на сегодня</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($stats['today_lessons'] as $lesson): ?>
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50">
                            <div class="flex-shrink-0 w-12 text-center">
                                <div class="text-sm font-medium text-gray-900"><?= $lesson['time'] ?></div>
                                <div class="text-xs text-gray-400"><?= $lesson['end_time'] ?></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900 truncate"><?= $lesson['group'] ?></div>
                                <div class="text-xs text-gray-500 truncate"><?= $lesson['teacher'] ?></div>
                            </div>
                            <?php if ($lesson['status'] == \app\models\Lesson::STATUS_FINISHED): ?>
                                <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    Завершено
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h2 class="text-sm font-medium text-gray-900 mb-4">Быстрые действия</h2>
            <div class="space-y-2">
                <a href="<?= OrganizationUrl::to(['/crm/pupil/create']) ?>"
                   class="flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <span class="text-sm">Добавить ученика</span>
                </a>
                <?php if (!$isAdminDashboard): ?>
                <a href="<?= OrganizationUrl::to(['/crm/payment/create']) ?>"
                   class="flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <span class="text-sm">Принять платеж</span>
                </a>
                <?php endif; ?>
                <a href="<?= OrganizationUrl::to(['/crm/lids/create']) ?>"
                   class="flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <span class="text-sm">Новый лид</span>
                </a>
                <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>"
                   class="flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm">Расписание</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <?php if (!$isAdminDashboard): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments (только для Director+) -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900">Последние платежи</h2>
                <a href="<?= OrganizationUrl::to(['/crm/payment']) ?>" class="text-xs text-blue-600 hover:text-blue-700">
                    Все платежи →
                </a>
            </div>
            <?php if (empty($stats['recent_payments'])): ?>
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нет платежей</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($stats['recent_payments'] as $payment): ?>
                        <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/50">
                            <div class="min-w-0">
                                <a href="<?= OrganizationUrl::to(['/crm/pupil/view', 'id' => $payment['pupil_id']]) ?>"
                                   class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate block">
                                    <?= $payment['pupil'] ?>
                                </a>
                                <span class="text-xs text-gray-500"><?= $payment['method'] ?></span>
                            </div>
                            <div class="text-right flex-shrink-0 ml-4">
                                <div class="text-sm font-medium text-green-600">+<?= number_format($payment['amount'], 0, '', ' ') ?> ₸</div>
                                <div class="text-xs text-gray-400"><?= Yii::$app->formatter->asDate($payment['date'], 'short') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Today's Lessons -->
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-900">Занятия сегодня</h2>
                <a href="<?= OrganizationUrl::to(['/crm/schedule']) ?>" class="text-xs text-blue-600 hover:text-blue-700">
                    Расписание →
                </a>
            </div>
            <?php if (empty($stats['today_lessons'])): ?>
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нет занятий на сегодня</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-50">
                    <?php foreach ($stats['today_lessons'] as $lesson): ?>
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/50">
                            <div class="flex-shrink-0 w-12 text-center">
                                <div class="text-sm font-medium text-gray-900"><?= $lesson['time'] ?></div>
                                <div class="text-xs text-gray-400"><?= $lesson['end_time'] ?></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900 truncate"><?= $lesson['group'] ?></div>
                                <div class="text-xs text-gray-500 truncate"><?= $lesson['teacher'] ?></div>
                            </div>
                            <?php if ($lesson['status'] == \app\models\Lesson::STATUS_FINISHED): ?>
                                <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    Завершено
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!$isAdminDashboard): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('weeklyPaymentsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $weekLabelsJson ?>,
                datasets: [{
                    data: <?= $weekPaymentsJson ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#F3F4F6',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>
