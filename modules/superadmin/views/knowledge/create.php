<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeArticle $model */
/** @var array $categories */

$this->title = 'Создание статьи';
?>

<div class="card card-custom">
    <div class="card-header">
        <span class="font-weight-bold"><?= $this->title ?></span>
    </div>
    <div class="card-body">
        <?= $this->render('_form', [
            'model' => $model,
            'categories' => $categories,
        ]) ?>
    </div>
</div>
