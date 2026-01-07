<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $organization */
/** @var app\models\OrganizationSubscriptionRequest[] $requests */

use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$this->title = 'Мои заявки';
$this->params['breadcrumbs'][] = ['label' => 'Подписка', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="text-gray-500 hover:text-gray-700 inline-flex items-center gap-1">
            <?= Icon::show('arrow-left', 'sm') ?>
            Назад к подписке
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6"><?= Html::encode($this->title) ?></h1>

    <?php if (empty($requests)): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <?= Icon::show('document-text', 'w-8 h-8 text-gray-400') ?>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Нет заявок</h3>
            <p class="text-gray-500 mb-4">Вы ещё не отправляли заявок на изменение подписки.</p>
            <a href="<?= \yii\helpers\Url::to(['plans']) ?>" class="btn btn-primary">
                Посмотреть тарифы
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($requests as $request): ?>
                <?php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-blue-100 text-blue-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'completed' => 'bg-green-100 text-green-800',
                ];
                $statusColor = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';

                $typeIcons = [
                    'renewal' => 'arrow-path',
                    'upgrade' => 'arrow-trending-up',
                    'downgrade' => 'arrow-trending-down',
                    'trial_convert' => 'sparkles',
                    'addon' => 'puzzle-piece',
                ];
                $typeIcon = $typeIcons[$request->request_type] ?? 'document';
                ?>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <?= Icon::show($typeIcon, 'w-5 h-5 text-primary-600') ?>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-medium text-gray-900"><?= $request->getTypeLabel() ?></h3>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full <?= $statusColor ?>">
                                        <?= $request->getStatusLabel() ?>
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php if ($request->requestedPlan): ?>
                                        Тариф: <strong><?= Html::encode($request->requestedPlan->name) ?></strong>
                                        •
                                    <?php endif; ?>
                                    <?= $request->billing_period === 'yearly' ? 'Годовой' : 'Месячный' ?>
                                    •
                                    <?= Yii::$app->formatter->asDatetime($request->created_at, 'php:d.m.Y H:i') ?>
                                </div>
                                <?php if ($request->comment): ?>
                                    <p class="text-sm text-gray-600 mt-2"><?= Html::encode($request->comment) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($request->admin_comment): ?>
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="text-xs text-gray-500 mb-1">Ответ администратора:</div>
                            <p class="text-sm text-gray-700"><?= Html::encode($request->admin_comment) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($request->isPending()): ?>
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2 text-sm text-yellow-600">
                                <?= Icon::show('clock', 'sm') ?>
                                Ожидает обработки администратором
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
