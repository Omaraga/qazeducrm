<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationAddon[] $activeTrials
 * @var array $availableTrials
 * @var array $statistics
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\TrialModal;

$this->title = Yii::t('main', 'Пробные периоды');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-trials">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-600">Управление пробными периодами функций</p>
        </div>
        <a href="<?= OrganizationUrl::to(['subscription/index']) ?>"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            ← К подписке
        </a>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Активных trial</div>
                    <div class="text-2xl font-bold text-gray-900"><?= $statistics['active_count'] ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-warning-100 flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Скоро истекают</div>
                    <div class="text-2xl font-bold text-gray-900"><?= $statistics['expiring_soon'] ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-success-100 flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Доступно для trial</div>
                    <div class="text-2xl font-bold text-gray-900"><?= count($availableTrials) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Активные trial -->
    <?php if (!empty($activeTrials)): ?>
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Активные пробные периоды</h2>

        <div class="space-y-4">
            <?php foreach ($activeTrials as $addon): ?>
                <?php
                $feature = $addon->feature;
                $daysRemaining = $addon->getDaysRemaining();
                $isExpiringSoon = $addon->isExpiringSoon();
                ?>
                <div class="flex items-center justify-between p-4 border rounded-lg <?= $isExpiringSoon ? 'border-warning-300 bg-warning-50' : 'border-gray-200' ?>">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?= Html::encode($feature->name ?? 'Аддон') ?></div>
                            <div class="text-sm text-gray-500"><?= Html::encode($feature->description ?? '') ?></div>
                            <div class="text-xs text-gray-400 mt-1">
                                Активирован: <?= Yii::$app->formatter->asDate($addon->started_at, 'long') ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Дни до окончания -->
                        <div class="text-right">
                            <?php if ($daysRemaining !== null): ?>
                                <div class="text-lg font-bold <?= $isExpiringSoon ? 'text-warning-600' : 'text-gray-900' ?>">
                                    <?= $daysRemaining ?> <?= Yii::t('main', 'дн.') ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    до <?= Yii::$app->formatter->asDate($addon->trial_ends_at, 'short') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Действия -->
                        <div class="flex gap-2">
                            <form action="<?= OrganizationUrl::to(['subscription/convert-trial']) ?>" method="post" class="inline">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <?= Html::hiddenInput('feature', $feature->code) ?>
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                                    Подключить
                                </button>
                            </form>

                            <form action="<?= OrganizationUrl::to(['subscription/cancel-trial']) ?>" method="post" class="inline"
                                  onsubmit="return confirm('Вы уверены, что хотите отменить пробный период?')">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <?= Html::hiddenInput('feature', $feature->code) ?>
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                    Отменить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Доступные для trial -->
    <?php if (!empty($availableTrials)): ?>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Доступные для пробного периода</h2>
        <p class="text-gray-600 mb-4">Попробуйте эти функции бесплатно</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($availableTrials as $trial): ?>
                <?php $feature = $trial['feature']; ?>
                <div class="p-4 border border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900"><?= Html::encode($feature->name) ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?= Html::encode($feature->description ?? '') ?></p>

                            <div class="flex items-center gap-4 mt-3 text-sm">
                                <span class="inline-flex items-center text-primary-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <?= $trial['trial_days'] ?> дней бесплатно
                                </span>
                                <span class="text-gray-500">
                                    Потом <?= number_format($trial['price_after'], 0, '', ' ') ?> KZT/мес
                                </span>
                            </div>
                        </div>

                        <button type="button"
                                class="ml-4 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition"
                                data-trial-modal="<?= Html::encode($feature->code) ?>">
                            Попробовать
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif (empty($activeTrials)): ?>
    <div class="bg-white rounded-lg shadow-sm border p-12 text-center">
        <div class="text-gray-400 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Нет доступных пробных периодов</h3>
        <p class="text-gray-500 mb-4">Все доступные trial уже использованы или активны в вашем тарифе</p>
        <a href="<?= OrganizationUrl::to(['subscription/upgrade']) ?>"
           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            Посмотреть все функции
        </a>
    </div>
    <?php endif; ?>

    <!-- Информация -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Как работает пробный период</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Пробный период даёт полный доступ к функции</li>
                        <li>Не требуется привязка карты</li>
                        <li>Автоматически отключается после окончания</li>
                        <li>Можно использовать только один раз для каждой функции</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= TrialModal::all() ?>
