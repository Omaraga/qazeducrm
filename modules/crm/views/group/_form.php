<?php

use app\helpers\OrganizationUrl;
use app\models\Subject;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('group-subject_id');
    const nameInput = document.getElementById('group-name');

    if (subjectSelect && nameInput) {
        subjectSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                nameInput.value = selectedOption.text;
            }
        });
    }
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <form method="post" class="space-y-6">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <!-- Основные данные -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Основные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="group-subject_id">Предмет <span class="text-danger-500">*</span></label>
                        <?= Html::activeDropDownList($model, 'subject_id', ArrayHelper::map(Subject::find()->byOrganization()->all(), 'id', 'name'), ['class' => 'form-select', 'id' => 'group-subject_id']) ?>
                        <?php if ($model->hasErrors('subject_id')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('subject_id') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="group-code">Код группы <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'code', ['class' => 'form-input', 'id' => 'group-code', 'placeholder' => 'Например: M-101']) ?>
                        <?php if ($model->hasErrors('code')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('code') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="group-name">Название <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'name', ['class' => 'form-input', 'id' => 'group-name']) ?>
                        <?php if ($model->hasErrors('name')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="group-category_id">Категория</label>
                        <?= Html::activeDropDownList($model, 'category_id', \app\helpers\Lists::getGroupCategories(), ['class' => 'form-select', 'id' => 'group-category_id']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="group-type">Тип</label>
                        <?= Html::activeDropDownList($model, 'type', \app\models\Group::getTypeList(), ['class' => 'form-select', 'id' => 'group-type']) ?>
                    </div>
                    <div>
                        <label class="form-label" for="group-color">Цвет</label>
                        <div class="flex gap-2">
                            <?= Html::activeInput('color', $model, 'color', ['class' => 'h-10 w-20 rounded border border-gray-300 cursor-pointer', 'id' => 'group-color']) ?>
                            <span class="flex items-center text-sm text-gray-500">Выберите цвет для календаря</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Системные сведения -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Системные сведения') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="group-status">Статус</label>
                        <?= Html::activeDropDownList($model, 'status', \app\models\enum\StatusEnum::getStatusList(), ['class' => 'form-select', 'id' => 'group-status']) ?>
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
            <a href="<?= OrganizationUrl::to(['group/index']) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
