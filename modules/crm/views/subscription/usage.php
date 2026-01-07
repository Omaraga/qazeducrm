<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\services\SubscriptionLimitService $limitService
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'Использование лимитов');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$plan = $subscription ? $subscription->saasPlan : null;
?>

<div class="subscription-usage">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-600">Детальная информация о использовании ресурсов</p>
        </div>
        <a href="<?= OrganizationUrl::to(['subscription/index']) ?>"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            ← К подписке
        </a>
    </div>

    <?php if ($subscription && $plan): ?>
        <!-- Текущий план -->
        <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900"><?= Html::encode($plan->name) ?></div>
                        <div class="text-sm text-gray-500">
                            Действует до <?= Yii::$app->formatter->asDate($subscription->expires_at, 'long') ?>
                        </div>
                    </div>
                </div>
                <a href="<?= OrganizationUrl::to(['subscription/upgrade']) ?>"
                   class="text-sm text-primary-600 hover:text-primary-700">
                    Увеличить лимиты →
                </a>
            </div>
        </div>

        <!-- Детальная статистика -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Ученики -->
            <?php
            $pupils = $limitService->getCurrentCount('max_pupils');
            $pupilsLimit = $limitService->getLimit('max_pupils');
            $pupilsPercent = $limitService->getUsagePercent('max_pupils');
            $pupilsProgressColor = $pupilsPercent >= 90 ? 'bg-danger-500' : ($pupilsPercent >= 70 ? 'bg-warning-500' : 'bg-success-500');
            ?>
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Ученики</h3>
                    </div>
                    <a href="<?= OrganizationUrl::to(['pupil/index']) ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Перейти →
                    </a>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Использовано</span>
                        <span class="font-medium text-gray-900">
                            <?= $pupils ?> / <?= $pupilsLimit > 0 ? $pupilsLimit : '∞' ?>
                        </span>
                    </div>
                    <?php if ($pupilsLimit > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="<?= $pupilsProgressColor ?> h-3 rounded-full transition-all" style="width: <?= min($pupilsPercent, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-gray-500"><?= round($pupilsPercent) ?>%</span>
                            <?php if ($pupilsLimit - $pupils > 0): ?>
                                <span class="text-gray-500">Осталось: <?= $pupilsLimit - $pupils ?></span>
                            <?php else: ?>
                                <span class="text-danger-600 font-medium">Лимит достигнут</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-success-600">Безлимитно</div>
                    <?php endif; ?>
                </div>

                <?php if ($pupilsPercent >= 90 && $pupilsLimit > 0): ?>
                    <div class="p-3 bg-warning-50 border border-warning-200 rounded-lg">
                        <p class="text-sm text-warning-800">
                            Вы приближаетесь к лимиту. Рекомендуем увеличить план.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Группы -->
            <?php
            $groups = $limitService->getCurrentCount('max_groups');
            $groupsLimit = $limitService->getLimit('max_groups');
            $groupsPercent = $limitService->getUsagePercent('max_groups');
            $groupsProgressColor = $groupsPercent >= 90 ? 'bg-danger-500' : ($groupsPercent >= 70 ? 'bg-warning-500' : 'bg-success-500');
            ?>
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Группы</h3>
                    </div>
                    <a href="<?= OrganizationUrl::to(['group/index']) ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Перейти →
                    </a>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Использовано</span>
                        <span class="font-medium text-gray-900">
                            <?= $groups ?> / <?= $groupsLimit > 0 ? $groupsLimit : '∞' ?>
                        </span>
                    </div>
                    <?php if ($groupsLimit > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="<?= $groupsProgressColor ?> h-3 rounded-full transition-all" style="width: <?= min($groupsPercent, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-gray-500"><?= round($groupsPercent) ?>%</span>
                            <?php if ($groupsLimit - $groups > 0): ?>
                                <span class="text-gray-500">Осталось: <?= $groupsLimit - $groups ?></span>
                            <?php else: ?>
                                <span class="text-danger-600 font-medium">Лимит достигнут</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-success-600">Безлимитно</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Учителя -->
            <?php
            $teachers = $limitService->getCurrentCount('max_teachers');
            $teachersLimit = $limitService->getLimit('max_teachers');
            $teachersPercent = $limitService->getUsagePercent('max_teachers');
            $teachersProgressColor = $teachersPercent >= 90 ? 'bg-danger-500' : ($teachersPercent >= 70 ? 'bg-warning-500' : 'bg-success-500');
            ?>
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Учителя</h3>
                    </div>
                    <a href="<?= OrganizationUrl::to(['user/teachers']) ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Перейти →
                    </a>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Использовано</span>
                        <span class="font-medium text-gray-900">
                            <?= $teachers ?> / <?= $teachersLimit > 0 ? $teachersLimit : '∞' ?>
                        </span>
                    </div>
                    <?php if ($teachersLimit > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="<?= $teachersProgressColor ?> h-3 rounded-full transition-all" style="width: <?= min($teachersPercent, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-gray-500"><?= round($teachersPercent) ?>%</span>
                            <?php if ($teachersLimit - $teachers > 0): ?>
                                <span class="text-gray-500">Осталось: <?= $teachersLimit - $teachers ?></span>
                            <?php else: ?>
                                <span class="text-danger-600 font-medium">Лимит достигнут</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-success-600">Безлимитно</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Администраторы -->
            <?php
            $admins = $limitService->getCurrentCount('max_admins');
            $adminsLimit = $limitService->getLimit('max_admins');
            $adminsPercent = $limitService->getUsagePercent('max_admins');
            $adminsProgressColor = $adminsPercent >= 90 ? 'bg-danger-500' : ($adminsPercent >= 70 ? 'bg-warning-500' : 'bg-success-500');
            ?>
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Администраторы</h3>
                    </div>
                    <a href="<?= OrganizationUrl::to(['user/admins']) ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Перейти →
                    </a>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Использовано</span>
                        <span class="font-medium text-gray-900">
                            <?= $admins ?> / <?= $adminsLimit > 0 ? $adminsLimit : '∞' ?>
                        </span>
                    </div>
                    <?php if ($adminsLimit > 0): ?>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="<?= $adminsProgressColor ?> h-3 rounded-full transition-all" style="width: <?= min($adminsPercent, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-gray-500"><?= round($adminsPercent) ?>%</span>
                            <?php if ($adminsLimit - $admins > 0): ?>
                                <span class="text-gray-500">Осталось: <?= $adminsLimit - $admins ?></span>
                            <?php else: ?>
                                <span class="text-danger-600 font-medium">Лимит достигнут</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-success-600">Безлимитно</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Рекомендации -->
        <?php
        $needsUpgrade = ($pupilsPercent >= 80 || $groupsPercent >= 80 || $teachersPercent >= 80 || $adminsPercent >= 80);
        ?>
        <?php if ($needsUpgrade): ?>
            <div class="mt-6 bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Приближаетесь к лимитам?</h3>
                        <p class="mt-1 text-primary-100">
                            Увеличьте лимиты, перейдя на более высокий тариф или докупив дополнительный пакет.
                        </p>
                    </div>
                    <a href="<?= OrganizationUrl::to(['subscription/upgrade']) ?>"
                       class="px-6 py-2 bg-white text-primary-600 font-medium rounded-lg hover:bg-gray-100 transition">
                        Увеличить лимиты
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Нет подписки -->
        <div class="bg-white rounded-lg shadow-sm border p-12 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Нет активной подписки</h3>
            <p class="text-gray-500 mb-4">Выберите тариф для просмотра использования лимитов</p>
            <a href="<?= OrganizationUrl::to(['subscription/plans']) ?>"
               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Выбрать тариф
            </a>
        </div>
    <?php endif; ?>
</div>
