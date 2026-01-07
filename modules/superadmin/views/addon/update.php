<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationAddon $model */
/** @var array $organizations */
/** @var array $features */
/** @var string|null $limitField */
/** @var int|null $limitValue */

use yii\helpers\Html;

$this->title = 'Редактировать аддон #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Аддон #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><?= $this->title ?></span>
        <?= Html::a('<i class="fas fa-eye"></i> Просмотр', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
    <div class="card-body">
        <?= $this->render('_form', [
            'model' => $model,
            'organizations' => $organizations,
            'features' => $features,
            'limitField' => $limitField,
            'limitValue' => $limitValue,
        ]) ?>
    </div>
</div>
