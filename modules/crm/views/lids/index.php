<?php

use app\helpers\OrganizationUrl;
use app\helpers\StatusHelper;
use app\models\Lids;
use app\widgets\tailwind\EmptyState;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use app\widgets\tailwind\Modal;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\search\LidsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $funnelStats */

$this->title = 'Лиды';
$this->params['breadcrumbs'][] = $this->title;

$getLidUrl = OrganizationUrl::to(['lids/get-lid']);
$updateUrl = OrganizationUrl::to(['lids/update-ajax']);
?>

<!-- Alpine Store для лидов -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('lids', {
        viewingLid: null,
        editingLid: null,
        isLoading: false,

        async loadLid(id) {
            this.isLoading = true;
            try {
                const response = await fetch('<?= $getLidUrl ?>?id=' + id);
                const data = await response.json();
                if (data.success) {
                    this.viewingLid = data.lid;
                    return true;
                }
            } catch (e) {
                console.error('Error loading lid:', e);
            } finally {
                this.isLoading = false;
            }
            return false;
        },

        openView(id) {
            this.loadLid(id).then(success => {
                if (success) {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'lids-view-modal' }));
                }
            });
        },

        openEdit() {
            this.editingLid = this.viewingLid;
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'lids-view-modal' }));
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'lids-edit-modal' }));
        }
    });
});
</script>

