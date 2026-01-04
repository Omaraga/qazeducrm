<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$this->title = 'Редактировать группу: ' . $model->code.'-'.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = ['label' => $model->code.'-'.$model->name, 'url' => \app\helpers\OrganizationUrl::to(['view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
