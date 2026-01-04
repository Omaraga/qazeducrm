<?php

use yii\helpers\Url;

$this->title = 'Редактирование: ' . $model->name;
?>

<div class="mb-3">
    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Назад
    </a>
</div>

<div class="card card-custom">
    <div class="card-header">Редактирование тарифного плана</div>
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
