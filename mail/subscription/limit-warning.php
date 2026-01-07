<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var string $limitType
 * @var string $limitLabel
 * @var int $current
 * @var int $limit
 * @var int $percent
 */

use yii\helpers\Html;

$remaining = $limit - $current;
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
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #5a6fd6; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .progress-container { background: #e9ecef; border-radius: 10px; height: 20px; margin: 20px 0; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #ffc107 0%, #f5576c 100%); transition: width 0.3s; }
        .stats { display: flex; justify-content: space-between; margin: 10px 0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Приближение к лимиту</h1>
            <p style="margin:10px 0 0;"><?= $percent ?>% использовано</p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="warning-box">
                Организация <strong><?= Html::encode($organization->name) ?></strong> использовала <strong><?= $percent ?>%</strong> лимита <?= $limitLabel ?>.
            </div>

            <p>Текущее использование:</p>

            <div class="progress-container">
                <div class="progress-bar" style="width: <?= min($percent, 100) ?>%;"></div>
            </div>

            <div class="stats">
                <span>Использовано: <strong><?= $current ?></strong></span>
                <span>Лимит: <strong><?= $limit ?></strong></span>
                <span>Осталось: <strong><?= $remaining ?></strong></span>
            </div>

            <p>Рекомендуем увеличить лимит или перейти на более высокий тариф, чтобы избежать ограничений при достижении 100%.</p>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/upgrade']) ?>" class="btn">
                    Увеличить лимит
                </a>
            </p>

            <p style="font-size: 14px; color: #666;">
                Также вы можете удалить неактивные записи, чтобы освободить место.
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
