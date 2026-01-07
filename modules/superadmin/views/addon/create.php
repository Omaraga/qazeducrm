<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationAddon $model */
/** @var array $organizations */
/** @var array $features */

$this->title = 'Добавить аддон';
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card card-custom">
    <div class="card-header">
        <span class="font-weight-bold"><?= $this->title ?></span>
    </div>
    <div class="card-body">
        <?= $this->render('_form', [
            'model' => $model,
            'organizations' => $organizations,
            'features' => $features,
            'limitField' => null,
            'limitValue' => null,
        ]) ?>
    </div>
</div>
