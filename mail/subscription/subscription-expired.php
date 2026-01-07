<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription $subscription
 */

use yii\helpers\Html;

$planName = $subscription->plan->name ?? 'Стандарт';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #f5576c; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #e04458; }
        .alert-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; }
        .timeline { margin: 20px 0; }
        .timeline-item { display: flex; margin: 15px 0; }
        .timeline-icon { width: 30px; height: 30px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0; }
        .timeline-icon.warning { background: #ffc107; }
        .timeline-icon.danger { background: #dc3545; }
        .timeline-content { flex: 1; }
        .timeline-content strong { display: block; margin-bottom: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Подписка истекла</h1>
            <p style="margin:10px 0 0;">Требуется действие</p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="alert-box">
                <strong>Важно!</strong> Подписка организации <strong><?= Html::encode($organization->name) ?></strong> истекла. Доступ к системе ограничен.
            </div>

            <p>Для сохранения всех данных и продолжения работы необходимо продлить подписку.</p>

            <h3>Что происходит сейчас:</h3>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon warning">1</div>
                    <div class="timeline-content">
                        <strong>Сейчас: Ограниченный режим (3 дня)</strong>
                        Вы можете просматривать и редактировать данные, но не можете создавать новые записи.
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon warning">2</div>
                    <div class="timeline-content">
                        <strong>Через 3 дня: Режим только для чтения</strong>
                        Вы сможете только просматривать данные и экспортировать их.
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon danger">3</div>
                    <div class="timeline-content">
                        <strong>Через 10 дней: Блокировка</strong>
                        Доступ к системе будет полностью заблокирован до оплаты.
                    </div>
                </div>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/renew']) ?>" class="btn">
                    Продлить подписку сейчас
                </a>
            </p>

            <p style="font-size: 14px; color: #666;">
                Ваши данные сохранены и будут доступны после продления подписки.
                Мы не удаляем данные клиентов.
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
