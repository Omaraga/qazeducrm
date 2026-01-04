<?php

use app\helpers\OrganizationUrl;
use app\models\PupilEducation;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->code . '-' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= Html::encode($model->color ?: '#3b82f6') ?>20">
                <svg class="w-6 h-6" style="color: <?= Html::encode($model->color ?: '#3b82f6') ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Ученики группы: <?= $dataProvider->getTotalCount() ?></p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="<?= OrganizationUrl::to(['group/view', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Основные данные
            </a>
            <a href="<?= OrganizationUrl::to(['group/teachers', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                Преподаватели
            </a>
            <a href="<?= OrganizationUrl::to(['group/pupils', 'id' => $model->id]) ?>"
               class="px-4 py-2 text-sm font-medium border-b-2 border-primary-500 text-primary-600">
                Ученики
            </a>
        </nav>
    </div>

    <!-- Pupils Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ИИН</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ФИО</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Контакты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Родители</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Класс</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оплачено до</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $pupil): ?>
                    <?php
                    // Get paid until date
                    $pupilEducation = PupilEducation::find()->innerJoinWith([
                        'groups' => function($q) {
                            $q->andWhere(['<>', 'education_group.is_deleted', 1]);
                        }
                    ])->where(['pupil_education.pupil_id' => $pupil->id, 'education_group.group_id' => $model->id])
                      ->orderBy('date_end DESC')->one();
                    $paidUntil = $pupilEducation ? date('d.m.Y', strtotime($pupilEducation->date_end)) : null;
                    $isPastDue = $pupilEducation && strtotime($pupilEducation->date_end) < time();
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $pupil->id ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= Html::encode($pupil->iin) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                <?= Html::encode($pupil->fio) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($pupil->phone): ?>
                                <div>+<?= Html::encode($pupil->phone) ?></div>
                            <?php endif; ?>
                            <?php if ($pupil->home_phone): ?>
                                <div class="text-gray-400">+<?= Html::encode($pupil->home_phone) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($pupil->parent_fio): ?>
                                <div><?= Html::encode($pupil->parent_fio) ?></div>
                                <div class="text-gray-400"><?= Html::encode($pupil->parent_phone) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($pupil->class_id): ?>
                                <span class="badge badge-secondary"><?= \app\helpers\Lists::getGrades()[$pupil->class_id] ?? $pupil->class_id ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($paidUntil): ?>
                                <span class="badge <?= $isPastDue ? 'badge-danger' : 'badge-success' ?>"><?= $paidUntil ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <p class="mt-2">В группе пока нет учеников</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
