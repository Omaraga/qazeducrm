<?php

/**
 * Создание пробного занятия
 *
 * @var yii\web\View $this
 * @var app\models\TrialLesson $model
 * @var app\models\Lids[] $lids
 * @var app\models\Group[] $groups
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Записать на пробное занятие');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Пробные занятия'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1"><?= Yii::t('app', 'Запишите лида на пробное занятие') ?></p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'lids' => $lids,
        'groups' => $groups,
    ]) ?>
</div>
