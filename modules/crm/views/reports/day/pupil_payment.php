<?php

use app\helpers\OrganizationUrl;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\Pupil[] $pupils */
/** @var array $pupilPupilEducations */
/** @var array $pupilPayments */

$resultTariffSum = 0;
$resultTariffSaleSum = 0;
$resultTariffPaySum = 0;
$resultPaymentSum = 0;
$resultDutySum = 0;
$resultDutyMonth = 0;
?>

<div class="card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ученик</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Класс</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Школа</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Тариф</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Скидка</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">К оплате</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Оплачено</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№ квит</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Период</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Долг</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Долг (мес.)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($pupils as $i => $pupil): ?>
                <?php
                // Calculate tariff data
                $tariffSum = 0; $tariffArr = []; $tariffText = '';
                $tariffSaleSum = 0; $tariffSaleArr = []; $tariffSaleText = '';
                $tariffPaySum = 0; $tariffPayArr = []; $tariffPayText = '';
                $tariffPeriodArr = []; $tariffPeriodText = '';

                if (array_key_exists($pupil->id, $pupilPupilEducations)) {
                    foreach ($pupilPupilEducations[$pupil->id] as $pupilEducation) {
                        $tariffSum += $pupilEducation['tariff_price'];
                        $tariffArr[] = $pupilEducation['tariff_price'];
                        $tariffSaleSum += $pupilEducation['tariff_price'] - $pupilEducation['total_price'];
                        $tariffSaleArr[] = ($pupilEducation['tariff_price'] - $pupilEducation['total_price']) . '(' . $pupilEducation['sale'] . '%)';
                        $tariffPaySum += $pupilEducation['total_price'];
                        $tariffPayArr[] = $pupilEducation['total_price'];
                        $tariffPeriodArr[] = date('d.m.Y', strtotime($pupilEducation['date_start'])) . '-' . date('d.m.Y', strtotime($pupilEducation['date_end']))
                            . '(' . intval((strtotime($pupilEducation['date_end']) - strtotime($pupilEducation['date_start'])) / 86400) . ')';
                    }
                    if (count($tariffArr) > 1) {
                        $tariffText = implode('+', $tariffArr) . '=' . $tariffSum;
                        $tariffSaleText = implode('+', $tariffSaleArr) . '=' . $tariffSaleSum . '(' . (intval($tariffSaleSum / $tariffSum * 100)) . '%)';
                        $tariffPayText = implode('+', $tariffPayArr) . '=' . $tariffPaySum;
                    } elseif (count($tariffArr) == 1) {
                        $tariffText = $tariffSum;
                        $tariffSaleText = $tariffSaleSum . '(' . ($tariffSum > 0 ? intval($tariffSaleSum / $tariffSum * 100) : 0) . '%)';
                        $tariffPayText = $tariffPaySum;
                    }
                    $resultTariffSum += $tariffSum;
                    $resultTariffSaleSum += $tariffSaleSum;
                    $resultTariffPaySum += $tariffPaySum;
                    $tariffPeriodText = implode('; ', $tariffPeriodArr);
                }

                // Calculate payment data
                $paymentSum = 0; $paymentArr = []; $paymentText = '';
                $paymentNumberArr = []; $paymentNumberText = '';
                $paymentDateArr = []; $paymentDateText = '';

                if (array_key_exists($pupil->id, $pupilPayments)) {
                    foreach ($pupilPayments[$pupil->id] as $payment) {
                        $paymentSum += $payment['amount'];
                        $paymentArr[] = $payment['amount'];
                        $paymentNumberArr[] = $payment['number'];
                        $paymentDateArr[] = date('d.m.Y', strtotime($payment['date']));
                    }
                    if (count($paymentArr) > 1) {
                        $paymentText = implode('+', $paymentArr) . '=' . $paymentSum;
                    } elseif (count($paymentArr) == 1) {
                        $paymentText = $paymentSum;
                    }
                    $paymentNumberText = implode('; ', $paymentNumberArr);
                    $paymentDateText = implode('; ', $paymentDateArr);
                }
                $resultPaymentSum += $paymentSum;

                // Calculate duty
                $duty = $tariffPaySum - $paymentSum;
                $resultDutySum += $duty;
                $dutyText = $duty > 1 ? $duty : '';

                // Calculate duty month
                $dutyMonth = $pupil->balance < 0 ? (-1) * $pupil->balance : 0;
                $dutyMonthText = $dutyMonth > 0 ? $dutyMonth : '';
                $resultDutyMonth += $dutyMonth;
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-3 text-gray-500"><?= $i + 1 ?></td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <a href="<?= OrganizationUrl::to(['pupil/payment', 'id' => $pupil->id]) ?>" target="_blank" class="text-primary-600 hover:text-primary-800">
                            <?= Html::encode($pupil->fio) ?>
                        </a>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap text-gray-500"><?= $pupil->class_id ?> класс</td>
                    <td class="px-3 py-3 text-gray-500"><?= Html::encode($pupil->school_name) ?></td>
                    <td class="px-3 py-3 text-right text-gray-900"><?= $tariffText ?></td>
                    <td class="px-3 py-3 text-right text-gray-500"><?= $tariffSaleText ?></td>
                    <td class="px-3 py-3 text-right font-medium text-gray-900"><?= $tariffPayText ?></td>
                    <td class="px-3 py-3 text-right font-medium text-success-600"><?= $paymentText ?></td>
                    <td class="px-3 py-3 text-gray-500"><?= Html::encode($paymentNumberText) ?></td>
                    <td class="px-3 py-3 text-gray-500"><?= $paymentDateText ?></td>
                    <td class="px-3 py-3 text-gray-500 text-xs"><?= $tariffPeriodText ?></td>
                    <td class="px-3 py-3 text-right <?= $dutyText ? 'font-medium text-danger-600' : 'text-gray-400' ?>"><?= $dutyText ?></td>
                    <td class="px-3 py-3 text-right <?= $dutyMonthText ? 'font-medium text-danger-600' : 'text-gray-400' ?>"><?= $dutyMonthText ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr class="font-bold">
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4 text-gray-900">Итого</td>
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4 text-right text-gray-900">
                        <div class="text-xs text-gray-500 font-normal">Тариф</div>
                        <?= number_format($resultTariffSum, 0, '.', ' ') ?> ₸
                    </td>
                    <td class="px-3 py-4 text-right text-gray-900">
                        <div class="text-xs text-gray-500 font-normal">Скидка</div>
                        <?= number_format($resultTariffSaleSum, 0, '.', ' ') ?> ₸
                    </td>
                    <td class="px-3 py-4 text-right text-gray-900">
                        <div class="text-xs text-gray-500 font-normal">К оплате</div>
                        <?= number_format($resultTariffPaySum, 0, '.', ' ') ?> ₸
                    </td>
                    <td class="px-3 py-4 text-right text-success-600">
                        <div class="text-xs text-gray-500 font-normal">Оплачено</div>
                        <?= number_format($resultPaymentSum, 0, '.', ' ') ?> ₸
                    </td>
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4"></td>
                    <td class="px-3 py-4 text-right text-danger-600">
                        <div class="text-xs text-gray-500 font-normal">Долг</div>
                        <?= number_format($resultDutySum, 0, '.', ' ') ?> ₸
                    </td>
                    <td class="px-3 py-4 text-right text-danger-600">
                        <div class="text-xs text-gray-500 font-normal">Долг (мес.)</div>
                        <?= number_format($resultDutyMonth, 0, '.', ' ') ?> ₸
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
