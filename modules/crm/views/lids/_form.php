<?php

use app\helpers\Lists;
use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\models\User;
use app\components\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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

// Status colors mapping for buttons
$statusColors = [
    Lids::STATUS_NEW => 'bg-blue-500 hover:bg-blue-600',
    Lids::STATUS_CONTACTED => 'bg-indigo-500 hover:bg-indigo-600',
    Lids::STATUS_TRIAL => 'bg-yellow-500 hover:bg-yellow-600',
    Lids::STATUS_THINKING => 'bg-gray-500 hover:bg-gray-600',
    Lids::STATUS_ENROLLED => 'bg-purple-500 hover:bg-purple-600',
    Lids::STATUS_PAID => 'bg-green-500 hover:bg-green-600',
    Lids::STATUS_LOST => 'bg-red-500 hover:bg-red-600',
];
?>

<form action="" method="post" id="lids-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Contact Data -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Контактные данные') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="lids-fio">ФИО <span class="text-danger-500">*</span></label>
                        <?= Html::activeTextInput($model, 'fio', [
                            'class' => 'form-input',
                            'id' => 'lids-fio',
                            'placeholder' => 'ФИО клиента'
                        ]) ?>
                        <?php if ($model->hasErrors('fio')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('fio') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-phone">Телефон</label>
                        <?= Html::activeTextInput($model, 'phone', [
                            'class' => 'form-input',
                            'id' => 'lids-phone',
                            'type' => 'tel',
                            'placeholder' => '+7(XXX)XXXXXXX'
                        ]) ?>
                        <?php if ($model->hasErrors('phone')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('phone') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="lids-school">Школа</label>
                        <?= Html::activeTextInput($model, 'school', [
                            'class' => 'form-input',
                            'id' => 'lids-school',
                            'placeholder' => 'Школа/учебное заведение'
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

        <!-- Sales Funnel -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Воронка продаж') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="lids-status">Статус <span class="text-danger-500">*</span></label>
                        <?= Html::activeDropDownList($model, 'status', Lids::getStatusList(), [
                            'class' => 'form-select',
                            'id' => 'lids-status'
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

                <!-- Lost reason (shown conditionally via JS) -->
                <div id="lost-reason-field" class="mt-4" style="display: <?= $model->status == Lids::STATUS_LOST ? 'block' : 'none' ?>;">
                    <label class="form-label" for="lids-lost_reason">Причина потери</label>
                    <?= Html::activeTextInput($model, 'lost_reason', [
                        'class' => 'form-input',
                        'id' => 'lids-lost_reason',
                        'placeholder' => 'Укажите причину потери лида'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Trial Test -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Пробное тестирование') ?></h3>
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
                <h3 class="text-lg font-semibold text-gray-900"><?= Yii::t('main', 'Комментарий') ?></h3>
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
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
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
            <div class="card-body space-y-2">
                <?php foreach (Lids::getStatusList() as $status => $label): ?>
                    <?php
                    $bgColor = $statusColors[$status] ?? 'bg-gray-500 hover:bg-gray-600';
                    $isActive = $model->status == $status;
                    ?>
                    <button type="button"
                            class="status-btn w-full px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $isActive ? $bgColor . ' text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                            data-status="<?= $status ?>"
                            data-active-class="<?= $bgColor ?> text-white">
                        <?= Html::encode($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$model->isNewRecord): ?>
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
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions (desktop) -->
        <div class="hidden lg:flex flex-col gap-2">
            <button type="submit" class="btn btn-primary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить
            </button>
            <a href="<?= OrganizationUrl::to(['lids/index']) ?>" class="btn btn-secondary w-full justify-center">Отмена</a>
        </div>
    </div>
</form>

<?php
$lostStatus = Lids::STATUS_LOST;
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('lids-status');
    const lostReasonField = document.getElementById('lost-reason-field');
    const statusButtons = document.querySelectorAll('.status-btn');

    // Toggle lost reason field visibility
    function toggleLostReason() {
        if (statusSelect.value == '{$lostStatus}') {
            lostReasonField.style.display = 'block';
        } else {
            lostReasonField.style.display = 'none';
        }
    }

    statusSelect.addEventListener('change', toggleLostReason);

    // Status button click handlers
    statusButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            statusSelect.value = status;

            // Reset all buttons
            statusButtons.forEach(function(b) {
                b.classList.remove('text-white');
                b.className = b.className.replace(/bg-\w+-\d+/g, '');
                b.classList.add('bg-gray-100', 'text-gray-700');
            });

            // Activate clicked button
            this.classList.remove('bg-gray-100', 'text-gray-700');
            const activeClass = this.dataset.activeClass;
            activeClass.split(' ').forEach(function(cls) {
                if (cls) btn.classList.add(cls);
            });

            toggleLostReason();
        });
    });
});
JS;
$this->registerJs($js);
?>
