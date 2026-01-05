<?php

/** @var yii\web\View $this */
/** @var app\models\KnowledgeCategory[] $categories */

use yii\helpers\Html;

$this->title = 'Категории базы знаний';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span class="font-weight-bold"><?= $this->title ?></span>
            <span class="badge badge-secondary ml-2"><?= count($categories) ?></span>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-arrow-left"></i> К статьям', ['index'], ['class' => 'btn btn-outline-secondary mr-2']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Добавить категорию', ['create-category'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Название</th>
                    <th>Slug</th>
                    <th>Иконка</th>
                    <th style="width: 80px;">Статей</th>
                    <th style="width: 80px;">Порядок</th>
                    <th style="width: 100px;">Статус</th>
                    <th style="width: 150px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category->id ?></td>
                    <td>
                        <strong><?= Html::encode($category->name) ?></strong>
                        <?php if ($category->description): ?>
                        <br><small class="text-muted"><?= Html::encode($category->description) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><code><?= Html::encode($category->slug) ?></code></td>
                    <td><?= Html::encode($category->icon) ?: '—' ?></td>
                    <td><?= $category->getArticleCount() ?></td>
                    <td><?= $category->sort_order ?></td>
                    <td>
                        <?php if ($category->is_active): ?>
                        <span class="badge badge-success">Активна</span>
                        <?php else: ?>
                        <span class="badge badge-secondary">Неактивна</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update-category', 'id' => $category->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Редактировать',
                        ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete-category', 'id' => $category->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Удалить',
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить эту категорию?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Категории не найдены
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
