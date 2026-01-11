<?php

/** @var yii\web\View $this */
/** @var app\models\Payment $payment */

use app\models\Payment;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Детали платежа');
$orgId = Module::getOrganizationId();

$isIncoming = $payment->type == Payment::TYPE_PAY;
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('document-text', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Платеж') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/payment/index', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('arrow-left', 'sm') ?>
            <?= Yii::t('app', 'Назад') ?>
        </a>
    </div>

    <!-- Amount Card -->
    <div class="card-glass-solid p-6 text-center">
        <div class="w-16 h-16 rounded-2xl <?= $isIncoming ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center mx-auto mb-4">
            <?= Icon::show($isIncoming ? 'arrow-down-tray' : 'arrow-up-tray', 'xl', $isIncoming ? 'text-green-600' : 'text-red-600') ?>
        </div>
        <p class="text-3xl font-bold <?= $isIncoming ? 'text-green-600' : 'text-red-600' ?>">
            <?= $isIncoming ? '+' : '-' ?><?= Yii::$app->formatter->asCurrency($payment->amount, 'KZT') ?>
        </p>
        <p class="text-gray-500 mt-2"><?= Yii::$app->formatter->asDate($payment->date, 'long') ?></p>

        <!-- Status Badge -->
        <div class="mt-4">
            <?php if ($payment->type == Payment::TYPE_PAY): ?>
                <span class="badge-ios bg-green-100 text-green-700 text-sm">
                    <?= Icon::show('check-circle', 'xs') ?>
                    <?= Yii::t('app', 'Оплата') ?>
                </span>
            <?php elseif ($payment->type == Payment::TYPE_REFUND): ?>
                <span class="badge-ios bg-red-100 text-red-700 text-sm">
                    <?= Icon::show('arrow-uturn-left', 'xs') ?>
                    <?= Yii::t('app', 'Возврат') ?>
                </span>
            <?php else: ?>
                <span class="badge-ios bg-gray-100 text-gray-700 text-sm">
                    <?= Icon::show('minus-circle', 'xs') ?>
                    <?= Yii::t('app', 'Расход') ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Details Card -->
    <div class="card-glass-solid overflow-hidden">
        <div class="section-header-ios">
            <?= Yii::t('app', 'Детали') ?>
        </div>

        <div class="list-group-ios">
            <!-- Pupil -->
            <div class="list-item-ios border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <?= Icon::show('user', 'sm', 'text-indigo-600') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500"><?= Yii::t('app', 'Ученик') ?></p>
                    <p class="font-medium text-gray-900"><?= Html::encode($payment->pupil->fio ?? '-') ?></p>
                </div>
            </div>

            <!-- Date -->
            <div class="list-item-ios border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <?= Icon::show('calendar', 'sm', 'text-gray-600') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-500"><?= Yii::t('app', 'Дата') ?></p>
                    <p class="font-medium text-gray-900"><?= Yii::$app->formatter->asDate($payment->date, 'long') ?></p>
                </div>
            </div>

            <!-- Payment Method -->
            <?php if ($payment->payMethod): ?>
                <div class="list-item-ios border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <?= Icon::show('credit-card', 'sm', 'text-purple-600') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500"><?= Yii::t('app', 'Способ оплаты') ?></p>
                        <p class="font-medium text-gray-900"><?= Html::encode($payment->payMethod->name ?? '-') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comment -->
            <?php if ($payment->comment): ?>
                <div class="list-item-ios">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <?= Icon::show('chat-bubble-bottom-center-text', 'sm', 'text-amber-600') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-500"><?= Yii::t('app', 'Комментарий') ?></p>
                        <p class="font-medium text-gray-900"><?= Html::encode($payment->comment) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card-glass-solid p-4">
        <a href="<?= Url::to(['/cabinet/payment/receipt', 'org' => $orgId, 'id' => $payment->id]) ?>"
           target="_blank"
           class="btn-ios-primary w-full justify-center">
            <?= Icon::show('document-arrow-down', 'sm') ?>
            <?= Yii::t('app', 'Скачать квитанцию') ?>
        </a>
    </div>
</div>
