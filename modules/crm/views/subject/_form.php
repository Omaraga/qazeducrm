<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Subject $model */
?>

<form action="" method="post" class="max-w-2xl">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Данные предмета</h3>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label class="form-label" for="subject-name">
                    Название <span class="text-danger-500">*</span>
                </label>
                <?= Html::activeTextInput($model, 'name', [
                    'class' => 'form-input',
                    'id' => 'subject-name',
                    'maxlength' => true,
                    'placeholder' => 'Например: Математика',
                    'autofocus' => true
                ]) ?>
                <?php if ($model->hasErrors('name')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('name') ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?= Yii::t('main', 'Сохранить') ?>
            </button>
            <a href="<?= OrganizationUrl::to(['subject/index']) ?>" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </div>
</form>
