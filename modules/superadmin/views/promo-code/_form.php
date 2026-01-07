<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPromoCode $model */
/** @var app\models\SaasPlan[] $plans */
/** @var app\models\SaasFeature[] $addons */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\SaasPromoCode;

$applicablePlans = $model->applicable_plans
    ? (is_array($model->applicable_plans) ? $model->applicable_plans : json_decode($model->applicable_plans, true))
    : [];

$applicableAddons = $model->applicable_addons
    ? (is_array($model->applicable_addons) ? $model->applicable_addons : json_decode($model->applicable_addons, true))
    : [];
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header">Основная информация</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'code', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="generate-code"><i class="fas fa-sync-alt"></i></button></div></div>{error}{hint}',
                        ])->textInput(['maxlength' => 50, 'class' => 'form-control', 'style' => 'text-transform: uppercase;']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => 255, 'class' => 'form-control']) ?>
                    </div>
                </div>

                <?= $form->field($model, 'description')->textarea(['rows' => 2, 'class' => 'form-control']) ?>

                <hr class="my-4">
                <h6 class="text-primary mb-3"><i class="fas fa-percent"></i> Скидка</h6>

                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'discount_type')->dropDownList(
                            SaasPromoCode::getDiscountTypeList(),
                            ['class' => 'form-control']
                        ) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'discount_value')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'min' => '0',
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'max_discount', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">KZT</span></div></div>{error}{hint}',
                        ])->textInput([
                            'type' => 'number',
                            'min' => '0',
                            'class' => 'form-control',
                            'placeholder' => 'Без ограничений',
                        ])->hint('Только для процентных скидок') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'applies_to')->dropDownList(
                            SaasPromoCode::getAppliesToList(),
                            ['class' => 'form-control']
                        ) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'min_amount', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">KZT</span></div></div>{error}{hint}',
                        ])->textInput([
                            'type' => 'number',
                            'min' => '0',
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt"></i> Период действия</h6>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'valid_from')->textInput([
                            'type' => 'datetime-local',
                            'class' => 'form-control',
                            'value' => $model->valid_from ? date('Y-m-d\TH:i', strtotime($model->valid_from)) : '',
                        ])->hint('Оставьте пустым для немедленного старта') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'valid_until')->textInput([
                            'type' => 'datetime-local',
                            'class' => 'form-control',
                            'value' => $model->valid_until ? date('Y-m-d\TH:i', strtotime($model->valid_until)) : '',
                        ])->hint('Оставьте пустым для бессрочного действия') ?>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-primary mb-3"><i class="fas fa-lock"></i> Ограничения</h6>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'usage_limit')->textInput([
                            'type' => 'number',
                            'min' => '1',
                            'class' => 'form-control',
                            'placeholder' => 'Без ограничений',
                        ])->hint('Общий лимит использований') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'usage_per_org')->textInput([
                            'type' => 'number',
                            'min' => '1',
                            'class' => 'form-control',
                        ])->hint('Сколько раз одна организация может использовать') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'first_payment_only')->checkbox([
                            'label' => 'Только для первого платежа',
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'new_customers_only')->checkbox([
                            'label' => 'Только для новых клиентов',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Применимость -->
        <?php if (!empty($plans)): ?>
        <div class="card card-custom mb-4">
            <div class="card-header">Применимые тарифы</div>
            <div class="card-body">
                <p class="text-muted small">Если ничего не выбрано — применяется ко всем тарифам</p>
                <?php foreach ($plans as $plan): ?>
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               name="applicable_plans[]"
                               value="<?= $plan->code ?>"
                               id="plan_<?= $plan->id ?>"
                            <?= in_array($plan->code, $applicablePlans) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="plan_<?= $plan->id ?>">
                            <?= Html::encode($plan->name) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($addons)): ?>
        <div class="card card-custom mb-4">
            <div class="card-header">Применимые аддоны</div>
            <div class="card-body">
                <p class="text-muted small">Если ничего не выбрано — применяется ко всем аддонам</p>
                <?php foreach ($addons as $addon): ?>
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               name="applicable_addons[]"
                               value="<?= $addon->code ?>"
                               id="addon_<?= $addon->id ?>"
                            <?= in_array($addon->code, $applicableAddons) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="addon_<?= $addon->id ?>">
                            <?= Html::encode($addon->name) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Статус -->
        <div class="card card-custom mb-4">
            <div class="card-header">Статус</div>
            <div class="card-body">
                <?= $form->field($model, 'is_active')->checkbox([
                    'label' => 'Промокод активен',
                ]) ?>
            </div>
        </div>

        <!-- Действия -->
        <div class="card card-custom">
            <div class="card-body">
                <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary btn-block']) ?>
                <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary btn-block']) ?>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
$('#generate-code').on('click', function() {
    $.get('/superadmin/promo-code/generate-code', function(data) {
        $('#saaspromocode-code').val(data.code);
    });
});

// Преобразование в верхний регистр
$('#saaspromocode-code').on('input', function() {
    this.value = this.value.toUpperCase();
});
JS
);
?>
