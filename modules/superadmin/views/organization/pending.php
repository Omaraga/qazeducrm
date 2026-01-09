<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations[] $organizations */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ожидают одобрения';
?>

<div class="card card-custom">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-hourglass-half text-warning mr-2"></i>
            <span class="font-weight-bold">Организации ожидающие одобрения</span>
            <span class="badge badge-warning ml-2"><?= count($organizations) ?></span>
        </div>
        <div>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Все организации
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($organizations)): ?>
            <div class="p-5 text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>Нет заявок на рассмотрении</h5>
                <p class="text-muted">Все организации обработаны</p>
                <a href="<?= Url::to(['/superadmin']) ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Вернуться на Dashboard
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Организация</th>
                            <th>Контакты</th>
                            <th>Email верификация</th>
                            <th>Дата регистрации</th>
                            <th class="text-center" style="width: 200px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $org): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <strong><?= Html::encode($org->name) ?></strong>
                                            <?php if ($org->bin): ?>
                                                <br><small class="text-muted">БИН: <?= Html::encode($org->bin) ?></small>
                                            <?php endif; ?>
                                            <?php if ($org->legal_name): ?>
                                                <br><small class="text-muted"><?= Html::encode($org->legal_name) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-envelope text-muted mr-1"></i>
                                        <a href="mailto:<?= Html::encode($org->email) ?>"><?= Html::encode($org->email) ?></a>
                                    </div>
                                    <?php if ($org->phone): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-phone text-muted mr-1"></i>
                                            <a href="tel:<?= Html::encode($org->phone) ?>"><?= Html::encode($org->phone) ?></a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($org->address): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-map-marker-alt text-muted mr-1"></i>
                                            <small><?= Html::encode($org->address) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($org->email_verified_at): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Подтверждён
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= Yii::$app->formatter->asDatetime($org->email_verified_at, 'php:d.m.Y H:i') ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Не подтверждён
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= Yii::$app->formatter->asDatetime($org->created_at, 'php:d.m.Y H:i') ?></div>
                                    <small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($org->created_at) ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= Url::to(['view', 'id' => $org->id]) ?>"
                                           class="btn btn-sm btn-outline-info" title="Подробнее">
                                            <i class="fas fa-eye"></i> Просмотр
                                        </a>
                                        <?= Html::a(
                                            '<i class="fas fa-check"></i> Одобрить',
                                            ['activate', 'id' => $org->id],
                                            [
                                                'class' => 'btn btn-sm btn-success',
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => "Одобрить организацию \"{$org->name}\"?\n\nПосле одобрения пользователь сможет войти в систему.",
                                                ],
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-times"></i> Отклонить',
                                            ['block', 'id' => $org->id],
                                            [
                                                'class' => 'btn btn-sm btn-danger',
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => "Отклонить заявку организации \"{$org->name}\"?\n\nОрганизация будет заблокирована.",
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
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-sm {
    font-size: 16px;
}
</style>
