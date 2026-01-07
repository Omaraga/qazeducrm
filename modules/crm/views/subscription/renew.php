<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\models\SaasPlan[] $plans
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'Продление подписки');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$selectedPlanId = Yii::$app->request->get('plan');
$currentPlan = $subscription ? $subscription->saasPlan : null;
?>

<div class="subscription-renew max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-600">Выберите тариф и период оплаты</p>
    </div>

    <!-- Текущая подписка -->
    <?php if ($subscription && $currentPlan): ?>
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-500">Текущий тариф:</span>
                <span class="font-medium text-gray-900"><?= Html::encode($currentPlan->name) ?></span>
            </div>
            <div>
                <span class="text-sm text-gray-500">Действует до:</span>
                <span class="font-medium <?= $subscription->isExpired() ? 'text-danger-600' : 'text-gray-900' ?>">
                    <?= Yii::$app->formatter->asDate($subscription->expires_at) ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?= OrganizationUrl::to(['subscription/request-renewal']) ?>" method="post" id="renewal-form">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <!-- Выбор тарифа -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">1. Выберите тариф</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($plans as $plan): ?>
                    <?php
                    $isSelected = $selectedPlanId ? $plan->id == $selectedPlanId : ($currentPlan && $currentPlan->id == $plan->id);
                    $cardClass = $isSelected ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-500' : 'border-gray-200 hover:border-gray-300';
                    ?>
                    <label class="relative border rounded-lg p-4 cursor-pointer <?= $cardClass ?> transition">
                        <input type="radio" name="plan_id" value="<?= $plan->id ?>" class="sr-only" <?= $isSelected ? 'checked' : '' ?>>

                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= Html::encode($plan->name) ?></h3>
                                <?php if ($plan->description): ?>
                                    <p class="text-sm text-gray-500 mt-1"><?= Html::encode($plan->description) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold text-gray-900">
                                    <?= number_format($plan->price_monthly, 0, '', ' ') ?>
                                </div>
                                <div class="text-sm text-gray-500">KZT/мес</div>
                            </div>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm">
                            <li class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= $plan->max_pupils == 0 ? 'Безлимит' : 'До ' . $plan->max_pupils ?> учеников
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= $plan->max_groups == 0 ? 'Безлимит' : 'До ' . $plan->max_groups ?> групп
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= $plan->max_teachers == 0 ? 'Безлимит' : 'До ' . $plan->max_teachers ?> учителей
                            </li>
                        </ul>

                        <?php if ($isSelected): ?>
                            <div class="absolute top-2 right-2">
                                <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Выбор периода -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">2. Выберите период</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative border rounded-lg p-4 cursor-pointer border-gray-200 hover:border-gray-300 transition period-option" data-period="monthly">
                    <input type="radio" name="period" value="monthly" class="sr-only" checked>
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-gray-900">Ежемесячно</h3>
                            <p class="text-sm text-gray-500">Гибкая оплата каждый месяц</p>
                        </div>
                        <div class="period-price text-right">
                            <span class="text-lg font-bold text-gray-900" id="monthly-price">-</span>
                            <span class="text-sm text-gray-500">KZT</span>
                        </div>
                    </div>
                </label>

                <label class="relative border rounded-lg p-4 cursor-pointer border-gray-200 hover:border-gray-300 transition period-option" data-period="yearly">
                    <input type="radio" name="period" value="yearly" class="sr-only">
                    <div class="absolute -top-2 -right-2 px-2 py-1 bg-success-500 text-white text-xs font-medium rounded">
                        2 месяца бесплатно
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-gray-900">Ежегодно</h3>
                            <p class="text-sm text-gray-500">Экономия 2 месяца</p>
                        </div>
                        <div class="period-price text-right">
                            <span class="text-lg font-bold text-gray-900" id="yearly-price">-</span>
                            <span class="text-sm text-gray-500">KZT</span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Комментарий -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">3. Комментарий (необязательно)</h2>
            <textarea name="comment" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Укажите дополнительную информацию или пожелания..."></textarea>
        </div>

        <!-- Итого -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-center text-lg">
                <span class="font-medium text-gray-700">Итого к оплате:</span>
                <span class="text-2xl font-bold text-gray-900" id="total-price">-</span>
            </div>
        </div>

        <!-- Кнопка -->
        <div class="flex justify-between items-center">
            <a href="<?= OrganizationUrl::to(['subscription/index']) ?>" class="text-gray-600 hover:text-gray-900">
                ← Назад
            </a>
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Отправить заявку
            </button>
        </div>
    </form>
</div>

<script>
const plans = <?= json_encode(array_map(function($p) {
    return [
        'id' => $p->id,
        'price_monthly' => (float)$p->price_monthly,
        'price_yearly' => (float)$p->price_yearly,
    ];
}, $plans)) ?>;

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function updatePrices() {
    const selectedPlan = document.querySelector('input[name="plan_id"]:checked');
    const selectedPeriod = document.querySelector('input[name="period"]:checked');

    if (!selectedPlan) return;

    const plan = plans.find(p => p.id == selectedPlan.value);
    if (!plan) return;

    document.getElementById('monthly-price').textContent = formatNumber(plan.price_monthly);
    document.getElementById('yearly-price').textContent = formatNumber(plan.price_yearly);

    const total = selectedPeriod.value === 'yearly' ? plan.price_yearly : plan.price_monthly;
    document.getElementById('total-price').textContent = formatNumber(total) + ' KZT';
}

function updateSelection() {
    // Plan selection
    document.querySelectorAll('input[name="plan_id"]').forEach(input => {
        const label = input.closest('label');
        if (input.checked) {
            label.classList.add('border-primary-500', 'bg-primary-50', 'ring-2', 'ring-primary-500');
            label.classList.remove('border-gray-200');
        } else {
            label.classList.remove('border-primary-500', 'bg-primary-50', 'ring-2', 'ring-primary-500');
            label.classList.add('border-gray-200');
        }
    });

    // Period selection
    document.querySelectorAll('input[name="period"]').forEach(input => {
        const label = input.closest('label');
        if (input.checked) {
            label.classList.add('border-primary-500', 'bg-primary-50', 'ring-2', 'ring-primary-500');
            label.classList.remove('border-gray-200');
        } else {
            label.classList.remove('border-primary-500', 'bg-primary-50', 'ring-2', 'ring-primary-500');
            label.classList.add('border-gray-200');
        }
    });

    updatePrices();
}

document.querySelectorAll('input[name="plan_id"], input[name="period"]').forEach(input => {
    input.addEventListener('change', updateSelection);
});

// Initial update
updateSelection();
</script>
