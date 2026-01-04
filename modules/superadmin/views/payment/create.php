<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationPayment $model */
/** @var app\models\Organizations[] $organizations */
/** @var app\models\OrganizationSubscription[] $subscriptions */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$this->title = 'Создание платежа';
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header">Новый платёж</div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'subscription_id')->dropDownList(
                    ArrayHelper::map($subscriptions, 'id', function($sub) {
                        $org = $sub->organization ? $sub->organization->name : 'Организация #' . $sub->organization_id;
                        $plan = $sub->saasPlan ? $sub->saasPlan->name : 'План #' . $sub->saas_plan_id;
                        return $org . ' — ' . $plan . ' (' . $sub->getStatusLabel() . ')';
                    }),
                    [
                        'class' => 'form-control',
                        'prompt' => '-- Выберите подписку --',
                        'disabled' => $model->subscription_id ? true : false,
                    ]
                )->label('Подписка') ?>

                <?= $form->field($model, 'amount', [
                    'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">KZT</span></div></div>{error}{hint}',
                ])->textInput(['type' => 'number', 'class' => 'form-control'])->label('Сумма') ?>

                <?= $form->field($model, 'payment_method')->dropDownList([
                    'kaspi' => 'Kaspi',
                    'bank_transfer' => 'Банковский перевод',
                    'cash' => 'Наличные',
                    'card' => 'Карта',
                    'other' => 'Другое',
                ], [
                    'class' => 'form-control',
                    'prompt' => '-- Способ оплаты --',
                ])->label('Способ оплаты') ?>

                <?= $form->field($model, 'payment_reference')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'Номер транзакции, чека и т.д.',
                ])->label('Референс') ?>

                <?= $form->field($model, 'notes')->textarea([
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Дополнительные комментарии...',
                ])->label('Примечания') ?>

                <div class="form-group">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Создать платёж', ['class' => 'btn btn-primary']) ?>
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
                    Платёж создаётся со статусом <strong>Ожидает оплаты</strong>.
                </p>
                <p class="text-muted mb-2">
                    После подтверждения платежа подписка автоматически продлевается.
                </p>
                <p class="text-muted mb-0">
                    Период определяется автоматически на основе текущего срока подписки.
                </p>
            </div>
        </div>
    </div>
</div>
