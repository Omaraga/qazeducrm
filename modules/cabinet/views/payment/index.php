<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var float $totalBalance */

use app\models\Payment;
use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = Yii::t('app', 'Платежи');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('wallet', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Платежи') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/payment/balance', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('calculator', 'sm') ?>
            <?= Yii::t('app', 'Баланс') ?>
        </a>
    </div>

    <!-- Total Balance Card -->
    <div class="card-glass-solid lg:card-glass-solid-desktop p-5 lg:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm lg:text-base text-gray-500"><?= Yii::t('app', 'Общий баланс') ?></p>
                <p class="text-2xl lg:text-4xl font-bold <?= $totalBalance >= 0 ? 'text-green-600' : 'text-red-600' ?> mt-1">
                    <?= Yii::$app->formatter->asCurrency($totalBalance, 'KZT') ?>
                </p>
            </div>
            <div class="w-14 h-14 lg:w-16 lg:h-16 rounded-2xl <?= $totalBalance >= 0 ? 'bg-green-100' : 'bg-red-100' ?> flex items-center justify-center">
                <?= Icon::show($totalBalance >= 0 ? 'arrow-trending-up' : 'arrow-trending-down', 'lg', $totalBalance >= 0 ? 'text-green-600' : 'text-red-600') ?>
            </div>
        </div>
    </div>

    <!-- Individual Balance Cards (if multiple pupils) -->
    <?php if (count($pupils) > 1): ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <?php foreach ($pupils as $pupil): ?>
                <div class="card-glass-solid lg:card-glass-solid-desktop p-4">
                    <div class="flex items-center gap-3">
                        <div class="avatar-ios-sm">
                            <?= mb_substr($pupil->first_name, 0, 1) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?= Html::encode($pupil->first_name) ?></p>
                            <p class="text-sm font-semibold <?= $pupil->balance >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                <?= Yii::$app->formatter->asCurrency($pupil->balance, 'KZT') ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pupil Selector (Segment Control) -->
    <?php if (count($pupils) > 1): ?>
        <div class="segment-control">
            <a href="<?= Url::to(['/cabinet/payment/index', 'org' => $orgId]) ?>"
               class="segment-item <?= !$selectedPupil ? 'active' : '' ?>">
                <?= Yii::t('app', 'Все') ?>
            </a>
            <?php foreach ($pupils as $pupil): ?>
                <a href="<?= Url::to(['/cabinet/payment/index', 'org' => $orgId, 'pupil_id' => $pupil->id]) ?>"
                   class="segment-item <?= $selectedPupil && $selectedPupil->id == $pupil->id ? 'active' : '' ?>">
                    <?= Html::encode($pupil->first_name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Payments History -->
    <div class="card-glass-solid lg:card-glass-solid-desktop overflow-hidden">
        <div class="section-header-ios lg:py-3 lg:px-6">
            <?= Yii::t('app', 'История платежей') ?>
        </div>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => function ($payment) use ($orgId) {
                $isIncoming = $payment->type == Payment::TYPE_PAY;
                $iconName = $isIncoming ? 'arrow-down-tray' : 'arrow-up-tray';
                $iconBgClass = $isIncoming ? 'bg-green-50' : 'bg-red-50';
                $iconClass = $isIncoming ? 'text-green-600' : 'text-red-600';
                $amountClass = $isIncoming ? 'text-green-600' : 'text-red-600';
                $sign = $isIncoming ? '+' : '-';

                return '
                <a href="' . Url::to(['/cabinet/payment/view', 'org' => $orgId, 'id' => $payment->id]) . '"
                   class="list-item-ios lg:list-item-ios-desktop border-b border-gray-100 last:border-0 lg:px-6">
                    <div class="w-11 h-11 rounded-xl ' . $iconBgClass . ' flex items-center justify-center flex-shrink-0">
                        ' . \app\modules\cabinet\widgets\Icon::show($iconName, 'sm', $iconClass) . '
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 lg:text-base">' . Yii::$app->formatter->asDate($payment->date, 'long') . '</p>
                        <p class="text-sm text-gray-500 truncate">' . Html::encode($payment->pupil->fio ?? '') . '</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-semibold lg:text-lg ' . $amountClass . '">' . $sign . Yii::$app->formatter->asCurrency($payment->amount, 'KZT') . '</p>
                    </div>
                    ' . \app\modules\cabinet\widgets\Icon::show('chevron-right', 'sm', 'text-gray-300 flex-shrink-0') . '
                </a>';
            },
            'layout' => "{items}\n<div class='px-4 py-3 border-t border-gray-100'>{pager}</div>",
            'summary' => '',
            'emptyText' => '
                <div class="empty-ios py-12">
                    <div class="empty-ios-icon">
                        ' . Icon::show('banknotes', 'xl', 'text-gray-300') . '
                    </div>
                    <p class="empty-ios-text">' . Yii::t('app', 'Нет платежей') . '</p>
                </div>',
            'emptyTextOptions' => ['class' => ''],
            'pager' => [
                'class' => \app\widgets\tailwind\LinkPager::class,
            ],
        ]) ?>
    </div>
</div>
