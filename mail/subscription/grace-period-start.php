<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription $subscription
 * @var int $graceDays
 */

use yii\helpers\Html;

$graceEndDate = date('d.m.Y', strtotime("+{$graceDays} days"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #333; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #ff9800; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #e68900; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .countdown { text-align: center; margin: 30px 0; }
        .countdown-number { font-size: 48px; font-weight: bold; color: #ff9800; }
        .countdown-label { font-size: 14px; color: #666; }
        .restrictions { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .restrictions h4 { margin-top: 0; color: #666; }
        .restrictions ul { margin: 0; padding-left: 20px; }
        .restrictions li { padding: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Grace период начался</h1>
            <p style="margin:10px 0 0;">У вас есть время для продления</p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="warning-box">
                Подписка организации <strong><?= Html::encode($organization->name) ?></strong> истекла.
                Начался grace период — у вас есть <strong><?= $graceDays ?> дней</strong> для продления без полной блокировки.
            </div>

            <div class="countdown">
                <div class="countdown-number"><?= $graceDays ?></div>
                <div class="countdown-label">дней до ограничения режима</div>
                <div style="font-size: 13px; color: #999; margin-top: 5px;">до <?= $graceEndDate ?></div>
            </div>

            <div class="restrictions">
                <h4>Текущие ограничения:</h4>
                <ul>
                    <li>❌ Создание новых учеников, групп, учителей</li>
                    <li>❌ Создание новых лидов</li>
                    <li>✅ Просмотр всех данных</li>
                    <li>✅ Редактирование существующих записей</li>
                    <li>✅ Отметка посещаемости</li>
                    <li>✅ Экспорт данных</li>
                </ul>
            </div>

            <p>Через <?= $graceDays ?> дней режим сменится на «только чтение», а затем доступ будет полностью заблокирован.</p>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/renew']) ?>" class="btn">
                    Продлить подписку
                </a>
            </p>

            <p style="font-size: 14px; color: #666; text-align: center;">
                Все ваши данные сохранены и будут доступны после продления.
            </p>

            <p>С уважением,<br>Команда QazEduCRM</p>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> QazEduCRM. Все права защищены.</p>
            <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
        </div>
    </div>
</body>
</html>
