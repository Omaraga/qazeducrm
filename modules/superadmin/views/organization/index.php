<?php

/** @var yii\web\View $this */
/** @var app\modules\superadmin\models\search\OrganizationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\Organizations;

$this->title = 'Организации';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold">Список организаций</span>
            <span class="badge badge-secondary ml-2"><?= $dataProvider->getTotalCount() ?></span>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-plus"></i> Добавить организацию', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Фильтры -->
        <div class="row mb-3">
            <div class="col-md-4">
                <?= Html::beginForm(['index'], 'get') ?>
                <div class="input-group">
                    <?= Html::textInput('OrganizationSearch[query]', $searchModel->query, [
                        'class' => 'form-control',
                        'placeholder' => 'Поиск по названию, email, БИН...'
                    ]) ?>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
            <div class="col-md-8">
                <div class="btn-group">
                    <?= Html::a('Все', ['index'], [
                        'class' => 'btn btn-outline-secondary' . (empty($searchModel->status) ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Активные', ['index', 'OrganizationSearch[status]' => 'active'], [
                        'class' => 'btn btn-outline-success' . ($searchModel->status === 'active' ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Ожидают', ['index', 'OrganizationSearch[status]' => 'pending'], [
                        'class' => 'btn btn-outline-primary' . ($searchModel->status === 'pending' ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Приостановлены', ['index', 'OrganizationSearch[status]' => 'suspended'], [
                        'class' => 'btn btn-outline-warning' . ($searchModel->status === 'suspended' ? ' active' : '')
                    ]) ?>
                    <?= Html::a('Заблокированы', ['index', 'OrganizationSearch[status]' => 'blocked'], [
                        'class' => 'btn btn-outline-danger' . ($searchModel->status === 'blocked' ? ' active' : '')
                    ]) ?>
                </div>

                <label class="ml-3">
                    <?= Html::checkbox('OrganizationSearch[showBranches]', $searchModel->showBranches, [
                        'id' => 'showBranches',
                        'onchange' => 'this.form.submit()'
                    ]) ?>
                    Показать филиалы
                </label>
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
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $badge = $model->isBranch()
                            ? '<span class="badge badge-branch ml-1">Филиал</span>'
                            : '<span class="badge badge-head ml-1">Головная</span>';

                        $name = Html::encode($model->name);
                        $link = Html::a($name, ['view', 'id' => $model->id], ['class' => 'font-weight-bold']);

                        $info = '';
                        if ($model->email) {
                            $info .= '<br><small class="text-muted">' . Html::encode($model->email) . '</small>';
                        }
                        if ($model->isBranch() && $model->parentOrganization) {
                            $info .= '<br><small class="text-muted">→ ' . Html::encode($model->parentOrganization->name) . '</small>';
                        }

                        return $link . $badge . $info;
                    },
                ],
                [
                    'attribute' => 'bin',
                    'value' => function ($model) {
                        return $model->bin ?: '—';
                    },
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $badges = [
                            'active' => 'badge-active',
                            'pending' => 'badge-pending',
                            'suspended' => 'badge-suspended',
                            'blocked' => 'badge-expired',
                        ];
                        $class = $badges[$model->status] ?? 'badge-secondary';
                        return '<span class="badge ' . $class . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'label' => 'Филиалов',
                    'value' => function ($model) {
                        if ($model->isHead()) {
                            return $model->getBranchCount();
                        }
                        return '—';
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'format' => ['datetime', 'short'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}',
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
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
