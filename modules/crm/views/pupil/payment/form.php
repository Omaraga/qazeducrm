<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PayMethod;
use app\widgets\tailwind\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var \app\models\forms\PaymentForm $model */
/** @var \app\models\Pupil $pupil */

if ($model->type == Payment::TYPE_PAY) {
    $this->title = 'Добавить оплату';
    $subtitle = 'ученику ' . $pupil->fio;
} else {
    $this->title = 'Добавить возврат';
    $subtitle = 'ученику ' . $pupil->fio;
}

$this->params['breadcrumbs'][] = ['label' => 'Оплата', 'url' => OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id])];
$this->params['breadcrumbs'][] = $this->title;

// Convert datetime for HTML5 input
$dateValue = '';
if ($model->date) {
    $timestamp = strtotime($model->date);
    $dateValue = date('Y-m-d\TH:i', $timestamp);
} else {
    $dateValue = date('Y-m-d\TH:i');
}

$payMethods = ArrayHelper::map(PayMethod::find()->byOrganization()->all(), 'id', 'name');
$purposes = Payment::getPurposeList();

// Правила валидации
$validationRules = [
    'amount' => ['required' => true, 'min' => 1],
    'date' => ['required' => true],
];

if ($model->type == Payment::TYPE_PAY) {
    $validationRules['purpose_id'] = ['required' => true];
    $validationRules['method_id'] = ['required' => true];
}
?>

<?php
$hasPayMethods = !empty($payMethods);
$canAddPayment = $hasPayMethods || $model->type != Payment::TYPE_PAY;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Html::encode($subtitle) ?></p>
        </div>
        <div>
            <?= Html::a(
                Icon::show('arrow-left', 'sm') . ' Назад к оплатам',
                OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]),
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>

    <?php if (!$hasPayMethods && $model->type == Payment::TYPE_PAY): ?>
    <!-- Warning: No payment methods -->
    <div class="rounded-lg bg-warning-50 border border-warning-200 p-6">
        <div class="flex items-start gap-4">
            <?= Icon::show('alert', 'lg', 'text-warning-500 flex-shrink-0') ?>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-warning-800 mb-2">Не настроены способы оплаты</h3>
                <p class="text-warning-700 mb-3">
                    Для добавления оплаты необходимо сначала создать способы оплаты (наличные, карта, перевод и т.д.)
                </p>
                <?= Html::a(
                    Icon::show('plus', 'sm') . ' Создать способ оплаты',
                    OrganizationUrl::to(['pay-method/create']),
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="" method="post" class="space-y-6"
          x-data="formValidation(<?= Json::htmlEncode($validationRules) ?>)"
          @submit="handleSubmit($event)">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="PaymentForm[type]" value="<?= $model->type ?>">
        <input type="hidden" name="PaymentForm[pupil_id]" value="<?= $model->pupil_id ?>">

        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?php if ($model->type == Payment::TYPE_PAY): ?>
                        <?= Yii::t('main', 'Оплата') ?>
                    <?php else: ?>
                        <?= Yii::t('main', 'Возврат') ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($model->type == Payment::TYPE_PAY): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label form-label-required" for="paymentform-purpose_id">Назначение платежа</label>
                        <?= Html::activeDropDownList($model, 'purpose_id', $purposes, [
                            'class' => 'form-select',
                            'id' => 'paymentform-purpose_id',
                            ':class' => 'inputClass("purpose_id")',
                            '@change' => 'validateField("purpose_id", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('purpose_id')">
                            <p class="form-error-message" x-text="getError('purpose_id')"></p>
                        </template>
                        <?php if ($model->hasErrors('purpose_id')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('purpose_id') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label form-label-required" for="paymentform-method_id">Способ оплаты</label>
                        <?= Html::activeDropDownList($model, 'method_id', $payMethods, [
                            'class' => 'form-select',
                            'id' => 'paymentform-method_id',
                            ':class' => 'inputClass("method_id")',
                            '@change' => 'validateField("method_id", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('method_id')">
                            <p class="form-error-message" x-text="getError('method_id')"></p>
                        </template>
                        <?php if ($model->hasErrors('method_id')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('method_id') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php if ($model->type == Payment::TYPE_PAY): ?>
                    <div>
                        <label class="form-label" for="paymentform-number">Номер квитанции</label>
                        <?= Html::activeTextInput($model, 'number', [
                            'class' => 'form-input',
                            'id' => 'paymentform-number',
                            'placeholder' => 'Номер документа'
                        ]) ?>
                        <?php if ($model->hasErrors('number')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('number') ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="form-label form-label-required" for="paymentform-amount">Сумма (₸)</label>
                        <?= Html::activeTextInput($model, 'amount', [
                            'class' => 'form-input',
                            'id' => 'paymentform-amount',
                            'type' => 'number',
                            'min' => '1',
                            'placeholder' => '0',
                            ':class' => 'inputClass("amount")',
                            '@blur' => 'validateField("amount", $event.target.value)',
                        ]) ?>
                        <template x-if="hasError('amount')">
                            <p class="form-error-message" x-text="getError('amount')"></p>
                        </template>
                        <?php if ($model->hasErrors('amount')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('amount') ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="form-label form-label-required" for="paymentform-date">Дата и время</label>
                        <input type="datetime-local" name="PaymentForm[date]" id="paymentform-date"
                               class="form-input" value="<?= $dateValue ?>" autocomplete="off"
                               :class="inputClass('date')"
                               @blur="validateField('date', $event.target.value)">
                        <template x-if="hasError('date')">
                            <p class="form-error-message" x-text="getError('date')"></p>
                        </template>
                        <?php if ($model->hasErrors('date')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('date') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label" for="paymentform-comment">Комментарий</label>
                    <?= Html::activeTextarea($model, 'comment', [
                        'class' => 'form-input',
                        'id' => 'paymentform-comment',
                        'rows' => 3,
                        'placeholder' => 'Дополнительная информация...'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit" class="btn <?= $model->type == Payment::TYPE_PAY ? 'btn-primary' : 'btn-warning' ?>" :disabled="isSubmitting">
                <template x-if="!isSubmitting">
                    <?= Icon::show('check', 'sm') ?>
                </template>
                <template x-if="isSubmitting">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isSubmitting ? 'Сохранение...' : 'Сохранить'"></span>
            </button>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
