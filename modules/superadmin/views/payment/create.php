<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationPayment $model */
/** @var app\models\Organizations[] $organizations */
/** @var app\models\OrganizationSubscription[] $subscriptions */
/** @var array $managers */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\OrganizationPayment;

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

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'original_amount', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">KZT</span></div></div>{error}{hint}',
                        ])->textInput(['type' => 'number', 'class' => 'form-control'])->label('Сумма до скидки') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'amount', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">KZT</span></div></div>{error}{hint}',
                        ])->textInput(['type' => 'number', 'class' => 'form-control'])->label('Сумма к оплате') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'discount_type')->dropDownList(
                            OrganizationPayment::getDiscountTypeList(),
                            [
                                'class' => 'form-control',
                                'prompt' => '-- Без скидки --',
                            ]
                        )->label('Тип скидки') ?>
                    </div>
                    <div class="col-md-6">
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
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-primary mb-3"><i class="fas fa-user-tie"></i> Менеджер продаж</h6>

                <div class="row">
                    <div class="col-md-8">
                        <?= $form->field($model, 'manager_id')->dropDownList(
                            $managers,
                            [
                                'class' => 'form-control',
                                'prompt' => '-- Без менеджера --',
                            ]
                        )->label('Менеджер') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'manager_bonus_percent', [
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text">%</span></div></div>{error}{hint}',
                        ])->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'min' => '0',
                            'max' => '100',
                            'class' => 'form-control',
                        ])->label('Бонус') ?>
                    </div>
                </div>

                <hr class="my-4">

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
                <p class="text-muted mb-2">
                    Период определяется автоматически на основе текущего срока подписки.
                </p>
                <hr>
                <p class="text-muted mb-2">
                    <strong>Менеджер:</strong> Если указан менеджер, при подтверждении платежа автоматически рассчитается его бонус.
                </p>
                <p class="text-muted mb-0">
                    <strong>Скидка:</strong> Укажите сумму до скидки и итоговую сумму для учёта скидок в отчётах.
                </p>
            </div>
        </div>

        <?php if (!empty($managers)): ?>
        <div class="card card-custom mt-3">
            <div class="card-header">Менеджеры</div>
            <div class="card-body">
                <p class="text-muted small mb-0">
                    В списке отображаются сотрудники, которые ранее были назначены менеджерами платежей
                    или имеют соответствующую роль.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
