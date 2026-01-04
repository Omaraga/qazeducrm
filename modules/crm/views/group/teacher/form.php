<?php

use app\helpers\OrganizationUrl;
use app\models\Organizations;
use app\models\relations\TeacherGroup;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\relations\TeacherGroup $model */

if ($model->id) {
    $this->title = 'Редактировать преподавателя: ' . ($model->teacher->fio ?? '') . ' - ' . ($model->group->name ?? '');
} else {
    $this->title = 'Добавить преподавателя';
}
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = ['label' => $model->group->code . '-' . $model->group->name, 'url' => OrganizationUrl::to(['group/teachers', 'id' => $model->target_id])];
$this->params['breadcrumbs'][] = $this->title;

$teachers = Organizations::getOrganizationTeachersMap();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Назначьте преподавателя для группы</p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['group/teachers', 'id' => $model->target_id]) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к списку
            </a>
        </div>
    </div>

    <form action="<?= OrganizationUrl::to(['group/create-teacher', 'group_id' => $model->target_id]) ?>" method="post" class="space-y-6">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label" for="teachergroup-related_id">Преподаватель <span class="text-danger-500">*</span></label>
                        <?= Html::activeDropDownList($model, 'related_id', $teachers, [
                            'class' => 'form-select',
                            'id' => 'teachergroup-related_id',
                            'prompt' => 'Выберите преподавателя'
                        ]) ?>
                        <?php if ($model->hasErrors('related_id')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('related_id') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="teachergroup-type">Тип оплаты</label>
                        <?= Html::activeDropDownList($model, 'type', TeacherGroup::getPriceTypeList(), [
                            'class' => 'form-select',
                            'id' => 'teachergroup-type'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="teachergroup-price">Ставка (₸)</label>
                        <?= Html::activeTextInput($model, 'price', [
                            'class' => 'form-input',
                            'id' => 'teachergroup-price',
                            'type' => 'number',
                            'placeholder' => '0'
                        ]) ?>
                        <?php if ($model->hasErrors('price')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('price') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
            </button>
            <a href="<?= OrganizationUrl::to(['group/teachers', 'id' => $model->target_id]) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
