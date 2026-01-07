<?php

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\models\TeacherSalary;
use app\widgets\tailwind\CollapsibleFilter;
use app\widgets\tailwind\EmptyState;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;

// Проверка: это только учитель (без админских прав)?
$isOnlyTeacher = Yii::$app->user->can(OrganizationRoles::TEACHER)
    && !Yii::$app->user->can(OrganizationRoles::ADMIN)
    && !Yii::$app->user->can(OrganizationRoles::DIRECTOR)
    && !Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR);

/** @var yii\web\View $this */
/** @var app\models\search\TeacherSalarySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Зарплаты учителей';
$this->params['breadcrumbs'][] = $this->title;

// Считаем активные фильтры
$activeFilters = 0;
if (!empty($searchModel->teacher_name)) $activeFilters++;
if (!empty($searchModel->status)) $activeFilters++;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Расчёт и управление зарплатами преподавателей</p>
        </div>
        <?php if (!$isOnlyTeacher): ?>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['salary/rates']) ?>" class="btn btn-secondary">
                <?= Icon::show('settings', 'sm') ?>
                Ставки
            </a>
            <a href="<?= OrganizationUrl::to(['salary/calculate']) ?>" class="btn btn-primary">
                <?= Icon::show('calculator', 'sm') ?>
                Рассчитать
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Help Info Block -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <?= Icon::show('info', 'md', 'text-blue-500') ?>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Как работать с зарплатами</h4>
                <div class="text-sm text-blue-700 space-y-1">
                    <p>Для расчёта зарплаты выберите преподавателя и период, затем нажмите "Рассчитать". Система автоматически подсчитает оплату за проведённые уроки на основе настроенных ставок.</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                        <span><strong>Черновик</strong> — можно редактировать бонусы и вычеты</span>
                        <span><strong>Утверждена</strong> — готова к выплате</span>
                        <span><strong>Выплачена</strong> — зарплата выдана</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <?php CollapsibleFilter::begin([
        'title' => 'Фильтры',
        'collapsed' => $activeFilters === 0,
        'badge' => $activeFilters,
    ]) ?>
        <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Преподаватель</label>
                <input type="text" name="TeacherSalarySearch[teacher_name]"
                       value="<?= Html::encode($searchModel->teacher_name ?? '') ?>"
                       class="form-input" placeholder="Поиск...">
            </div>
            <div>
                <label class="form-label">Статус</label>
                <?= Html::activeDropDownList($searchModel, 'status', TeacherSalary::getStatusList(), [
                    'class' => 'form-select',
                    'prompt' => 'Все статусы'
                ]) ?>
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="submit" class="btn btn-primary" data-loading-text="Поиск...">
                    <?= Icon::show('search', 'sm') ?>
                    Поиск
                </button>
                <a href="<?= OrganizationUrl::to(['salary/index']) ?>" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>
    <?php CollapsibleFilter::end() ?>

    <!-- Table -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Преподаватель</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Период</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Уроков</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Учеников</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <?= $model->teacher ? Html::encode($model->teacher->fio) : 'Не указан' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $model->getPeriodLabel() ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                            <?= $model->lessons_count ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                            <?= $model->students_count ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900"><?= $model->getFormattedTotal() ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?= StatusBadge::show('salary', $model->status) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="<?= OrganizationUrl::to(['salary/view', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary">
                                <?= Icon::show('eye', 'sm') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <?= EmptyState::tableRow(7, 'wallet', 'Нет данных о зарплатах', 'Используйте кнопку "Рассчитать" для создания расчёта', ['salary/calculate'], 'Рассчитать') ?>
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
