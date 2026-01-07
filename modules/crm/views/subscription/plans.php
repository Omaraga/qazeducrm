<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\models\SaasPlan[] $plans
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'Тарифные планы');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$currentPlan = $subscription ? $subscription->saasPlan : null;
?>

<div class="subscription-plans">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-600">Выберите оптимальный тариф для вашего учебного центра</p>
    </div>

    <!-- Переключатель периода оплаты -->
    <div class="flex justify-center mb-8">
        <div class="inline-flex bg-gray-100 rounded-lg p-1">
            <button type="button" class="period-btn px-4 py-2 rounded-lg text-sm font-medium bg-white shadow text-gray-900" data-period="monthly">
                Ежемесячно
            </button>
            <button type="button" class="period-btn px-4 py-2 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700" data-period="yearly">
                Ежегодно <span class="text-success-600 text-xs">-17%</span>
            </button>
        </div>
    </div>

    <!-- Сетка тарифов -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($plans as $index => $plan): ?>
            <?php
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
            $isPopular = $index === 1; // Второй план как "популярный"
            $cardClass = $isCurrent ? 'border-primary-500 ring-2 ring-primary-500' : ($isPopular ? 'border-primary-300' : 'border-gray-200');
            ?>
            <div class="relative bg-white rounded-xl shadow-sm border <?= $cardClass ?> transition-all hover:shadow-md">
                <?php if ($isPopular && !$isCurrent): ?>
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="px-3 py-1 bg-primary-600 text-white text-xs font-medium rounded-full">
                            Популярный
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($isCurrent): ?>
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="px-3 py-1 bg-success-600 text-white text-xs font-medium rounded-full">
                            Текущий план
                        </span>
                    </div>
                <?php endif; ?>

                <div class="p-6">
                    <!-- Название -->
                    <h3 class="text-xl font-bold text-gray-900"><?= Html::encode($plan->name) ?></h3>
                    <?php if ($plan->description): ?>
                        <p class="text-sm text-gray-500 mt-1"><?= Html::encode($plan->description) ?></p>
                    <?php endif; ?>

                    <!-- Цена -->
                    <div class="mt-4">
                        <div class="price-monthly">
                            <span class="text-3xl font-bold text-gray-900">
                                <?= number_format($plan->price_monthly, 0, '', ' ') ?>
                            </span>
                            <span class="text-gray-500">KZT/мес</span>
                        </div>
                        <div class="price-yearly hidden">
                            <span class="text-3xl font-bold text-gray-900">
                                <?= number_format($plan->price_yearly / 12, 0, '', ' ') ?>
                            </span>
                            <span class="text-gray-500">KZT/мес</span>
                            <div class="text-sm text-gray-400">
                                <?= number_format($plan->price_yearly, 0, '', ' ') ?> KZT/год
                            </div>
                        </div>
                    </div>

                    <!-- Функции -->
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">
                                <?= $plan->max_pupils == 0 ? '<strong>Безлимит</strong>' : 'До <strong>' . $plan->max_pupils . '</strong>' ?> учеников
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">
                                <?= $plan->max_groups == 0 ? '<strong>Безлимит</strong>' : 'До <strong>' . $plan->max_groups . '</strong>' ?> групп
                            </span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">
                                <?= $plan->max_teachers == 0 ? '<strong>Безлимит</strong>' : 'До <strong>' . $plan->max_teachers . '</strong>' ?> учителей
                            </span>
                        </li>
                        <?php if ($plan->max_admins > 0 || $plan->max_admins === 0): ?>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">
                                <?= $plan->max_admins == 0 ? '<strong>Безлимит</strong>' : 'До <strong>' . $plan->max_admins . '</strong>' ?> админов
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Кнопка -->
                    <div class="mt-6">
                        <?php if ($isCurrent): ?>
                            <span class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg">
                                Ваш текущий план
                            </span>
                        <?php elseif ($currentPlan && $plan->sort_order > $currentPlan->sort_order): ?>
                            <a href="<?= OrganizationUrl::to(['subscription/renew', 'plan' => $plan->id]) ?>"
                               class="block w-full text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                                Перейти на этот план
                            </a>
                        <?php elseif ($currentPlan && $plan->sort_order < $currentPlan->sort_order): ?>
                            <a href="<?= OrganizationUrl::to(['subscription/renew', 'plan' => $plan->id]) ?>"
                               class="block w-full text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Понизить план
                            </a>
                        <?php else: ?>
                            <a href="<?= OrganizationUrl::to(['subscription/renew', 'plan' => $plan->id]) ?>"
                               class="block w-full text-center px-4 py-2 <?= $isPopular ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800' ?> rounded-lg transition">
                                Выбрать план
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FAQ -->
    <div class="mt-12 bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Часто задаваемые вопросы</h2>

        <div class="space-y-4">
            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer list-none p-4 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-900">Могу ли я сменить тариф в любое время?</span>
                    <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="p-4 text-gray-600">
                    Да, вы можете перейти на более высокий тариф в любое время. При переходе на более низкий тариф
                    изменения вступят в силу со следующего расчётного периода.
                </p>
            </details>

            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer list-none p-4 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-900">Что произойдёт, если я превышу лимит?</span>
                    <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="p-4 text-gray-600">
                    При достижении лимита вы не сможете создавать новые записи этого типа. Существующие данные
                    останутся доступны. Мы рекомендуем перейти на более высокий тариф или докупить пакет лимитов.
                </p>
            </details>

            <details class="group">
                <summary class="flex justify-between items-center cursor-pointer list-none p-4 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-900">Есть ли скидка за годовую оплату?</span>
                    <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="p-4 text-gray-600">
                    Да! При оплате за год вы получаете 2 месяца бесплатно, что составляет экономию около 17%.
                </p>
            </details>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const period = this.dataset.period;

        // Обновляем кнопки
        document.querySelectorAll('.period-btn').forEach(b => {
            b.classList.remove('bg-white', 'shadow', 'text-gray-900');
            b.classList.add('text-gray-500');
        });
        this.classList.add('bg-white', 'shadow', 'text-gray-900');
        this.classList.remove('text-gray-500');

        // Показываем соответствующие цены
        if (period === 'yearly') {
            document.querySelectorAll('.price-monthly').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-yearly').forEach(el => el.classList.remove('hidden'));
        } else {
            document.querySelectorAll('.price-monthly').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.price-yearly').forEach(el => el.classList.add('hidden'));
        }
    });
});
</script>
