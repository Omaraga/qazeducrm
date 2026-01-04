<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PayMethod;
use app\models\Pupil;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$pupils = Pupil::find()->byOrganization()->notDeleted()->orderBy(['fio' => SORT_ASC])->all();
$payMethods = PayMethod::find()->byOrganization()->notDeleted()->orderBy(['name' => SORT_ASC])->all();
?>

<form action="" method="post" class="space-y-6">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Payment Data -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Данные платежа</h3>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="payment-type">Тип операции <span class="text-danger-500">*</span></label>
                    <?= Html::activeDropDownList($model, 'type', Payment::getTypeList(), [
                        'class' => 'form-select',
                        'id' => 'payment-type',
                        'prompt' => 'Выберите тип'
                    ]) ?>
                    <?php if ($model->hasErrors('type')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('type') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="payment-amount">Сумма <span class="text-danger-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="Payment[amount]" id="payment-amount"
                               class="form-input pr-12" step="0.01" min="0"
                               value="<?= Html::encode($model->amount) ?>">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">₸</span>
                    </div>
                    <?php if ($model->hasErrors('amount')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('amount') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="payment-pupil_id">Ученик <span class="text-danger-500">*</span></label>
                    <?= Html::activeDropDownList($model, 'pupil_id', ArrayHelper::map($pupils, 'id', 'fio'), [
                        'class' => 'form-select',
                        'id' => 'payment-pupil_id',
                        'prompt' => 'Выберите ученика'
                    ]) ?>
                    <?php if ($model->hasErrors('pupil_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('pupil_id') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="payment-date">Дата <span class="text-danger-500">*</span></label>
                    <input type="datetime-local" name="Payment[date]" id="payment-date" class="form-input"
                           value="<?= $model->date ? date('Y-m-d\TH:i', strtotime($model->date)) : date('Y-m-d\TH:i') ?>">
                    <?php if ($model->hasErrors('date')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="payment-purpose_id">Назначение</label>
                    <?= Html::activeDropDownList($model, 'purpose_id', Payment::getPurposeList(), [
                        'class' => 'form-select',
                        'id' => 'payment-purpose_id',
                        'prompt' => 'Выберите назначение'
                    ]) ?>
                    <?php if ($model->hasErrors('purpose_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('purpose_id') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-label" for="payment-method_id">Способ оплаты</label>
                    <?= Html::activeDropDownList($model, 'method_id', ArrayHelper::map($payMethods, 'id', 'name'), [
                        'class' => 'form-select',
                        'id' => 'payment-method_id',
                        'prompt' => 'Выберите способ'
                    ]) ?>
                    <?php if ($model->hasErrors('method_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('method_id') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="form-label" for="payment-number">Номер квитанции</label>
                <input type="text" name="Payment[number]" id="payment-number" class="form-input"
                       value="<?= Html::encode($model->number) ?>" maxlength="255">
                <?php if ($model->hasErrors('number')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('number') ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="form-label" for="payment-comment">Комментарий</label>
                <textarea name="Payment[comment]" id="payment-comment" rows="3" class="form-input"><?= Html::encode($model->comment) ?></textarea>
                <?php if ($model->hasErrors('comment')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('comment') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <?php if ($model->id): ?>
        <?= Html::a('Удалить', OrganizationUrl::to(['payment/delete', 'id' => $model->id]), [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить этот платеж?',
                'method' => 'post',
            ],
        ]) ?>
        <?php else: ?>
        <div></div>
        <?php endif; ?>
        <div class="flex items-center gap-3">
            <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">Отмена</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
            </button>
        </div>
    </div>
</form>
