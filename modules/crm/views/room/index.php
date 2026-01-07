<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\LinkPager;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Кабинеты');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Управление кабинетами и аудиториями</p>
        </div>
        <div>
            <a href="<?= OrganizationUrl::to(['room/create']) ?>" class="btn btn-primary">
                <?= Icon::show('plus') ?>
                <?= Yii::t('main', 'Добавить кабинет') ?>
            </a>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Кабинет</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Код</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вместимость</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="sortable-rooms">
                    <?php foreach ($dataProvider->getModels() as $index => $model): ?>
                    <tr class="hover:bg-gray-50 cursor-move" data-id="<?= $model->id ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <?= Icon::show('menu', 'sm', 'text-gray-400 handle cursor-move') ?>
                                <span class="text-sm text-gray-500"><?= $index + 1 ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: <?= Html::encode($model->color) ?>20">
                                    <?= Icon::show('building-office', 'sm', '', ['style' => 'color: ' . Html::encode($model->color)]) ?>
                                </div>
                                <span class="text-sm font-medium text-gray-900"><?= Html::encode($model->name) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($model->code): ?>
                                <span class="badge badge-gray"><?= Html::encode($model->code) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($model->capacity > 0): ?>
                                <div class="flex items-center gap-1">
                                    <?= Icon::show('users', 'sm', 'text-gray-400') ?>
                                    <?= $model->capacity ?> чел.
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= OrganizationUrl::to(['room/update', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary" title="Редактировать">
                                    <?= Icon::show('pencil', 'sm') ?>
                                </a>
                                <form action="<?= OrganizationUrl::to(['room/delete', 'id' => $model->id]) ?>" method="post" class="inline" onsubmit="return confirm('Удалить кабинет?')">
                                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary text-danger-600 hover:text-danger-700" title="Удалить">
                                        <?= Icon::show('trash', 'sm') ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <?= Icon::show('building-office', 'xl', 'mx-auto text-gray-400') ?>
                            <p class="mt-2">Кабинеты не найдены</p>
                            <a href="<?= OrganizationUrl::to(['room/create']) ?>" class="btn btn-primary mt-4">
                                Добавить первый кабинет
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

<?php
$sortUrl = OrganizationUrl::to(['room/sort']);
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('sortable-rooms');
    if (!tbody || !window.Sortable) return;

    new Sortable(tbody, {
        handle: '.handle',
        animation: 150,
        ghostClass: 'bg-primary-50',
        onEnd: function(evt) {
            const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(row => row.dataset.id);
            fetch('{$sortUrl}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ ids: ids })
            });
        }
    });
});
JS;
$this->registerJs($js);
?>
