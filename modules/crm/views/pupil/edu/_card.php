<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\PupilEducation $model */

$isExpired = strtotime($model->date_end) < time();
?>

<div class="card" x-data="{ open: false }">
    <div class="card-body">
        <!-- Header - clickable to toggle -->
        <button type="button" @click="open = !open" class="w-full text-left flex items-center justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <span class="badge <?= $isExpired ? 'badge-danger' : 'badge-success' ?>">
                        <?= date('d.m.Y', strtotime($model->date_start)) ?> - <?= date('d.m.Y', strtotime($model->date_end)) ?>
                    </span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <?php foreach ($model->groups as $eduGroup): ?>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?= Html::encode($eduGroup->group->nameFull ?? '—') ?>
                        </span>
                        <br>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <!-- Collapsible content -->
        <div x-show="open" x-collapse class="mt-4 pt-4 border-t border-gray-200">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Тариф') ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->tariff->nameFull ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Скидка') ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php if ($model->sale > 0): ?>
                            <span class="badge badge-warning"><?= $model->sale ?>%</span>
                        <?php else: ?>
                            <span class="text-gray-400">нет</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Период') ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?= date('d.m.Y', strtotime($model->date_start)) ?> - <?= date('d.m.Y', strtotime($model->date_end)) ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Итого к оплате') ?></dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        <?= number_format($model->total_price, 0, '.', ' ') ?> ₸
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Выбранные группы') ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php foreach ($model->groups as $eduGroup): ?>
                            <span class="badge badge-secondary mr-1 mb-1"><?= Html::encode($eduGroup->group->nameFull ?? '—') ?></span>
                        <?php endforeach; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500" title="Кол-во дней когда ученик имел уважительную причину пропуска">
                        Кол-во переносов
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">0</dd>
                </div>
                <?php if ($model->comment): ?>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500"><?= Yii::t('main', 'Примечание') ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?= Html::encode($model->comment) ?></dd>
                </div>
                <?php endif; ?>
            </dl>

            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center gap-2">
                <a href="<?= OrganizationUrl::to(['pupil/update-edu', 'pupil_id' => $model->pupil_id, 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Редактировать
                </a>
                <a href="<?= OrganizationUrl::to(['pupil/copy-edu', 'pupil_id' => $model->pupil_id, 'id' => $model->id]) ?>" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Дублировать
                </a>
                <?= Html::a('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Удалить',
                    OrganizationUrl::to(['pupil/delete-edu', 'id' => $model->id]), [
                    'class' => 'btn btn-sm btn-danger',
                    'data' => [
                        'confirm' => 'Вы действительно хотите удалить обучение?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
