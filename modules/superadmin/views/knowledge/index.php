<?php

/** @var yii\web\View $this */
/** @var app\modules\superadmin\models\search\KnowledgeArticleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $categories */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'База знаний';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold">Статьи базы знаний</span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?></span>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-folder"></i> Категории', ['categories'], ['class' => 'btn btn-outline-secondary mr-2']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Добавить статью', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="row mb-3">
            <div class="col-md-4">
                <?= Html::beginForm(['index'], 'get') ?>
                <div class="input-group">
                    <?= Html::textInput('KnowledgeArticleSearch[query]', $searchModel->query, [
                        'class' => 'form-control',
                        'placeholder' => 'Поиск по заголовку...'
                    ]) ?>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
            <div class="col-md-4">
                <?= Html::beginForm(['index'], 'get') ?>
                <?= Html::dropDownList('KnowledgeArticleSearch[category_id]', $searchModel->category_id,
                    ['' => 'Все категории'] + $categories, [
                    'class' => 'form-control',
                    'onchange' => 'this.form.submit()'
                ]) ?>
                <?= Html::endForm() ?>
            </div>
            <div class="col-md-4">
                <div class="btn-group">
                    <?= Html::a('Все', ['index'], [
                        'class' => 'btn btn-outline-secondary' . ($searchModel->is_active === null ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Активные', ['index', 'KnowledgeArticleSearch[is_active]' => 1], [
                        'class' => 'btn btn-outline-success' . ($searchModel->is_active == 1 ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Неактивные', ['index', 'KnowledgeArticleSearch[is_active]' => 0], [
                        'class' => 'btn btn-outline-warning' . ($searchModel->is_active === '0' ? ' active' : '')
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Таблица -->
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover'],
            'layout' => "{items}\n{pager}",
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['style' => 'width: 60px'],
                ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $title = Html::encode($model->title);
                        $link = Html::a($title, ['view', 'id' => $model->id], ['class' => 'font-weight-bold']);

                        $badges = '';
                        if ($model->is_featured) {
                            $badges .= ' <span class="badge badge-warning">Избранная</span>';
                        }

                        $info = '<br><small class="text-muted">' . Html::encode($model->slug) . '</small>';

                        return $link . $badges . $info;
                    },
                ],
                [
                    'attribute' => 'category_id',
                    'value' => function ($model) {
                        return $model->category ? $model->category->name : '—';
                    },
                ],
                [
                    'attribute' => 'views',
                    'headerOptions' => ['style' => 'width: 80px'],
                    'value' => function ($model) {
                        return $model->views;
                    },
                ],
                [
                    'attribute' => 'sort_order',
                    'headerOptions' => ['style' => 'width: 80px'],
                ],
                [
                    'attribute' => 'is_active',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'width: 100px'],
                    'value' => function ($model) {
                        return $model->is_active
                            ? '<span class="badge badge-success">Активна</span>'
                            : '<span class="badge badge-secondary">Неактивна</span>';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'title' => 'Просмотр',
                            ]);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fas fa-edit"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'title' => 'Редактировать',
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<i class="fas fa-trash"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'Удалить',
                                'data' => [
                                    'confirm' => 'Вы уверены, что хотите удалить эту статью?',
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
