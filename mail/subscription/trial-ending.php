<?php
/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var app\models\OrganizationSubscription $subscription
 * @var int $daysRemaining
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
        .header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 24px; background: #11998e; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .btn:hover { background: #0e857a; }
        .plans { display: flex; gap: 15px; margin: 20px 0; }
        .plan { flex: 1; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; text-align: center; }
        .plan.popular { border-color: #11998e; position: relative; }
        .plan.popular::before { content: 'Популярный'; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #11998e; color: white; padding: 2px 10px; border-radius: 4px; font-size: 11px; }
        .plan-name { font-weight: bold; font-size: 18px; margin-bottom: 5px; }
        .plan-price { color: #11998e; font-size: 24px; font-weight: bold; }
        .plan-price span { font-size: 14px; color: #666; font-weight: normal; }
        .features { list-style: none; padding: 0; margin: 15px 0; text-align: left; font-size: 13px; }
        .features li { padding: 5px 0; padding-left: 20px; position: relative; }
        .features li::before { content: '✓'; position: absolute; left: 0; color: #11998e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Пробный период заканчивается</h1>
            <p style="margin:10px 0 0;">Осталось <?= $daysRemaining ?> <?= $daysRemaining == 1 ? 'день' : ($daysRemaining < 5 ? 'дня' : 'дней') ?></p>
        </div>

        <div class="content">
            <p>Здравствуйте!</p>

            <p>Пробный период для организации <strong><?= Html::encode($organization->name) ?></strong> заканчивается через <?= $daysRemaining ?> <?= $daysRemaining == 1 ? 'день' : ($daysRemaining < 5 ? 'дня' : 'дней') ?>.</p>

            <p>Надеемся, что вам понравилась система QazEduCRM! Чтобы продолжить использование всех функций, выберите подходящий тарифный план:</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="width: 33%; padding: 10px; vertical-align: top;">
                        <div class="plan">
                            <div class="plan-name">Стандарт</div>
                            <div class="plan-price">9 990 <span>KZT/мес</span></div>
                            <ul class="features">
                                <li>До 50 учеников</li>
                                <li>5 учителей</li>
                                <li>CRM Лиды</li>
                                <li>SMS уведомления</li>
                            </ul>
                        </div>
                    </td>
                    <td style="width: 33%; padding: 10px; vertical-align: top;">
                        <div class="plan popular">
                            <div class="plan-name">Бизнес</div>
                            <div class="plan-price">29 990 <span>KZT/мес</span></div>
                            <ul class="features">
                                <li>До 150 учеников</li>
                                <li>15 учителей</li>
                                <li>Филиалы</li>
                                <li>Расширенные отчёты</li>
                            </ul>
                        </div>
                    </td>
                    <td style="width: 33%; padding: 10px; vertical-align: top;">
                        <div class="plan">
                            <div class="plan-name">Премиум</div>
                            <div class="plan-price">79 990 <span>KZT/мес</span></div>
                            <ul class="features">
                                <li>Безлимит учеников</li>
                                <li>Безлимит учителей</li>
                                <li>Приоритетная поддержка</li>
                                <li>Интеграции API</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </table>

            <p style="text-align: center;">
                <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/subscription']) ?>" class="btn">
                    Выбрать тариф
                </a>
            </p>

            <p style="font-size: 14px; color: #666; text-align: center;">
                При годовой оплате — 2 месяца в подарок!
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
