<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$this->title = 'Редактировать: ' . $model->code . '-' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = ['label' => $model->code . '-' . $model->name, 'url' => OrganizationUrl::to(['view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = 'Редактировать';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Редактирование данных группы</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['group/view', 'id' => $model->id]) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к карточке
            </a>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
