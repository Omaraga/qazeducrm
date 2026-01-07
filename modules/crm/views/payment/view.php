<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$this->title = Yii::t('main', 'Платеж') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('main', 'Бухгалтерия'), 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center <?= $model->type == Payment::TYPE_PAY ? 'bg-success-100' : 'bg-danger-100' ?>">
                <?php if ($model->type == Payment::TYPE_PAY): ?>
                    <?= Icon::show('arrow-up', 'lg', 'text-success-600') ?>
                <?php else: ?>
                    <?= Icon::show('arrow-down', 'lg', 'text-danger-600') ?>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
                <p class="text-gray-500 mt-1">
                    <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?>
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['payment/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                <?= Icon::show('edit', 'sm') ?>
                Редактировать
            </a>
            <?= Html::a(Icon::show('trash', 'sm') . ' Удалить',
                OrganizationUrl::to(['payment/delete', 'id' => $model->id]), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить этот платеж?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Payment Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Информация о платеже</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Тип операции</dt>
                            <dd class="mt-1">
                                <?php if ($model->type == Payment::TYPE_PAY): ?>
                                    <span class="badge badge-success">Приход</span>
                                <?php elseif ($model->type == Payment::TYPE_REFUND): ?>
                                    <span class="badge badge-warning">Возврат</span>
                                <?php elseif ($model->type == Payment::TYPE_SPENDING): ?>
                                    <span class="badge badge-danger">Расход</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Неизвестно</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Сумма</dt>
                            <dd class="mt-1">
                                <?php if ($model->type == Payment::TYPE_PAY): ?>
                                    <span class="text-xl font-bold text-success-600">+<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                                <?php else: ?>
                                    <span class="text-xl font-bold text-danger-600">-<?= number_format($model->amount, 0, '.', ' ') ?> ₸</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ученик</dt>
                            <dd class="mt-1">
                                <?php if ($model->pupil): ?>
                                    <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                        <?= Html::encode($model->pupil->fio) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if ($model->type == Payment::TYPE_PAY): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Назначение</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->purposeLabel ?? '—') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Способ оплаты</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->method->name ?? '—') ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->number): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Номер квитанции</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Html::encode($model->number) ?>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Comment -->
            <?php if ($model->comment): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Комментарий</h3>
                </div>
                <div class="card-body">
                    <p class="text-gray-700"><?= nl2br(Html::encode($model->comment)) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Meta Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Системная информация</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?= $model->id ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Создано</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->created_at, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Обновлено</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?= Yii::$app->formatter->asDatetime($model->updated_at, 'dd.MM.yyyy HH:mm') ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Back Button -->
            <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary w-full justify-center">
                <?= Icon::show('arrow-left', 'sm') ?>
                Назад к бухгалтерии
            </a>

            <?php if ($model->pupil): ?>
            <!-- Quick Links -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900">Быстрые ссылки</h3>
                </div>
                <div class="card-body space-y-2">
                    <a href="<?= OrganizationUrl::to(['pupil/view', 'id' => $model->pupil_id]) ?>" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800">
                        <?= Icon::show('user', 'sm') ?>
                        Карточка ученика
                    </a>
                    <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $model->pupil_id]) ?>" class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800">
                        <?= Icon::show('credit-card', 'sm') ?>
                        Все платежи ученика
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
