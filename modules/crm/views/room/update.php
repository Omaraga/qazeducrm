<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Room $model */

$this->title = Yii::t('main', 'Редактировать кабинет');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Кабинеты'), 'url' => OrganizationUrl::to(['room/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($model->name) ?></h1>
        <p class="text-gray-500 mt-1">Редактирование данных кабинета</p>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
