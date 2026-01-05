<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeArticle $model */
/** @var array $categories */

use yii\helpers\Html;

$this->title = 'Редактирование: ' . $model->title;
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><?= Html::encode($this->title) ?></span>
        <div>
            <?= Html::a('<i class="fas fa-eye"></i> Просмотр', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <div class="card-body">
        <?= $this->render('_form', [
            'model' => $model,
            'categories' => $categories,
        ]) ?>
    </div>
</div>
