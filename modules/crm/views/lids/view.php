<?php

use app\helpers\OrganizationUrl;
use app\models\LidHistory;
use app\models\Lids;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Lids $model */
/** @var app\models\LidHistory[] $histories */

$this->title = $model->fio ?: 'Лид #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Лиды', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;

$contactPhone = $model->getContactPhone();
$contactName = $model->getContactName();
?>

<div class="space-y-6" x-data="lidView()" x-cloak>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center text-primary-600">
                <?= Icon::show('user', 'lg') ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-gray-500">Карточка лида</p>
                    <?php if ($model->getDaysInStatus() > 0): ?>
                        <span class="text-xs text-gray-400">• <?= $model->getDaysInStatus() ?> дн. в статусе</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if ($model->canConvertToPupil()): ?>
                <a href="<?= OrganizationUrl::to(['lids/convert-to-pupil', 'id' => $model->id]) ?>" class="btn btn-success">
                    <?= Icon::show('user-plus', 'sm') ?>
                    Создать ученика
                </a>
            <?php elseif ($model->isConverted()): ?>
                <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="btn btn-outline text-success-600 border-success-600 hover:bg-success-50">
                    <?= Icon::show('academic-cap', 'sm') ?>
                    Карточка ученика
                </a>
            <?php endif; ?>
            <a href="<?= OrganizationUrl::to(['lids/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <?= Icon::show('pencil', 'sm') ?>
                Редактировать
            </a>
            <?= Html::a(Icon::show('trash', 'sm') . ' Удалить',
                OrganizationUrl::to(['lids/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить лид?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Status & Alerts -->
    <div class="flex flex-wrap items-center gap-3">
        <?= StatusBadge::show('lids', $model->status, ['size' => 'lg']) ?>
        <?php if ($model->source): ?>
            <span class="badge badge-secondary"><?= Html::encode($model->getSourceLabel()) ?></span>
        <?php endif; ?>
        <?php if ($model->isOverdue()): ?>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-danger-100 text-danger-700">
                <?= Icon::show('exclamation-circle', 'sm') ?>
                Просрочен контакт
            </span>
        <?php elseif ($model->isContactToday()): ?>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-warning-100 text-warning-700">
                <?= Icon::show('clock', 'sm') ?>
                Контакт сегодня
            </span>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Child Contact Info -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Данные ребёнка</h3>
                    <?php if ($model->contact_person === Lids::CONTACT_PUPIL): ?>
                        <span class="badge badge-primary">Контактное лицо</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ФИО</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->fio ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Телефон</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->phone): ?>
                                    <a href="tel:<?= Html::encode($model->phone) ?>" class="text-primary-600 hover:text-primary-800">
                                        <?= Html::encode($model->phone) ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Школа</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->school ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Класс</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->class_id): ?>
                                    <span class="badge badge-secondary"><?= \app\helpers\Lists::getGrades()[$model->class_id] ?? $model->class_id ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Parent Contact Info -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Данные родителя</h3>
                    <?php if ($model->contact_person === Lids::CONTACT_PARENT): ?>
                        <span class="badge badge-primary">Контактное лицо</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ФИО</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->parent_fio ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Телефон</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->parent_phone): ?>
                                    <a href="tel:<?= Html::encode($model->parent_phone) ?>" class="text-primary-600 hover:text-primary-800">
                                        <?= Html::encode($model->parent_phone) ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Funnel Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Воронка продаж</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Источник</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->getSourceLabel() ?: '—') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Менеджер</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->manager ? $model->manager->fio : ($model->manager_name ?: '—')) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата обращения</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->date ? date('d.m.Y', strtotime($model->date)) : '—' ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Следующий контакт</dt>
                            <dd class="mt-1 text-sm">
                                <?php if ($model->next_contact_date): ?>
                                    <?php
                                    $colorClass = 'text-gray-900';
                                    if ($model->isOverdue()) {
                                        $colorClass = 'text-danger-600 font-medium';
                                    } elseif ($model->isContactToday()) {
                                        $colorClass = 'text-warning-600 font-medium';
                                    }
                                    ?>
                                    <span class="<?= $colorClass ?>"><?= date('d.m.Y', strtotime($model->next_contact_date)) ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if ($model->status_changed_at): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Статус изменён</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= Yii::$app->formatter->asDatetime($model->status_changed_at, 'php:d.m.Y H:i') ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->converted_at): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Конвертирован</dt>
                            <dd class="mt-1 text-sm text-success-600 font-medium"><?= Yii::$app->formatter->asDatetime($model->converted_at, 'php:d.m.Y H:i') ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->lost_reason): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Причина потери</dt>
                            <dd class="mt-1 text-sm text-danger-600"><?= Html::encode($model->lost_reason) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Test Results -->
            <?php if ($model->total_point || $model->sale || $model->total_sum): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Пробное тестирование</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Баллы</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold"><?= $model->total_point ?: '—' ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Скидка</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($model->sale): ?>
                                    <span class="badge badge-warning"><?= $model->sale ?>%</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Сумма</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                <?= $model->total_sum ? number_format($model->total_sum, 0, '.', ' ') . ' ₸' : '—' ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comment -->
            <?php if ($model->comment): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Комментарий</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= Html::encode($model->comment) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Interaction History -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">История взаимодействий</h3>
                    <button type="button" @click="showAddForm = !showAddForm" class="btn btn-sm btn-primary">
                        <?= Icon::show('plus', 'sm') ?>
                        Добавить
                    </button>
                </div>

                <!-- Quick Add Form -->
                <div x-show="showAddForm" x-collapse class="border-b border-gray-200 bg-gray-50 p-4">
                    <form @submit.prevent="addInteraction" class="space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" @click="interactionType = 'call'"
                                    :class="interactionType === 'call' ? 'ring-2 ring-primary-500 bg-primary-50' : 'hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 p-3 rounded-lg border border-gray-200 transition-all">
                                <?= Icon::show('phone', 'sm', 'text-green-600') ?>
                                <span class="text-xs font-medium">Звонок</span>
                            </button>
                            <button type="button" @click="interactionType = 'whatsapp'"
                                    :class="interactionType === 'whatsapp' ? 'ring-2 ring-primary-500 bg-primary-50' : 'hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 p-3 rounded-lg border border-gray-200 transition-all">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                <span class="text-xs font-medium">WhatsApp</span>
                            </button>
                            <button type="button" @click="interactionType = 'message'"
                                    :class="interactionType === 'message' ? 'ring-2 ring-primary-500 bg-primary-50' : 'hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 p-3 rounded-lg border border-gray-200 transition-all">
                                <?= Icon::show('chat-bubble-left', 'sm', 'text-blue-600') ?>
                                <span class="text-xs font-medium">Сообщение</span>
                            </button>
                            <button type="button" @click="interactionType = 'note'"
                                    :class="interactionType === 'note' ? 'ring-2 ring-primary-500 bg-primary-50' : 'hover:bg-gray-100'"
                                    class="flex flex-col items-center gap-1 p-3 rounded-lg border border-gray-200 transition-all">
                                <?= Icon::show('document-text', 'sm', 'text-gray-600') ?>
                                <span class="text-xs font-medium">Заметка</span>
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
                            <textarea x-model="interactionComment" rows="2"
                                      class="form-control w-full"
                                      placeholder="Опишите результат взаимодействия..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div x-show="interactionType === 'call'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Длительность (мин)</label>
                                <input type="number" x-model="callDuration" min="0"
                                       class="form-control w-full" placeholder="5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Следующий контакт</label>
                                <input type="date" x-model="nextContactDate"
                                       class="form-control w-full">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showAddForm = false; resetForm()" class="btn btn-secondary">
                                Отмена
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span x-show="loading" class="animate-spin mr-2">⟳</span>
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Timeline -->
                <div class="card-body">
                    <?php if (empty($histories)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                <?= Icon::show('clock') ?>
                            </div>
                            <p class="text-sm">История взаимодействий пуста</p>
                            <p class="text-xs text-gray-400 mt-1">Добавьте первую запись</p>
                        </div>
                    <?php else: ?>
                        <div class="relative" id="history-timeline">
                            <!-- Timeline line -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-6">
                                <?php foreach ($histories as $history): ?>
                                    <?= $this->render('_history-item', ['item' => $history]) ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Быстрые действия</h3>
                </div>
                <div class="card-body space-y-2">
                    <?php if ($contactPhone): ?>
                    <a href="tel:<?= Html::encode($contactPhone) ?>" class="btn btn-secondary w-full justify-start gap-3">
                        <?= Icon::show('phone', 'sm', 'text-green-600') ?>
                        <span>Позвонить <?= $contactName ? '(' . Html::encode($contactName) . ')' : '' ?></span>
                    </a>
                    <?php if ($model->getWhatsAppUrl()): ?>
                    <a href="<?= $model->getWhatsAppUrl() ?>" target="_blank" class="btn btn-secondary w-full justify-start gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($model->parent_phone && $model->parent_phone !== $model->phone): ?>
                    <a href="tel:<?= Html::encode($model->parent_phone) ?>" class="btn btn-outline w-full justify-start gap-3">
                        <?= Icon::show('phone', 'sm', 'text-blue-600') ?>
                        <span>Родитель: <?= Html::encode($model->parent_phone) ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if (!$model->isConverted() && $model->status !== Lids::STATUS_LOST): ?>
                    <hr class="my-3">
                    <a href="<?= OrganizationUrl::to(['lids/update', 'id' => $model->id, 'status' => Lids::STATUS_LOST]) ?>"
                       class="btn btn-outline text-danger-600 border-danger-300 hover:bg-danger-50 w-full justify-center">
                        <?= Icon::show('x-circle', 'sm') ?>
                        Отметить как потерян
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Progress -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Прогресс</h3>
                </div>
                <div class="card-body">
                    <?php
                    $statuses = Lids::getKanbanStatusList();
                    $currentIndex = array_search($model->status, array_keys($statuses));
                    $progressPercent = $currentIndex !== false ? (($currentIndex + 1) / count($statuses)) * 100 : 0;
                    ?>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Этап воронки</span>
                            <span class="font-medium"><?= ($currentIndex !== false ? $currentIndex + 1 : 0) ?>/<?= count($statuses) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full transition-all" style="width: <?= $progressPercent ?>%"></div>
                        </div>
                        <div class="space-y-2 mt-4">
                            <?php foreach ($statuses as $status => $label): ?>
                                <?php
                                $isDone = array_search($status, array_keys($statuses)) < $currentIndex;
                                $isCurrent = $status === $model->status;
                                ?>
                                <div class="flex items-center gap-2 text-sm <?= $isCurrent ? 'font-medium text-primary-600' : ($isDone ? 'text-gray-500' : 'text-gray-400') ?>">
                                    <?php if ($isDone): ?>
                                        <span class="w-5 h-5 rounded-full bg-success-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    <?php elseif ($isCurrent): ?>
                                        <span class="w-5 h-5 rounded-full bg-primary-500 flex items-center justify-center">
                                            <span class="w-2 h-2 rounded-full bg-white"></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="w-5 h-5 rounded-full border-2 border-gray-300"></span>
                                    <?php endif; ?>
                                    <span><?= Html::encode($label) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Даты</h3>
                </div>
                <div class="card-body text-sm text-gray-500 space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium">Создан:</span>
                        <span><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') : '—' ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Обновлён:</span>
                        <span><?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') : '—' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function lidView() {
    return {
        showAddForm: false,
        loading: false,
        interactionType: 'call',
        interactionComment: '',
        callDuration: '',
        nextContactDate: '',

        resetForm() {
            this.interactionType = 'call';
            this.interactionComment = '';
            this.callDuration = '';
            this.nextContactDate = '';
        },

        async addInteraction() {
            if (!this.interactionComment.trim()) {
                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    Alpine.store('toast').error('Введите комментарий');
                }
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('<?= OrganizationUrl::to(['lids/add-interaction']) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: new URLSearchParams({
                        lid_id: <?= $model->id ?>,
                        type: this.interactionType,
                        comment: this.interactionComment,
                        call_duration: this.callDuration ? this.callDuration * 60 : '',
                        next_contact_date: this.nextContactDate
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Добавляем новую запись в начало таймлайна
                    const timeline = document.getElementById('history-timeline');
                    if (timeline) {
                        const container = timeline.querySelector('.space-y-6');
                        if (container) {
                            container.insertAdjacentHTML('afterbegin', data.history);
                        }
                    } else {
                        // Если истории не было - перезагружаем страницу
                        location.reload();
                    }

                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').success(data.message);
                    }

                    this.showAddForm = false;
                    this.resetForm();
                } else {
                    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                        Alpine.store('toast').error(data.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
                    Alpine.store('toast').error('Ошибка сети');
                }
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
