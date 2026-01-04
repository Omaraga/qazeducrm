<?php

use app\models\SmsTemplate;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'SMS шаблоны';
$this->params['breadcrumbs'][] = ['label' => 'SMS уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sms-templates">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
            <a href="<?= Url::to(['create-defaults']) ?>" class="btn btn-outline-primary">
                <i class="fas fa-magic"></i> Создать стандартные
            </a>
            <a href="<?= Url::to(['create-template']) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-hover mb-0'],
                'layout' => '{items}',
                'columns' => [
                    [
                        'attribute' => 'code',
                        'label' => 'Тип',
                        'value' => function ($model) {
                            return $model->getCodeLabel();
                        },
                    ],
                    'name',
                    [
                        'attribute' => 'content',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $text = Html::encode($model->content);
                            if (mb_strlen($text) > 100) {
                                $text = mb_substr($text, 0, 100) . '...';
                            }
                            return '<small class="text-muted">' . $text . '</small>';
                        },
                    ],
                    [
                        'attribute' => 'is_active',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['style' => 'width: 80px'],
                        'value' => function ($model) {
                            return $model->is_active
                                ? '<span class="badge bg-success">Да</span>'
                                : '<span class="badge bg-secondary">Нет</span>';
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'headerOptions' => ['style' => 'width: 100px'],
                        'buttons' => [
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', ['update-template', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'Редактировать',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fas fa-trash"></i>', ['delete-template', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-danger ms-1',
                                    'title' => 'Удалить',
                                    'data' => [
                                        'confirm' => 'Удалить этот шаблон?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Доступные плейсхолдеры</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach (SmsTemplate::getPlaceholders() as $placeholder => $description): ?>
                    <div class="col-md-4 mb-2">
                        <code><?= $placeholder ?></code> — <?= $description ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
