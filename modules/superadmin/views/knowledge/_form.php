<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeArticle $model */
/** @var array $categories */
/** @var yii\widgets\ActiveForm $form */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-8">
        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'category_id')->dropDownList($categories, ['prompt' => 'Выберите категорию']) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'slug')->textInput(['maxlength' => true])->hint('URL-ключ статьи (латиницей, через дефис)') ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'is_featured')->checkbox() ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'is_active')->checkbox() ?>
    </div>
</div>

<?= $form->field($model, 'excerpt')->textarea(['rows' => 2])->hint('Краткое описание для списков') ?>

<?= $form->field($model, 'content')->textarea(['rows' => 20, 'class' => 'form-control', 'style' => 'font-family: monospace;'])->hint('HTML-содержимое статьи') ?>

<hr>

<div class="form-group">
    <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
