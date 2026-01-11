<?php

/**
 * Создание домашнего задания
 *
 * @var yii\web\View $this
 * @var app\models\Homework $model
 * @var app\models\Group[] $groups
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Создать домашнее задание');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Домашние задания'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Yii::t('app', 'Задайте домашнее задание для группы') ?></p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'groups' => $groups,
    ]) ?>
</div>
