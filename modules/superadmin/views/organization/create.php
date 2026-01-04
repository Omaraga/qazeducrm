<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Новая организация';
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<div class="card card-custom">
    <div class="card-header">
        Создание организации
    </div>
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
