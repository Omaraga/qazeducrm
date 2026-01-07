<?php

use app\helpers\FeatureHelper;
use app\models\Pupil;
use app\models\search\PupilSearch;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LimitProgress;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\search\PupilSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ученики';
$this->params['breadcrumbs'][] = $this->title;

$canAddPupil = FeatureHelper::canAddPupil();
$buttonStatus = LimitProgress::addButtonStatus('pupils');

// Данные для расширенных фильтров
$groups = PupilSearch::getGroupsList();
$tariffs = PupilSearch::getTariffsList();
$balanceTypes = PupilSearch::getBalanceTypeOptions();
$statusOptions = PupilSearch::getStatusOptions();

// Проверка наличия активных расширенных фильтров
$hasAdvancedFilters = $searchModel->status !== null && $searchModel->status !== ''
    || !empty($searchModel->balance_type)
    || !empty($searchModel->group_id)
    || !empty($searchModel->tariff_id)
    || !empty($searchModel->date_from)
    || !empty($searchModel->date_to);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <?= Html::encode($this->title) ?>
                <?= LimitProgress::inline('pupils', ['options' => ['class' => 'ml-2']]) ?>
            </h1>
            <p class="text-gray-500 mt-1">Всего: <?= $dataProvider->getTotalCount() ?> учеников</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($canAddPupil): ?>
                <?= Html::a(Icon::show('plus', 'sm') . ' Добавить ученика', OrganizationUrl::to(['create']), ['class' => 'btn btn-primary', 'title' => 'Добавить нового ученика']) ?>
            <?php else: ?>
                <span class="btn btn-secondary opacity-50 cursor-not-allowed" title="<?= Html::encode($buttonStatus['message'] ?? 'Достигнут лимит') ?>">
                    <?= Icon::show('lock', 'sm') ?>
                    Добавить ученика
                </span>
                <?= Html::a('Увеличить лимит', OrganizationUrl::to(['subscription/upgrade']), ['class' => 'btn btn-warning btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search/Filter Card -->
    <div class="card" x-data="{ showAdvanced: <?= $hasAdvancedFilters ? 'true' : 'false' ?> }">
        <div class="card-body">
            <form method="get">
                <!-- Базовые фильтры -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <button type="submit" class="btn btn-primary" title="Поиск по заданным критериям">
                            <?= Icon::show('search', 'sm') ?>
                            Найти
                        </button>
                        <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary" title="Сбросить все фильтры">
                            <?= Icon::show('refresh', 'sm') ?>
                            Сброс
                        </a>
                    </div>
                </div>

                <!-- Кнопка расширенных фильтров -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="text-sm text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                        <?= Icon::show('filter', 'xs') ?>
                        <span x-text="showAdvanced ? 'Скрыть расширенные фильтры' : 'Расширенные фильтры'"></span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAdvanced }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <?php if ($hasAdvancedFilters): ?>
                            <span class="ml-2 px-2 py-0.5 text-xs bg-primary-100 text-primary-700 rounded-full">активны</span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Расширенные фильтры -->
                <div x-show="showAdvanced" x-collapse class="mt-4 pt-4 border-t border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="form-label">Статус</label>
                            <?= Html::activeDropDownList($searchModel, 'status', $statusOptions, [
                                'class' => 'form-select',
                                'prompt' => 'Все статусы',
                            ]) ?>
                        </div>
                        <div>
                            <label class="form-label">Баланс</label>
                            <?= Html::activeDropDownList($searchModel, 'balance_type', $balanceTypes, [
                                'class' => 'form-select',
                                'prompt' => 'Любой баланс',
                            ]) ?>
                        </div>
                        <div>
                            <label class="form-label">Группа</label>
                            <?= Html::activeDropDownList($searchModel, 'group_id', $groups, [
                                'class' => 'form-select',
                                'prompt' => empty($groups) ? 'Нет групп' : 'Все группы',
                                'disabled' => empty($groups),
                            ]) ?>
                            <?php if (empty($groups)): ?>
                                <a href="<?= OrganizationUrl::to(['group/create']) ?>" class="text-xs text-primary-600 hover:underline mt-1 inline-block">
                                    + Создать группу
                                </a>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label">Тариф</label>
                            <?= Html::activeDropDownList($searchModel, 'tariff_id', $tariffs, [
                                'class' => 'form-select',
                                'prompt' => empty($tariffs) ? 'Нет тарифов' : 'Все тарифы',
                                'disabled' => empty($tariffs),
                            ]) ?>
                            <?php if (empty($tariffs)): ?>
                                <a href="<?= OrganizationUrl::to(['tariff/create']) ?>" class="text-xs text-primary-600 hover:underline mt-1 inline-block">
                                    + Создать тариф
                                </a>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label">Добавлен с</label>
                            <?= Html::activeTextInput($searchModel, 'date_from', [
                                'class' => 'form-input',
                                'placeholder' => 'дд.мм.гггг',
                                'data-datepicker' => 'true',
                            ]) ?>
                        </div>
                        <div>
                            <label class="form-label">Добавлен по</label>
                            <?= Html::activeTextInput($searchModel, 'date_to', [
                                'class' => 'form-input',
                                'placeholder' => 'дд.мм.гггг',
                                'data-datepicker' => 'true',
                            ]) ?>
                        </div>
                    </div>
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
                        <th class="w-24 text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="text-gray-500"><?= $model->id ?></td>
                        <td><?= Html::encode($model->iin) ?></td>
                        <td>
                            <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>" class="font-medium text-primary-600 hover:text-primary-700">
                                <?= Html::encode($model->fio) ?>
                            </a>
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
                                <span class="font-semibold text-primary-600" title="Положительный баланс"><?= number_format($model->balance, 0, '.', ' ') ?> ₸</span>
                            <?php elseif ($model->balance < 0): ?>
                                <span class="font-semibold text-danger-600" title="Отрицательный баланс"><?= number_format($model->balance, 0, '.', ' ') ?> ₸</span>
                            <?php else: ?>
                                <span class="font-semibold text-gray-500" title="Нулевой баланс">0 ₸</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->id]) ?>"
                                   class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100"
                                   title="Просмотр">
                                    <?= Icon::show('eye', 'sm') ?>
                                </a>
                                <a href="<?= OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $model->id]) ?>"
                                   class="p-1.5 text-gray-400 hover:text-success-600 rounded hover:bg-gray-100"
                                   title="Добавить обучение">
                                    <?= Icon::show('book', 'sm') ?>
                                </a>
                                <a href="<?= OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $model->id]) ?>"
                                   class="p-1.5 text-gray-400 hover:text-warning-600 rounded hover:bg-gray-100"
                                   title="Добавить оплату">
                                    <?= Icon::show('wallet', 'sm') ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="8" class="!py-12 text-center text-gray-500">
                            <div class="mx-auto text-gray-400">
                                <?= Icon::show('users', 'xl', 'mx-auto') ?>
                            </div>
                            <p class="mt-2">Ученики не найдены</p>
                            <?php if ($canAddPupil): ?>
                            <p class="mt-1">
                                <?= Html::a(Icon::show('plus', 'xs') . ' Добавить первого ученика', OrganizationUrl::to(['create']), ['class' => 'text-primary-600 hover:text-primary-700']) ?>
                            </p>
                            <?php endif; ?>
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
