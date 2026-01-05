<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeCategory $model */
/** @var yii\widgets\ActiveForm $form */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'Создание категории' : 'Редактирование: ' . $model->name;
?>

<div class="card card-custom">
    <div class="card-header">
        <span class="font-weight-bold"><?= Html::encode($this->title) ?></span>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'slug')->textInput(['maxlength' => true])->hint('URL-ключ (латиницей, через дефис)') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'icon')->dropDownList([
                    'rocket' => 'rocket (Ракета)',
                    'book' => 'book (Книга)',
                    'puzzle' => 'puzzle (Пазл)',
                    'question-mark' => 'question-mark (Вопрос)',
                    'support' => 'support (Поддержка)',
                ], ['prompt' => 'Выберите иконку']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'is_active')->checkbox() ?>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена', ['categories'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
