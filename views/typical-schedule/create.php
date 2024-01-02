<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\TypicalSchedule $model */

$this->title = Yii::t('main', 'Create Typical Schedule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Typical Schedules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="typical-schedule-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
