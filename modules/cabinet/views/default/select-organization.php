<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations[] $organizations */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Выбор организации');
?>

<div class="flex justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= Yii::t('app', 'Личный кабинет') ?></h1>
                    <p class="text-gray-500 mt-2"><?= Yii::t('app', 'Выберите учебный центр') ?></p>
                </div>

                <!-- Organizations List -->
                <?php if (empty($organizations)): ?>
                    <div class="bg-warning-50 border border-warning-200 rounded-xl p-4 text-warning-800">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span><?= Yii::t('app', 'Нет доступных организаций') ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($organizations as $org): ?>
                            <a href="<?= Url::to(['/cabinet/default/login', 'org' => $org->id]) ?>"
                               class="group flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all duration-200">
                                <?php if ($org->logo): ?>
                                    <img src="<?= Html::encode($org->logo) ?>" alt="" class="w-12 h-12 rounded-xl object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                        <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate"><?= Html::encode($org->name) ?></h3>
                                    <?php if ($org->address): ?>
                                        <p class="text-sm text-gray-500 truncate"><?= Html::encode($org->address) ?></p>
                                    <?php endif; ?>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Back Link -->
                <div class="text-center mt-6">
                    <a href="<?= Url::to(['/']) ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <?= Yii::t('app', 'На главную') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
