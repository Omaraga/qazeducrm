<?php

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PayMethod;
use app\widgets\tailwind\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Payment $model */

$this->title = 'Запрос на изменение платежа #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = ['label' => 'Платеж #' . $model->id, 'url' => OrganizationUrl::to(['payment/view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = 'Запрос на изменение';

$payMethods = ArrayHelper::map(
    PayMethod::find()->byOrganization()->notDeleted()->all(),
    'id', 'name'
);
?>

<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center">
        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto">
            <?= Icon::show('edit', 'xl', 'text-blue-600') ?>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-500 mt-1">Запрос будет отправлен директору на рассмотрение</p>
    </div>

    <!-- Current Payment Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Текущие данные платежа</h3>
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
                        <?= number_format($model->amount, 0, '.', ' ') ?> ₸
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Дата</dt>
                    <dd class="font-medium text-gray-900"><?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Способ оплаты</dt>
                    <dd class="font-medium text-gray-900"><?= Html::encode($model->method->name ?? '—') ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Request Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Новые значения</h3>
            <p class="text-sm text-gray-500 mt-1">Заполните только те поля, которые нужно изменить</p>
        </div>
        <div class="card-body">
            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Новая сумма</label>
                            <input type="number" name="amount" class="form-input"
                                   placeholder="<?= $model->amount ?>" step="0.01" min="0.01">
                        </div>
                        <div>
                            <label class="form-label">Новая дата</label>
                            <input type="datetime-local" name="date" class="form-input"
                                   value="">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Способ оплаты</label>
                            <?= Html::dropDownList('method_id', null, $payMethods, [
                                'class' => 'form-input',
                                'prompt' => 'Не изменять'
                            ]) ?>
                        </div>
                        <div>
                            <label class="form-label">Назначение</label>
                            <?= Html::dropDownList('purpose_id', null, Payment::getPurposeList(), [
                                'class' => 'form-input',
                                'prompt' => 'Не изменять'
                            ]) ?>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Комментарий к платежу</label>
                        <input type="text" name="comment" class="form-input"
                               placeholder="Новый комментарий">
                    </div>

                    <hr class="my-4">

                    <div>
                        <label class="form-label required">Причина изменения</label>
                        <textarea name="reason" class="form-input" rows="3"
                                  placeholder="Укажите причину, по которой необходимо изменить платёж" required></textarea>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <?= Icon::show('information-circle', 'md', 'text-blue-600 flex-shrink-0') ?>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium">Информация</p>
                                <p class="mt-1">Директор увидит текущие и новые значения платежа, а также причину изменения. После одобрения платёж будет обновлён.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary flex-1">
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
