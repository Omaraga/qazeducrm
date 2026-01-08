<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$this->title = 'Запрос на удаление платежа #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Платеж #' . $model->id, 'url' => OrganizationUrl::to(['payment/view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = 'Запрос на удаление';
?>

<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto">
            <?= Icon::show('trash', 'xl', 'text-red-600') ?>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-500 mt-1">Запрос будет отправлен директору на рассмотрение</p>
    </div>

    <!-- Payment Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Информация о платеже</h3>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Ученик</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($model->pupil->fio ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Сумма</dt>
                    <dd class="font-medium <?= $model->type == Payment::TYPE_PAY ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $model->type == Payment::TYPE_PAY ? '+' : '-' ?><?= number_format($model->amount, 0, '.', ' ') ?> ₸
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Дата</dt>
                    <dd class="font-medium text-gray-900"><?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Тип</dt>
                    <dd>
                        <?php if ($model->type == Payment::TYPE_PAY): ?>
                            <span class="badge badge-success">Приход</span>
                        <?php elseif ($model->type == Payment::TYPE_REFUND): ?>
                            <span class="badge badge-warning">Возврат</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Расход</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if ($model->method): ?>
                <div>
                    <dt class="text-gray-500">Способ оплаты</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($model->method->name) ?></dd>
                </div>
                <?php endif; ?>
                <?php if ($model->comment): ?>
                <div class="col-span-2">
                    <dt class="text-gray-500">Комментарий</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($model->comment) ?></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <!-- Request Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Причина удаления</h3>
        </div>
        <div class="card-body">
            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="space-y-4">
                    <div>
                        <label class="form-label required">Причина</label>
                        <textarea name="reason" class="form-input" rows="4"
                                  placeholder="Укажите причину, по которой необходимо удалить этот платёж" required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Опишите подробно причину запроса на удаление</p>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <?= Icon::show('exclamation-triangle', 'md', 'text-amber-600 flex-shrink-0') ?>
                            <div class="text-sm text-amber-700">
                                <p class="font-medium">Внимание!</p>
                                <p class="mt-1">После одобрения директором платёж будет удалён без возможности восстановления. Баланс ученика будет пересчитан.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-danger flex-1">
                        <?= Icon::show('paper-airplane', 'sm') ?>
                        Отправить запрос
                    </button>
                    <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $model->id]) ?>" class="btn btn-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
