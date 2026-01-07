<?php

use app\helpers\OrganizationUrl;
use app\models\PupilEducation;
use app\widgets\tailwind\GroupTabs;
use app\widgets\tailwind\Icon;
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
                <?= Icon::show('users', 'lg', '', ['style' => 'color: ' . Html::encode($model->color ?: '#3b82f6')]) ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">Ученики группы: <?= $dataProvider->getTotalCount() ?></p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <?= GroupTabs::widget(['model' => $model, 'activeTab' => 'pupils']) ?>

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
                            <?= Icon::show('users', 'xl', 'mx-auto text-gray-400') ?>
                            <p class="mt-4">В группе пока нет учеников</p>
                            <p class="text-sm text-gray-400 mt-1">Ученики появятся после добавления обучения в группу</p>
                            <a href="<?= OrganizationUrl::to(['pupil/index']) ?>" class="btn btn-secondary mt-4">
                                <?= Icon::show('arrow-right', 'sm') ?> К списку учеников
                            </a>
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
