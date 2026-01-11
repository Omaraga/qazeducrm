<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var array $balanceData */

use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Детали баланса');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('calculator', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Баланс') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/payment/index', 'org' => $orgId]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('arrow-left', 'sm') ?>
            <?= Yii::t('app', 'Назад') ?>
        </a>
    </div>

    <!-- Balance Cards for Each Pupil -->
    <?php foreach ($balanceData as $data): ?>
        <?php
        $pupil = $data['pupil'];
        $educations = $data['educations'];
        $totalPaid = $data['totalPaid'];
        $totalRefund = $data['totalRefund'];
        $totalCharged = $data['totalCharged'];
        $balance = $data['balance'];
        ?>

        <div class="card-glass-solid overflow-hidden">
            <!-- Pupil Header -->
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3 bg-gray-50">
                <div class="avatar-ios-md">
                    <?= mb_substr($pupil->first_name, 0, 1) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-semibold text-gray-900 truncate">
                        <?= Html::encode($pupil->fio ?: $pupil->first_name . ' ' . $pupil->last_name) ?>
                    </h2>
                    <p class="text-sm <?= $balance >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= Yii::t('app', 'Баланс') ?>: <?= Yii::$app->formatter->asCurrency($balance, 'KZT') ?>
                    </p>
                </div>
            </div>

            <div class="p-4">
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-green-50 rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-2">
                            <?= Icon::show('arrow-down-tray', 'sm', 'text-green-600') ?>
                        </div>
                        <p class="text-xs text-green-600 font-medium"><?= Yii::t('app', 'Оплачено') ?></p>
                        <p class="text-lg font-bold text-green-700"><?= Yii::$app->formatter->asCurrency($totalPaid, 'KZT') ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-4 text-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center mx-auto mb-2">
                            <?= Icon::show('document-text', 'sm', 'text-gray-600') ?>
                        </div>
                        <p class="text-xs text-gray-500 font-medium"><?= Yii::t('app', 'Начислено') ?></p>
                        <p class="text-lg font-bold text-gray-700"><?= Yii::$app->formatter->asCurrency($totalCharged, 'KZT') ?></p>
                    </div>
                </div>

                <?php if ($totalRefund > 0): ?>
                    <div class="bg-red-50 rounded-2xl p-4 text-center mb-5">
                        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center mx-auto mb-2">
                            <?= Icon::show('arrow-up-tray', 'sm', 'text-red-600') ?>
                        </div>
                        <p class="text-xs text-red-600 font-medium"><?= Yii::t('app', 'Возвраты') ?></p>
                        <p class="text-lg font-bold text-red-700"><?= Yii::$app->formatter->asCurrency($totalRefund, 'KZT') ?></p>
                    </div>
                <?php endif; ?>

                <!-- Educations List -->
                <div class="section-header-ios -mx-4 px-4">
                    <?= Yii::t('app', 'Обучения') ?>
                </div>

                <?php if (empty($educations)): ?>
                    <div class="empty-ios py-8">
                        <p class="empty-ios-text"><?= Yii::t('app', 'Нет записей об обучении') ?></p>
                    </div>
                <?php else: ?>
                    <div class="list-group-ios -mx-4">
                        <?php foreach ($educations as $education): ?>
                            <div class="list-item-ios border-b border-gray-100 last:border-0">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <?= Icon::show('academic-cap', 'sm', 'text-indigo-600') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900"><?= Html::encode($education->tariff->name ?? '-') ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?= Yii::$app->formatter->asDate($education->date_start, 'short') ?>
                                        -
                                        <?= Yii::$app->formatter->asDate($education->date_end, 'short') ?>
                                    </p>
                                    <?php
                                    $groupNames = [];
                                    foreach ($education->groups as $eg) {
                                        if ($eg->group) {
                                            $groupNames[] = Html::encode($eg->group->name);
                                        }
                                    }
                                    if (!empty($groupNames)): ?>
                                        <p class="text-xs text-gray-400 mt-1"><?= implode(', ', $groupNames) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-semibold text-gray-900"><?= Yii::$app->formatter->asCurrency($education->total_price, 'KZT') ?></p>
                                    <?php if ($education->sale > 0): ?>
                                        <span class="badge-ios bg-green-100 text-green-700 text-xs">-<?= $education->sale ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
