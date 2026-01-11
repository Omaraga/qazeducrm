<?php

/**
 * Редактирование домашнего задания
 *
 * @var yii\web\View $this
 * @var app\models\Homework $model
 * @var app\models\Group[] $groups
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Редактировать') . ': ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Домашние задания'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Редактировать');
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($model->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Yii::t('app', 'Редактирование задания') ?></p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'groups' => $groups,
    ]) ?>
</div>
