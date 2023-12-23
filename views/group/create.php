<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Group $model */

$this->title = Yii::t('main', 'Создать группу');
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
