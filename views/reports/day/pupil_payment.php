<?php
/* @var \yii\web\View $this*/
/* @var \app\models\Pupil[] $pupils*/
/* @var array $pupilPupilEducations*/
/* @var array $pupilPayments*/

$js = <<<JS
    $('#main-container-block').removeClass('container').addClass('container-fluid');
    $('.payment-search').addClass('container');
    $('#report-title').addClass('container pt-5 mt-5');
JS;
$this->registerJs($js);
$resultTariffSum = 0;
$resultTariffSaleSum = 0;
$resultTariffPaySum = 0;
$resultPaymentSum = 0;
?>
<table id="pupil-payment-table" class="table table-bordered table-report">
    <tbody>
    <tr>
        <th>№</th>
        <th>Ученик</th>
        <th>Класс</th>
        <th>Школа</th>
        <th>Тариф</th>
        <th>Скидка</th>
        <th>К оплате</th>
        <th>Оплачено</th>
        <th>№ квит</th>
        <th>Дата</th>
        <th>Период</th>
        <th>Долг</th>
        <th>Долг (на конец месяца)</th>

    </tr>
    <?foreach ($pupils as $i => $pupil):?>
    <tr>
        <td><?=$i+1;?></td>
        <td>
            <nobr><a href="<?=\app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $pupil->id]);?>" class="" target="_blank"><?=$pupil->fio;?></a></nobr>
        </td>
        <td>
            <nobr><?=$pupil->class_id;?> класс</nobr>
        </td>
        <td>
            <?=$pupil->school_name;?>
        </td>
        <?
        $tariffSum = 0; $tariffArr = [];$tariffText = ''; //Тариф
        $tariffSaleSum = 0;  $tariffSaleArr = []; $tariffSaleText = ''; //Скидка тарифа
        $tariffPaySum = 0;  $tariffPayArr = []; $tariffPayText = ''; //К оплате

        if (key_exists($pupil->id, $pupilPupilEducations)){
            foreach ($pupilPupilEducations[$pupil->id] as $pupilEducation){
                //Тариф
                $tariffSum += $pupilEducation['tariff_price'];
                $tariffArr[] = $pupilEducation['tariff_price'];
                //Скидка тарифа
                $tariffSaleSum += $pupilEducation['tariff_price'] - $pupilEducation['total_price'];
                $tariffSaleArr[] = ($pupilEducation['tariff_price'] - $pupilEducation['total_price']).'('.$pupilEducation['sale'].'%)';
                //К оплате
                $tariffPaySum += $pupilEducation['total_price'];
                $tariffPayArr[] = $pupilEducation['total_price'];


            }
            if (sizeof($tariffArr) > 1){
                //Тариф
                $tariffText = implode('+', $tariffArr);
                $tariffText .= '='.$tariffSum;
                //Скидка тарифа
                $tariffSaleText = implode('+', $tariffSaleArr);
                $tariffSaleText .= '='.$tariffSaleSum.'('.(intval($tariffSaleSum/$tariffSum*100)).'%)';
                //К оплате
                $tariffPayText = implode('+', $tariffPayArr);
                $tariffPayText .= '='.$tariffPaySum;
            }else if(sizeof($tariffArr) == 1){
                $tariffText = $tariffSum;
                $tariffSaleText = $tariffSaleSum.'('.(intval($tariffSaleSum/$tariffSum)*100).'%)';
                $tariffPayText = $tariffPaySum;
            }
            $resultTariffSum += $tariffSum;
            $resultTariffSaleSum += $tariffSaleSum;
            $resultTariffPaySum += $tariffPaySum;
        }
        ?>
        <td>

            <?=$tariffText;?>
        </td>
        <td>
            <?=$tariffSaleText;?>
        </td>
        <td>
            <?=$tariffPayText;?>
        </td>
        <?
        $paymentSum = 0; $paymentArr = []; $paymentText = ''; //Оплачено
        $paymentNumberArr = []; $paymentNumberText = '';
        if (key_exists($pupil->id, $pupilPayments)){
            foreach ($pupilPayments[$pupil->id] as $payment){
                $paymentSum += $payment['amount'];
                $paymentArr[] = $payment['amount'];
                $paymentNumberArr[] = $payment['number'];
            }
            if (sizeof($paymentArr) > 1){
                //Оплачено
                $paymentText = implode('+', $paymentArr);
                $paymentText .= '='.$paymentSum;
            }else if(sizeof($paymentArr) == 1){
                $paymentText = $paymentSum;
            }
            $paymentNumberText = implode('; ', $paymentNumberArr);
        }
        $resultPaymentSum += $paymentSum;
        ?>
        <td>
            <?=$paymentText;?>
        </td>
        <td>
            <?=$paymentNumberText;?>
        </td>
        <td>
            05.01.2024
        </td>
        <td>
            04.01-03.02 (30)
        </td>
        <td>

        </td>
        <td>

        </td>

    </tr>
    <?endforeach;?>

    <tr>
        <th></th>
        <th>Итого</th>
        <th></th>
        <th></th>
        <th>Тариф<br><?=number_format($resultTariffSum, 0, '.', ' ');?> тг</th>
        <th>Скидка<br><?=number_format($resultTariffSaleSum, 0, '.', ' ');?> тг</th>
        <th>К оплате<br><?=number_format($resultTariffPaySum, 0, '.', ' ');?> тг</th>
        <th>Оплачено<br><?=number_format($resultPaymentSum, 0, '.', ' ');?>тг</th>
        <th></th>
        <th></th>
        <th></th>
        <th>Долг<br>4&nbsp;285&nbsp;920тг</th>
        <th>Долг (на конец месяца)<br>2&nbsp;180&nbsp;044тг</th>

    </tr>
    </tbody>
</table>