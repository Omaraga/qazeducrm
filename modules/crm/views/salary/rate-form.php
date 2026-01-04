<?php

use app\models\TeacherRate;
use app\models\Subject;
use app\models\Group;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\TeacherRate $model */
/** @var app\models\User[] $teachers */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Добавить ставку' : 'Редактировать ставку';
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Ставки учителей', 'url' => ['rates']];
$this->params['breadcrumbs'][] = $this->title;

$subjects = Subject::find()->andWhere(['!=', 'is_deleted', 1])->all();
$groups = Group::find()
    ->andWhere(['organization_id' => \app\models\Organizations::getCurrentOrganizationId()])
    ->andWhere(['!=', 'is_deleted', 1])
    ->all();
?>

<div class="rate-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['rates']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="mb-3">
                        <label class="form-label">Преподаватель <span class="text-danger">*</span></label>
                        <?= Html::activeDropDownList($model, 'teacher_id',
                            ArrayHelper::map($teachers, 'id', 'fio'),
                            ['class' => 'form-control', 'prompt' => 'Выберите преподавателя...']
                        ) ?>
                        <?php if ($model->hasErrors('teacher_id')): ?>
                            <div class="text-danger small"><?= $model->getFirstError('teacher_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Тип ставки <span class="text-danger">*</span></label>
                        <?= Html::activeDropDownList($model, 'rate_type',
                            TeacherRate::getRateTypeList(),
                            ['class' => 'form-control']
                        ) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Значение ставки <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <?= Html::activeTextInput($model, 'rate_value', ['class' => 'form-control', 'type' => 'number', 'min' => 0, 'step' => 'any']) ?>
                            <span class="input-group-text rate-suffix">₸</span>
                        </div>
                        <small class="text-muted rate-hint">Сумма в тенге</small>
                        <?php if ($model->hasErrors('rate_value')): ?>
                            <div class="text-danger small"><?= $model->getFirstError('rate_value') ?></div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">Область применения (опционально)</h6>

                    <div class="mb-3">
                        <label class="form-label">Предмет</label>
                        <?= Html::activeDropDownList($model, 'subject_id',
                            ArrayHelper::map($subjects, 'id', 'name'),
                            ['class' => 'form-control', 'prompt' => 'Все предметы']
                        ) ?>
                        <small class="text-muted">Оставьте пустым для применения ко всем предметам</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Группа</label>
                        <?= Html::activeDropDownList($model, 'group_id',
                            ArrayHelper::map($groups, 'id', 'name'),
                            ['class' => 'form-control', 'prompt' => 'Все группы']
                        ) ?>
                        <small class="text-muted">Оставьте пустым для применения ко всем группам</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <?= Html::activeCheckbox($model, 'is_active', ['class' => 'form-check-input', 'label' => false]) ?>
                            <label class="form-check-label">Активна</label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= Url::to(['rates']) ?>" class="btn btn-outline-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= $isNew ? 'Создать' : 'Сохранить' ?>
                        </button>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Приоритет ставок</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">При расчёте зарплаты система ищет ставку в следующем порядке:</p>
                    <ol>
                        <li><strong>Ставка для конкретной группы</strong> - наивысший приоритет</li>
                        <li><strong>Ставка для предмета</strong> - средний приоритет</li>
                        <li><strong>Общая ставка учителя</strong> - базовая ставка</li>
                    </ol>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb"></i>
                        Рекомендуется создать общую ставку для каждого учителя, а затем добавлять специфичные ставки для отдельных групп или предметов.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rateTypeSelect = document.querySelector('select[name="TeacherRate[rate_type]"]');
    const rateSuffix = document.querySelector('.rate-suffix');
    const rateHint = document.querySelector('.rate-hint');

    function updateRateUI() {
        const type = parseInt(rateTypeSelect.value);
        if (type === 3) { // Процент
            rateSuffix.textContent = '%';
            rateHint.textContent = 'Процент от оплаты ученика';
        } else {
            rateSuffix.textContent = '₸';
            rateHint.textContent = type === 1 ? 'Сумма за каждого ученика' : 'Сумма за урок';
        }
    }

    rateTypeSelect.addEventListener('change', updateRateUI);
    updateRateUI();
});
</script>
