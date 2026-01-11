<?php

/** @var yii\web\View $this */
/** @var array $pupilsData */
/** @var app\models\Organizations $organization */

use app\models\Payment;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Главная');
?>

<?php
// Получаем настройки кабинета
$cabinetSettings = $this->params['cabinetSettings'] ?? [];
$welcomeMessage = $cabinetSettings['welcomeMessage'] ?? '';
$showBalance = $cabinetSettings['showBalance'] ?? true;
?>

<div class="space-y-5">
    <!-- Welcome Card -->
    <div class="card-glass-solid p-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    <?= !empty($welcomeMessage) ? Html::encode($welcomeMessage) : Yii::t('app', 'Добро пожаловать!') ?>
                </h1>
                <p class="text-sm text-gray-500 mt-1"><?= Yii::$app->formatter->asDate(time(), 'EEEE, d MMMM') ?></p>
            </div>
        </div>
    </div>

    <!-- Pupils Cards (Grid on desktop) -->
    <div class="pupil-cards-grid">
    <?php foreach ($pupilsData as $data): ?>
        <?php
        $pupil = $data['pupil'];
        $upcomingLessons = $data['upcomingLessons'];
        $attendanceStats = $data['attendanceStats'];
        $recentPayments = $data['recentPayments'];

        $total = $attendanceStats['total'] ?? 0;
        $visited = $attendanceStats['visited'] ?? 0;
        $percent = $total > 0 ? round($visited / $total * 100) : 0;
        $percentColor = $percent >= 80 ? 'text-green-600' : ($percent >= 60 ? 'text-amber-500' : 'text-red-500');
        ?>

        <div class="card-glass-solid lg:card-glass-solid-desktop overflow-hidden">
            <!-- Pupil Header -->
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="avatar-ios-lg">
                        <?= mb_substr($pupil->first_name, 0, 1) ?>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-900"><?= Html::encode($pupil->fio ?: $pupil->first_name . ' ' . $pupil->last_name) ?></h2>
                        <?php if ($showBalance): ?>
                        <p class="text-sm <?= $pupil->balance >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= Yii::t('app', 'Баланс') ?>: <?= Yii::$app->formatter->asCurrency($pupil->balance, 'KZT') ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?= Icon::show('chevron-right', 'sm', 'text-gray-300') ?>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 divide-x divide-gray-100">
                <div class="stat-ios">
                    <p class="stat-ios-value"><?= $total ?></p>
                    <p class="stat-ios-label"><?= Yii::t('app', 'Занятий') ?></p>
                </div>
                <div class="stat-ios">
                    <p class="stat-ios-value <?= $percentColor ?>"><?= $percent ?>%</p>
                    <p class="stat-ios-label"><?= Yii::t('app', 'Посещаемость') ?></p>
                </div>
            </div>

            <!-- Upcoming Lessons -->
            <?php if (!empty($upcomingLessons)): ?>
                <div class="border-t border-gray-100">
                    <div class="section-header-ios">
                        <?= Yii::t('app', 'Ближайшие занятия') ?>
                    </div>
                    <div class="list-group-ios rounded-none">
                        <?php foreach (array_slice($upcomingLessons, 0, 3) as $lesson): ?>
                            <a href="<?= Url::to(['/cabinet/schedule/index', 'pupil_id' => $pupil->id]) ?>"
                               class="list-item-ios">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                    <?= Icon::show('calendar', 'sm', 'text-indigo-600') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate"><?= Html::encode($lesson->group->name ?? '') ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?= Yii::$app->formatter->asDate($lesson->date, 'EEE, d MMM') ?>
                                        <span class="text-indigo-600 font-medium"><?= substr($lesson->start_time, 0, 5) ?></span>
                                    </p>
                                </div>
                                <?= Icon::show('chevron-right', 'sm', 'text-gray-300 flex-shrink-0') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Payments -->
            <?php if (!empty($recentPayments)): ?>
                <div class="border-t border-gray-100">
                    <div class="section-header-ios">
                        <?= Yii::t('app', 'Последние операции') ?>
                    </div>
                    <div class="list-group-ios rounded-none">
                        <?php foreach (array_slice($recentPayments, 0, 2) as $payment): ?>
                            <a href="<?= Url::to(['/cabinet/payment/view', 'id' => $payment->id]) ?>"
                               class="list-item-ios">
                                <div class="w-10 h-10 rounded-xl <?= $payment->type == Payment::TYPE_PAY ? 'bg-green-50' : 'bg-red-50' ?> flex items-center justify-center flex-shrink-0">
                                    <?php if ($payment->type == Payment::TYPE_PAY): ?>
                                        <?= Icon::show('arrow-down-tray', 'sm', 'text-green-600') ?>
                                    <?php else: ?>
                                        <?= Icon::show('arrow-up-tray', 'sm', 'text-red-600') ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900">
                                        <?= $payment->type == Payment::TYPE_PAY ? Yii::t('app', 'Пополнение') : Yii::t('app', 'Списание') ?>
                                    </p>
                                    <p class="text-sm text-gray-500"><?= Yii::$app->formatter->asDate($payment->date, 'short') ?></p>
                                </div>
                                <span class="text-sm font-semibold <?= $payment->type == Payment::TYPE_PAY ? 'text-green-600' : 'text-red-600' ?> flex-shrink-0">
                                    <?= $payment->type == Payment::TYPE_PAY ? '+' : '-' ?>
                                    <?= Yii::$app->formatter->asCurrency($payment->amount, 'KZT') ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>

    <!-- Quick Actions (more columns on desktop) -->
    <?php
    $showSchedule = $cabinetSettings['showSchedule'] ?? true;
    $showPayments = $cabinetSettings['showPayments'] ?? true;
    $showAttendance = $cabinetSettings['showAttendance'] ?? true;
    $showHomework = $cabinetSettings['showHomework'] ?? true;
    ?>
    <div class="quick-actions-grid">
        <?php if ($showSchedule): ?>
        <a href="<?= Url::to(['/cabinet/schedule/week']) ?>" class="quick-action-ios lg:quick-action-ios-desktop">
            <div class="quick-action-ios-icon bg-indigo-50">
                <?= Icon::show('calendar', 'lg', 'text-indigo-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Расписание') ?></span>
        </a>
        <?php endif; ?>

        <?php if ($showBalance): ?>
        <a href="<?= Url::to(['/cabinet/payment/balance']) ?>" class="quick-action-ios lg:quick-action-ios-desktop">
            <div class="quick-action-ios-icon bg-green-50">
                <?= Icon::show('banknotes', 'lg', 'text-green-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Баланс') ?></span>
        </a>
        <?php endif; ?>

        <?php if ($showAttendance): ?>
        <a href="<?= Url::to(['/cabinet/attendance/stats']) ?>" class="quick-action-ios lg:quick-action-ios-desktop">
            <div class="quick-action-ios-icon bg-purple-50">
                <?= Icon::show('clipboard-check', 'lg', 'text-purple-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Статистика') ?></span>
        </a>
        <?php endif; ?>

        <?php if ($showHomework): ?>
        <a href="<?= Url::to(['/cabinet/homework/index']) ?>" class="quick-action-ios lg:quick-action-ios-desktop hidden sm:flex">
            <div class="quick-action-ios-icon bg-amber-50">
                <?= Icon::show('book-open', 'lg', 'text-amber-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Задания') ?></span>
        </a>
        <?php endif; ?>

        <?php if ($showPayments): ?>
        <a href="<?= Url::to(['/cabinet/payment/index']) ?>" class="quick-action-ios lg:quick-action-ios-desktop hidden lg:flex">
            <div class="quick-action-ios-icon bg-sky-50">
                <?= Icon::show('document-text', 'lg', 'text-sky-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Платежи') ?></span>
        </a>
        <?php endif; ?>

        <?php if ($showAttendance): ?>
        <a href="<?= Url::to(['/cabinet/attendance/index']) ?>" class="quick-action-ios lg:quick-action-ios-desktop hidden lg:flex">
            <div class="quick-action-ios-icon bg-rose-50">
                <?= Icon::show('chart-bar', 'lg', 'text-rose-600') ?>
            </div>
            <span class="quick-action-ios-label"><?= Yii::t('app', 'Посещения') ?></span>
        </a>
        <?php endif; ?>
    </div>
</div>
