<?php

/** @var yii\web\View $this */
/** @var int $totalOrganizations */
/** @var int $headOrganizations */
/** @var int $branchOrganizations */
/** @var array $organizationsByStatus */
/** @var array $subscriptionsByStatus */
/** @var int $expiringSoon */
/** @var int $pendingPayments */
/** @var float $pendingPaymentsAmount */
/** @var float $thisMonthRevenue */
/** @var float $lastMonthRevenue */
/** @var array $recentOrganizations */
/** @var array $recentPendingPayments */
/** @var array $planStats */
/** @var array $awaitingApproval */
/** @var int $awaitingApprovalCount */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Dashboard';

$revenueChange = $lastMonthRevenue > 0
    ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
    : 0;
?>

<div class="row mb-4">
    <!-- Организации -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $totalOrganizations ?></div>
                    <div class="stat-label">Всего организаций</div>
                    <small class="text-muted">
                        <?= $headOrganizations ?> головных, <?= $branchOrganizations ?> филиалов
                    </small>
                </div>
                <div class="stat-icon" style="background: #4f46e5;">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Активные подписки -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $subscriptionsByStatus['active'] + $subscriptionsByStatus['trial'] ?></div>
                    <div class="stat-label">Активных подписок</div>
                    <small class="text-muted">
                        <?= $subscriptionsByStatus['trial'] ?> trial, <?= $subscriptionsByStatus['active'] ?> paid
                    </small>
                </div>
                <div class="stat-icon" style="background: #10b981;">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Выручка за месяц -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= number_format($thisMonthRevenue, 0, '.', ' ') ?></div>
                    <div class="stat-label">Выручка за месяц (KZT)</div>
                    <small class="<?= $revenueChange >= 0 ? 'text-success' : 'text-danger' ?>">
                        <i class="fas fa-arrow-<?= $revenueChange >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs($revenueChange) ?>% к прошлому месяцу
                    </small>
                </div>
                <div class="stat-icon" style="background: #f59e0b;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Ожидающие действия -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $awaitingApprovalCount + $pendingPayments + $expiringSoon ?></div>
                    <div class="stat-label">Требуют внимания</div>
                    <small class="text-muted">
                        <?php if ($awaitingApprovalCount > 0): ?>
                            <span class="text-warning"><?= $awaitingApprovalCount ?> заявок</span>,
                        <?php endif; ?>
                        <?= $pendingPayments ?> платежей, <?= $expiringSoon ?> истекают
                    </small>
                </div>
                <div class="stat-icon" style="background: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($awaitingApprovalCount > 0): ?>
