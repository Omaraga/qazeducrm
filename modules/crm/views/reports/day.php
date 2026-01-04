<?php

use app\helpers\OrganizationUrl;
use app\helpers\OrganizationRoles;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dataArray */
/** @var integer $type */

$this->title = Yii::t('main', 'Дневной отчет');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Отчет за <?= Html::encode($searchModel->date) ?></p>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <div class="card-body">
            <?= $this->render('_search', ['model' => $searchModel, 'onlyMonth' => false]) ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex flex-wrap gap-2" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['reports/day', 'type' => 1, 'DateSearch[date]' => $searchModel->date]) ?>"
               class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 1 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Посещаемость по группам
            </a>
            <?php if (Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR) || Yii::$app->user->can(OrganizationRoles::DIRECTOR)): ?>
            <a href="<?= OrganizationUrl::to(['reports/day', 'type' => 2, 'DateSearch[date]' => $searchModel->date]) ?>"
               class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 2 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Оплата преподавателям
            </a>
            <?php endif; ?>
            <a href="<?= OrganizationUrl::to(['reports/day', 'type' => 3, 'DateSearch[date]' => $searchModel->date]) ?>"
               class="px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 3 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Принятые платежи
            </a>
        </nav>
    </div>

    <!-- Content -->
    <?php if ($type == 1): ?>
        <?php if (!empty($dataArray['lessons'])): ?>
            <?= $this->render('day/attendance', [
                'lessons' => $dataArray['lessons'],
                'attendances' => $dataArray['attendances']
            ]) ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-900"><?= Html::encode($searchModel->date) ?> нет ни одного занятия</p>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif ($type == 2): ?>
        <?php if (!empty($dataArray['teachers'])): ?>
            <?= $this->render('day/teacher', [
                'teachers' => $dataArray['teachers'],
                'teacherLessons' => $dataArray['teacherLessons'],
                'lessonPupilSalary' => $dataArray['lessonPupilSalary']
            ]) ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-900"><?= Html::encode($searchModel->date) ?> нет ни одного занятия</p>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif ($type == 3): ?>
        <?php if (!empty($dataArray)): ?>
            <?= $this->render('day/payment', [
                'payments' => $dataArray,
            ]) ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-900"><?= Html::encode($searchModel->date) ?> нет ни одной оплаты</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
