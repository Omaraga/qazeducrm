<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var float $amount
 */

use yii\helpers\Html;

$formattedAmount = number_format($amount, 0, '', ' ');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #11998e; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #0e857a; }
        .success-box { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
        .amount { text-align: center; margin: 30px 0; }
        .amount-value { font-size: 36px; font-weight: bold; color: #11998e; }
        .amount-label { font-size: 14px; color: #666; }
        .receipt { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .receipt table { width: 100%; border-collapse: collapse; }
        .receipt td { padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
        .receipt td:last-child { text-align: right; }
        .receipt tr:last-child td { border-bottom: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 48px; margin-bottom: 10px;">✓</div>
            <h1 style="margin:0;">Платёж получен</h1>
            <p style="margin:10px 0 0;">Спасибо за оплату!</p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="success-box">
                Мы получили ваш платёж. Подписка организации <strong><?= Html::encode($organization->name) ?></strong> активирована.
            </div>

            <div class="amount">
                <div class="amount-value"><?= $formattedAmount ?> KZT</div>
                <div class="amount-label">Сумма платежа</div>
            </div>

            <div class="receipt">
                <table>
                    <tr>
                        <td>Организация</td>
                        <td><?= Html::encode($organization->name) ?></td>
                    </tr>
                    <tr>
                        <td>Дата платежа</td>
                        <td><?= date('d.m.Y H:i') ?></td>
                    </tr>
                    <tr>
                        <td>Сумма</td>
                        <td><?= $formattedAmount ?> KZT</td>
                    </tr>
                </table>
            </div>

            <p>Теперь вы можете продолжить работу с системой без ограничений.</p>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/']) ?>" class="btn">
                    Перейти в систему
                </a>
            </p>

            <p style="font-size: 14px; color: #666;">
                Квитанция об оплате доступна в разделе «Подписка» → «История платежей».
            </p>

            <p>Спасибо, что выбираете QazEduCRM!</p>

            <p>С уважением,<br>Команда QazEduCRM</p>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> QazEduCRM. Все права защищены.</p>
            <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
        </div>
    </div>
</body>
</html>
