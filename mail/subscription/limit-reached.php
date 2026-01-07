<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var string $limitType
 * @var string $limitLabel
 * @var int $current
 * @var int $limit
 */

use yii\helpers\Html;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #c82333; }
        .alert-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; }
        .progress-container { background: #e9ecef; border-radius: 10px; height: 20px; margin: 20px 0; overflow: hidden; }
        .progress-bar { height: 100%; background: #dc3545; }
        .stats { display: flex; justify-content: space-between; margin: 10px 0; font-size: 14px; }
        .options { margin: 20px 0; }
        .option { display: flex; align-items: flex-start; margin: 15px 0; }
        .option-icon { width: 30px; height: 30px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0; }
        .option-content { flex: 1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Лимит достигнут</h1>
            <p style="margin:10px 0 0;"><?= ucfirst($limitLabel) ?></p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <div class="alert-box">
                <strong>Внимание!</strong> Организация <strong><?= Html::encode($organization->name) ?></strong> достигла лимита <?= $limitLabel ?>. Создание новых записей заблокировано.
            </div>

            <p>Текущее использование:</p>

            <div class="progress-container">
                <div class="progress-bar" style="width: 100%;"></div>
            </div>

            <div class="stats">
                <span>Использовано: <strong><?= $current ?></strong></span>
                <span>Лимит: <strong><?= $limit ?></strong></span>
                <span style="color: #dc3545;">Осталось: <strong>0</strong></span>
            </div>

            <h3>Что делать:</h3>

            <div class="options">
                <div class="option">
                    <div class="option-icon">1</div>
                    <div class="option-content">
                        <strong>Увеличить лимит</strong><br>
                        Докупите пакет дополнительных <?= $limitLabel ?> или перейдите на более высокий тариф.
                    </div>
                </div>

                <div class="option">
                    <div class="option-icon">2</div>
                    <div class="option-content">
                        <strong>Удалить неактивные записи</strong><br>
                        Освободите место, удалив старые или неактивные записи из системы.
                    </div>
                </div>
            </div>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription/upgrade']) ?>" class="btn">
                    Увеличить лимит
                </a>
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
