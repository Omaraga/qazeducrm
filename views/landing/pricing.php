<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan[] $plans */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Тарифы';
?>

<!-- Hero -->
<section class="bg-gray-50 py-20 text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Выберите подходящий тариф</h1>
        <p class="text-xl text-gray-500">Начните бесплатно, масштабируйтесь по мере роста вашего центра</p>
    </div>
</section>

<!-- Pricing Cards -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 lg:grid-cols-<?= min(count($plans), 4) ?> gap-6 max-w-6xl mx-auto">
            <?php foreach ($plans as $index => $plan): ?>
            <div class="relative bg-white border-2 <?= $index === 1 ? 'border-orange-500 shadow-xl scale-105' : 'border-gray-100' ?> rounded-2xl p-8 transition-all hover:shadow-lg">
                <?php if ($index === 1): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs font-semibold px-4 py-1 rounded-full">Популярный</span>
                <?php endif; ?>

                <div class="text-center">
                    <div class="text-xl font-bold text-gray-900 mb-2"><?= Html::encode($plan->name) ?></div>
                    <div class="text-gray-500 text-sm mb-6"><?= Html::encode($plan->description) ?></div>
                    <div class="text-4xl font-extrabold text-orange-500 mb-1">
                        <?= $plan->getFormattedPriceMonthly() ?>
                    </div>
                    <?php if ($plan->price_monthly > 0): ?>
                    <div class="text-sm text-gray-500 mb-4">в месяц</div>
                    <?php else: ?>
                    <div class="text-sm text-gray-500 mb-4">&nbsp;</div>
                    <?php endif; ?>
                    <div class="flex items-center justify-center gap-2 text-green-600 text-sm mb-6">
                        <i class="fas fa-gift"></i>
                        <span><?= $plan->trial_days ?> дней бесплатно</span>
                    </div>
                </div>

                <!-- Limits -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-gray-600 text-sm">Ученики</span>
                        <strong class="text-gray-900"><?= $plan->max_pupils ?: '∞' ?></strong>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-gray-600 text-sm">Преподаватели</span>
                        <strong class="text-gray-900"><?= $plan->max_teachers ?: '∞' ?></strong>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 text-sm">Группы</span>
                        <strong class="text-gray-900"><?= $plan->max_groups ?: '∞' ?></strong>
                    </div>
                </div>

                <!-- Features -->
                <ul class="space-y-3 mb-6">
                    <li class="flex items-center gap-3 text-sm">
                        <i class="fas <?= $plan->hasFeature('crm_basic') ? 'fa-check text-green-500' : 'fa-times text-gray-300' ?>"></i>
                        <span class="text-gray-600">Базовый CRM</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <i class="fas <?= $plan->hasFeature('sms') ? 'fa-check text-green-500' : 'fa-times text-gray-300' ?>"></i>
                        <span class="text-gray-600">SMS уведомления</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <i class="fas <?= $plan->hasFeature('reports') ? 'fa-check text-green-500' : 'fa-times text-gray-300' ?>"></i>
                        <span class="text-gray-600">Отчёты</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <i class="fas <?= $plan->hasFeature('api') ? 'fa-check text-green-500' : 'fa-times text-gray-300' ?>"></i>
                        <span class="text-gray-600">API</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm">
                        <i class="fas <?= $plan->hasFeature('priority_support') ? 'fa-check text-green-500' : 'fa-times text-gray-300' ?>"></i>
                        <span class="text-gray-600">Приоритетная поддержка</span>
                    </li>
                </ul>

                <a href="<?= Url::to(['/register']) ?>" class="block w-full py-3 rounded-lg font-semibold text-center transition-all <?= $index === 1 ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'border-2 border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white' ?>">
                    Начать бесплатно
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Часто задаваемые вопросы</h2>
        </div>
        <div class="max-w-3xl mx-auto space-y-4" x-data="{ openFaq: 0 }">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="openFaq = openFaq === 1 ? 0 : 1" class="w-full px-6 py-4 text-left font-semibold text-gray-900 flex justify-between items-center hover:text-orange-500 transition-colors">
                    Могу ли я сменить тариф?
                    <i class="fas fa-chevron-down transition-transform" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-6 pb-4 text-gray-500">
                    Да, вы можете изменить тариф в любой момент. При переходе на более дорогой тариф изменения вступят в силу сразу.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="openFaq = openFaq === 2 ? 0 : 2" class="w-full px-6 py-4 text-left font-semibold text-gray-900 flex justify-between items-center hover:text-orange-500 transition-colors">
                    Как работает пробный период?
                    <i class="fas fa-chevron-down transition-transform" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-6 pb-4 text-gray-500">
                    После регистрации вы получаете полный доступ ко всем функциям на период пробного периода. Карта не требуется.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="openFaq = openFaq === 3 ? 0 : 3" class="w-full px-6 py-4 text-left font-semibold text-gray-900 flex justify-between items-center hover:text-orange-500 transition-colors">
                    Какие способы оплаты?
                    <i class="fas fa-chevron-down transition-transform" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-6 pb-4 text-gray-500">
                    Мы принимаем Kaspi, банковские карты, банковский перевод для юридических лиц.
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="openFaq = openFaq === 4 ? 0 : 4" class="w-full px-6 py-4 text-left font-semibold text-gray-900 flex justify-between items-center hover:text-orange-500 transition-colors">
                    Есть ли скидка за год?
                    <i class="fas fa-chevron-down transition-transform" :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-6 pb-4 text-gray-500">
                    Да, при оплате за год вы экономите 2 месяца — это 17% экономии.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-gray-900 to-gray-800 text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-white mb-4">Остались вопросы?</h2>
        <p class="text-xl text-white/70 mb-8">Свяжитесь с нами, мы поможем выбрать подходящий тариф</p>
        <a href="<?= Url::to(['/contact']) ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-all">
            Связаться с нами
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
