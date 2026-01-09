<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\models\User;
use app\components\ActiveRecord;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\StatusBadge;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var app\models\Lids $model */

// Получаем менеджеров организации
$managers = User::find()
    ->innerJoinWith(['currentUserOrganizations' => function($q) {
        $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED]);
    }])
    ->all();

// Convert dates for HTML5 date input
$dateValue = $model->date ? date('Y-m-d', strtotime($model->date)) : date('Y-m-d');
$nextContactDate = $model->next_contact_date ? date('Y-m-d', strtotime($model->next_contact_date)) : '';

// Правила валидации
$validationRules = [
    'fio' => ['required' => false],
    'phone' => ['phone' => true],
    'parent_phone' => ['phone' => true],
];

// Данные для Alpine.js компонента
$lidsFormData = [
    'status' => $model->status ?: Lids::STATUS_NEW,
    'lostStatus' => Lids::STATUS_LOST,
    'contactPerson' => $model->contact_person ?: Lids::CONTACT_PARENT,
    'isSubmitting' => false,
];
?>

<form action="" method="post" id="lids-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6"
      x-data='{ ...formValidation(<?= Json::encode($validationRules) ?>), ...<?= Json::encode($lidsFormData) ?> }'
      @submit="handleSubmit($event)">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Child Data -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= Icon::show('user', 'sm', 'text-primary-500 mr-2') ?>
                    <?= Yii::t('main', 'Данные ребёнка') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="lids-fio">ФИО ребёнка</label>
                        <?= Html::activeTextInput($model, 'fio', [
                            'class' => 'form-input',
                            'id' => 'lids-fio',
                            'placeholder' => 'Иванов Иван Иванович',
                        ]) ?>
                        <?php if ($model->hasErrors('fio')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('fio') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-phone">Телефон ребёнка</label>
                        <?= Html::activeTextInput($model, 'phone', [
                            'class' => 'form-input',
                            'id' => 'lids-phone',
                            'placeholder' => '+7 (XXX) XXX-XX-XX',
                            'x-mask-phone' => true,
                        ]) ?>
                        <?php if ($model->hasErrors('phone')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('phone') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-school">Школа</label>
                        <?= Html::activeTextInput($model, 'school', [
                            'class' => 'form-input',
                            'id' => 'lids-school',
                            'placeholder' => 'Школа/лицей/гимназия'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-class_id">Класс</label>
                        <?= Html::activeDropDownList($model, 'class_id', Lists::getGrades(), [
                            'class' => 'form-select',
                            'id' => 'lids-class_id',
                            'prompt' => 'Выберите класс'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Data -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= Icon::show('users', 'sm', 'text-indigo-500 mr-2') ?>
                    <?= Yii::t('main', 'Данные родителя') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="lids-parent_fio">ФИО родителя</label>
                        <?= Html::activeTextInput($model, 'parent_fio', [
                            'class' => 'form-input',
                            'id' => 'lids-parent_fio',
                            'placeholder' => 'Иванова Мария Петровна',
                        ]) ?>
                        <?php if ($model->hasErrors('parent_fio')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('parent_fio') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-parent_phone">Телефон родителя</label>
                        <?= Html::activeTextInput($model, 'parent_phone', [
                            'class' => 'form-input',
                            'id' => 'lids-parent_phone',
                            'placeholder' => '+7 (XXX) XXX-XX-XX',
                            'x-mask-phone' => true,
                        ]) ?>
                        <?php if ($model->hasErrors('parent_phone')): ?>
                            <p class="form-error-message"><?= $model->getFirstError('parent_phone') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Person Toggle -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <label class="form-label">Контактное лицо</label>
                    <div class="flex gap-3 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg transition-colors"
                               :class="contactPerson === 'parent' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'">
                            <input type="radio" name="Lids[contact_person]" value="parent"
                                   x-model="contactPerson"
                                   class="sr-only">
                            <?= Icon::show('user', 'sm') ?>
                            <span class="text-sm font-medium">Родитель</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg transition-colors"
                               :class="contactPerson === 'pupil' ? 'bg-primary-100 text-primary-700' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'">
                            <input type="radio" name="Lids[contact_person]" value="pupil"
                                   x-model="contactPerson"
                                   class="sr-only">
                            <?= Icon::show('academic-cap', 'sm') ?>
                            <span class="text-sm font-medium">Ребёнок</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">С кем в первую очередь связываться</p>
                </div>
            </div>
        </div>

        <!-- Sales Funnel -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= Icon::show('funnel', 'sm', 'text-amber-500 mr-2') ?>
                    <?= Yii::t('main', 'Воронка продаж') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label form-label-required" for="lids-status">Статус</label>
                        <?= Html::activeDropDownList($model, 'status', Lids::getStatusList(), [
                            'class' => 'form-select',
                            'id' => 'lids-status',
                            'x-model' => 'status',
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-source">Источник</label>
                        <?= Html::activeDropDownList($model, 'source', Lids::getSourceList(), [
                            'class' => 'form-select',
                            'id' => 'lids-source',
                            'prompt' => 'Выберите источник'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-next_contact_date">Следующий контакт</label>
                        <input type="date" name="Lids[next_contact_date]" id="lids-next_contact_date" class="form-input" value="<?= $nextContactDate ?>">
                    </div>
                    <div>
                        <label class="form-label" for="lids-manager_id">Ответственный</label>
                        <?= Html::activeDropDownList($model, 'manager_id', ArrayHelper::map($managers, 'id', 'fio'), [
                            'class' => 'form-select',
                            'id' => 'lids-manager_id',
                            'prompt' => 'Выберите ответственного'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-date">Дата обращения</label>
                        <input type="date" name="Lids[date]" id="lids-date" class="form-input" value="<?= $dateValue ?>">
                    </div>
                </div>

                <!-- Lost reason (shown conditionally via Alpine.js) -->
                <div class="mt-4" x-show="status == lostStatus" x-cloak>
                    <label class="form-label" for="lids-lost_reason">Причина потери</label>
                    <?= Html::activeDropDownList($model, 'lost_reason', [
                        'Дорого' => 'Дорого',
                        'Далеко' => 'Далеко',
                        'Не устроило расписание' => 'Не устроило расписание',
                        'Выбрали конкурента' => 'Выбрали конкурента',
                        'Передумали' => 'Передумали',
                        'Не дозвонились' => 'Не дозвонились',
                        'Ребёнок отказался' => 'Ребёнок отказался',
                        'Другое' => 'Другое',
                    ], [
                        'class' => 'form-select',
                        'id' => 'lids-lost_reason',
                        'prompt' => 'Выберите причину'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Trial Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= Icon::show('clipboard-document-check', 'sm', 'text-green-500 mr-2') ?>
                    <?= Yii::t('main', 'Пробное тестирование') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="lids-total_point">Баллы</label>
                        <?= Html::activeTextInput($model, 'total_point', [
                            'class' => 'form-input',
                            'id' => 'lids-total_point',
                            'type' => 'number',
                            'placeholder' => 'Баллы'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-sale">Скидка (%)</label>
                        <?= Html::activeTextInput($model, 'sale', [
                            'class' => 'form-input',
                            'id' => 'lids-sale',
                            'type' => 'number',
                            'min' => '0',
                            'max' => '100',
                            'placeholder' => '0'
                        ]) ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-total_sum">Сумма (₸)</label>
                        <?= Html::activeTextInput($model, 'total_sum', [
                            'class' => 'form-input',
                            'id' => 'lids-total_sum',
                            'type' => 'number',
                            'placeholder' => '0'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comment -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= Icon::show('chat-bubble-left-right', 'sm', 'text-gray-500 mr-2') ?>
                    <?= Yii::t('main', 'Комментарий') ?>
                </h3>
            </div>
            <div class="card-body">
                <?= Html::activeTextarea($model, 'comment', [
                    'class' => 'form-input',
                    'rows' => 3,
                    'placeholder' => 'Дополнительная информация о лиде'
                ]) ?>
            </div>
        </div>

        <!-- Actions (mobile) -->
        <div class="lg:hidden flex items-center gap-3">
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <template x-if="!isSubmitting">
                    <?= Icon::show('check', 'sm') ?>
                </template>
                <template x-if="isSubmitting">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isSubmitting ? 'Сохранение...' : 'Сохранить'"></span>
            </button>
            <a href="<?= OrganizationUrl::to(['lids/index']) ?>" class="btn btn-secondary">Отмена</a>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Status Buttons -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Статус</h3>
            </div>
            <div class="card-body">
                <?php
                $statuses = \app\helpers\StatusHelper::getStatuses('lids');
                ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($statuses as $value => $config): ?>
                        <?php
                        $color = $config['color'];
                        $label = $config['label'];

                        $activeClasses = [
                            'primary' => 'bg-primary-600 text-white ring-2 ring-primary-600 ring-offset-2',
                            'success' => 'bg-success-600 text-white ring-2 ring-success-600 ring-offset-2',
                            'warning' => 'bg-warning-500 text-white ring-2 ring-warning-500 ring-offset-2',
                            'danger' => 'bg-danger-600 text-white ring-2 ring-danger-600 ring-offset-2',
                            'info' => 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2',
                            'gray' => 'bg-gray-600 text-white ring-2 ring-gray-600 ring-offset-2',
                            'purple' => 'bg-purple-600 text-white ring-2 ring-purple-600 ring-offset-2',
                            'indigo' => 'bg-indigo-600 text-white ring-2 ring-indigo-600 ring-offset-2',
                        ];

                        $inactiveClasses = [
                            'primary' => 'bg-primary-50 text-primary-700 hover:bg-primary-100',
                            'success' => 'bg-success-50 text-success-700 hover:bg-success-100',
                            'warning' => 'bg-warning-50 text-warning-700 hover:bg-warning-100',
                            'danger' => 'bg-danger-50 text-danger-700 hover:bg-danger-100',
                            'info' => 'bg-blue-50 text-blue-700 hover:bg-blue-100',
                            'gray' => 'bg-gray-50 text-gray-700 hover:bg-gray-100',
                            'purple' => 'bg-purple-50 text-purple-700 hover:bg-purple-100',
                            'indigo' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
                        ];
                        ?>
                        <button type="button"
                                @click="status = '<?= $value ?>'"
                                :class="status == '<?= $value ?>' ? '<?= $activeClasses[$color] ?? $activeClasses['gray'] ?>' : '<?= $inactiveClasses[$color] ?? $inactiveClasses['gray'] ?>'"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all">
                            <?= Html::encode($label) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (!$model->isNewRecord): ?>
        <!-- Converted pupil info -->
        <?php if ($model->pupil_id): ?>
        <div class="card border-green-200 bg-green-50">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <?= Icon::show('check-circle') ?>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-green-800">Конвертирован в ученика</p>
                        <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="text-sm text-green-600 hover:underline">
                            Перейти к карточке ученика
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- History -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">История</h3>
            </div>
            <div class="card-body text-sm text-gray-500 space-y-2">
                <div>
                    <span class="font-medium">Создан:</span>
                    <?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') : '—' ?>
                </div>
                <div>
                    <span class="font-medium">Обновлён:</span>
                    <?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') : '—' ?>
                </div>
                <?php if ($model->status_changed_at): ?>
                <div>
                    <span class="font-medium">Статус изменён:</span>
                    <?= Yii::$app->formatter->asDatetime($model->status_changed_at, 'php:d.m.Y H:i') ?>
                </div>
                <?php endif; ?>
                <?php if ($model->converted_at): ?>
                <div>
                    <span class="font-medium">Конвертирован:</span>
                    <?= Yii::$app->formatter->asDatetime($model->converted_at, 'php:d.m.Y H:i') ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions (desktop) -->
        <div class="hidden lg:flex flex-col gap-2">
            <button type="submit" class="btn btn-primary w-full justify-center" :disabled="isSubmitting">
                <template x-if="!isSubmitting">
                    <?= Icon::show('check', 'sm') ?>
                </template>
                <template x-if="isSubmitting">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="isSubmitting ? 'Сохранение...' : 'Сохранить'"></span>
            </button>
            <a href="<?= OrganizationUrl::to(['lids/index']) ?>" class="btn btn-secondary w-full justify-center">Отмена</a>
        </div>
    </div>
</form>
