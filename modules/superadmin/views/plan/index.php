<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Тарифные планы';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold">Тарифные планы</span>
        <?= Html::a('<i class="fas fa-plus"></i> Добавить план', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($dataProvider->getModels() as $plan): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 <?= $plan->is_active ? '' : 'bg-light' ?>">
                        <div class="card-header text-center">
                            <h5 class="mb-0"><?= Html::encode($plan->name) ?></h5>
                            <small class="text-muted"><?= $plan->code ?></small>
                            <?php if (!$plan->is_active): ?>
                                <span class="badge badge-secondary ml-1">Неактивен</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body text-center">
                            <div class="display-4 mb-2">
                                <?php if ($plan->price_monthly > 0): ?>
                                    <?= number_format($plan->price_monthly, 0, '.', ' ') ?>
                                    <small class="text-muted" style="font-size: 0.4em;">KZT/мес</small>
                                <?php else: ?>
                                    <span class="text-success">Бесплатно</span>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <ul class="list-unstyled text-left mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-users text-primary"></i>
                                    <?= $plan->max_pupils ?: '∞' ?> учеников
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-chalkboard-teacher text-primary"></i>
                                    <?= $plan->max_teachers ?: '∞' ?> учителей
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-layer-group text-primary"></i>
                                    <?= $plan->max_groups ?: '∞' ?> групп
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-building text-primary"></i>
                                    <?= $plan->max_branches ?: '∞' ?> филиалов
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group btn-group-sm w-100">
                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $plan->id], ['class' => 'btn btn-outline-secondary']) ?>
                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $plan->id], ['class' => 'btn btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
