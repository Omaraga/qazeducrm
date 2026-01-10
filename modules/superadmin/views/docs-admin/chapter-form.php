<?php

/** @var yii\web\View $this */
/** @var app\models\DocsChapter $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'Создание главы' : 'Редактирование главы';
$this->params['breadcrumbs'][] = ['label' => 'Документация', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$icons = [
    'rocket' => 'Ракета (Начало)',
    'users' => 'Пользователи',
    'user-group' => 'Группа',
    'user-tie' => 'Сотрудник',
    'calendar' => 'Календарь',
    'clipboard-check' => 'Чек-лист',
    'credit-card' => 'Карта',
    'money-bill' => 'Деньги',
    'funnel' => 'Воронка',
    'comments' => 'Комментарии',
    'cog' => 'Настройки',
    'book' => 'Книга',
];
?>

<div class="docs-chapter-form">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            Назад
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'max-w-2xl'],
        ]); ?>

        <?= $form->field($model, 'title')->textInput([
            'maxlength' => true,
            'class' => 'form-control',
            'placeholder' => 'Название главы',
        ]) ?>

        <?= $form->field($model, 'slug')->textInput([
            'maxlength' => true,
            'class' => 'form-control',
            'placeholder' => 'url-slug (латиница, дефисы)',
        ])->hint('URL-адрес главы: /docs/<slug>') ?>

        <?= $form->field($model, 'description')->textarea([
            'rows' => 3,
            'class' => 'form-control',
            'placeholder' => 'Краткое описание главы',
        ]) ?>

        <?= $form->field($model, 'icon')->dropDownList($icons, [
            'class' => 'form-control',
            'prompt' => '— Выберите иконку —',
        ]) ?>

        <?= $form->field($model, 'sort_order')->textInput([
            'type' => 'number',
            'class' => 'form-control',
            'min' => 0,
        ]) ?>

        <?= $form->field($model, 'is_active')->checkbox([
            'class' => 'form-check-input',
        ]) ?>

        <div class="flex gap-3 mt-6">
            <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', [
                'class' => 'btn btn-primary'
            ]) ?>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">Отмена</a>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
