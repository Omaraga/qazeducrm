<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationAddon $model */
/** @var array $organizations */
/** @var array $features */
/** @var string|null $limitField */
/** @var int|null $limitValue */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\OrganizationAddon;

$limitField = $limitField ?? $model->getValue('limit_field');
$limitValue = $limitValue ?? $model->getValue('limit_value');
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'organization_id')->dropDownList($organizations, [
            'prompt' => 'Выберите организацию...',
            'class' => 'form-control',
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'feature_id')->dropDownList($features, [
            'prompt' => 'Выберите аддон...',
            'class' => 'form-control',
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'status')->dropDownList(OrganizationAddon::getStatusList(), [
            'class' => 'form-control',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'billing_period')->dropDownList(OrganizationAddon::getBillingPeriodList(), [
            'class' => 'form-control',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'quantity')->textInput([
            'type' => 'number',
            'min' => 1,
            'class' => 'form-control',
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'price')->textInput([
            'type' => 'number',
            'step' => '0.01',
            'class' => 'form-control',
            'placeholder' => 'Оставьте пустым для автоматического расчёта',
        ])->hint('Если не указана, будет взята из настроек аддона') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'expires_at')->textInput([
            'type' => 'datetime-local',
            'class' => 'form-control',
            'value' => $model->expires_at ? date('Y-m-d\TH:i', strtotime($model->expires_at)) : '',
        ])->hint('Оставьте пустым для автоматического расчёта') ?>
    </div>
</div>

<!-- Дополнительные настройки для лимит-аддонов -->
<div class="card card-custom mt-3">
    <div class="card-header">
        <span class="font-weight-bold">Настройки лимита (для пакетов учеников/групп)</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Поле лимита</label>
                    <?= Html::dropDownList('limit_field', $limitField, [
                        '' => '- Не применимо -',
                        'max_pupils' => 'Ученики (max_pupils)',
                        'max_groups' => 'Группы (max_groups)',
                        'max_teachers' => 'Учителя (max_teachers)',
                        'max_admins' => 'Админы (max_admins)',
                        'max_branches' => 'Филиалы (max_branches)',
                    ], ['class' => 'form-control']) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Значение (бонус)</label>
                    <?= Html::textInput('limit_value', $limitValue, [
                        'type' => 'number',
                        'class' => 'form-control',
                        'placeholder' => 'Например: 50',
                    ]) ?>
                    <small class="text-muted">Сколько добавить к лимиту (за 1 quantity)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
