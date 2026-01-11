<?php

/**
 * Просмотр домашнего задания в ЛК
 *
 * @var yii\web\View $this
 * @var app\models\Homework $homework
 * @var app\models\HomeworkSubmission|null $submission
 * @var app\models\Pupil[] $pupils
 * @var app\models\Pupil|null $selectedPupil
 */

use app\models\Homework;
use app\models\HomeworkSubmission;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $homework->title;
$orgId = Module::getOrganizationId();
$attachments = $homework->getAttachmentsList();

$canSubmit = $homework->canSubmit() && $selectedPupil && (!$submission || $submission->status !== HomeworkSubmission::STATUS_CHECKED);
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2 truncate">
            <?= Icon::show('document-text', 'md', 'text-indigo-600') ?>
            <span class="truncate"><?= Html::encode($homework->title) ?></span>
        </h1>
        <a href="<?= Url::to(['/cabinet/homework/index', 'org' => $orgId, 'pupil_id' => $selectedPupil ? $selectedPupil->id : null]) ?>"
           class="btn-ios-ghost flex-shrink-0">
            <?= Icon::show('arrow-left', 'sm') ?>
            <?= Yii::t('app', 'Назад') ?>
        </a>
    </div>

    <!-- Pupil Selector -->
    <?php if (count($pupils) > 1): ?>
        <div class="segment-control">
            <?php foreach ($pupils as $pupil): ?>
                <a href="<?= Url::to(['/cabinet/homework/view', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $pupil->id]) ?>"
                   class="segment-item <?= $selectedPupil && $selectedPupil->id == $pupil->id ? 'active' : '' ?>">
                    <?= Html::encode($pupil->first_name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Grade Card (if checked) -->
    <?php if ($submission && $submission->isChecked()): ?>
        <div class="card-glass-solid p-6 text-center">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-green-100 flex items-center justify-center mb-3">
                <span class="text-4xl font-bold text-green-600"><?= $submission->grade ?></span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('app', 'Оценка') ?></h3>
            <?php if ($submission->comment): ?>
                <p class="text-sm text-gray-500 mt-2"><?= Html::encode($submission->comment) ?></p>
            <?php endif; ?>
            <?php if ($submission->checked_at): ?>
                <p class="text-xs text-gray-400 mt-2">
                    <?= Yii::t('app', 'Проверено') ?>: <?= Yii::$app->formatter->asDatetime($submission->checked_at, 'short') ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Task Card -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios flex items-center justify-between">
            <span><?= Yii::t('app', 'Задание') ?></span>
            <?php
            $statusConfig = match($homework->status) {
                Homework::STATUS_ACTIVE => ['class' => 'bg-green-100 text-green-700', 'label' => $homework->getStatusLabel()],
                Homework::STATUS_CLOSED => ['class' => 'bg-amber-100 text-amber-700', 'label' => $homework->getStatusLabel()],
                Homework::STATUS_ARCHIVED => ['class' => 'bg-gray-100 text-gray-700', 'label' => $homework->getStatusLabel()],
                default => ['class' => 'bg-gray-100 text-gray-700', 'label' => $homework->getStatusLabel()],
            };
            ?>
            <span class="badge-ios <?= $statusConfig['class'] ?>"><?= $statusConfig['label'] ?></span>
        </div>

        <div class="p-4">
            <!-- Info Row -->
            <div class="flex flex-wrap gap-4 mb-4 text-sm">
                <span class="flex items-center gap-2 text-gray-600">
                    <?= Icon::show('user-group', 'sm', 'text-gray-400') ?>
                    <?= Html::encode($homework->group->name ?? '') ?>
                </span>
                <span class="flex items-center gap-2 <?= $homework->isOverdue() ? 'text-red-600' : 'text-gray-600' ?>">
                    <?= Icon::show('calendar', 'sm', $homework->isOverdue() ? 'text-red-400' : 'text-gray-400') ?>
                    <?= Yii::t('app', 'До') ?>: <?= Yii::$app->formatter->asDate($homework->due_date, 'long') ?>
                </span>
            </div>

            <!-- Description -->
            <?php if ($homework->description): ?>
                <div class="bg-gray-50 rounded-xl p-4 text-gray-700 leading-relaxed">
                    <?= nl2br(Html::encode($homework->description)) ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400 italic"><?= Yii::t('app', 'Описание не указано') ?></p>
            <?php endif; ?>

            <!-- Attachments -->
            <?php if (!empty($attachments)): ?>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <?= Icon::show('paper-clip', 'sm', 'text-gray-400') ?>
                        <?= Yii::t('app', 'Прикреплённые файлы') ?>
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($attachments as $file): ?>
                            <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                               class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-300 hover:bg-indigo-50 transition-colors group"
                               target="_blank">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <?= Icon::show('document', 'sm', 'text-indigo-600') ?>
                                </div>
                                <span class="flex-1 text-sm text-gray-700 group-hover:text-indigo-700 truncate"><?= Html::encode($file['name']) ?></span>
                                <?= Icon::show('arrow-down-tray', 'sm', 'text-gray-400 group-hover:text-indigo-600 flex-shrink-0') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- My Answer Card -->
    <?php if ($selectedPupil): ?>
        <div class="card-glass-solid overflow-hidden">
            <div class="section-header-ios flex items-center justify-between">
                <span><?= Yii::t('app', 'Мой ответ') ?></span>
                <?php if ($submission): ?>
                    <?php
                    $submissionStatusConfig = match($submission->status) {
                        HomeworkSubmission::STATUS_PENDING => ['class' => 'bg-gray-100 text-gray-700'],
                        HomeworkSubmission::STATUS_SUBMITTED => ['class' => 'bg-blue-100 text-blue-700'],
                        HomeworkSubmission::STATUS_CHECKED => ['class' => 'bg-green-100 text-green-700'],
                        HomeworkSubmission::STATUS_RETURNED => ['class' => 'bg-amber-100 text-amber-700'],
                        default => ['class' => 'bg-gray-100 text-gray-700'],
                    };
                    ?>
                    <span class="badge-ios <?= $submissionStatusConfig['class'] ?>"><?= $submission->getStatusLabel() ?></span>
                <?php endif; ?>
            </div>

            <div class="p-4">
                <?php if (!$submission || $submission->status === HomeworkSubmission::STATUS_PENDING): ?>
                    <div class="empty-ios py-8">
                        <div class="empty-ios-icon">
                            <?= Icon::show('pencil', 'xl', 'text-gray-300') ?>
                        </div>
                        <p class="empty-ios-text"><?= Yii::t('app', 'Вы ещё не сдали это задание') ?></p>
                        <?php if ($canSubmit): ?>
                            <a href="<?= Url::to(['/cabinet/homework/submit', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $selectedPupil->id]) ?>"
                               class="btn-ios-primary mt-4">
                                <?= Icon::show('paper-airplane', 'sm') ?>
                                <?= Yii::t('app', 'Сдать задание') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Answer Text -->
                    <?php if ($submission->answer): ?>
                        <div class="bg-gray-50 rounded-xl p-4 mb-4">
                            <p class="text-gray-700 leading-relaxed"><?= nl2br(Html::encode($submission->answer)) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Files -->
                    <?php $files = $submission->getFilesList(); ?>
                    <?php if (!empty($files)): ?>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($files as $file): ?>
                                <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:border-indigo-300 hover:bg-indigo-50 transition-colors"
                                   target="_blank">
                                    <?= Icon::show('document', 'xs', 'text-indigo-600') ?>
                                    <?= Html::encode($file['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Returned Warning -->
                    <?php if ($submission->status === HomeworkSubmission::STATUS_RETURNED && $canSubmit): ?>
                        <div class="alert-ios-warning mb-4">
                            <div class="flex gap-3">
                                <?= Icon::show('exclamation-triangle', 'sm', 'text-amber-500 flex-shrink-0 mt-0.5') ?>
                                <div>
                                    <p class="font-medium text-amber-800"><?= Yii::t('app', 'Работа возвращена на доработку') ?></p>
                                    <?php if ($submission->comment): ?>
                                        <p class="text-sm text-amber-700 mt-1"><?= Html::encode($submission->comment) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= Url::to(['/cabinet/homework/submit', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $selectedPupil->id]) ?>"
                           class="btn-ios-secondary">
                            <?= Icon::show('pencil', 'sm') ?>
                            <?= Yii::t('app', 'Исправить и сдать') ?>
                        </a>
                    <?php endif; ?>

                    <!-- Submission Time -->
                    <?php if ($submission->submitted_at): ?>
                        <p class="text-sm text-gray-500 mt-4 flex items-center gap-2">
                            <?= Icon::show('clock', 'xs', 'text-gray-400') ?>
                            <?= Yii::t('app', 'Сдано') ?>: <?= Yii::$app->formatter->asDatetime($submission->submitted_at, 'short') ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert-ios-info">
            <div class="flex gap-3">
                <?= Icon::show('information-circle', 'sm', 'text-blue-500 flex-shrink-0 mt-0.5') ?>
                <p class="text-blue-800"><?= Yii::t('app', 'Выберите ученика для просмотра и сдачи задания') ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
