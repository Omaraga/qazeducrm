<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription|null $subscription
 * @var app\models\SaasPlan|null $currentPlan
 * @var app\models\SaasPlan[] $plans
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'Увеличить лимиты');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-upgrade max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-600">Выберите способ увеличения лимитов</p>
    </div>

    <!-- Текущий план -->
    <?php if ($currentPlan): ?>
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ваш текущий план</h2>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xl font-bold text-gray-900"><?= Html::encode($currentPlan->name) ?></div>
                <div class="text-sm text-gray-500">
                    <?= $currentPlan->max_pupils == 0 ? 'Безлимит' : $currentPlan->max_pupils ?> учеников,
                    <?= $currentPlan->max_groups == 0 ? 'Безлимит' : $currentPlan->max_groups ?> групп,
                    <?= $currentPlan->max_teachers == 0 ? 'Безлимит' : $currentPlan->max_teachers ?> учителей
                </div>
            </div>
            <div class="text-right">
                <div class="text-lg font-bold text-gray-900">
                    <?= number_format($currentPlan->price_monthly, 0, '', ' ') ?> KZT
                </div>
                <div class="text-sm text-gray-500">в месяц</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Варианты апгрейда -->
    <?php if (!empty($plans)): ?>
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Перейти на более высокий план</h2>
        <p class="text-gray-600 mb-4">Получите больше лимитов и дополнительные функции</p>

        <div class="space-y-4">
            <?php foreach ($plans as $plan): ?>
                <div class="flex items-center justify-between p-4 border rounded-lg hover:border-primary-300 hover:bg-primary-50 transition cursor-pointer"
                     onclick="window.location.href='<?= OrganizationUrl::to(['subscription/renew', 'plan' => $plan->id]) ?>'">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <span class="font-semibold text-gray-900"><?= Html::encode($plan->name) ?></span>
                            <?php if ($currentPlan): ?>
                                <?php
                                $pupilsIncrease = $plan->max_pupils > 0 && $currentPlan->max_pupils > 0
                                    ? $plan->max_pupils - $currentPlan->max_pupils
                                    : null;
                                ?>
                                <?php if ($pupilsIncrease > 0): ?>
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-success-100 text-success-700 rounded">
                                        +<?= $pupilsIncrease ?> учеников
                                    </span>
                                <?php elseif ($plan->max_pupils == 0 && $currentPlan->max_pupils > 0): ?>
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-success-100 text-success-700 rounded">
                                        Безлимит
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            <?= $plan->max_pupils == 0 ? 'Безлимит' : $plan->max_pupils ?> учеников,
                            <?= $plan->max_groups == 0 ? 'Безлимит' : $plan->max_groups ?> групп,
                            <?= $plan->max_teachers == 0 ? 'Безлимит' : $plan->max_teachers ?> учителей
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <div class="font-bold text-gray-900">
                            <?= number_format($plan->price_monthly, 0, '', ' ') ?> KZT
                        </div>
                        <div class="text-sm text-gray-500">в месяц</div>
                        <?php if ($currentPlan): ?>
                            <?php $diff = $plan->price_monthly - $currentPlan->price_monthly; ?>
                            <div class="text-xs text-primary-600">
                                +<?= number_format($diff, 0, '', ' ') ?> KZT к текущему
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ml-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="text-center py-8">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">У вас максимальный план</h3>
            <p class="text-gray-500">Вы уже используете план с максимальными лимитами</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Дополнительные пакеты (Placeholder для аддонов) -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Дополнительные пакеты</h2>
        <p class="text-gray-600 mb-4">Докупите дополнительные лимиты без смены тарифа</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- +30 учеников -->
            <div class="p-4 border rounded-lg hover:border-primary-300 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">+30 учеников</div>
                        <div class="text-sm text-gray-500">Дополнительный лимит</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">3,990 KZT</div>
                        <div class="text-xs text-gray-500">в месяц</div>
                    </div>
                </div>
                <button type="button" class="mt-3 w-full px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition text-sm"
                        onclick="requestAddon('pupils_30')">
                    Добавить
                </button>
            </div>

            <!-- +50 учеников -->
            <div class="p-4 border rounded-lg hover:border-primary-300 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">+50 учеников</div>
                        <div class="text-sm text-gray-500">Дополнительный лимит</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">5,990 KZT</div>
                        <div class="text-xs text-gray-500">в месяц</div>
                    </div>
                </div>
                <button type="button" class="mt-3 w-full px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition text-sm"
                        onclick="requestAddon('pupils_50')">
                    Добавить
                </button>
            </div>

            <!-- +100 учеников -->
            <div class="p-4 border rounded-lg hover:border-primary-300 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">+100 учеников</div>
                        <div class="text-sm text-gray-500">Дополнительный лимит</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">9,990 KZT</div>
                        <div class="text-xs text-gray-500">в месяц</div>
                    </div>
                </div>
                <button type="button" class="mt-3 w-full px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition text-sm"
                        onclick="requestAddon('pupils_100')">
                    Добавить
                </button>
            </div>

            <!-- +100 SMS -->
            <div class="p-4 border rounded-lg hover:border-primary-300 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">+100 SMS</div>
                        <div class="text-sm text-gray-500">Пакет сообщений</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-900">1,990 KZT</div>
                        <div class="text-xs text-gray-500">в месяц</div>
                    </div>
                </div>
                <button type="button" class="mt-3 w-full px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition text-sm"
                        onclick="requestAddon('sms_100')">
                    Добавить
                </button>
            </div>
        </div>

        <p class="mt-4 text-sm text-gray-500">
            * Для подключения дополнительных пакетов свяжитесь с нашей командой поддержки
        </p>
    </div>

    <!-- Кнопка назад -->
    <div class="flex justify-start">
        <a href="<?= OrganizationUrl::to(['subscription/index']) ?>" class="text-gray-600 hover:text-gray-900">
            ← Назад к подписке
        </a>
    </div>
</div>

<script>
function requestAddon(addonCode) {
    // В будущем здесь будет AJAX-запрос на добавление аддона
    // Пока показываем сообщение
    alert('Для подключения дополнительного пакета свяжитесь с поддержкой:\nsupport@qazeducrm.kz');
}
</script>
