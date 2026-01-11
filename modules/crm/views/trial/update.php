<?php

/**
 * Редактирование пробного занятия
 *
 * @var yii\web\View $this
 * @var app\models\TrialLesson $model
 * @var app\models\Group[] $groups
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Редактировать пробное') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Пробные занятия'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Редактировать');
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Html::encode($model->getLidName()) ?></p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'lids' => [],
        'groups' => $groups,
    ]) ?>
</div>
