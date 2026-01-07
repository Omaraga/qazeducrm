<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription $subscription
 * @var int $daysRemaining
 * @var string $expiresAt
 */

use yii\helpers\Html;

$planName = $subscription->plan->name ?? 'Стандарт';
$expiresDate = Yii::$app->formatter->asDate($expiresAt, 'long');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #5a6fd6; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; width: 40%; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Подписка истекает</h1>
            <p style="margin:10px 0 0;">через <?= $daysRemaining ?> <?= $daysRemaining == 1 ? 'день' : ($daysRemaining < 5 ? 'дня' : 'дней') ?></p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <p>Напоминаем, что подписка организации <strong><?= Html::encode($organization->name) ?></strong> на систему QazEduCRM истекает <strong><?= $expiresDate ?></strong>.</p>

            <div class="warning-box">
                <strong>Что произойдёт после истечения:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>3 дня — ограниченный доступ (нельзя создавать записи)</li>
                    <li>7 дней — режим только для чтения</li>
                    <li>После — полная блокировка доступа</li>
                </ul>
            </div>

            <table class="info-table">
                <tr>
                    <td>Организация</td>
                    <td><?= Html::encode($organization->name) ?></td>
                </tr>
                <tr>
                    <td>Текущий тариф</td>
                    <td><?= Html::encode($planName) ?></td>
                </tr>
                <tr>
                    <td>Дата истечения</td>
                    <td><?= $expiresDate ?></td>
                </tr>
            </table>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/renew']) ?>" class="btn">
                    Продлить подписку
                </a>
            </p>

            <p>Если у вас есть вопросы, свяжитесь с нашей службой поддержки.</p>

            <p>С уважением,<br>Команда QazEduCRM</p>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> QazEduCRM. Все права защищены.</p>
            <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
        </div>
    </div>
</body>
</html>
