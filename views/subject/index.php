<?php

use app\models\Subject;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use lo\widgets\fullcalendar\models\Event;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('main', 'Предметы');
$this->params['breadcrumbs'][] = $this->title;
$events = [
    new Event([
        'title' => 'Appointment #' . rand(1, 999),
        'start' => '2016-03-18T14:00:00',
    ]),
    // Everything editable
    new Event([
        'id'               => uniqid(),
        'title'            => 'Appointment #' . rand(1, 999),
        'start'            => '2016-03-17T12:30:00',
        'end'              => '2016-03-17T13:30:00',
        'editable'         => true,
        'startEditable'    => true,
        'durationEditable' => true,
    ]),
    // No overlap
    new Event([
        'id'               => uniqid(),
        'title'            => 'Appointment #' . rand(1, 999),
        'start'            => '2016-03-17T15:30:00',
        'end'              => '2016-03-17T19:30:00',
        'overlap'          => false, // Overlap is default true
        'editable'         => true,
        'startEditable'    => true,
        'durationEditable' => true,
    ]),
    // Only duration editable
    new Event([
        'id'               => uniqid(),
        'title'            => 'Appointment #' . rand(1, 999),
        'start'            => '2016-03-16T11:00:00',
        'end'              => '2016-03-16T11:30:00',
        'startEditable'    => false,
        'durationEditable' => true,
    ]),
    // Only start editable
    new Event([
        'id'               => uniqid(),
        'title'            => 'Appointment #' . rand(1, 999),
        'start'            => '2016-03-15T14:00:00',
        'end'              => '2016-03-15T15:30:00',
        'startEditable'    => true,
        'durationEditable' => false,
    ]),
];
?>
<div class="subject-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('main', 'Создать предмет'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= \app\components\SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'sortUrl' => \app\helpers\OrganizationUrl::to(['sort']),
        'columns' => [
            'id',
            'name',
            'created_at',
            'updated_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Subject $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
