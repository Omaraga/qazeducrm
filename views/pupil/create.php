<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pupil $model */

$this->title = 'Добавить ученика';
$this->params['breadcrumbs'][] = ['label' => 'Ученики', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pupil-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
