<?php

use app\models\Payment;
use yii\helpers\Html;

/** @var app\models\Payment $model */

$organization = $model->organization;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квитанция №<?= $model->id ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .org-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .org-info {
            font-size: 12px;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .receipt-number {
            text-align: center;
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        .details {
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #ccc;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #666;
        }
        .detail-value {
            font-weight: bold;
            text-align: right;
        }
        .amount-section {
            background: #f5f5f5;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #333;
        }
        .amount-label {
            font-size: 14px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
        .amount-value {
            font-size: 32px;
            font-weight: bold;
            color: #1a7f37;
        }
        .amount-words {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        .signature-block {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .qr-placeholder {
            width: 80px;
            height: 80px;
            border: 1px solid #ccc;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #999;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 15px 40px;
            font-size: 16px;
            background: #1a7f37;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .print-button:hover {
            background: #156b2d;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Распечатать квитанцию</button>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="org-name"><?= Html::encode($organization->name ?? 'Учебный центр') ?></div>
            <div class="org-info">
                <?php if ($organization): ?>
                    <?php if ($organization->bin): ?>БИН: <?= Html::encode($organization->bin) ?><?php endif; ?>
                    <?php if ($organization->address): ?><br><?= Html::encode($organization->address) ?><?php endif; ?>
                    <?php if ($organization->phone): ?><br>Тел: <?= Html::encode($organization->phone) ?><?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Title -->
        <div class="receipt-title">Квитанция об оплате</div>
        <div class="receipt-number">
            № <?= str_pad($model->id, 6, '0', STR_PAD_LEFT) ?>
            от <?= Yii::$app->formatter->asDate($model->date, 'dd.MM.yyyy') ?>
        </div>

        <!-- Details -->
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Плательщик:</span>
                <span class="detail-value"><?= Html::encode($model->pupil->fio ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Назначение платежа:</span>
                <span class="detail-value"><?= Html::encode($model->getPurposeLabel()) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Способ оплаты:</span>
                <span class="detail-value"><?= Html::encode($model->method->name ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Дата и время:</span>
                <span class="detail-value"><?= Yii::$app->formatter->asDatetime($model->date, 'dd.MM.yyyy HH:mm') ?></span>
            </div>
            <?php if ($model->number): ?>
            <div class="detail-row">
                <span class="detail-label">Номер документа:</span>
                <span class="detail-value"><?= Html::encode($model->number) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Amount -->
        <div class="amount-section">
            <div class="amount-label">Сумма к оплате</div>
            <div class="amount-value"><?= number_format($model->amount, 0, '.', ' ') ?> ₸</div>
            <div class="amount-words">
                <?php
                // Простое преобразование числа в слова (упрощённое)
                $amount = (int)$model->amount;
                echo "(" . $amount . " тенге)";
                ?>
            </div>
        </div>

        <?php if ($model->comment): ?>
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Примечание:</span>
                <span class="detail-value"><?= Html::encode($model->comment) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">Принял (подпись)</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">Плательщик (подпись)</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Квитанция действительна при наличии подписи и печати</p>
            <p>Документ сформирован: <?= Yii::$app->formatter->asDatetime(date('Y-m-d H:i:s'), 'dd.MM.yyyy HH:mm') ?></p>
            <p>QazEduCRM</p>
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">Распечатать квитанцию</button>
</body>
</html>
