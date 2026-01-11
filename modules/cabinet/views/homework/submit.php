<?php

/**
 * Сдача домашнего задания в ЛК
 *
 * @var yii\web\View $this
 * @var app\models\Homework $homework
 * @var app\models\HomeworkSubmission $submission
 * @var app\models\Pupil $pupil
 */

use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Сдать задание');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('paper-airplane', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Сдать задание') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/homework/view', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $pupil->id]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('arrow-left', 'sm') ?>
            <?= Yii::t('app', 'Назад') ?>
        </a>
    </div>

    <!-- Task Info Card -->
    <div class="card-glass-solid p-4 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
            <?= Icon::show('document-text', 'md', 'text-indigo-600') ?>
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="font-semibold text-gray-900 truncate"><?= Html::encode($homework->title) ?></h2>
            <div class="flex flex-wrap gap-3 text-sm text-gray-500 mt-0.5">
                <span class="flex items-center gap-1">
                    <?= Icon::show('user-group', 'xs', 'text-gray-400') ?>
                    <?= Html::encode($homework->group->name ?? '') ?>
                </span>
                <span class="flex items-center gap-1 <?= $homework->isOverdue() ? 'text-red-500' : '' ?>">
                    <?= Icon::show('calendar', 'xs', $homework->isOverdue() ? 'text-red-400' : 'text-gray-400') ?>
                    <?= Yii::t('app', 'До') ?>: <?= Yii::$app->formatter->asDate($homework->due_date, 'short') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Submit Form -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios">
            <?= Yii::t('app', 'Ваш ответ') ?>
        </div>

        <div class="p-4">
            <?= Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']) ?>

            <!-- Answer Text -->
            <div class="mb-5">
                <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                    <?= Yii::t('app', 'Текст ответа') ?>
                </label>
                <textarea name="answer" id="answer" rows="6"
                          class="input-ios resize-none"
                          placeholder="<?= Yii::t('app', 'Напишите ваш ответ здесь...') ?>"><?= Html::encode($submission->answer ?? '') ?></textarea>
                <p class="mt-2 text-sm text-gray-500">
                    <?= Yii::t('app', 'Опишите выполненную работу или ответьте на вопросы задания') ?>
                </p>
            </div>

            <!-- Existing Files -->
            <?php $existingFiles = $submission->getFilesList(); ?>
            <?php if (!empty($existingFiles)): ?>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <?= Yii::t('app', 'Ранее загруженные файлы') ?>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($existingFiles as $file): ?>
                            <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 hover:border-indigo-300 hover:bg-indigo-50 transition-colors"
                               target="_blank">
                                <?= Icon::show('document', 'xs', 'text-indigo-600') ?>
                                <?= Html::encode($file['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- File Upload -->
            <div class="mb-6">
                <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                    <?= Yii::t('app', 'Прикрепить файлы') ?>
                </label>
                <div class="relative">
                    <input type="file" name="files[]" id="files" multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer border border-gray-200 rounded-xl transition-colors">
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <?= Yii::t('app', 'PDF, Word, Excel, изображения. Можно выбрать несколько файлов.') ?>
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-ios-primary flex-1 sm:flex-none justify-center">
                    <?= Icon::show('paper-airplane', 'sm') ?>
                    <?= Yii::t('app', 'Отправить') ?>
                </button>
                <a href="<?= Url::to(['/cabinet/homework/view', 'org' => $orgId, 'id' => $homework->id, 'pupil_id' => $pupil->id]) ?>"
                   class="btn-ios-ghost flex-1 sm:flex-none justify-center">
                    <?= Yii::t('app', 'Отмена') ?>
                </a>
            </div>

            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Tips Card -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios">
            <?= Icon::show('light-bulb', 'sm', 'text-amber-500') ?>
            <?= Yii::t('app', 'Подсказки') ?>
        </div>
        <div class="p-4">
            <ul class="space-y-3">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">1</span>
                    <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Внимательно прочитайте задание') ?></span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">2</span>
                    <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Убедитесь, что ваш ответ полный') ?></span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Прикрепите все необходимые файлы') ?></span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold">!</span>
                    <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'После отправки изменить ответ нельзя') ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>
