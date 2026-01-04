<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $organization */
/** @var app\models\User $admin */
/** @var string $verificationLink */

use yii\helpers\Html;
?>

<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 560px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
        <!-- Header -->
        <div style="background: #1a1a2e; padding: 32px; text-align: center;">
            <h1 style="color: #fff; margin: 0; font-size: 24px; font-weight: 600;">
                Qazaq Education <span style="color: #FE8D00;">CRM</span>
            </h1>
            <p style="color: rgba(255,255,255,0.7); margin: 8px 0 0; font-size: 14px;">Система управления учебным центром</p>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <p style="color: #1e293b; font-size: 16px; margin: 0 0 16px;">
                Здравствуйте, <strong><?= Html::encode($admin->first_name) ?></strong>!
            </p>

            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px;">
                Спасибо за регистрацию организации <strong><?= Html::encode($organization->name) ?></strong>.
                Для активации аккаунта подтвердите ваш email.
            </p>

            <!-- Button -->
            <div style="text-align: center; margin: 32px 0;">
                <a href="<?= Html::encode($verificationLink) ?>"
                   style="display: inline-block; background: #FE8D00; color: #fff; text-decoration: none; padding: 14px 32px; font-size: 15px; font-weight: 500; border-radius: 8px;">
                    Подтвердить email
                </a>
            </div>

            <!-- Credentials -->
            <div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 24px 0;">
                <p style="color: #1e293b; font-weight: 600; margin: 0 0 12px; font-size: 14px;">Данные для входа:</p>
                <p style="color: #475569; margin: 0; font-size: 14px;">
                    <strong>Email:</strong> <?= Html::encode($admin->email) ?><br>
                    <strong>Пароль:</strong> указанный при регистрации
                </p>
            </div>

            <!-- Link fallback -->
            <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 24px 0 0;">
                Если кнопка не работает, скопируйте ссылку:<br>
                <a href="<?= Html::encode($verificationLink) ?>" style="color: #FE8D00; word-break: break-all;">
                    <?= Html::encode($verificationLink) ?>
                </a>
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f8fafc; padding: 20px 32px; border-top: 1px solid #e2e8f0;">
            <p style="color: #94a3b8; font-size: 12px; margin: 0; text-align: center;">
                &copy; <?= date('Y') ?> Qazaq Education CRM. Все права защищены.
            </p>
        </div>
    </div>
</div>
