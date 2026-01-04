<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var \app\models\Payment[] $payments */

$sum = 0;
?>

<div class="card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ученик</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№ квитанции</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Примечание</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($payments as $payment): ?>
                <?php $sum += $payment->amount; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= date('d.m.Y H:i', strtotime($payment->date)) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="text-sm font-semibold text-success-600">+<?= number_format($payment->amount, 0, '.', ' ') ?> ₸</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $payment->pupil_id]) ?>" target="_blank" class="text-sm text-primary-600 hover:text-primary-800">
                            <?= Html::encode($payment->pupil->fio) ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= Html::encode($payment->number ?? '—') ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                        <?= Html::encode($payment->comment ?? '') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900">Итого</td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-lg font-bold text-success-600"><?= number_format($sum, 0, '.', ' ') ?> ₸</span>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
