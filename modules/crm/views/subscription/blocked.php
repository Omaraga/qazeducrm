<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\models\SaasPlan[] $plans
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'Доступ заблокирован');
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-2xl w-full">
        <!-- Заголовок -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-danger-100 mb-4">
                <svg class="w-10 h-10 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Доступ заблокирован</h1>
            <p class="text-gray-600">
                Подписка организации <strong><?= Html::encode($organization->name) ?></strong> истекла.
            </p>
        </div>

        <!-- Информация -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-900">Что произошло?</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ваша подписка истекла и прошёл льготный период. Для восстановления доступа необходимо оплатить подписку.
                    </p>
                </div>
            </div>

            <div class="mt-4 flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-900">Ваши данные в безопасности</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Все данные организации сохранены. После оплаты вы получите полный доступ ко всей информации.
                    </p>
                </div>
            </div>
        </div>

        <!-- Тарифы -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Выберите тариф для продолжения</h2>

            <div class="space-y-3">
                <?php foreach ($plans as $plan): ?>
                    <div class="flex items-center justify-between p-4 border rounded-lg hover:border-primary-300 transition cursor-pointer"
                         onclick="selectPlan(<?= $plan->id ?>)">
                        <div>
                            <div class="font-medium text-gray-900"><?= Html::encode($plan->name) ?></div>
                            <div class="text-sm text-gray-500">
                                <?= $plan->max_pupils == 0 ? '∞' : $plan->max_pupils ?> учеников,
                                <?= $plan->max_groups == 0 ? '∞' : $plan->max_groups ?> групп
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-gray-900">
                                <?= number_format($plan->price_monthly, 0, '', ' ') ?> KZT
                            </div>
                            <div class="text-sm text-gray-500">в месяц</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Действия -->
        <div class="text-center space-y-4">
            <a href="<?= OrganizationUrl::to(['subscription/renew']) ?>"
               class="inline-flex items-center justify-center w-full px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Оплатить подписку
            </a>

            <p class="text-sm text-gray-500">
                Нужна помощь?
                <a href="mailto:support@qazeducrm.kz" class="text-primary-600 hover:text-primary-700">
                    Свяжитесь с поддержкой
                </a>
            </p>
        </div>
    </div>
</div>

<script>
function selectPlan(planId) {
    window.location.href = '<?= OrganizationUrl::to(['subscription/renew']) ?>?plan=' + planId;
}
</script>
