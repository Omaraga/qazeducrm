<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TypicalSchedule $model */

$this->title = Yii::t('main', 'Расписание');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Расписание'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="typical-schedule-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
