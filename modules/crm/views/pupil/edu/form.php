<?php

use app\helpers\OrganizationUrl;
use app\models\forms\EducationForm;
use app\models\Group;
use app\models\Tariff;
use app\widgets\tailwind\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\forms\EducationForm $model */

if ($model->getScenario() === EducationForm::TYPE_EDIT) {
    $this->title = 'Редактировать обучение';
} elseif ($model->getScenario() === EducationForm::TYPE_COPY) {
    $this->title = 'Дублировать обучение';
} else {
    $this->title = 'Добавить обучение';
}

$this->params['breadcrumbs'][] = ['label' => 'Обучение', 'url' => OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id])];
$this->params['breadcrumbs'][] = $this->title;

$tariffs = ArrayHelper::map(Tariff::find()->byOrganization()->notDeleted()->all(), 'id', 'nameFull');
$groups = ArrayHelper::map(Group::find()->byOrganization()->notDeleted()->all(), 'id', 'nameFull');
$subjects = Tariff::getSubjectsMap();
$isEdit = $model->getScenario() == EducationForm::TYPE_EDIT;

// Проверка наличия необходимых данных
$hasTariffs = !empty($tariffs);
$hasGroups = !empty($groups);
$canCreateEducation = $hasTariffs && $hasGroups;

// Convert date formats for HTML5 date input
$dateStart = $model->date_start ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_start))) : '';
$dateEnd = $model->date_end ? date('Y-m-d', strtotime(str_replace('.', '-', $model->date_end))) : '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Заполните данные об обучении</p>
        </div>
        <div>
            <?= Html::a(
                Icon::show('arrow-left', 'sm') . ' Назад к обучению',
                OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id]),
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>

    <?php if (!$canCreateEducation): ?>
    <!-- Warning: Missing prerequisites -->
    <div class="rounded-lg bg-warning-50 border border-warning-200 p-6">
        <div class="flex items-start gap-4">
            <?= Icon::show('alert', 'lg', 'text-warning-500 flex-shrink-0') ?>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-warning-800 mb-2">Для добавления обучения необходимо</h3>
                <ul class="space-y-2 text-warning-700">
                    <?php if (!$hasTariffs): ?>
                    <li class="flex items-center gap-2">
                        <?= Icon::show('x', 'xs', 'text-danger-500') ?>
                        <span>Создать хотя бы один тариф</span>
                        <?= Html::a(
                            Icon::show('plus', 'xs') . ' Создать тариф',
                            OrganizationUrl::to(['tariff/create']),
                            ['class' => 'text-primary-600 hover:text-primary-700 font-medium ml-2']
                        ) ?>
                    </li>
                    <?php else: ?>
                    <li class="flex items-center gap-2">
                        <?= Icon::show('check', 'xs', 'text-success-500') ?>
                        <span class="text-success-700">Тарифы созданы (<?= count($tariffs) ?>)</span>
                    </li>
                    <?php endif; ?>

                    <?php if (!$hasGroups): ?>
                    <li class="flex items-center gap-2">
                        <?= Icon::show('x', 'xs', 'text-danger-500') ?>
                        <span>Создать хотя бы одну группу</span>
                        <?= Html::a(
                            Icon::show('plus', 'xs') . ' Создать группу',
                            OrganizationUrl::to(['group/create']),
                            ['class' => 'text-primary-600 hover:text-primary-700 font-medium ml-2']
                        ) ?>
                    </li>
                    <?php else: ?>
                    <li class="flex items-center gap-2">
                        <?= Icon::show('check', 'xs', 'text-success-500') ?>
                        <span class="text-success-700">Группы созданы (<?= count($groups) ?>)</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?= $model->getActionUrl() ?>" method="post" id="education-form" class="space-y-6">
        <input type="hidden" name="<?= \Yii::$app->request->csrfParam ?>" value="<?= \Yii::$app->request->csrfToken ?>">
        <input type="hidden" id="scenario" value="<?= $model->getScenario() ?>">

        <!-- Tariff Selection -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= \Yii::t('main', 'Тариф') ?></h3>
            </div>
            <div class="card-body">
                <div>
                    <label class="form-label" for="educationform-tariff_id">Выберите тариф <span class="text-danger-500">*</span></label>
                    <?= Html::activeDropDownList($model, 'tariff_id', $tariffs, [
                        'class' => 'form-select',
                        'id' => 'educationform-tariff_id',
                        'prompt' => 'Выберите тариф',
                        'disabled' => $isEdit,
                    ]) ?>
                    <?php if ($model->hasErrors('tariff_id')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('tariff_id') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Groups Selection -->
        <div class="card" id="group-card" style="<?= $model->tariff_id ? '' : 'display: none;' ?>">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= \Yii::t('main', 'Выберите группы согласно тарифа') ?></h3>
            </div>
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="education-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= \Yii::t('main', 'Предмет') ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= \Yii::t('main', 'Группа') ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($model->groups ?: [new \app\models\relations\EducationGroup()] as $k => $group): ?>
                            <tr class="group-block">
                                <td class="px-4 py-3">
                                    <select name="EducationForm[groups][<?= $k ?>][subject_id]" class="form-select subject_input" disabled>
                                        <option value="">Выберите предмет</option>
                                        <?php foreach ($subjects as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= ($group->subject_id ?? '') == $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <select name="EducationForm[groups][<?= $k ?>][group_id]" class="form-select group_input" <?= $isEdit ? 'disabled' : '' ?>>
                                        <option value=""><?= \Yii::t('main', 'Выберите группу') ?></option>
                                        <?php foreach ($groups as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= ($group->group_id ?? '') == $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dates and Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900"><?= \Yii::t('main', 'Период и условия') ?></h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label" for="date_start_display"><?= $model->getAttributeLabel('date_start') ?> <span class="text-danger-500">*</span></label>
                        <input type="date" id="date_start_display" class="form-input" value="<?= $dateStart ?>" autocomplete="off" onchange="updateEduDateHidden('date_start', this.value)">
                        <input type="hidden" name="EducationForm[date_start]" id="date_start_input" value="<?= $model->date_start ?>">
                        <?php if ($model->hasErrors('date_start')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date_start') ?></p>
                        <?php endif; ?>
                    </div>
                    <div id="field-date_end">
                        <label class="form-label" for="date_end_display"><?= $model->getAttributeLabel('date_end') ?></label>
                        <input type="date" id="date_end_display" class="form-input" value="<?= $dateEnd ?>" autocomplete="off" onchange="updateEduDateHidden('date_end', this.value)">
                        <input type="hidden" name="EducationForm[date_end]" id="date_end_input" value="<?= $model->date_end ?>">
                        <?php if ($model->hasErrors('date_end')): ?>
                            <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('date_end') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label" for="edu-sale"><?= $model->getAttributeLabel('sale') ?> (%)</label>
                        <input type="number" name="EducationForm[sale]" id="edu-sale" class="form-input" value="<?= $model->sale ?>" min="0" max="100" placeholder="0">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="form-label" for="educationform-comment"><?= $model->getAttributeLabel('comment') ?></label>
                    <textarea name="EducationForm[comment]" id="educationform-comment" class="form-input" rows="3"><?= Html::encode($model->comment) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Payment Info Alert -->
        <div id="divPaymentDescription" class="rounded-lg bg-warning-50 border border-warning-200 p-4 text-warning-800">
            Выберите тариф для расчета стоимости
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <?php if ($canCreateEducation): ?>
            <button type="button" id="submit-btn" class="btn btn-primary" title="Сохранить данные обучения">
                <?= Icon::show('check', 'sm') ?>
                <?= \Yii::t('main', 'Сохранить') ?>
            </button>
            <?php else: ?>
            <span class="btn btn-secondary opacity-50 cursor-not-allowed" title="Сначала создайте тарифы и группы">
                <?= Icon::show('lock', 'sm') ?>
                <?= \Yii::t('main', 'Сохранить') ?>
            </span>
            <?php endif; ?>
            <?= Html::a('Отмена', OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id]), ['class' => 'btn btn-secondary']) ?>
        </div>
    </form>
</div>

<?php
// Extract CSRF values for use in heredoc (heredoc doesn't process PHP tags)
$csrfParam = \Yii::$app->request->csrfParam;
$csrfToken = \Yii::$app->request->csrfToken;
$tariffInfoUrl = OrganizationUrl::to(['tariff/get-info']);

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const tariffSelect = document.getElementById('educationform-tariff_id');
    const dateStartInput = document.getElementById('date_start_input');
    const dateEndInput = document.getElementById('date_end_input');
    const saleInput = document.getElementById('edu-sale');
    const groupCard = document.getElementById('group-card');
    const dateEndField = document.getElementById('field-date_end');
    const paymentDescription = document.getElementById('divPaymentDescription');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('education-form');
    const scenario = document.getElementById('scenario').value;

    // Validate sale input
    saleInput.addEventListener('change', function() {
        let val = parseInt(this.value) || 0;
        if (val < 0) val = 0;
        if (val > 100) val = 100;
        this.value = val;
    });

    // Format date for display (dd.mm.yyyy)
    function formatDateForApi(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return day + '.' + month + '.' + year;
    }

    // Load groups based on tariff subjects
    function loadGroups(subjects) {
        if (scenario === 'edit' || !subjects || subjects.length === 0) return;

        const tbody = document.querySelector('#education-table tbody');
        const templateRow = tbody.querySelector('.group-block').cloneNode(true);

        // Clear existing rows
        tbody.innerHTML = '';

        subjects.forEach(function(subjectId, index) {
            const row = templateRow.cloneNode(true);

            // Update field names
            row.querySelectorAll('select').forEach(function(select) {
                select.name = select.name.replace(/\[\d+\]/, '[' + index + ']');
            });

            // Set subject
            const subjectSelect = row.querySelector('.subject_input');
            subjectSelect.value = subjectId;

            // Clear group selection
            const groupSelect = row.querySelector('.group_input');
            groupSelect.value = '';

            tbody.appendChild(row);
        });
    }

    // Update tariff info via AJAX
    function updateInfo(isLoadSubjects) {
        const tariffId = tariffSelect.value;
        const dateStart = formatDateForApi(dateStartInput.value);
        const dateEnd = formatDateForApi(dateEndInput.value);
        const sale = saleInput.value || 0;

        if (!tariffId) {
            groupCard.style.display = 'none';
            paymentDescription.innerHTML = 'Выберите тариф для расчета стоимости';
            return;
        }

        const formData = new FormData();
        formData.append('id', tariffId);
        formData.append('date_start', dateStart);
        formData.append('date_end', dateEnd);
        formData.append('sale', sale);
        formData.append('{$csrfParam}', '{$csrfToken}');

        fetch('{$tariffInfoUrl}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(function(data) {
            if (data.id) {
                paymentDescription.innerHTML = data.info_text;

                // Toggle date_end field based on duration type
                if (data.duration == 3) {
                    dateEndField.style.display = 'none';
                } else {
                    dateEndField.style.display = 'block';
                }

                groupCard.style.display = 'block';

                if (isLoadSubjects && data.subjects) {
                    loadGroups(data.subjects);
                }
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
    }

    // Event listeners
    tariffSelect.addEventListener('change', function() {
        updateInfo(true);
    });

    dateStartInput.addEventListener('change', function() {
        updateInfo(false);
    });

    dateEndInput.addEventListener('change', function() {
        updateInfo(false);
    });

    saleInput.addEventListener('change', function() {
        updateInfo(false);
    });

    // Initial load
    if (tariffSelect.value) {
        updateInfo(false);
    }

    // Submit handler - enable disabled fields before submit
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        form.querySelectorAll('input[disabled], select[disabled]').forEach(function(el) {
            el.removeAttribute('disabled');
        });
        form.submit();
    });

    // Initialize date values on load
    initEduDates();
});

// Function to convert YYYY-MM-DD to DD.MM.YYYY and update hidden field
function updateEduDateHidden(fieldName, dateValue) {
    if (!dateValue) return;
    const [year, month, day] = dateValue.split('-');
    const formatted = day + '.' + month + '.' + year;
    document.getElementById(fieldName + '_input').value = formatted;
}

// Initialize hidden date fields on page load
function initEduDates() {
    const dateStartDisplay = document.getElementById('date_start_display');
    const dateEndDisplay = document.getElementById('date_end_display');
    if (dateStartDisplay && dateStartDisplay.value) {
        updateEduDateHidden('date_start', dateStartDisplay.value);
    }
    if (dateEndDisplay && dateEndDisplay.value) {
        updateEduDateHidden('date_end', dateEndDisplay.value);
    }
}
JS;
$this->registerJs($js);
?>
