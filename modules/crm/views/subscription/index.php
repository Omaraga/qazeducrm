<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\services\SubscriptionAccessService $accessService
 * @var app\services\SubscriptionLimitService $limitService
 * @var app\models\SaasPlan[] $plans
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LimitProgress;

$this->title = Yii::t('main', 'Подписка');
$this->params['breadcrumbs'][] = $this->title;

$accessMode = $accessService->getAccessMode();
$plan = $subscription ? $subscription->saasPlan : null;
?>

<div class="subscription-index">
    <!-- Статус подписки -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Ваша подписка</h2>
            <?php if ($subscription): ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?= $subscription->getAccessModeBadgeClass() ?>">
                    <?= Html::encode($subscription->getAccessModeLabel()) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($subscription && $plan): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Текущий план -->
                <div>
                    <div class="text-sm text-gray-500 mb-1">Текущий тариф</div>
                    <div class="text-lg font-semibold text-gray-900"><?= Html::encode($plan->name) ?></div>
                    <div class="text-sm text-gray-500">
                        <?= number_format($plan->price_monthly, 0, '', ' ') ?> KZT/мес
                    </div>
                </div>

                <!-- Статус -->
                <div>
                    <div class="text-sm text-gray-500 mb-1">Статус</div>
                    <div class="text-lg font-semibold text-gray-900"><?= Html::encode($subscription->getStatusLabel()) ?></div>
                    <?php if ($subscription->isTrial()): ?>
                        <div class="text-sm text-blue-600">Пробный период</div>
                    <?php endif; ?>
                </div>

                <!-- Дата окончания -->
                <div>
                    <div class="text-sm text-gray-500 mb-1">Действует до</div>
                    <?php $daysRemaining = $subscription->getDaysRemaining(); ?>
                    <div class="text-lg font-semibold <?= $daysRemaining <= 7 ? 'text-warning-600' : 'text-gray-900' ?>">
                        <?= Yii::$app->formatter->asDate($subscription->expires_at, 'long') ?>
                    </div>
                    <div class="text-sm <?= $daysRemaining <= 3 ? 'text-danger-600' : 'text-gray-500' ?>">
                        <?php if ($daysRemaining > 0): ?>
                            Осталось <?= $daysRemaining ?> дн.
                        <?php elseif ($daysRemaining === 0): ?>
                            Истекает сегодня
                        <?php else: ?>
                            Истекла
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="mt-6 pt-6 border-t flex flex-wrap gap-3">
                <?php if ($subscription->isExpired() || $subscription->isExpiringSoon(7)): ?>
                    <a href="<?= OrganizationUrl::to(['subscription/renew']) ?>"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Продлить подписку
                    </a>
                <?php endif; ?>

                <a href="<?= OrganizationUrl::to(['subscription/upgrade']) ?>"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Увеличить лимиты
                </a>

                <a href="<?= OrganizationUrl::to(['subscription/payments']) ?>"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    История платежей
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Нет активной подписки</h3>
                <p class="text-gray-500 mb-4">Выберите тарифный план для начала работы</p>
                <a href="<?= OrganizationUrl::to(['subscription/plans']) ?>"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    Выбрать тариф
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Использование лимитов -->
    <?php if ($subscription && $limitService): ?>
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Использование лимитов</h2>
            <a href="<?= OrganizationUrl::to(['subscription/usage']) ?>" class="text-sm text-primary-600 hover:text-primary-700">
                Подробнее →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?= LimitProgress::widget(['type' => 'pupils', 'showAction' => true]) ?>
            <?= LimitProgress::widget(['type' => 'groups', 'showAction' => true]) ?>
            <?= LimitProgress::widget(['type' => 'teachers', 'showAction' => true]) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Доступные тарифы -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Доступные тарифы</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($plans as $planItem): ?>
                <?php
                $isCurrent = $plan && $plan->id === $planItem->id;
                $cardClass = $isCurrent ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300';
                ?>
                <div class="border rounded-lg p-4 <?= $cardClass ?> transition">
                    <?php if ($isCurrent): ?>
                        <span class="inline-block px-2 py-1 text-xs font-medium bg-primary-600 text-white rounded mb-2">
                            Текущий
                        </span>
                    <?php endif; ?>

                    <h3 class="text-lg font-semibold text-gray-900"><?= Html::encode($planItem->name) ?></h3>

                    <div class="mt-2">
                        <span class="text-2xl font-bold text-gray-900">
                            <?= number_format($planItem->price_monthly, 0, '', ' ') ?>
                        </span>
                        <span class="text-gray-500">KZT/мес</span>
                    </div>

                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <?= $planItem->max_pupils == 0 ? 'Безлимит' : $planItem->max_pupils ?> учеников
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <?= $planItem->max_groups == 0 ? 'Безлимит' : $planItem->max_groups ?> групп
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <?= $planItem->max_teachers == 0 ? 'Безлимит' : $planItem->max_teachers ?> учителей
                        </li>
                    </ul>

                    <?php if (!$isCurrent): ?>
                        <a href="<?= OrganizationUrl::to(['subscription/plans', 'plan' => $planItem->id]) ?>"
                           class="mt-4 block text-center px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition">
                            Выбрать
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
