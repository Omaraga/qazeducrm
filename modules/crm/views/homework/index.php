<?php

/**
 * Список домашних заданий
 *
 * @var yii\web\View $this
 * @var app\models\search\HomeworkSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use app\helpers\OrganizationUrl;
use app\models\Homework;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Домашние задания');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Yii::t('app', 'Создание и проверка домашних заданий') ?></p>
        </div>
        <div class="flex gap-2">
            <?= Html::a(Icon::show('plus', 'sm') . ' ' . Yii::t('app', 'Создать задание'), OrganizationUrl::to(['homework/create']), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card">
        <div class="card-body">
            <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="form-label"><?= Yii::t('app', 'Статус') ?></label>
                    <?= Html::activeDropDownList($searchModel, 'status', Homework::getStatusList(), ['class' => 'form-select', 'prompt' => Yii::t('app', 'Все статусы')]) ?>
                </div>
                <div>
                    <label class="form-label"><?= Yii::t('app', 'Срок с') ?></label>
                    <?= Html::activeInput('date', $searchModel, 'date_from', ['class' => 'form-input']) ?>
                </div>
                <div>
                    <label class="form-label"><?= Yii::t('app', 'Срок по') ?></label>
                    <?= Html::activeInput('date', $searchModel, 'date_to', ['class' => 'form-input']) ?>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label"><?= Yii::t('app', 'Поиск') ?></label>
                    <?= Html::activeTextInput($searchModel, 'title', ['class' => 'form-input', 'placeholder' => Yii::t('app', 'Название задания')]) ?>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= Icon::show('search', 'sm') ?>
                        <?= Yii::t('app', 'Найти') ?>
                    </button>
                    <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary"><?= Yii::t('app', 'Сброс') ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица -->
    <?php if (count($dataProvider->getModels()) > 0): ?>
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('app', 'Задание') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('app', 'Срок сдачи') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('app', 'Статус') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('app', 'Сдано') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= Yii::t('app', 'Автор') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <?php
                        /** @var Homework $model */
                        $isOverdue = $model->isOverdue() && $model->status === Homework::STATUS_ACTIVE;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= OrganizationUrl::to(['homework/view', 'id' => $model->id]) ?>" class="font-medium text-gray-900 hover:text-primary-600">
                                    <?= Html::encode($model->title) ?>
                                </a>
                                <?php if ($model->group): ?>
                                    <div class="text-sm text-gray-500"><?= Html::encode($model->group->name) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="<?= $isOverdue ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= Yii::$app->formatter->asDate($model->due_date, 'short') ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <span class="text-xs text-red-500 ml-1"><?= Yii::t('app', 'просрочено') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $model->getStatusClass() ?>">
                                    <?= $model->getStatusLabel() ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $submitted = $model->getSubmittedCount();
                                $checked = $model->getCheckedCount();
                                $total = $model->getStudentsCount();
                                ?>
                                <span class="font-medium"><?= $submitted ?></span>/<span class="text-gray-500"><?= $total ?></span>
                                <?php if ($checked > 0): ?>
                                    <span class="text-green-600 text-xs">(<?= $checked ?> <?= Yii::t('app', 'проверено') ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $model->creator ? Html::encode($model->creator->first_name . ' ' . $model->creator->last_name) : '—' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= OrganizationUrl::to(['homework/view', 'id' => $model->id]) ?>" class="text-gray-400 hover:text-primary-600" title="<?= Yii::t('app', 'Просмотр') ?>">
                                        <?= Icon::show('eye', 'md') ?>
                                    </a>
                                    <a href="<?= OrganizationUrl::to(['homework/update', 'id' => $model->id]) ?>" class="text-gray-400 hover:text-primary-600" title="<?= Yii::t('app', 'Редактировать') ?>">
                                        <?= Icon::show('pencil', 'md') ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body py-12 text-center text-gray-500">
            <?= Icon::show('clipboard-document-list', 'xl', 'mx-auto text-gray-300') ?>
            <p class="mt-4 text-lg font-medium"><?= Yii::t('app', 'Домашние задания не найдены') ?></p>
            <p class="text-sm text-gray-400 mt-1"><?= Yii::t('app', 'Создайте первое задание для группы') ?></p>
            <?= Html::a(Icon::show('plus', 'sm') . ' ' . Yii::t('app', 'Создать задание'), OrganizationUrl::to(['homework/create']), ['class' => 'btn btn-primary mt-4']) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
    <div class="flex justify-center">
        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
    <?php endif; ?>
</div>
