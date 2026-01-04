<?php

/** @var yii\web\View $this */
/** @var string $orgName */
/** @var string $orgEmail */
/** @var string $adminEmail */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Регистрация завершена';
?>

<style>
.success-container {
    max-width: 480px;
    margin: 0 auto;
}
.success-icon {
    width: 64px;
    height: 64px;
    background: #dcfce7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}
.success-icon i {
    font-size: 28px;
    color: #16a34a;
}
.success-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}
.success-desc {
    color: #64748b;
    margin-bottom: 24px;
}
.info-box {
    background: #fff7eb;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}
.info-box-title {
    font-weight: 600;
    color: #c2410c;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-box p {
    color: #475569;
    margin: 0;
    font-size: 0.9rem;
}
.credentials {
    background: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}
.credentials-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
    font-size: 0.9rem;
}
.credentials-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.9rem;
}
.credentials-item:last-child {
    border-bottom: none;
}
.credentials-label {
    color: #64748b;
}
.credentials-value {
    color: #1e293b;
    font-weight: 500;
}
</style>

<div class="success-container">
    <div class="guest-card p-4 p-md-5 text-center">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <div class="success-title">Регистрация завершена!</div>
        <p class="success-desc">
            Организация <strong><?= Html::encode($orgName) ?></strong> успешно зарегистрирована
        </p>

        <div class="info-box text-left">
            <div class="info-box-title">
                <i class="fas fa-envelope"></i> Подтвердите email
            </div>
            <p>
                Мы отправили письмо на <strong><?= Html::encode($orgEmail) ?></strong>.
                Перейдите по ссылке в письме для активации аккаунта.
            </p>
        </div>

        <div class="credentials text-left">
            <div class="credentials-title">Данные для входа</div>
            <div class="credentials-item">
                <span class="credentials-label">Email</span>
                <span class="credentials-value"><?= Html::encode($adminEmail) ?></span>
            </div>
            <div class="credentials-item">
                <span class="credentials-label">Пароль</span>
                <span class="credentials-value">указанный при регистрации</span>
            </div>
        </div>

        <a href="<?= Url::to(['/site/login']) ?>" class="btn btn-primary btn-block">
            Войти в систему
        </a>

        <p class="text-muted small mt-4 mb-0">
            Не получили письмо? Проверьте папку "Спам"
        </p>
    </div>
</div>
