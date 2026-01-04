<?php

/** @var yii\web\View $this */
/** @var array $data */
/** @var string $week */

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

$chartData = '[' . implode(',', $data) . ']';
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('weekChart');
    if (!ctx) return;

    const labels = $week;
    const data = {
      labels: labels,
      datasets: [{
        label: 'Сумма платежей',
        data: $chartData,
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 2,
        borderRadius: 6,
      }]
    };
    const config = {
      type: 'bar',
      data: data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            grid: { display: false }
          }
        }
      },
    };
    new Chart(ctx, config);
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pupils -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">—</div>
                        <div class="text-sm text-gray-500">Учеников</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-info-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">—</div>
                        <div class="text-sm text-gray-500">Групп</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">—</div>
                        <div class="text-sm text-gray-500">Доход (мес)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debtors -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-warning-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">—</div>
                        <div class="text-sm text-gray-500">Должников</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Платежи за неделю</h3>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="weekChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Быстрые действия</h3>
            </div>
            <div class="card-body space-y-3">
                <a href="<?= OrganizationUrl::to(['pupil/create']) ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <span class="text-gray-700 group-hover:text-primary-700 font-medium">Добавить ученика</span>
                </a>

                <a href="<?= OrganizationUrl::to(['group/create']) ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-info-100 flex items-center justify-center group-hover:bg-info-200 transition-colors">
                        <svg class="w-5 h-5 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-gray-700 group-hover:text-primary-700 font-medium">Создать группу</span>
                </a>

                <a href="<?= OrganizationUrl::to(['payment/create']) ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-success-100 flex items-center justify-center group-hover:bg-success-200 transition-colors">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <span class="text-gray-700 group-hover:text-primary-700 font-medium">Принять платёж</span>
                </a>

                <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-warning-100 flex items-center justify-center group-hover:bg-warning-200 transition-colors">
                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-gray-700 group-hover:text-primary-700 font-medium">Расписание</span>
                </a>
            </div>
        </div>
    </div>
</div>
