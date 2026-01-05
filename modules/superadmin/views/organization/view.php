<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $model */
/** @var app\models\Organizations[] $branches */
/** @var app\models\OrganizationSubscription|null $subscription */
/** @var app\models\OrganizationActivityLog[] $activityLogs */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Organizations;

$this->title = $model->name;
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
    <?= Html::a('<i class="fas fa-edit"></i> Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

    <?php if ($model->isHead()): ?>
        <?= Html::a('<i class="fas fa-plus"></i> Добавить филиал', ['create-branch', 'parent_id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация об организации</span>
                <?php
                $badges = [
                    'active' => 'badge-active',
                    'pending' => 'badge-pending',
                    'suspended' => 'badge-suspended',
                    'blocked' => 'badge-expired',
                ];
                $class = $badges[$model->status] ?? 'badge-secondary';
                ?>
                <span class="badge <?= $class ?>"><?= $model->getStatusLabel() ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 140px;">ID</td>
                                <td><strong><?= $model->id ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Тип</td>
                                <td>
                                    <?php if ($model->isHead()): ?>
                                        <span class="badge badge-head">Головная</span>
                                    <?php else: ?>
                                        <span class="badge badge-branch">Филиал</span>
                                        <?php if ($model->parentOrganization): ?>
                                            <br><small>→ <?= Html::a($model->parentOrganization->name, ['view', 'id' => $model->parent_id]) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Название</td>
                                <td><strong><?= Html::encode($model->name) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Юр. название</td>
                                <td><?= Html::encode($model->legal_name) ?: '—' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">БИН</td>
                                <td><?= Html::encode($model->bin) ?: '—' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 140px;">Email</td>
                                <td>
                                    <?= Html::encode($model->email) ?: '—' ?>
                                    <?php if ($model->isEmailVerified()): ?>
                                        <span class="badge badge-success ml-1">Подтверждён</span>
                                    <?php elseif ($model->email): ?>
                                        <span class="badge badge-warning ml-1">Не подтверждён</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Телефон</td>
                                <td><?= Html::encode($model->phone) ?: '—' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Адрес</td>
                                <td><?= Html::encode($model->address) ?: '—' ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Часовой пояс</td>
                                <td><?= Html::encode($model->timezone) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Создана</td>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Филиалы -->
        <?php if ($model->isHead()): ?>
            <div class="card card-custom mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        Филиалы
                        <span class="badge badge-secondary ml-1"><?= count($branches) ?></span>
                    </span>
                    <?= Html::a('<i class="fas fa-plus"></i> Добавить', ['create-branch', 'parent_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($branches)): ?>
                        <div class="p-4 text-center text-muted">
                            Филиалов нет
                        </div>
                    <?php else: ?>
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Статус</th>
                                    <th>Адрес</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($branches as $branch): ?>
                                    <tr>
                                        <td>
                                            <?= Html::a(Html::encode($branch->name), ['view', 'id' => $branch->id], ['class' => 'font-weight-bold']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $class = $badges[$branch->status] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $class ?>"><?= $branch->getStatusLabel() ?></span>
                                        </td>
                                        <td><?= Html::encode($branch->address) ?: '—' ?></td>
                                        <td>
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $branch->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Логи активности -->
        <div class="card card-custom">
            <div class="card-header">
                История активности
            </div>
            <div class="card-body p-0">
                <?php if (empty($activityLogs)): ?>
                    <div class="p-4 text-center text-muted">
                        Нет записей
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td style="width: 150px;">
                                        <small class="text-muted"><?= Yii::$app->formatter->asDatetime($log->created_at, 'php:d.m.Y') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= $log->getCategoryLabel() ?></span>
                                        <?= Html::encode($log->getActionLabel()) ?>
                                        <?php if ($log->description): ?>
                                            <br><small class="text-muted"><?= Html::encode($log->description) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 100px;">
                                        <small class="text-muted"><?= $log->user_type ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Боковая панель -->
    <div class="col-lg-4">
        <!-- Подписка -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                Подписка
            </div>
            <div class="card-body">
                <?php if ($subscription): ?>
                    <div class="text-center mb-3">
                        <h4 class="mb-1"><?= Html::encode($subscription->saasPlan->name ?? 'N/A') ?></h4>
                        <?php
                        $subBadges = [
                            'trial' => 'badge-trial',
                            'active' => 'badge-active',
                            'expired' => 'badge-expired',
                            'suspended' => 'badge-suspended',
                        ];
                        $subClass = $subBadges[$subscription->status] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?= $subClass ?>"><?= $subscription->getStatusLabel() ?></span>
                    </div>

                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted">Период</td>
                            <td><?= $subscription->billing_period === 'yearly' ? 'Годовая' : 'Месячная' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Начало</td>
                            <td><?= $subscription->started_at ? Yii::$app->formatter->asDate($subscription->started_at, 'php:d.m.Y') : '—' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Истекает</td>
                            <td>
                                <?php if ($subscription->expires_at): ?>
                                    <?= Yii::$app->formatter->asDate($subscription->expires_at, 'php:d.m.Y') ?>
                                    <?php if ($subscription->isExpiringSoon()): ?>
                                        <span class="badge badge-warning">Скоро</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($subscription->isTrial()): ?>
                            <tr>
                                <td class="text-muted">Trial до</td>
                                <td><?= Yii::$app->formatter->asDate($subscription->trial_ends_at, 'php:d.m.Y') ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <hr>
                    <a href="<?= Url::to(['/superadmin/subscription/view', 'id' => $subscription->id]) ?>" class="btn btn-outline-primary btn-block">
                        Управление подпиской
                    </a>
                <?php else: ?>
                    <div class="text-center text-muted mb-3">
                        <i class="fas fa-credit-card fa-3x mb-2"></i>
                        <p>Нет активной подписки</p>
                    </div>
                    <a href="<?= Url::to(['/superadmin/subscription/create', 'organization_id' => $model->id]) ?>" class="btn btn-primary btn-block">
                        Создать подписку
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Действия -->
        <div class="card card-custom">
            <div class="card-header">
                Действия
            </div>
            <div class="card-body">
                <?php if ($model->status !== Organizations::STATUS_ACTIVE): ?>
                    <?= Html::a('<i class="fas fa-check"></i> Активировать', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Активировать организацию?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status !== Organizations::STATUS_SUSPENDED): ?>
                    <?= Html::a('<i class="fas fa-pause"></i> Приостановить', ['suspend', 'id' => $model->id], [
                        'class' => 'btn btn-warning btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Приостановить организацию?',
                    ]) ?>
                <?php endif; ?>

                <?php if ($model->status !== Organizations::STATUS_BLOCKED): ?>
                    <?= Html::a('<i class="fas fa-ban"></i> Заблокировать', ['block', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-block mb-2',
                        'data-method' => 'post',
                        'data-confirm' => 'Заблокировать организацию? Пользователи не смогут войти.',
                    ]) ?>
                <?php endif; ?>

                <hr>

                <?= Html::a('<i class="fas fa-trash"></i> Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger btn-block',
                    'data-method' => 'post',
                    'data-confirm' => 'Вы уверены, что хотите удалить эту организацию?',
                ]) ?>
            </div>
        </div>
    </div>
</div>
