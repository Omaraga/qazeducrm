<?php

/** @var yii\web\View $this */
/** @var app\models\OrganizationSubscription $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Продление подписки #' . $model->id;
?>

<div class="mb-3">
    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Назад
    </a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header">Продление подписки</div>
            <div class="card-body">
                <div class="mb-4">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Организация</td>
                            <td><strong><?= Html::encode($model->organization->name ?? '—') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Текущий тариф</td>
                            <td><?= Html::encode($model->saasPlan->name ?? '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Текущий срок</td>
                            <td>
                                <?php if ($model->expires_at): ?>
                                    <?php if ($model->isExpired()): ?>
                                        <span class="text-danger"><?= Yii::$app->formatter->asDate($model->expires_at) ?></span>
                                        <span class="badge badge-danger">Истёк</span>
                                    <?php else: ?>
                                        <?= Yii::$app->formatter->asDate($model->expires_at) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <form method="post">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                    <div class="form-group">
                        <label class="font-weight-bold">Выберите период продления:</label>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-1"><?= $model->saasPlan ? $model->saasPlan->getFormattedPriceMonthly() : '—' ?></div>
                                        <div class="text-muted mb-3">в месяц</div>
                                        <button type="submit" name="period" value="monthly" class="btn btn-outline-primary btn-block">
                                            Продлить на месяц
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <div class="h4 mb-1"><?= $model->saasPlan ? $model->saasPlan->getFormattedPriceYearly() : '—' ?></div>
                                        <div class="text-muted mb-3">в год</div>
                                        <button type="submit" name="period" value="yearly" class="btn btn-primary btn-block">
                                            Продлить на год
                                        </button>
                                        <?php if ($model->saasPlan && $model->saasPlan->price_yearly > 0): ?>
                                        <?php
                                        $monthlyTotal = $model->saasPlan->price_monthly * 12;
                                        $savings = $monthlyTotal - $model->saasPlan->price_yearly;
                                        if ($savings > 0):
                                        ?>
                                        <small class="text-success d-block mt-2">
                                            Экономия <?= number_format($savings, 0, '', ' ') ?> KZT
                                        </small>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-custom">
            <div class="card-header">Расчёт нового срока</div>
            <div class="card-body">
                <?php
                $baseDate = $model->expires_at && strtotime($model->expires_at) > time()
                    ? $model->expires_at
                    : date('Y-m-d H:i:s');
                ?>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">База для расчёта</td>
                        <td>
                            <?= Yii::$app->formatter->asDate($baseDate) ?>
                            <?php if ($model->isExpired()): ?>
                            <small class="text-muted">(от сегодня, т.к. истёк)</small>
                            <?php else: ?>
                            <small class="text-muted">(от текущего срока)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">При продлении на месяц</td>
                        <td class="text-success">
                            до <?= Yii::$app->formatter->asDate(date('Y-m-d', strtotime('+1 month', strtotime($baseDate)))) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">При продлении на год</td>
                        <td class="text-success">
                            до <?= Yii::$app->formatter->asDate(date('Y-m-d', strtotime('+1 year', strtotime($baseDate)))) ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
