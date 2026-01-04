<?php

use app\models\SmsTemplate;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\SmsTemplate $model */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Новый шаблон' : 'Редактировать шаблон';
$this->params['breadcrumbs'][] = ['label' => 'SMS уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Шаблоны', 'url' => ['templates']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sms-template-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['templates']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'code')->dropDownList(SmsTemplate::getCodeList()) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Название для удобства']) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'content')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Здравствуйте, {name}! Напоминаем о занятии {date} в {time}. {org_name}'
                    ])->hint('Используйте плейсхолдеры из списка справа') ?>

                    <div class="form-check mb-3">
                        <?= Html::activeCheckbox($model, 'is_active', ['class' => 'form-check-input', 'label' => false]) ?>
                        <label class="form-check-label">Активен</label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= Url::to(['templates']) ?>" class="btn btn-outline-secondary">Отмена</a>
                        <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Плейсхолдеры</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Вставляйте в текст для автоматической подстановки:</p>
                    <ul class="list-unstyled mb-0">
                        <?php foreach (SmsTemplate::getPlaceholders() as $placeholder => $description): ?>
                            <li class="mb-2">
                                <code class="placeholder-btn" style="cursor: pointer" title="Нажмите, чтобы вставить"><?= $placeholder ?></code>
                                <br><small class="text-muted"><?= $description ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Советы</h5>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-0">
                        <li>Длина SMS до 70 символов кириллицей</li>
                        <li>Латиницей — до 160 символов</li>
                        <li>Всегда указывайте название организации</li>
                        <li>Избегайте спам-слов</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.placeholder-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var textarea = document.querySelector('textarea[name="SmsTemplate[content]"]');
        var placeholder = this.textContent;

        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;

        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
        textarea.focus();
    });
});
</script>
