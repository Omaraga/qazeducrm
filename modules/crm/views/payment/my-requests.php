<?php

use app\helpers\OrganizationUrl;
use app\models\PaymentChangeRequest;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Мои запросы на изменение';
$this->params['breadcrumbs'][] = ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['payment/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">История ваших запросов на изменение платежей</p>
        </div>
        <a href="<?= OrganizationUrl::to(['payment/index']) ?>" class="btn btn-secondary">
            <?= Icon::show('arrow-left', 'sm') ?>
            К бухгалтерии
        </a>
    </div>

    <!-- Requests List -->
    <?php if (count($dataProvider->getModels()) > 0): ?>
    <div class="card">
        <div class="card-body p-0">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Платёж</th>
                        <th class="px-6 py-3 text-left">Тип запроса</th>
                        <th class="px-6 py-3 text-left">Статус</th>
                        <th class="px-6 py-3 text-left">Дата запроса</th>
                        <th class="px-6 py-3 text-left">Обработан</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($dataProvider->getModels() as $request): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <?php if ($request->payment): ?>
                                <a href="<?= OrganizationUrl::to(['payment/view', 'id' => $request->payment_id]) ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                    #<?= $request->payment_id ?>
                                </a>
                                <p class="text-sm text-gray-500">
                                    <?= Html::encode($request->payment->pupil->fio ?? '—') ?> •
                                    <?= number_format($request->payment->amount, 0, '.', ' ') ?> ₸
                                </p>
                            <?php else: ?>
                                <span class="text-gray-400">Платёж удалён</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($request->request_type === PaymentChangeRequest::TYPE_DELETE): ?>
                                <span class="badge badge-danger">Удаление</span>
                            <?php else: ?>
                                <span class="badge badge-primary">Изменение</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?= $request->getStatusBadgeClass() ?>">
                                <?= $request->getStatusLabel() ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?= Yii::$app->formatter->asDatetime($request->created_at, 'dd.MM.yyyy HH:mm') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php if ($request->processed_at): ?>
                                <?= Yii::$app->formatter->asDatetime($request->processed_at, 'dd.MM.yyyy HH:mm') ?>
                                <?php if ($request->admin_comment): ?>
                                    <p class="text-xs text-gray-500 mt-1" title="<?= Html::encode($request->admin_comment) ?>">
                                        <?= Icon::show('chat-bubble-left', 'xs') ?>
                                        <?= Html::encode(mb_substr($request->admin_comment, 0, 50)) ?><?= mb_strlen($request->admin_comment) > 50 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body py-12 text-center text-gray-500">
            <?= Icon::show('document-text', 'xl', 'mx-auto text-gray-400') ?>
            <p class="mt-4">У вас пока нет запросов на изменение</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($dataProvider->pagination->pageCount > 1): ?>
    <div class="flex justify-center">
        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
    <?php endif; ?>
</div>
