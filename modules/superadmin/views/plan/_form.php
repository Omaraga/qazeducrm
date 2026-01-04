<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$features = $model->getFeaturesArray();
$availableFeatures = [
    'crm_basic' => 'Базовый CRM',
    'sms' => 'SMS уведомления',
    'reports' => 'Расширенные отчёты',
    'api' => 'API доступ',
    'leads' => 'Управление лидами',
    'custom' => 'Кастомизация',
    'priority_support' => 'Приоритетная поддержка',
];
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'code')->textInput(['maxlength' => 50]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    </div>
</div>

<?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>

<hr>
<h5>Лимиты</h5>
<p class="text-muted">0 = безлимит</p>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'max_pupils')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'max_teachers')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'max_groups')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'max_admins')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'max_branches')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'trial_days')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
</div>

<hr>
<h5>Цены (KZT)</h5>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'price_monthly')->textInput(['type' => 'number', 'min' => 0, 'step' => '0.01']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'price_yearly')->textInput(['type' => 'number', 'min' => 0, 'step' => '0.01']) ?>
    </div>
</div>

<hr>
<h5>Функции</h5>

<div class="row">
    <?php foreach ($availableFeatures as $key => $label): ?>
        <div class="col-md-4 mb-2">
            <div class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input"
                       id="feature_<?= $key ?>"
                       name="features[<?= $key ?>]"
                       value="1"
                       <?= !empty($features[$key]) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="feature_<?= $key ?>"><?= $label ?></label>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'is_active')->checkbox() ?>
    </div>
</div>

<hr>

<div class="form-group">
    <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
