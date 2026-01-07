<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Промокоды';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mb-3">
    <?= Html::a('<i class="fas fa-plus"></i> Создать промокод', ['create'], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('<i class="fas fa-chart-bar"></i> Статистика', ['stats'], ['class' => 'btn btn-outline-info ml-2']) ?>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-custom bg-primary text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= $stats['total'] ?></div>
                <small>Всего промокодов</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-success text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= $stats['active'] ?></div>
                <small>Активных</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-info text-white">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= $stats['total_usage'] ?></div>
                <small>Использований</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-warning text-dark">
            <div class="card-body text-center">
                <div class="h2 mb-0"><?= number_format($stats['total_discount'], 0, '', ' ') ?></div>
                <small>Сумма скидок (KZT)</small>
            </div>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="card card-custom mb-4">
    <div class="card-body py-3">
        <form method="get" class="form-inline">
            <label class="mr-2">Статус:</label>
            <select name="status" class="form-control form-control-sm mr-3" onchange="this.form.submit()">
                <option value="">Все</option>
                <option value="active" <?= Yii::$app->request->get('status') === 'active' ? 'selected' : '' ?>>Активные</option>
                <option value="inactive" <?= Yii::$app->request->get('status') === 'inactive' ? 'selected' : '' ?>>Неактивные</option>
                <option value="expired" <?= Yii::$app->request->get('status') === 'expired' ? 'selected' : '' ?>>Истёкшие</option>
            </select>
        </form>
    </div>
</div>

<!-- Таблица -->
<div class="card card-custom">
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{pager}",
            'tableOptions' => ['class' => 'table table-hover mb-0'],
            'columns' => [
                [
                    'attribute' => 'code',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a('<code class="h5">' . Html::encode($model->code) . '</code>', ['view', 'id' => $model->id]);
                    },
                ],
                'name',
                [
                    'attribute' => 'discount_value',
                    'label' => 'Скидка',
                    'value' => function ($model) {
                        return $model->getFormattedDiscount();
                    },
                ],
                [
                    'attribute' => 'applies_to',
                    'value' => function ($model) {
                        return $model->getAppliesToLabel();
                    },
                ],
                [
                    'label' => 'Использовано',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $count = $model->getUsageCount();
                        $limit = $model->usage_limit ?: '∞';
                        return "<strong>{$count}</strong> / {$limit}";
                    },
                ],
                [
                    'attribute' => 'valid_until',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (!$model->valid_until) {
                            return '<span class="text-muted">Бессрочно</span>';
                        }
                        $date = Yii::$app->formatter->asDate($model->valid_until, 'php:d.m.Y');
                        if ($model->isExpired()) {
                            return '<span class="text-danger">' . $date . '</span>';
                        }
                        return $date;
                    },
                ],
                [
                    'label' => 'Статус',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusLabel() . '</span>';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{toggle} {view} {update}',
                    'buttons' => [
                        'toggle' => function ($url, $model) {
                            $icon = $model->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-secondary';
                            $title = $model->is_active ? 'Деактивировать' : 'Активировать';
                            return Html::a('<i class="fas ' . $icon . '"></i>', ['toggle', 'id' => $model->id], [
                                'title' => $title,
                                'data-method' => 'post',
                            ]);
                        },
                        'view' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], ['title' => 'Просмотр']);
                        },
                        'update' => function ($url, $model) {
                            return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], ['title' => 'Редактировать']);
                        },
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>
