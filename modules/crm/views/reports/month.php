<?php

use app\helpers\OrganizationUrl;
use app\models\search\DateSearch;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dataArray */
/** @var integer $type */

$this->title = Yii::t('main', 'Отчет за месяц');
$onlyMonth = true;

if ($type == DateSearch::TYPE_ATTENDANCE) {
    $this->title = 'Статистика посещаемости занятий';
    $onlyMonth = false;
} elseif ($type == DateSearch::TYPE_SALARY) {
    $this->title = 'Отчет за месяц. Зарплата преподавателей';
} elseif ($type == DateSearch::TYPE_PAYMENT) {
    $this->title = 'Отчет за месяц. Приход';
} elseif ($type == DateSearch::TYPE_PUPIL_PAYMENT) {
    $this->title = 'Отчет за месяц. Оплата и задолженность по ученикам';
}

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900" id="report-title"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">
                <?php if ($onlyMonth): ?>
                    За <?= date('F Y', strtotime($searchModel->date ? str_replace('.', '-', $searchModel->date) : 'now')) ?>
                <?php else: ?>
                    За <?= Html::encode($searchModel->date) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <div class="card-body">
            <?= $this->render('_search', ['model' => $searchModel, 'onlyMonth' => $onlyMonth]) ?>
        </div>
    </div>

    <!-- Content -->
    <?php if ($type == DateSearch::TYPE_ATTENDANCE): ?>
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

    <?php elseif ($type == DateSearch::TYPE_SALARY): ?>
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
                    <p class="text-lg font-medium text-gray-900">За выбранный период нет ни одного занятия</p>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($type == DateSearch::TYPE_PAYMENT): ?>
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
                    <p class="text-lg font-medium text-gray-900">За выбранный период нет ни одной оплаты</p>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($type == DateSearch::TYPE_PUPIL_PAYMENT): ?>
        <?= $this->render('day/pupil_payment', [
            'pupils' => $dataArray['pupils'],
            'pupilPupilEducations' => $dataArray['pupilPupilEducations'],
            'pupilPayments' => $dataArray['pupilPayments']
        ]) ?>
    <?php endif; ?>
</div>
