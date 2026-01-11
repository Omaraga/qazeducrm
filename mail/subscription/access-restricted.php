<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription $subscription
 * @var string $accessMode
 * @var string $modeLabel
 */

use yii\helpers\Html;

$isBlocked = $accessMode === 'blocked';
$isReadOnly = $accessMode === 'read_only';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: <?= $isBlocked ? 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)' : 'linear-gradient(135deg, #6c757d 0%, #495057 100%)' ?>; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 14px 28px; background: <?= $isBlocked ? '#dc3545' : '#667eea' ?>; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; font-size: 16px; }
        .btn:hover { opacity: 0.9; }
        .alert-box { background: <?= $isBlocked ? '#f8d7da' : '#e2e3e5' ?>; border-left: 4px solid <?= $isBlocked ? '#dc3545' : '#6c757d' ?>; padding: 15px; margin: 20px 0; }
        .status-icon { font-size: 48px; text-align: center; margin: 20px 0; }
        .restrictions { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .restrictions ul { margin: 0; padding-left: 20px; }
        .restrictions li { padding: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Доступ <?= $modeLabel ?></h1>
            <p style="margin:10px 0 0;"><?= Html::encode($organization->name) ?></p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="status-icon">
                <?= $isBlocked ? '🔒' : '👁️' ?>
            </div>

            <div class="alert-box">
                <?php if ($isBlocked): ?>
                    <strong>Доступ к системе заблокирован.</strong>
                    Для продолжения работы необходимо оплатить подписку.
                <?php else: ?>
                    <strong>Система переведена в режим «только чтение».</strong>
                    Вы можете просматривать и экспортировать данные, но не можете вносить изменения.
                <?php endif; ?>
            </div>

            <?php if ($isReadOnly): ?>
            <div class="restrictions">
                <h4 style="margin-top: 0;">Что доступно:</h4>
                <ul>
                    <li>✅ Просмотр всех данных</li>
                    <li>✅ Экспорт в Excel/PDF</li>
                    <li>✅ Просмотр отчётов</li>
                    <li>❌ Создание записей</li>
                    <li>❌ Редактирование</li>
                    <li>❌ Отметка посещаемости</li>
                </ul>
            </div>

            <p><strong>Важно:</strong> Через несколько дней доступ будет полностью заблокирован. Экспортируйте важные данные сейчас.</p>
            <?php endif; ?>

            <?php if ($isBlocked): ?>
            <p style="text-align: center; font-size: 18px; margin: 30px 0;">
                Для восстановления доступа необходимо оплатить подписку.
                <br>Все ваши данные сохранены.
            </p>
            <?php endif; ?>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/renew']) ?>" class="btn">
                    <?= $isBlocked ? 'Оплатить и восстановить доступ' : 'Продлить подписку' ?>
                </a>
            </p>

            <?php if ($isReadOnly): ?>
            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/export']) ?>" style="color: #667eea;">
                    Экспортировать данные →
                </a>
            </p>
            <?php endif; ?>

            <p>Если у вас есть вопросы или нужна помощь, свяжитесь с нашей службой поддержки.</p>

            <p>С уважением,<br>Команда Education CRM</p>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> Education CRM. Все права защищены.</p>
            <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
        </div>
    </div>
</body>
</html>