<!-- Организации ожидающие одобрения -->
<div class="card card-custom mb-4 border-warning">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-hourglass-half mr-2"></i>
            <strong>Ожидают одобрения</strong>
            <span class="badge badge-dark ml-2"><?= $awaitingApprovalCount ?></span>
        </span>
        <a href="<?= Url::to(['/superadmin/organization/pending']) ?>" class="btn btn-sm btn-dark">
            Все заявки
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Организация</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Дата регистрации</th>
                    <th class="text-center">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($awaitingApproval as $org): ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode($org->name) ?></strong>
                            <?php if ($org->bin): ?>
                                <br><small class="text-muted">БИН: <?= Html::encode($org->bin) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="mailto:<?= Html::encode($org->email) ?>"><?= Html::encode($org->email) ?></a>
                            <br><small class="text-success"><i class="fas fa-check-circle"></i> Подтверждён</small>
                        </td>
                        <td>
                            <?php if ($org->phone): ?>
                                <a href="tel:<?= Html::encode($org->phone) ?>"><?= Html::encode($org->phone) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= Yii::$app->formatter->asDatetime($org->created_at, 'php:d.m.Y H:i') ?></small>
                            <br><small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($org->created_at) ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="<?= Url::to(['/superadmin/organization/view', 'id' => $org->id]) ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?= Html::a(
                                    '<i class="fas fa-check"></i>',
                                    ['/superadmin/organization/activate', 'id' => $org->id],
                                    [
                                        'class' => 'btn btn-sm btn-success',
                                        'title' => 'Одобрить',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => 'Вы уверены, что хотите одобрить организацию "' . Html::encode($org->name) . '"?',
                                        ],
                                    ]
                                ) ?>
                                <?= Html::a(
                                    '<i class="fas fa-times"></i>',
                                    ['/superadmin/organization/block', 'id' => $org->id],
                                    [
                                        'class' => 'btn btn-sm btn-danger',
                                        'title' => 'Отклонить',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => 'Вы уверены, что хотите отклонить организацию "' . Html::encode($org->name) . '"?',
                                        ],
                                    ]
                                ) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Левая колонка -->
    <div class="col-lg-8">
        <!-- Статус организаций -->
        <div class="card card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Статус организаций</span>
                <a href="<?= Url::to(['/superadmin/organization/index']) ?>" class="btn btn-sm btn-outline-primary">
                    Все организации
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="h3 mb-0 text-success"><?= $organizationsByStatus['active'] ?></div>
                        <small class="text-muted">Активные</small>
                    </div>
                    <div class="col-3">
                        <div class="h3 mb-0 text-primary"><?= $organizationsByStatus['pending'] ?></div>
                        <small class="text-muted">Ожидают</small>
                    </div>
                    <div class="col-3">
                        <div class="h3 mb-0 text-warning"><?= $organizationsByStatus['suspended'] ?></div>
                        <small class="text-muted">Приостановлены</small>
                    </div>
                    <div class="col-3">
                        <div class="h3 mb-0 text-danger"><?= $organizationsByStatus['blocked'] ?></div>
                        <small class="text-muted">Заблокированы</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Последние регистрации -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                Последние регистрации
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentOrganizations)): ?>
                    <div class="p-4 text-center text-muted">
                        Нет зарегистрированных организаций
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Организация</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrganizations as $org): ?>
                                <tr>
                                    <td>
                                        <strong><?= Html::encode($org->name) ?></strong>
                                        <?php if ($org->email): ?>
                                            <br><small class="text-muted"><?= Html::encode($org->email) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $org->status ?>">
                                            <?= $org->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= Yii::$app->formatter->asDatetime($org->created_at, 'php:d.m.Y') ?></small>
                                    </td>
                                    <td>
                                        <a href="<?= Url::to(['/superadmin/organization/view', 'id' => $org->id]) ?>"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ожидающие платежи -->
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    Ожидающие платежи
                    <?php if ($pendingPayments > 0): ?>
                        <span class="badge badge-warning ml-2"><?= $pendingPayments ?></span>
                    <?php endif; ?>
                </span>
                <a href="<?= Url::to(['/superadmin/payment/index', 'PaymentSearch[status]' => 'pending']) ?>"
                   class="btn btn-sm btn-outline-primary">
                    Все платежи
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentPendingPayments)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <br>Нет ожидающих платежей
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Организация</th>
                                <th>Сумма</th>
                                <th>Дата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPendingPayments as $payment): ?>
                                <tr>
                                    <td><?= Html::encode($payment->organization->name ?? 'N/A') ?></td>
                                    <td><strong><?= $payment->getFormattedAmount() ?></strong></td>
                                    <td>
                                        <small><?= Yii::$app->formatter->asDatetime($payment->created_at, 'php:d.m.Y') ?></small>
                                    </td>
                                    <td>
                                        <a href="<?= Url::to(['/superadmin/payment/view', 'id' => $payment->id]) ?>"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Подтвердить
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Правая колонка -->
    <div class="col-lg-4">
        <!-- Статистика по тарифам -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                Распределение по тарифам
            </div>
            <div class="card-body">
                <?php foreach ($planStats as $plan): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong><?= Html::encode($plan['name']) ?></strong>
                            <br>
                            <small class="text-muted"><?= $plan['code'] ?></small>
                        </div>
                        <div class="text-right">
                            <span class="h4 mb-0"><?= (int)$plan['subscription_count'] ?></span>
                            <br>
                            <small class="text-muted">подписок</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Статистика подписок -->
        <div class="card card-custom mb-4">
            <div class="card-header">
                Статус подписок
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><span class="badge badge-trial">Trial</span> Пробный период</span>
                    <strong><?= $subscriptionsByStatus['trial'] ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><span class="badge badge-active">Active</span> Активные</span>
                    <strong><?= $subscriptionsByStatus['active'] ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><span class="badge badge-expired">Expired</span> Истекшие</span>
                    <strong><?= $subscriptionsByStatus['expired'] ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span><span class="badge badge-suspended">Suspended</span> Приостановлены</span>
                    <strong><?= $subscriptionsByStatus['suspended'] ?></strong>
                </div>

                <?php if ($expiringSoon > 0): ?>
                    <hr>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-clock"></i>
                        <strong><?= $expiringSoon ?></strong> подписок истекают в течение 7 дней
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="card card-custom">
            <div class="card-header">
                Быстрые действия
            </div>
            <div class="card-body">
                <?php if ($awaitingApprovalCount > 0): ?>
                <a href="<?= Url::to(['/superadmin/organization/pending']) ?>"
                   class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-hourglass-half"></i> Одобрить заявки
                    <span class="badge badge-light ml-1"><?= $awaitingApprovalCount ?></span>
                </a>
                <?php endif; ?>
                <a href="<?= Url::to(['/superadmin/organization/create']) ?>" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Добавить организацию
                </a>
                <a href="<?= Url::to(['/superadmin/subscription/index', 'SubscriptionSearch[expiring]' => 1]) ?>"
                   class="btn btn-outline-warning btn-block mb-2">
                    <i class="fas fa-clock"></i> Истекающие подписки
                </a>
                <a href="<?= Url::to(['/superadmin/payment/index', 'PaymentSearch[status]' => 'pending']) ?>"
                   class="btn btn-outline-success btn-block">
                    <i class="fas fa-money-bill"></i> Подтвердить платежи
                </a>
            </div>
        </div>
    </div>
</div>
