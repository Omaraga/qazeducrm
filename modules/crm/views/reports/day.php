<?php

use app\helpers\OrganizationUrl;
use app\helpers\OrganizationRoles;
use app\widgets\tailwind\EmptyState;
use app\widgets\tailwind\Icon;
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
               class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 1 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <?= Icon::show('check', 'sm') ?>
                Посещаемость по группам
            </a>
            <?php if (Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR) || Yii::$app->user->can(OrganizationRoles::DIRECTOR)): ?>
            <a href="<?= OrganizationUrl::to(['reports/day', 'type' => 2, 'DateSearch[date]' => $searchModel->date]) ?>"
               class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 2 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <?= Icon::show('wallet', 'sm') ?>
                Оплата преподавателям
            </a>
            <?php endif; ?>
            <a href="<?= OrganizationUrl::to(['reports/day', 'type' => 3, 'DateSearch[date]' => $searchModel->date]) ?>"
               class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 <?= $type == 3 ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>">
                <?= Icon::show('payment', 'sm') ?>
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
            <?= EmptyState::card('calendar', $searchModel->date . ' нет ни одного занятия', 'Выберите другую дату для просмотра отчёта') ?>
        <?php endif; ?>
    <?php elseif ($type == 2): ?>
        <?php if (!empty($dataArray['teachers'])): ?>
            <?= $this->render('day/teacher', [
                'teachers' => $dataArray['teachers'],
                'teacherLessons' => $dataArray['teacherLessons'],
                'lessonPupilSalary' => $dataArray['lessonPupilSalary']
            ]) ?>
        <?php else: ?>
            <?= EmptyState::card('calendar', $searchModel->date . ' нет ни одного занятия', 'Выберите другую дату для просмотра отчёта') ?>
        <?php endif; ?>
    <?php elseif ($type == 3): ?>
        <?php if (!empty($dataArray)): ?>
            <?= $this->render('day/payment', [
                'payments' => $dataArray,
            ]) ?>
        <?php else: ?>
            <?= EmptyState::card('payment', $searchModel->date . ' нет ни одной оплаты', 'Выберите другую дату для просмотра отчёта') ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
