<?php

/**
 * Просмотр пробного занятия
 *
 * @var yii\web\View $this
 * @var app\models\TrialLesson $model
 */

use app\helpers\OrganizationUrl;
use app\models\TrialLesson;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Пробное занятие') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Пробные занятия'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($model->getLidName()) ?></h1>
            <p class="text-gray-500 mt-1">
                <?= Yii::$app->formatter->asDate($model->date, 'long') ?> в <?= substr($model->time, 0, 5) ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?= OrganizationUrl::to(['trial/index']) ?>" class="btn btn-outline">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <?= Yii::t('app', 'Назад') ?>
            </a>
            <?php if ($model->canEdit()): ?>
                <a href="<?= OrganizationUrl::to(['trial/update', 'id' => $model->id]) ?>" class="btn btn-outline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <?= Yii::t('app', 'Редактировать') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Основная информация -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Детали -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Информация о пробном') ?></h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $model->getStatusClass() ?>">
                        <?= $model->getStatusLabel() ?>
                    </span>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Дата') ?></div>
                            <div class="text-sm font-medium text-gray-900"><?= Yii::$app->formatter->asDate($model->date, 'long') ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Время') ?></div>
                            <div class="text-sm font-medium text-gray-900"><?= substr($model->time, 0, 5) ?></div>
                        </div>
                    </div>

                    <?php if ($model->group): ?>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Группа') ?></div>
                            <a href="<?= OrganizationUrl::to(['/crm/group/view', 'id' => $model->group_id]) ?>"
                               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                <?= Html::encode($model->group->name) ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->feedback): ?>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Отзыв') ?></div>
                            <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3"><?= Html::encode($model->feedback) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->rating): ?>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Оценка') ?></div>
                            <div class="flex gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-5 h-5 <?= $i <= $model->rating ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->converted_pupil_id): ?>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= Yii::t('app', 'Конвертирован в ученика') ?></div>
                            <a href="<?= OrganizationUrl::to(['/crm/pupil/view', 'id' => $model->converted_pupil_id]) ?>"
                               class="text-sm font-medium text-purple-600 hover:text-purple-700">
                                <?= Html::encode($model->convertedPupil->fio ?? '—') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Действия -->
            <?php if (!$model->isFinished()): ?>
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Действия') ?></h2>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Отметить как проведённое -->
                    <?= Html::beginForm(OrganizationUrl::to(['trial/complete', 'id' => $model->id]), 'post', ['class' => 'space-y-3']) ?>
                        <div>
                            <label class="text-sm font-medium text-gray-700"><?= Yii::t('app', 'Оценка') ?></label>
                            <div class="flex gap-2 mt-1" id="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" data-rating="<?= $i ?>" class="rating-star text-gray-300 hover:text-yellow-400 transition-colors">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700"><?= Yii::t('app', 'Отзыв') ?></label>
                            <textarea name="feedback" rows="2" class="form-input mt-1" placeholder="<?= Yii::t('app', 'Впечатление от пробного занятия...') ?>"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?= Yii::t('app', 'Отметить как проведённое') ?>
                        </button>
                    <?= Html::endForm() ?>

                    <div class="border-t border-gray-100 pt-4 flex gap-2">
                        <?= Html::beginForm(OrganizationUrl::to(['trial/no-show', 'id' => $model->id]), 'post', ['class' => 'flex-1']) ?>
                            <button type="submit" class="btn btn-outline-danger w-full" onclick="return confirm('<?= Yii::t('app', 'Отметить как "не пришёл"?') ?>')">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <?= Yii::t('app', 'Не пришёл') ?>
                            </button>
                        <?= Html::endForm() ?>

                        <?= Html::beginForm(OrganizationUrl::to(['trial/cancel', 'id' => $model->id]), 'post', ['class' => 'flex-1']) ?>
                            <button type="submit" class="btn btn-outline-secondary w-full" onclick="return confirm('<?= Yii::t('app', 'Отменить пробное занятие?') ?>')">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <?= Yii::t('app', 'Отменить') ?>
                            </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Конвертация -->
            <?php if ($model->status === TrialLesson::STATUS_COMPLETED && !$model->converted_pupil_id): ?>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-medium text-purple-900"><?= Yii::t('app', 'Готов к конвертации') ?></h3>
                        <p class="text-sm text-purple-700 mt-1"><?= Yii::t('app', 'Пробное занятие прошло успешно. Создайте ученика из этого лида.') ?></p>
                        <a href="<?= OrganizationUrl::to(['trial/convert', 'id' => $model->id]) ?>" class="btn btn-primary mt-3">
                            <?= Yii::t('app', 'Конвертировать в ученика') ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Боковая панель: Лид -->
        <div class="space-y-6">
            <?php if ($model->lid): ?>
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Информация о лиде') ?></h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-lg">
                            <?= mb_substr($model->getLidName(), 0, 1) ?>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?= Html::encode($model->getLidName()) ?></div>
                            <?php if ($model->getLidPhone()): ?>
                                <a href="tel:<?= $model->getLidPhone() ?>" class="text-sm text-blue-600 hover:text-blue-700">
                                    <?= $model->getLidPhone() ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($model->lid->source): ?>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><?= Yii::t('app', 'Источник') ?></span>
                            <span class="font-medium text-gray-900"><?= Html::encode(ucfirst($model->lid->source)) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->lid->comment): ?>
                        <div>
                            <div class="text-xs text-gray-500 mb-1"><?= Yii::t('app', 'Комментарий') ?></div>
                            <div class="text-sm text-gray-700 bg-gray-50 rounded p-2"><?= Html::encode($model->lid->comment) ?></div>
                        </div>
                    <?php endif; ?>

                    <a href="<?= OrganizationUrl::to(['/crm/lids/view', 'id' => $model->lid_id]) ?>"
                       class="btn btn-outline-primary btn-sm w-full mt-3">
                        <?= Yii::t('app', 'Открыть карточку лида') ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Мета информация -->
            <div class="bg-white rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-900"><?= Yii::t('app', 'Информация') ?></h2>
                </div>
                <div class="p-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Создано') ?></span>
                        <span class="text-gray-900"><?= Yii::$app->formatter->asDatetime($model->created_at, 'short') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500"><?= Yii::t('app', 'Обновлено') ?></span>
                        <span class="text-gray-900"><?= Yii::$app->formatter->asDatetime($model->updated_at, 'short') ?></span>
                    </div>
                    <?php if ($model->reminder_sent): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500"><?= Yii::t('app', 'Напоминание') ?></span>
                            <span class="text-gray-900"><?= Yii::$app->formatter->asDatetime($model->reminder_sent, 'short') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    const input = document.getElementById('rating-input');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            input.value = rating;

            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.add('text-gray-300');
                    s.classList.remove('text-yellow-400');
                }
            });
        });
    });
});
</script>
