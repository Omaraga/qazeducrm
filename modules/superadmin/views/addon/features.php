<?php

/** @var yii\web\View $this */
/** @var app\models\SaasFeature[] $features */

use yii\helpers\Html;

$this->title = 'Доступные аддоны';
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card card-custom">
    <div class="card-header">
        <span class="font-weight-bold">Каталог доступных аддонов</span>
    </div>
    <div class="card-body">
        <?php if (empty($features)): ?>
            <p class="text-muted text-center py-4">Нет доступных аддонов</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($features as $feature): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= Html::encode($feature->name) ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <?= Html::encode($feature->description) ?>
                            </p>

                            <div class="mb-2">
                                <span class="badge badge-secondary"><?= $feature->code ?></span>
                                <span class="badge badge-info"><?= $feature->getCategoryLabel() ?></span>
                                <span class="badge badge-light"><?= $feature->getTypeLabel() ?></span>
                            </div>

                            <?php if ($feature->addon_price_monthly): ?>
                            <div class="mt-3">
                                <strong class="text-primary">
                                    <?= number_format($feature->addon_price_monthly, 0, '.', ' ') ?> KZT/мес
                                </strong>
                                <?php if ($feature->addon_price_yearly): ?>
                                <br>
                                <small class="text-muted">
                                    или <?= number_format($feature->addon_price_yearly, 0, '.', ' ') ?> KZT/год
                                </small>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($feature->trial_available): ?>
                            <div class="mt-2">
                                <span class="badge badge-warning">
                                    Trial <?= $feature->trial_days ?> дней
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <?= Html::a(
                                '<i class="fas fa-plus"></i> Добавить организации',
                                ['create', 'feature_id' => $feature->id],
                                ['class' => 'btn btn-sm btn-outline-primary']
                            ) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
