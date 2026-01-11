<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */
/** @var string $currentMonth */
/** @var array $months */

use app\models\LessonAttendance;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = Yii::t('app', 'Посещаемость');
$orgId = Module::getOrganizationId();

$total = $stats['total'] ?? 0;
$visited = $stats['visited'] ?? 0;
$missed = ($stats['missed_with_pay'] ?? 0) + ($stats['missed_without_pay'] ?? 0);
$percent = $total > 0 ? round($visited / $total * 100) : 0;
$percentColor = $percent >= 80 ? 'text-green-600' : ($percent >= 60 ? 'text-amber-500' : 'text-red-500');
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('check-circle', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Посещения') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/attendance/stats', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('chart-bar', 'sm') ?>
            <?= Yii::t('app', 'Статистика') ?>
        </a>
    </div>

    <!-- Summary Card -->
    <div class="card-glass-solid lg:card-glass-solid-desktop p-5 lg:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm lg:text-base text-gray-500"><?= Yii::t('app', 'Посещаемость') ?></p>
                <p class="text-3xl lg:text-4xl font-bold <?= $percentColor ?>"><?= $percent ?>%</p>
            </div>
            <div class="w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-indigo-100 flex items-center justify-center">
                <?= Icon::show('clipboard-check', 'lg', 'text-indigo-600') ?>
            </div>
        </div>

        <!-- Mini Stats (4 columns on desktop) -->
        <div class="grid grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-4">
            <div class="bg-gray-50 rounded-xl p-3 lg:p-4 text-center">
                <p class="text-lg lg:text-2xl font-bold text-indigo-600"><?= $total ?></p>
                <p class="text-xs lg:text-sm text-gray-500"><?= Yii::t('app', 'Всего') ?></p>
            </div>
            <div class="bg-green-50 rounded-xl p-3 lg:p-4 text-center">
                <p class="text-lg lg:text-2xl font-bold text-green-600"><?= $visited ?></p>
                <p class="text-xs lg:text-sm text-gray-500"><?= Yii::t('app', 'Посещено') ?></p>
            </div>
            <div class="bg-red-50 rounded-xl p-3 lg:p-4 text-center">
                <p class="text-lg lg:text-2xl font-bold text-red-600"><?= $missed ?></p>
                <p class="text-xs lg:text-sm text-gray-500"><?= Yii::t('app', 'Пропущено') ?></p>
            </div>
            <div class="bg-amber-50 rounded-xl p-3 lg:p-4 text-center hidden lg:block">
                <p class="text-lg lg:text-2xl font-bold text-amber-600"><?= $stats['missed_valid'] ?? 0 ?></p>
                <p class="text-xs lg:text-sm text-gray-500"><?= Yii::t('app', 'Ув. причина') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Pupil Selector -->
        <?php if (count($pupils) > 1): ?>
            <div class="segment-control flex-1">
                <a href="<?= Url::to(['/cabinet/attendance/index', 'org' => $orgId, 'month' => $currentMonth]) ?>"
                   class="segment-item <?= !$selectedPupil ? 'active' : '' ?>">
                    <?= Yii::t('app', 'Все') ?>
                </a>
                <?php foreach ($pupils as $pupil): ?>
                    <a href="<?= Url::to(['/cabinet/attendance/index', 'org' => $orgId, 'pupil_id' => $pupil->id, 'month' => $currentMonth]) ?>"
                       class="segment-item <?= $selectedPupil && $selectedPupil->id == $pupil->id ? 'active' : '' ?>">
                        <?= Html::encode($pupil->first_name) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Month Select -->
        <select class="input-ios flex-shrink-0" onchange="location.href=this.value">
            <?php foreach ($months as $monthKey => $monthName): ?>
                <option value="<?= Url::to(['/cabinet/attendance/index', 'org' => $orgId, 'pupil_id' => $selectedPupil ? $selectedPupil->id : null, 'month' => $monthKey]) ?>"
                        <?= $monthKey == $currentMonth ? 'selected' : '' ?>>
                    <?= Html::encode($monthName) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Attendance List -->
    <div class="card-glass-solid lg:card-glass-solid-desktop overflow-hidden">
        <div class="section-header-ios lg:py-3 lg:px-6">
            <?= Yii::t('app', 'Журнал посещений') ?>
        </div>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => function ($attendance) {
                $lesson = $attendance->lesson;
                $group = $lesson ? $lesson->group : null;

                $statusConfig = match($attendance->status) {
                    LessonAttendance::STATUS_VISIT => [
                        'label' => Yii::t('app', 'Был'),
                        'bgClass' => 'bg-green-100',
                        'textClass' => 'text-green-700',
                        'icon' => 'check-circle',
                    ],
                    LessonAttendance::STATUS_MISS_WITH_PAY => [
                        'label' => Yii::t('app', 'Пропуск'),
                        'bgClass' => 'bg-red-100',
                        'textClass' => 'text-red-700',
                        'icon' => 'x-circle',
                    ],
                    LessonAttendance::STATUS_MISS_WITHOUT_PAY => [
                        'label' => Yii::t('app', 'Пропуск'),
                        'bgClass' => 'bg-red-100',
                        'textClass' => 'text-red-700',
                        'icon' => 'x-circle',
                    ],
                    LessonAttendance::STATUS_MISS_VALID_REASON => [
                        'label' => Yii::t('app', 'Ув. причина'),
                        'bgClass' => 'bg-amber-100',
                        'textClass' => 'text-amber-700',
                        'icon' => 'exclamation-triangle',
                    ],
                    default => [
                        'label' => '-',
                        'bgClass' => 'bg-gray-100',
                        'textClass' => 'text-gray-700',
                        'icon' => 'minus',
                    ],
                };

                $iconColor = str_replace('text-', '', $statusConfig['textClass']);

                return '
                <div class="list-item-ios lg:list-item-ios-desktop border-b border-gray-100 last:border-0 lg:px-6">
                    <div class="w-11 h-11 rounded-xl ' . $statusConfig['bgClass'] . ' flex items-center justify-center flex-shrink-0">
                        ' . \app\modules\cabinet\widgets\Icon::show($statusConfig['icon'], 'sm', $statusConfig['textClass']) . '
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 lg:text-base">' . \yii\helpers\Html::encode($group->name ?? '') . '</p>
                        <p class="text-sm text-gray-500">
                            ' . Yii::$app->formatter->asDate($lesson->date ?? '', 'EEEE, d MMM') . '
                            <span class="text-indigo-600 font-medium">' . ($lesson ? substr($lesson->start_time, 0, 5) : '') . '</span>
                        </p>
                    </div>
                    <span class="badge-ios ' . $statusConfig['bgClass'] . ' ' . $statusConfig['textClass'] . '">
                        ' . $statusConfig['label'] . '
                    </span>
                </div>';
            },
            'layout' => "{items}\n<div class='px-4 py-3 border-t border-gray-100'>{pager}</div>",
            'summary' => '',
            'emptyText' => '
                <div class="empty-ios py-12">
                    <div class="empty-ios-icon">
                        ' . Icon::show('calendar', 'xl', 'text-gray-300') . '
                    </div>
                    <p class="empty-ios-text">' . Yii::t('app', 'Нет записей за этот период') . '</p>
                </div>',
            'emptyTextOptions' => ['class' => ''],
            'pager' => [
                'class' => \app\widgets\tailwind\LinkPager::class,
            ],
        ]) ?>
    </div>
</div>
