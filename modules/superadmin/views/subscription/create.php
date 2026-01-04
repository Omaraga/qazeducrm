<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationSubscription $model */
/** @var app\models\Organizations[] $organizations */
/** @var app\models\SaasPlan[] $plans */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\OrganizationSubscription;

$this->title = 'Создание подписки';
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header">Новая подписка</div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'organization_id')->dropDownList(
                    ArrayHelper::map($organizations, 'id', function($org) {
                        return $org->name . ($org->type === 'branch' ? ' (филиал)' : '');
                    }),
                    [
                        'class' => 'form-control',
                        'prompt' => '-- Выберите организацию --',
                        'disabled' => $model->organization_id ? true : false,
                    ]
                )->label('Организация') ?>

                <?= $form->field($model, 'saas_plan_id')->dropDownList(
                    ArrayHelper::map($plans, 'id', function($plan) {
                        return $plan->name . ' — ' . $plan->getFormattedPriceMonthly() . '/мес';
                    }),
                    [
                        'class' => 'form-control',
                        'prompt' => '-- Выберите тариф --',
                    ]
                )->label('Тарифный план') ?>

                <?= $form->field($model, 'status')->dropDownList([
                    OrganizationSubscription::STATUS_TRIAL => 'Trial (пробный период)',
                    OrganizationSubscription::STATUS_ACTIVE => 'Активный',
                ], ['class' => 'form-control'])->label('Статус') ?>

                <?= $form->field($model, 'billing_period')->dropDownList([
                    OrganizationSubscription::PERIOD_MONTHLY => 'Месячная оплата',
                    OrganizationSubscription::PERIOD_YEARLY => 'Годовая оплата',
                ], ['class' => 'form-control'])->label('Период оплаты') ?>

                <div class="form-group">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Создать подписку', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom">
            <div class="card-header">Справка</div>
            <div class="card-body">
                <p class="text-muted mb-2">
                    <strong>Trial</strong> — пробный период согласно настройкам тарифа.
                </p>
                <p class="text-muted mb-2">
                    <strong>Активный</strong> — подписка начинается сразу.
                </p>
                <p class="text-muted mb-0">
                    Даты начала и окончания установятся автоматически.
                </p>
            </div>
        </div>
    </div>
</div>
