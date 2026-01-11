<?php

/**
 * Список домашних заданий в ЛК
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\Pupil[] $pupils
 * @var app\models\Pupil|null $selectedPupil
 */

use app\models\Homework;
use app\models\HomeworkSubmission;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Домашние задания');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('book-open', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Задания') ?>
        </h1>
    </div>

    <!-- Pupil Selector (Segment Control) -->
    <?php if (count($pupils) > 1): ?>
        <div class="segment-control">
            <a href="<?= Url::to(['/cabinet/homework/index', 'org' => $orgId]) ?>"
               class="segment-item <?= !$selectedPupil ? 'active' : '' ?>">
                <?= Yii::t('app', 'Все') ?>
            </a>
            <?php foreach ($pupils as $pupil): ?>
                <a href="<?= Url::to(['/cabinet/homework/index', 'org' => $orgId, 'pupil_id' => $pupil->id]) ?>"
                   class="segment-item <?= $selectedPupil && $selectedPupil->id == $pupil->id ? 'active' : '' ?>">
                    <?= Html::encode($pupil->first_name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Homework List -->
    <div class="card-glass-solid lg:card-glass-solid-desktop overflow-hidden">
        <div class="section-header-ios lg:py-3 lg:px-6">
            <?= Yii::t('app', 'Активные задания') ?>
        </div>

        <?php if ($dataProvider->totalCount === 0): ?>
            <div class="empty-ios py-12">
                <div class="empty-ios-icon bg-green-100">
                    <?= Icon::show('check-circle', 'xl', 'text-green-500') ?>
                </div>
                <p class="empty-ios-text"><?= Yii::t('app', 'Нет активных заданий') ?></p>
            </div>
        <?php else: ?>
            <div class="list-group-ios">
                <?php foreach ($dataProvider->models as $homework): ?>
                    <?php
                    /** @var Homework $homework */
                    $isOverdue = $homework->isOverdue() && $homework->status === Homework::STATUS_ACTIVE;

                    $submission = null;
                    $isSubmitted = false;
                    $isChecked = false;
                    if ($selectedPupil) {
                        $submission = $homework->getSubmissionByPupil($selectedPupil->id);
                        $isSubmitted = $submission && $submission->isSubmitted();
                        $isChecked = $submission && $submission->isChecked();
                    }

                    // Determine status badge
                    if ($isChecked && $submission->grade) {
                        $badgeClass = 'bg-green-100 text-green-700';
                        $badgeText = $submission->grade;
                        $iconName = 'check-circle';
                    } elseif ($isSubmitted) {
                        $badgeClass = 'bg-blue-100 text-blue-700';
                        $badgeText = Yii::t('app', 'На проверке');
                        $iconName = 'clock';
                    } elseif ($isOverdue) {
                        $badgeClass = 'bg-red-100 text-red-700';
                        $badgeText = Yii::t('app', 'Просрочено');
                        $iconName = 'x-circle';
                    } else {
                        $badgeClass = 'bg-amber-100 text-amber-700';
                        $badgeText = Yii::t('app', 'Не сдано');
                        $iconName = 'exclamation-triangle';
                    }
                    ?>
                    <a href="<?= Url::to(['/cabinet/homework/view', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $selectedPupil ? $selectedPupil->id : null]) ?>"
                       class="list-item-ios lg:list-item-ios-desktop border-b border-gray-100 last:border-0 lg:px-6">
                        <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <?= Icon::show('document-text', 'sm', 'text-indigo-600') ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 lg:text-base truncate"><?= Html::encode($homework->title) ?></p>
                            <p class="text-sm text-gray-500">
                                <?= Html::encode($homework->group->name ?? '') ?>
                                <span class="<?= $isOverdue ? 'text-red-500' : 'text-gray-400' ?>">
                                    &bull; <?= Yii::t('app', 'До') ?>: <?= Yii::$app->formatter->asDate($homework->due_date, 'short') ?>
                                </span>
                            </p>
                        </div>
                        <span class="badge-ios <?= $badgeClass ?> flex-shrink-0">
                            <?= $badgeText ?>
                        </span>
                        <?= Icon::show('chevron-right', 'sm', 'text-gray-300 flex-shrink-0') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($dataProvider->totalCount > $dataProvider->pagination->pageSize): ?>
            <div class="px-4 py-3 border-t border-gray-100">
                <?= \app\widgets\tailwind\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
