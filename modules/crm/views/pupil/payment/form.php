<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PayMethod;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Html::encode($subtitle) ?></p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к оплатам
            </a>
        </div>
    </div>

    <form action="" method="post" class="space-y-6">
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
                        <label class="form-label" for="paymentform-purpose_id">Назначение платежа <span class="text-danger-500">*</span></label>
                        <?= Html::activeDropDownList($model, 'purpose_id', $purposes, [
                            'class' => 'form-select',
                            'id' => 'paymentform-purpose_id',
                        ]) ?>
                        <?php if ($model->hasErrors('purpose_id')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('purpose_id') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="paymentform-method_id">Способ оплаты <span class="text-danger-500">*</span></label>
                        <?= Html::activeDropDownList($model, 'method_id', $payMethods, [
                            'class' => 'form-select',
                            'id' => 'paymentform-method_id',
                        ]) ?>
                        <?php if ($model->hasErrors('method_id')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('method_id') ?></p>
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
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('number') ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="form-label" for="paymentform-amount">Сумма (₸) <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'amount', [
                            'class' => 'form-input',
                            'id' => 'paymentform-amount',
                            'type' => 'number',
                            'min' => '0',
                            'placeholder' => '0'
                        ]) ?>
                        <?php if ($model->hasErrors('amount')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('amount') ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="form-label" for="paymentform-date">Дата и время <span class="text-danger-500">*</span></label>
                        <input type="datetime-local" name="PaymentForm[date]" id="paymentform-date" class="form-input" value="<?= $dateValue ?>" autocomplete="off">
                        <?php if ($model->hasErrors('date')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date') ?></p>
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
            <button type="submit" class="btn <?= $model->type == Payment::TYPE_PAY ? 'btn-primary' : 'btn-warning' ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?= Yii::t('main', 'Сохранить') ?>
            </button>
            <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
