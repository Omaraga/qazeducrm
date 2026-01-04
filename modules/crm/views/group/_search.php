<?php

use app\helpers\OrganizationUrl;
use app\models\Subject;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\search\GroupSearch $model */
?>

<div class="card">
    <div class="card-body">
        <form method="get" action="<?= OrganizationUrl::to(['index']) ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Код</label>
                <?= Html::activeTextInput($model, 'code', ['class' => 'form-input', 'placeholder' => 'Код группы...']) ?>
            </div>
            <div>
                <label class="form-label">Название</label>
                <?= Html::activeTextInput($model, 'name', ['class' => 'form-input', 'placeholder' => 'Название...']) ?>
            </div>
            <div>
                <label class="form-label">Предмет</label>
                <?= Html::activeDropDownList($model, 'subject_id', ArrayHelper::map(Subject::find()->all(), 'id', 'name'), ['class' => 'form-select', 'prompt' => 'Все предметы']) ?>
            </div>
            <div>
                <label class="form-label">Категория</label>
                <?= Html::activeDropDownList($model, 'category_id', \app\helpers\Lists::getGroupCategories(), ['class' => 'form-select', 'prompt' => 'Все категории']) ?>
            </div>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Найти
                </button>
                <a href="<?= OrganizationUrl::to(['index']) ?>" class="btn btn-secondary">Сброс</a>
            </div>
        </form>
    </div>
</div>