<div class="space-y-6" x-data x-cloak>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Воронка продаж и управление лидами</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Switcher -->
            <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
                <a href="<?= OrganizationUrl::to(['lids-funnel/kanban']) ?>"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
                   title="Kanban">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="5" height="18" rx="1" stroke-width="2"/>
                        <rect x="10" y="3" width="5" height="12" rx="1" stroke-width="2"/>
                        <rect x="17" y="3" width="5" height="15" rx="1" stroke-width="2"/>
                    </svg>
                </a>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-primary-50 text-primary-600" title="Таблица">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                </span>
            </div>
            <button type="button"
                    @click="$dispatch('open-modal', 'lids-form-modal')"
                    class="btn btn-primary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить лид
            </button>
        </div>
    </div>

    <!-- Funnel Stats -->
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($funnelStats as $status => $stat): ?>
                    <?php
                    $isActive = $searchModel->status == $status;
                    $color = StatusHelper::getColor('lids', $status);
                    $colorClasses = [
                        'primary' => ['active' => 'bg-primary-600 text-white', 'inactive' => 'bg-primary-50 text-primary-700 hover:bg-primary-100'],
                        'success' => ['active' => 'bg-success-600 text-white', 'inactive' => 'bg-success-50 text-success-700 hover:bg-success-100'],
                        'warning' => ['active' => 'bg-warning-500 text-white', 'inactive' => 'bg-warning-50 text-warning-700 hover:bg-warning-100'],
                        'danger' => ['active' => 'bg-danger-600 text-white', 'inactive' => 'bg-danger-50 text-danger-700 hover:bg-danger-100'],
                        'info' => ['active' => 'bg-blue-600 text-white', 'inactive' => 'bg-blue-50 text-blue-700 hover:bg-blue-100'],
                        'gray' => ['active' => 'bg-gray-600 text-white', 'inactive' => 'bg-gray-100 text-gray-700 hover:bg-gray-200'],
                        'purple' => ['active' => 'bg-purple-600 text-white', 'inactive' => 'bg-purple-50 text-purple-700 hover:bg-purple-100'],
                        'indigo' => ['active' => 'bg-indigo-600 text-white', 'inactive' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'],
                    ];
                    $btnClass = $isActive
                        ? ($colorClasses[$color]['active'] ?? $colorClasses['gray']['active']) . ' shadow-md ring-2 ring-offset-2 ring-' . $color . '-600'
                        : ($colorClasses[$color]['inactive'] ?? $colorClasses['gray']['inactive']);
                    ?>
                    <a href="<?= Url::to(['index', 'LidsSearch[status]' => $status]) ?>"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= $btnClass ?>">
                        <?= Html::encode($stat['label']) ?>
                        <?= StatusBadge::count($stat['count'], ['color' => $isActive ? 'gray' : $color]) ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($searchModel->status): ?>
                    <a href="<?= Url::to(['index']) ?>" class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                        <?= Icon::show('x', 'sm') ?>
                        Сбросить
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="table-container table-container-scrollable">
            <table class="data-table data-table-sticky">
                <thead>
                    <tr>
                        <th>Контакт</th>
                        <th>Родитель</th>
                        <th>Источник</th>
                        <th>Статус</th>
                        <th>След. контакт</th>
                        <th>Менеджер</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="hover:bg-gray-50 <?= $model->isOverdue() ? 'bg-danger-50' : ($model->isContactToday() ? 'bg-warning-50' : '') ?>"
                        @click="$store.lids.openView(<?= $model->id ?>)"
                        style="cursor: pointer;">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0 text-primary-600">
                                    <?= Icon::show('user') ?>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 hover:text-primary-600">
                                        <?= Html::encode($model->fio ?: '—') ?>
                                    </span>
                                    <?php if ($model->phone): ?>
                                        <div class="text-sm text-gray-500"><?= Html::encode($model->phone) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($model->parent_fio || $model->parent_phone): ?>
                                <div class="text-sm">
                                    <?php if ($model->parent_fio): ?>
                                        <div class="text-gray-900"><?= Html::encode($model->parent_fio) ?></div>
                                    <?php endif; ?>
                                    <?php if ($model->parent_phone): ?>
                                        <div class="text-gray-500"><?= Html::encode($model->parent_phone) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($model->source): ?>
                                <span class="text-sm text-gray-600"><?= Html::encode($model->getSourceLabel()) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= StatusBadge::show('lids', $model->status) ?>
                        </td>
                        <td>
                            <?php if ($model->next_contact_date): ?>
                                <?php
                                $colorClass = 'text-gray-500';
                                if ($model->isOverdue()) {
                                    $colorClass = 'text-danger-600 font-medium';
                                } elseif ($model->isContactToday()) {
                                    $colorClass = 'text-warning-600 font-medium';
                                }
                                ?>
                                <span class="<?= $colorClass ?>"><?= date('d.m.Y', strtotime($model->next_contact_date)) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-gray-500">
                            <?= Html::encode($model->manager ? $model->manager->fio : ($model->manager_name ?: '—')) ?>
                        </td>
                        <td class="text-right" @click.stop>
                            <div class="flex items-center justify-end gap-1">
                                <?php if ($model->getContactPhone()): ?>
                                <a href="tel:<?= Html::encode($model->getContactPhone()) ?>" class="table-action-btn text-green-600" title="Позвонить">
                                    <?= Icon::show('phone', 'sm') ?>
                                </a>
                                <?php if ($model->getWhatsAppUrl()): ?>
                                <a href="<?= $model->getWhatsAppUrl() ?>" target="_blank" class="table-action-btn text-green-500" title="WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= OrganizationUrl::to(['lids/view', 'id' => $model->id]) ?>" class="table-action-btn" title="Просмотр">
                                    <?= Icon::show('eye', 'sm') ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dataProvider->getModels())): ?>
                        <?= EmptyState::tableRow(7, 'funnel', 'Лиды не найдены', 'Добавьте первый лид в воронку продаж', null, null) ?>
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

<!-- Create Modal -->
<?php Modal::begin(['id' => 'lids-form-modal', 'title' => 'Новый лид', 'size' => 'lg']); ?>
<?= $this->render('_form-modal', ['isEdit' => false]) ?>
<?php Modal::end(); ?>

<!-- View Modal -->
<?php Modal::begin(['id' => 'lids-view-modal', 'title' => 'Карточка лида', 'size' => 'xl']); ?>
<?= $this->render('_view-modal') ?>
<?php Modal::end(); ?>

<!-- Edit Modal -->
<?php Modal::begin(['id' => 'lids-edit-modal', 'title' => 'Редактирование лида', 'size' => 'lg']); ?>
<?= $this->render('_form-modal', ['isEdit' => true]) ?>
<?php Modal::end(); ?>
