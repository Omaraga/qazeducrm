<?php

use app\helpers\OrganizationUrl;
use app\helpers\Lists;
use app\models\search\DateSearch;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dateTeacherSalary */

$this->title = Yii::t('main', 'Отчет. Заработная плата преподавателей');
$this->params['breadcrumbs'][] = $this->title;

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-collapse-toggle]').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-collapse-toggle');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('svg');
            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                target.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">За <?= date('F Y', strtotime($searchModel->date ? str_replace('.', '-', $searchModel->date) : 'now')) ?></p>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <div class="card-body">
            <?= $this->render('_search', ['model' => $searchModel]) ?>
        </div>
    </div>

    <!-- Teachers -->
    <div class="space-y-4">
        <?php foreach ($teachers as $teacher): ?>
        <?php $sum = 0; ?>
        <div class="card border-l-4 border-l-success-500">
            <div class="card-header flex items-center justify-between cursor-pointer" data-collapse-toggle="collapse_<?= $teacher->id ?>">
                <h3 class="text-lg font-semibold text-gray-900"><?= Html::encode($teacher->fio) ?></h3>
                <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div id="collapse_<?= $teacher->id ?>" class="hidden">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($dateTeacherSalary as $date => $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="<?= OrganizationUrl::to(['reports/day', 'DateSearch[date]' => $date, 'type' => DateSearch::TYPE_SALARY]) ?>" target="_blank" class="text-primary-600 hover:text-primary-800">
                                            <?= date('d.m.Y', strtotime($date)) ?>
                                            <span class="text-gray-500 ml-1"><?= Lists::getWeekDays()[date('w', strtotime($date)) == 0 ? 7 : date('w', strtotime($date))] ?></span>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        <?= number_format($item[$teacher->id], 0, '.', ' ') ?> ₸
                                    </td>
                                </tr>
                                <?php $sum += $item[$teacher->id]; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success-50 border-t border-success-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-success-800">Итого за месяц</span>
                    <span class="text-lg font-bold text-success-700"><?= number_format($sum, 0, '.', ' ') ?> ₸</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($teachers)): ?>
        <div class="card">
            <div class="card-body text-center py-12">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-lg font-medium text-gray-900">Нет преподавателей с занятиями за выбранный период</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
