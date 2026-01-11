<?php

/**
 * Просмотр домашнего задания
 *
 * @var yii\web\View $this
 * @var app\models\Homework $model
 * @var app\models\HomeworkSubmission[] $submissions
 */

use app\helpers\OrganizationUrl;
use app\models\Homework;
use app\models\HomeworkSubmission;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Домашние задания'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$attachments = $model->getAttachmentsList();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">
                <?= Html::encode($model->group->name ?? '') ?> &bull;
                <?= Yii::t('app', 'Срок сдачи') ?>: <?= Yii::$app->formatter->asDate($model->due_date, 'long') ?>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= OrganizationUrl::to(['homework/index']) ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <?= Yii::t('app', 'Назад') ?>
            </a>
            <a href="<?= OrganizationUrl::to(['homework/update', 'id' => $model->id]) ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <?= Yii::t('app', 'Редактировать') ?>
            </a>
            <?php if ($model->status === Homework::STATUS_ACTIVE): ?>
                <a href="<?= OrganizationUrl::to(['homework/close', 'id' => $model->id]) ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <?= Yii::t('app', 'Закрыть приём') ?>
                </a>
            <?php elseif ($model->status === Homework::STATUS_CLOSED): ?>
                <a href="<?= OrganizationUrl::to(['homework/reopen', 'id' => $model->id]) ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    <?= Yii::t('app', 'Открыть приём') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Основная информация -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Описание задания -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Задание') ?></h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $model->getStatusClass() ?>">
                        <?= $model->getStatusLabel() ?>
                    </span>
                </div>
                <div class="p-5">
                    <?php if ($model->description): ?>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            <?= nl2br(Html::encode($model->description)) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic"><?= Yii::t('app', 'Описание не указано') ?></p>
                    <?php endif; ?>

                    <?php if (!empty($attachments)): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <h3 class="text-sm font-medium text-gray-900 mb-2"><?= Yii::t('app', 'Прикреплённые файлы') ?></h3>
                            <div class="space-y-2">
                                <?php foreach ($attachments as $file): ?>
                                    <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                                       class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700" target="_blank">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <?= Html::encode($file['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ответы учеников -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900">
                        <?= Yii::t('app', 'Ответы учеников') ?>
                        <span class="text-gray-500">(<?= count($submissions) ?>)</span>
                    </h2>
                </div>

                <?php if (empty($submissions)): ?>
                    <div class="p-5 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p><?= Yii::t('app', 'Пока нет ответов') ?></p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($submissions as $submission): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                            <?= mb_substr($submission->pupil->first_name ?? '?', 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?= Html::encode($submission->pupil->fio ?? $submission->pupil->first_name . ' ' . $submission->pupil->last_name) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php if ($submission->submitted_at): ?>
                                                    <?= Yii::t('app', 'Сдано') ?>: <?= Yii::$app->formatter->asDatetime($submission->submitted_at, 'short') ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $submission->getStatusClass() ?>">
                                            <?= $submission->getStatusLabel() ?>
                                        </span>
                                        <?php if ($submission->grade): ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 font-bold">
                                                <?= $submission->grade ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($submission->answer || !empty($submission->getFilesList())): ?>
                                    <div class="mt-3 pl-13">
                                        <?php if ($submission->answer): ?>
                                            <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3 mb-2">
                                                <?= nl2br(Html::encode(mb_substr($submission->answer, 0, 200))) ?>
                                                <?= mb_strlen($submission->answer) > 200 ? '...' : '' ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php $files = $submission->getFilesList(); ?>
                                        <?php if (!empty($files)): ?>
                                            <div class="flex flex-wrap gap-2">
                                                <?php foreach ($files as $file): ?>
                                                    <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                                                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 bg-blue-50 rounded px-2 py-1" target="_blank">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                        </svg>
                                                        <?= Html::encode($file['name']) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Форма проверки -->
                                <?php if ($submission->status === HomeworkSubmission::STATUS_SUBMITTED): ?>
                                    <div class="mt-4 ml-13 pt-4 border-t border-gray-100">
                                        <div class="flex flex-wrap items-end gap-3">
                                            <?= Html::beginForm(OrganizationUrl::to(['homework/check', 'id' => $submission->id]), 'post', ['class' => 'flex flex-wrap items-end gap-3 flex-1']) ?>
                                                <div class="w-20">
                                                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= Yii::t('app', 'Оценка') ?></label>
                                                    <select name="grade" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                                        <option value="">—</option>
                                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                                            <option value="<?= $i ?>"><?= $i ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <div class="flex-1 min-w-[180px]">
                                                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= Yii::t('app', 'Комментарий') ?></label>
                                                    <input type="text" name="comment" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= Yii::t('app', 'Комментарий к оценке') ?>">
                                                </div>
                                                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <?= Yii::t('app', 'Оценить') ?>
                                                </button>
                                            <?= Html::endForm() ?>

                                            <?= Html::beginForm(OrganizationUrl::to(['homework/return', 'id' => $submission->id]), 'post', ['class' => 'inline-flex']) ?>
                                                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors" onclick="return confirm('<?= Yii::t('app', 'Вернуть на доработку?') ?>')">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                    </svg>
                                                    <?= Yii::t('app', 'Вернуть') ?>
                                                </button>
                                            <?= Html::endForm() ?>
                                        </div>
                                    </div>
                                <?php elseif ($submission->comment): ?>
                                    <div class="mt-3 pl-13 text-sm">
                                        <span class="text-gray-500"><?= Yii::t('app', 'Комментарий') ?>:</span>
                                        <span class="text-gray-700"><?= Html::encode($submission->comment) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="space-y-6">
            <!-- Статистика -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Статистика') ?></h2>
                </div>
                <div class="p-5 space-y-4">
                    <?php
                    $studentsCount = $model->getStudentsCount();
                    $submittedCount = $model->getSubmittedCount();
                    $checkedCount = $model->getCheckedCount();
                    $percent = $studentsCount > 0 ? round($submittedCount / $studentsCount * 100) : 0;
                    ?>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500"><?= Yii::t('app', 'Сдано работ') ?></span>
                            <span class="font-medium"><?= $submittedCount ?>/<?= $studentsCount ?></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500"><?= Yii::t('app', 'Проверено') ?></span>
                        <span class="font-medium text-green-600"><?= $checkedCount ?></span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500"><?= Yii::t('app', 'Ожидают проверки') ?></span>
                        <span class="font-medium text-blue-600"><?= $submittedCount - $checkedCount ?></span>
                    </div>
                </div>
            </div>

            <!-- Информация -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Информация') ?></h2>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Группа') ?></span>
                        <a href="<?= OrganizationUrl::to(['/crm/group/view', 'id' => $model->group_id]) ?>"
                           class="font-medium text-blue-600 hover:text-blue-700">
                            <?= Html::encode($model->group->name ?? '—') ?>
                        </a>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Срок сдачи') ?></span>
                        <span class="font-medium <?= $model->isOverdue() ? 'text-red-600' : 'text-gray-900' ?>">
                            <?= Yii::$app->formatter->asDate($model->due_date, 'short') ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Создал') ?></span>
                        <span class="text-gray-900"><?= Html::encode($model->creator ? $model->creator->first_name . ' ' . $model->creator->last_name : '—') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Создано') ?></span>
                        <span class="text-gray-900"><?= Yii::$app->formatter->asDatetime($model->created_at, 'short') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
