<?php

/**
 * Форма домашнего задания
 *
 * @var yii\web\View $this
 * @var app\models\Homework $model
 * @var app\models\Group[] $groups
 */

use app\helpers\OrganizationUrl;
use app\models\Homework;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$groupItems = ArrayHelper::map($groups ?? [], 'id', 'name');
$attachments = $model->getAttachmentsList();
?>

<div class="card">
    <form method="post" class="card-body space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label" for="homework-group_id"><?= Yii::t('app', 'Группа') ?> <span class="text-danger-500">*</span></label>
                <?= Html::activeDropDownList($model, 'group_id', $groupItems, [
                    'prompt' => Yii::t('app', 'Выберите группу'),
                    'class' => 'form-select',
                    'id' => 'homework-group_id',
                ]) ?>
                <?php if ($model->hasErrors('group_id')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('group_id') ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="form-label" for="homework-due_date"><?= Yii::t('app', 'Срок сдачи') ?> <span class="text-danger-500">*</span></label>
                <?= Html::activeInput('date', $model, 'due_date', [
                    'class' => 'form-input',
                    'id' => 'homework-due_date',
                    'value' => $model->due_date ?: date('Y-m-d', strtotime('+7 days')),
                ]) ?>
                <?php if ($model->hasErrors('due_date')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('due_date') ?></p>
                <?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="form-label" for="homework-title"><?= Yii::t('app', 'Название') ?> <span class="text-danger-500">*</span></label>
                <?= Html::activeTextInput($model, 'title', [
                    'class' => 'form-input',
                    'id' => 'homework-title',
                    'placeholder' => Yii::t('app', 'Например: Выучить слова Unit 5'),
                ]) ?>
                <?php if ($model->hasErrors('title')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('title') ?></p>
                <?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="form-label" for="homework-description"><?= Yii::t('app', 'Описание') ?></label>
                <?= Html::activeTextarea($model, 'description', [
                    'class' => 'form-input',
                    'id' => 'homework-description',
                    'rows' => 5,
                    'placeholder' => Yii::t('app', 'Подробное описание задания...'),
                ]) ?>
                <?php if ($model->hasErrors('description')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('description') ?></p>
                <?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <label class="form-label" for="homework-status"><?= Yii::t('app', 'Статус') ?></label>
                <?= Html::activeDropDownList($model, 'status', Homework::getStatusList(), [
                    'class' => 'form-select',
                    'id' => 'homework-status',
                ]) ?>
                <?php if ($model->hasErrors('status')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('status') ?></p>
                <?php endif; ?>
            </div>

            <!-- Существующие вложения -->
            <?php if (!empty($attachments)): ?>
                <div class="md:col-span-2">
                    <label class="form-label"><?= Yii::t('app', 'Прикреплённые файлы') ?></label>
                    <div class="space-y-2">
                        <?php foreach ($attachments as $i => $file): ?>
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                                <a href="<?= Yii::getAlias('@web/' . $file['path']) ?>"
                                   class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700" target="_blank">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <?= Html::encode($file['name']) ?>
                                </a>
                                <a href="<?= OrganizationUrl::to(['homework/delete-attachment', 'id' => $model->id, 'index' => $i]) ?>"
                                   class="text-red-500 hover:text-red-700"
                                   onclick="return confirm('<?= Yii::t('app', 'Удалить файл?') ?>')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="md:col-span-2">
                <label class="form-label" for="homework-attachments"><?= Yii::t('app', 'Добавить файлы') ?></label>
                <input type="file" name="Homework[attachments][]" id="homework-attachments" class="form-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                <p class="mt-1 text-sm text-gray-500"><?= Yii::t('app', 'PDF, Word, Excel, изображения. Можно выбрать несколько файлов.') ?></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="<?= OrganizationUrl::to(['homework/index']) ?>" class="btn btn-secondary">
                <?= Yii::t('app', 'Отмена') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?= $model->isNewRecord ? Yii::t('app', 'Создать задание') : Yii::t('app', 'Сохранить') ?>
            </button>
        </div>
    </form>
</div>
