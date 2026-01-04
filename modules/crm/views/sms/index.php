<?php

use app\models\SmsLog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'SMS уведомления';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sms-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <a href="<?= Url::to(['templates']) ?>" class="btn btn-outline-primary">
                <i class="fas fa-file-alt"></i> Шаблоны
            </a>
            <a href="<?= Url::to(['settings']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Настройки
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            История отправок
        </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-hover mb-0'],
                'layout' => '{items}',
                'columns' => [
                    [
                        'attribute' => 'created_at',
                        'format' => ['datetime', 'php:d.m.Y H:i'],
                        'headerOptions' => ['style' => 'width: 140px'],
                    ],
                    [
                        'attribute' => 'phone',
                        'headerOptions' => ['style' => 'width: 140px'],
                    ],
                    [
                        'attribute' => 'message',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $text = Html::encode($model->message);
                            if (mb_strlen($text) > 80) {
                                $text = mb_substr($text, 0, 80) . '...';
                            }
                            return '<small>' . $text . '</small>';
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 100px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function ($model) {
                            return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                        },
                    ],
                    [
                        'attribute' => 'error_message',
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 150px'],
                        'value' => function ($model) {
                            if (!$model->error_message) return '';
                            return '<small class="text-danger">' . Html::encode($model->error_message) . '</small>';
                        },
                    ],
                ],
            ]); ?>
        </div>
        <div class="card-footer">
            <?= \yii\bootstrap4\LinkPager::widget([
                'pagination' => $dataProvider->pagination
            ]) ?>
        </div>
    </div>
</div>
