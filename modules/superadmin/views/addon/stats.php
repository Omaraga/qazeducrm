<?php

/** @var yii\web\View $this */
/** @var array $stats */
/** @var float $revenue */
/** @var array $byFeature */
/** @var array $expiringSoon */

use yii\helpers\Html;

$this->title = 'Статистика аддонов';
$this->params['breadcrumbs'][] = ['label' => 'Аддоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <!-- Общая статистика -->
    <div class="col-md-3">
        <div class="card card-custom bg-primary text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $stats['total'] ?></h2>
                <small>Всего аддонов</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-success text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $stats['active'] ?></h2>
                <small>Активных</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-warning text-dark">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= $stats['trial'] ?></h2>
                <small>На trial</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom bg-info text-white">
            <div class="card-body text-center">
                <h2 class="mb-0"><?= number_format($revenue, 0, '.', ' ') ?> KZT</h2>
                <small>MRR от аддонов</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- По типам аддонов -->
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header">
                <span class="font-weight-bold">Популярные аддоны</span>
            </div>
            <div class="card-body">
                <?php if (empty($byFeature)): ?>
                    <p class="text-muted text-center">Нет данных</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Аддон</th>
                                <th class="text-center">Кол-во</th>
                                <th class="text-right">Выручка</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byFeature as $item): ?>
                            <tr>
                                <td>
                                    <?= Html::encode($item['name']) ?>
                                    <small class="text-muted"><?= $item['code'] ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?= $item['count'] ?></span>
                                </td>
                                <td class="text-right">
                                    <?= number_format($item['revenue'] ?? 0, 0, '.', ' ') ?> KZT
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Истекающие скоро -->
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Истекают в течение 7 дней</span>
                <?= Html::a('Все', ['index', 'expiring' => 1], ['class' => 'btn btn-sm btn-outline-warning']) ?>
            </div>
            <div class="card-body">
                <?php if (empty($expiringSoon)): ?>
                    <p class="text-muted text-center">Нет истекающих аддонов</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Организация</th>
                                <th>Аддон</th>
                                <th class="text-right">Истекает</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiringSoon as $addon): ?>
                            <tr>
                                <td>
                                    <?php if ($addon->organization): ?>
                                        <?= Html::a(
                                            Html::encode($addon->organization->name),
                                            ['/superadmin/organization/view', 'id' => $addon->organization_id],
                                            ['class' => 'text-primary']
                                        ) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= Html::a(
                                        Html::encode($addon->feature->name ?? 'Аддон #' . $addon->feature_id),
                                        ['view', 'id' => $addon->id]
                                    ) ?>
                                </td>
                                <td class="text-right">
                                    <?php $days = $addon->getDaysRemaining(); ?>
                                    <span class="badge <?= $days <= 3 ? 'badge-danger' : 'badge-warning' ?>">
                                        <?= $days ?> дн.
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header">
                <span class="font-weight-bold">Статусы аддонов</span>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <h4><?= $stats['active'] ?></h4>
                        <span class="badge badge-success">Активных</span>
                    </div>
                    <div class="col">
                        <h4><?= $stats['trial'] ?></h4>
                        <span class="badge badge-warning">Trial</span>
                    </div>
                    <div class="col">
                        <h4><?= $stats['expired'] ?></h4>
                        <span class="badge badge-danger">Истекших</span>
                    </div>
                    <div class="col">
                        <h4><?= $stats['cancelled'] ?></h4>
                        <span class="badge badge-secondary">Отменённых</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
