<?php

use app\models\Pupil;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\search\PupilSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ученики';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Всего: <?= $dataProvider->getTotalCount() ?> учеников</p>
        </div>
        <div>
            <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> Добавить ученика', OrganizationUrl::to(['create']), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <!-- Search/Filter Card -->
    <div class="card">
        <div class="card-body">
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">ФИО</label>
                    <?= Html::activeTextInput($searchModel, 'fio', ['class' => 'form-input', 'placeholder' => 'Поиск по ФИО...']) ?>
                </div>
                <div>
                    <label class="form-label">ИИН</label>
                    <?= Html::activeTextInput($searchModel, 'iin', ['class' => 'form-input', 'placeholder' => 'Поиск по ИИН...']) ?>
                </div>
                <div>
                    <label class="form-label">Класс</label>
                    <?= Html::activeDropDownList($searchModel, 'class_id', \app\helpers\Lists::getGrades(), ['class' => 'form-select', 'prompt' => 'Все классы']) ?>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Найти
                    </button>
                    <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary">Сброс</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-container table-container-scrollable">
            <table class="data-table data-table-sticky">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ИИН</th>
                        <th>ФИО</th>
                        <th>Класс</th>
                        <th>Контакты</th>
                        <th>Родители</th>
                        <th>Баланс</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr>
                        <td class="text-gray-500"><?= $model->id ?></td>
                        <td><?= Html::encode($model->iin) ?></td>
                        <td>
                            <div class="font-medium"><?= Html::encode($model->fio) ?></div>
                        </td>
                        <td>
                            <span class="badge badge-gray"><?= \app\helpers\Lists::getGrades()[$model->class_id] ?? '-' ?></span>
                        </td>
                        <td class="text-gray-500">
                            <?php if ($model->phone): ?>
                                <div>+<?= Html::encode($model->phone) ?></div>
                            <?php endif; ?>
                            <?php if ($model->home_phone): ?>
                                <div class="text-gray-400">+<?= Html::encode($model->home_phone) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-gray-500">
                            <?php if ($model->parent_fio): ?>
                                <div><?= Html::encode($model->parent_fio) ?></div>
                                <div class="text-gray-400">+<?= Html::encode($model->parent_phone) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($model->balance > 0): ?>
                                <span class="font-semibold text-primary-600"><?= number_format($model->balance, 0, '.', ' ') ?> ₸</span>
                            <?php else: ?>
                                <span class="font-semibold text-danger-600"><?= number_format($model->balance, 0, '.', ' ') ?> ₸</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>" class="table-action-btn" title="Посмотреть">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="8" class="!py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <p class="mt-2">Ученики не найдены</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($dataProvider->pagination->pageCount > 1): ?>
        <div class="card-footer">
            <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
