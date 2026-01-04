<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan $model */
/** @var int $activeSubscriptions */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->name;
$features = $model->getFeaturesArray();
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
    <?= Html::a('<i class="fas fa-edit"></i> Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация о плане</span>
                <?php if ($model->is_active): ?>
                    <span class="badge badge-success">Активен</span>
                <?php else: ?>
                    <span class="badge badge-secondary">Неактивен</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Код</td>
                                <td><code><?= $model->code ?></code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Название</td>
                                <td><strong><?= Html::encode($model->name) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Описание</td>
                                <td><?= Html::encode($model->description) ?: '—' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Цена/месяц</td>
                                <td><strong><?= $model->getFormattedPriceMonthly() ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Цена/год</td>
                                <td><?= $model->getFormattedPriceYearly() ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trial</td>
                                <td><?= $model->trial_days ?> дней</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-header">Лимиты</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col">
                        <div class="h3"><?= $model->max_pupils ?: '∞' ?></div>
                        <small class="text-muted">Учеников</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->max_teachers ?: '∞' ?></div>
                        <small class="text-muted">Учителей</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->max_groups ?: '∞' ?></div>
                        <small class="text-muted">Групп</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->max_admins ?: '∞' ?></div>
                        <small class="text-muted">Админов</small>
                    </div>
                    <div class="col">
                        <div class="h3"><?= $model->max_branches ?: '∞' ?></div>
                        <small class="text-muted">Филиалов</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header">Функции</div>
            <div class="card-body">
                <?php
                $featureLabels = [
                    'crm_basic' => 'Базовый CRM',
                    'sms' => 'SMS уведомления',
                    'reports' => 'Расширенные отчёты',
                    'api' => 'API доступ',
                    'leads' => 'Управление лидами',
                    'custom' => 'Кастомизация',
                    'priority_support' => 'Приоритетная поддержка',
                ];
                ?>
                <div class="row">
                    <?php foreach ($featureLabels as $key => $label): ?>
                        <div class="col-md-4 mb-2">
                            <?php if (!empty($features[$key])): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-muted"></i>
                            <?php endif; ?>
                            <?= $label ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-header">Статистика</div>
            <div class="card-body text-center">
                <div class="h1 mb-0"><?= $activeSubscriptions ?></div>
                <div class="text-muted">Активных подписок</div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header">Действия</div>
            <div class="card-body">
                <?= Html::a('<i class="fas fa-trash"></i> Удалить план', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger btn-block',
                    'data-method' => 'post',
                    'data-confirm' => 'Вы уверены? План можно удалить только если нет активных подписок.',
                ]) ?>
            </div>
        </div>
    </div>
</div>
