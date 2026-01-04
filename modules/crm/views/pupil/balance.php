<?php
/** @var \app\models\Pupil $model */

$isPositive = $model->balance > 0;
?>
<?php if ($model->id): ?>
<div class="mb-6">
    <div class="inline-flex items-center rounded-lg overflow-hidden shadow-sm">
        <div class="px-4 py-2 <?= $isPositive ? 'bg-success-100' : 'bg-danger-100' ?> font-medium text-sm <?= $isPositive ? 'text-success-800' : 'text-danger-800' ?>">
            <?= $isPositive ? Yii::t('main', 'На счету ученика') : Yii::t('main', 'Задолженность ученика') ?>:
        </div>
        <div class="px-4 py-2 <?= $isPositive ? 'bg-success-50' : 'bg-danger-50' ?> font-bold text-lg <?= $isPositive ? 'text-success-700' : 'text-danger-700' ?>">
            <?= number_format($model->balance ?: 0, 0, '.', ' ') ?> ₸
        </div>
    </div>
</div>
<?php endif; ?>
