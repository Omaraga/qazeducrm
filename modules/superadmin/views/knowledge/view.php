<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeArticle $model */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->title;
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><?= Html::encode($model->title) ?></span>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить эту статью?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-bordered detail-view'],
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'category_id',
                            'value' => $model->category ? $model->category->name : '—',
                        ],
                        'slug',
                        'views',
                        'sort_order',
                        [
                            'attribute' => 'is_featured',
                            'format' => 'raw',
                            'value' => $model->is_featured
                                ? '<span class="badge badge-warning">Да</span>'
                                : '<span class="badge badge-secondary">Нет</span>',
                        ],
                        [
                            'attribute' => 'is_active',
                            'format' => 'raw',
                            'value' => $model->is_active
                                ? '<span class="badge badge-success">Активна</span>'
                                : '<span class="badge badge-secondary">Неактивна</span>',
                        ],
                        'created_at:datetime',
                        'updated_at:datetime',
                    ],
                ]) ?>
            </div>
            <div class="col-md-8">
                <h5>Краткое описание</h5>
                <p class="text-muted"><?= Html::encode($model->excerpt) ?></p>

                <hr>

                <h5>Содержимое</h5>
                <div class="border rounded p-3 bg-light" style="max-height: 500px; overflow-y: auto;">
                    <?= $model->content ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <?= Html::a('<i class="fas fa-arrow-left"></i> К списку статей', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>
