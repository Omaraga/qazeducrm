<?php
/**
 * @var yii\web\View $this
 * @var array $grouped
 * @var array $statuses
 */

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$this->title = 'Скрипты продаж';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-sm text-gray-500 mt-1">Скрипты для работы с лидами на разных этапах воронки</p>
        </div>
        <a href="<?= OrganizationUrl::to(['sales-script/create']) ?>" class="btn btn-primary">
            <?= Icon::show('plus', 'sm') ?>
            Добавить скрипт
        </a>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <!-- Scripts by status -->
    <?php foreach ($grouped as $status => $data): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <?= Icon::show('document-text', 'md', 'text-primary-600') ?>
                    <?= Html::encode($data['label']) ?>
                    <span class="text-sm font-normal text-gray-500">(<?= count($data['scripts']) ?>)</span>
                </h2>
            </div>

            <div class="divide-y divide-gray-100">
                <?php foreach ($data['scripts'] as $script): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors" x-data="{ expanded: false }">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-medium text-gray-900"><?= Html::encode($script->title) ?></h3>
                                    <?php if (!$script->is_active): ?>
                                        <span class="badge badge-warning text-xs">Неактивен</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Preview -->
                                <p class="text-sm text-gray-600 line-clamp-2"><?= Html::encode(substr($script->content, 0, 200)) ?>...</p>

                                <!-- Expand button -->
                                <button @click="expanded = !expanded"
                                        class="mt-3 text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <span x-text="expanded ? 'Свернуть' : 'Подробнее'"></span>
                                    <svg class="w-4 h-4 transition-transform" :class="expanded && 'rotate-180'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Expanded content -->
                                <div x-show="expanded" x-collapse class="mt-4 space-y-4">
                                    <!-- Full script -->
                                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-100">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Текст скрипта:</h4>
                                        <p class="text-sm text-gray-600 whitespace-pre-wrap"><?= Html::encode($script->content) ?></p>
                                    </div>

                                    <!-- Tips -->
                                    <?php $tips = $script->getTipsArray(); ?>
                                    <?php if (!empty($tips)): ?>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                                <?= Icon::show('light-bulb', 'sm', 'text-amber-500') ?>
                                                Советы:
                                            </h4>
                                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                                <?php foreach ($tips as $tip): ?>
                                                    <li><?= Html::encode($tip) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Objections -->
                                    <?php $objections = $script->getObjectionsArray(); ?>
                                    <?php if (!empty($objections)): ?>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1">
                                                <?= Icon::show('chat-bubble-left-right', 'sm', 'text-red-500') ?>
                                                Работа с возражениями:
                                            </h4>
                                            <div class="space-y-2">
                                                <?php foreach ($objections as $obj): ?>
                                                    <div class="bg-gray-50 rounded-lg p-3">
                                                        <p class="text-sm font-medium text-red-600 mb-1">"<?= Html::encode($obj['objection']) ?>"</p>
                                                        <p class="text-sm text-green-700"><?= Html::encode($obj['response']) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="<?= OrganizationUrl::to(['sales-script/update', 'id' => $script->id]) ?>"
                                   class="btn btn-sm btn-secondary">
                                    <?= Icon::show('pencil', 'sm') ?>
                                </a>
                                <button type="button"
                                        onclick="if(confirm('Удалить скрипт?')) { document.getElementById('delete-form-<?= $script->id ?>').submit(); }"
                                        class="btn btn-sm btn-outline-danger">
                                    <?= Icon::show('trash', 'sm') ?>
                                </button>
                                <form id="delete-form-<?= $script->id ?>"
                                      action="<?= OrganizationUrl::to(['sales-script/delete', 'id' => $script->id]) ?>"
                                      method="post" style="display:none;">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($grouped)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <?= Icon::show('document-text', 'w-8 h-8 text-gray-400') ?>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Скриптов пока нет</h3>
            <p class="text-sm text-gray-500 mb-4">Создайте первый скрипт для работы с лидами</p>
            <a href="<?= OrganizationUrl::to(['sales-script/create']) ?>" class="btn btn-primary">
                <?= Icon::show('plus', 'sm') ?>
                Создать скрипт
            </a>
        </div>
    <?php endif; ?>
</div>
