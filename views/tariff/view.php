<?php

use yii\bootstrap4\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Тарифы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="tariff-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?if(Yii::$app->user->can(\app\helpers\OrganizationRoles::GENERAL_DIRECTOR)):?>
    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?endif;?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name:ntext',
            'statusLabel',
            'durationLabel',
            'lesson_amount',
            'typeLabel',
            'price',
            'description:ntext',
        ],
    ]) ?>

</div>
