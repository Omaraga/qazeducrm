<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationPayment[] $payments
 */

use yii\helpers\Html;
use app\helpers\OrganizationUrl;

$this->title = Yii::t('main', 'История платежей');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Подписка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-payments">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-600">Все платежи за подписку</p>
        </div>
        <a href="<?= OrganizationUrl::to(['subscription/index']) ?>"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            ← К подписке
        </a>
    </div>

    <?php if (!empty($payments)): ?>
        <!-- Статистика -->
        <?php
        $totalPaid = array_sum(array_map(function($p) {
            return $p->status === 'completed' ? $p->amount : 0;
        }, $payments));
        $paymentsCount = count(array_filter($payments, fn($p) => $p->status === 'completed'));
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="text-sm text-gray-500">Всего оплачено</div>
                <div class="text-2xl font-bold text-gray-900">
                    <?= number_format($totalPaid, 0, '', ' ') ?> KZT
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="text-sm text-gray-500">Количество платежей</div>
                <div class="text-2xl font-bold text-gray-900"><?= $paymentsCount ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="text-sm text-gray-500">Средний платёж</div>
                <div class="text-2xl font-bold text-gray-900">
                    <?= $paymentsCount > 0 ? number_format($totalPaid / $paymentsCount, 0, '', ' ') : 0 ?> KZT
                </div>
            </div>
        </div>

        <!-- Таблица платежей -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Дата
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Описание
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Период
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Сумма
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($payments as $payment): ?>
                        <?php
                        $statusClass = match($payment->status) {
                            'completed' => 'bg-success-100 text-success-800',
                            'pending' => 'bg-warning-100 text-warning-800',
                            'failed' => 'bg-danger-100 text-danger-800',
                            'cancelled' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $statusLabel = match($payment->status) {
                            'completed' => 'Оплачен',
                            'pending' => 'Ожидает',
                            'failed' => 'Ошибка',
                            'cancelled' => 'Отменён',
                            default => $payment->status,
                        };
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= Yii::$app->formatter->asDate($payment->created_at, 'long') ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= Yii::$app->formatter->asTime($payment->created_at, 'short') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= Html::encode($payment->description ?? 'Оплата подписки') ?>
                                </div>
                                <?php if ($payment->subscription): ?>
                                    <div class="text-xs text-gray-500">
                                        <?= Html::encode($payment->subscription->saasPlan->name ?? '') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= $payment->billing_period === 'yearly' ? 'Годовой' : 'Месячный' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= number_format($payment->amount, 0, '', ' ') ?> KZT
                                </div>
                                <?php if ($payment->discount_amount > 0): ?>
                                    <div class="text-xs text-success-600">
                                        Скидка: -<?= number_format($payment->discount_amount, 0, '', ' ') ?> KZT
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Пустое состояние -->
        <div class="bg-white rounded-lg shadow-sm border p-12 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Нет платежей</h3>
            <p class="text-gray-500 mb-4">История платежей пуста</p>
            <a href="<?= OrganizationUrl::to(['subscription/plans']) ?>"
               class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Выбрать тариф
            </a>
        </div>
    <?php endif; ?>

    <!-- Информация о способах оплаты -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Способы оплаты</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Kaspi QR</li>
                        <li>Банковский перевод</li>
                        <li>Наличные (в офисе)</li>
                    </ul>
                </div>
                <p class="mt-2 text-sm text-blue-700">
                    По вопросам оплаты: <a href="mailto:billing@qazeducrm.kz" class="underline">billing@qazeducrm.kz</a>
                </p>
            </div>
        </div>
    </div>
</div>
