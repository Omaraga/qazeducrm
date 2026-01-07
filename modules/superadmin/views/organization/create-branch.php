<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $model */
/** @var app\models\Organizations $parent */
/** @var app\models\OrganizationSubscription|null $parentSubscription */
/** @var app\models\SaasPlan[] $plans */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\Organizations;

$this->title = 'Новый филиал';
?>

<div class="mb-3">
    <a href="<?= Url::to(['view', 'id' => $parent->id]) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К организации
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Создание филиала для: <strong><?= Html::encode($parent->name) ?></strong>
    <?php if ($parentSubscription): ?>
        <br>
        <small>Текущий план головной: <strong><?= Html::encode($parentSubscription->saasPlan->name ?? 'Не указан') ?></strong></small>
    <?php endif; ?>
</div>

<div class="card card-custom">
    <div class="card-header">
        Создание филиала
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'parent_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Название филиала']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList(Organizations::getStatusList()) ?>
            </div>
        </div>

        <hr>
        <h5 class="mb-3"><i class="fas fa-credit-card"></i> Режим подписки</h5>

        <?= $form->field($model, 'billing_mode')->radioList([
            Organizations::BILLING_POOLED => '<strong>Общая подписка</strong> <small class="text-muted">- лимиты суммируются с головной организацией</small>',
            Organizations::BILLING_ISOLATED => '<strong>Отдельная подписка</strong> <small class="text-muted">- индивидуальные лимиты, скидка при оплате (рекомендуется)</small>',
        ], [
            'encode' => false,
            'itemOptions' => ['labelOptions' => ['class' => 'd-block mb-2']],
        ])->label('Режим биллинга') ?>

        <div id="subscription-plan-block" style="<?= $model->billing_mode === Organizations::BILLING_POOLED ? 'display:none;' : '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="saas_plan_id">Тарифный план</label>
                        <?= Html::dropDownList('saas_plan_id', $parentSubscription?->saas_plan_id, ArrayHelper::map($plans, 'id', function($plan) {
                            return $plan->name . ' (' . number_format($plan->price_monthly, 0, '.', ' ') . ' KZT/мес)';
                        }), [
                            'class' => 'form-control',
                            'id' => 'saas_plan_id',
                            'prompt' => 'Выберите план...',
                        ]) ?>
                        <small class="form-text text-muted">
                            Если не выбрать, будет использован план головной организации.
                            Скидка применяется вручную при принятии оплаты.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Создать филиал', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена', ['view', 'id' => $parent->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
// Показать/скрыть блок выбора плана в зависимости от billing_mode
$('input[name="Organizations[billing_mode]"]').on('change', function() {
    if ($(this).val() === 'isolated') {
        $('#subscription-plan-block').slideDown();
    } else {
        $('#subscription-plan-block').slideUp();
    }
});
JS;
$this->registerJs($js);
?>
