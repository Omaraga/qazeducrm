<?php
/* @var \app\models\Payment[] $payments*/
?>
<table class="table table-bordered">
    <tbody>
    <tr>
        <th>Дата</th>
        <th>Сумма</th>
        <th>Ученик</th>
        <th>№ квитанции</th>
        <th>Примечание</th>
    </tr>
    <?
    $sum = 0;
    ?>
    <?foreach ($payments as $payment):?>
    <tr>
        <td>
            <?=date('d.m.Y H:i', strtotime($payment->date));?>
        </td>
        <td>
            <?=number_format($payment->amount, 0, '.', ' ');?> тг
        </td>
        <td>
            <a href="<?=\app\helpers\OrganizationUrl::to(['pupil/payment', 'id' => $payment->pupil_id]);?>" class="" target="_blank"><?=$payment->pupil->fio;?> </a>
        </td>
        <td>
            <?=$payment->number;?>
        </td>

        <td>
            <?=$payment->comment;?>
        </td>
    </tr>
    <?
    $sum += $payment->amount;
    ?>
    <?endforeach;?>

    <tr>
        <th>Итого</th>
        <th colspan="4"><?=number_format($sum, 0, '.', ' ');?> тг</th>
    </tr>
    </tbody>
</table>
