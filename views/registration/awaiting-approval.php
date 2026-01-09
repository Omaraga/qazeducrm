<?php

/** @var yii\web\View $this */
/** @var app\models\Organizations $organization */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Заявка на рассмотрении';
?>

<style>
.awaiting-container {
    max-width: 480px;
    margin: 0 auto;
}
.awaiting-icon {
    width: 64px;
    height: 64px;
    background: #fef3c7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}
.awaiting-icon i {
    font-size: 28px;
    color: #d97706;
}
.awaiting-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}
.awaiting-desc {
    color: #64748b;
    margin-bottom: 24px;
}
.info-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}
.info-box-title {
    font-weight: 600;
    color: #1d4ed8;
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
.status-box {
    background: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}
.status-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.9rem;
}
.status-item:last-child {
    border-bottom: none;
}
.status-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 12px;
}
.status-icon.done {
    background: #dcfce7;
    color: #16a34a;
}
.status-icon.pending {
    background: #fef3c7;
    color: #d97706;
}
.status-label {
    flex: 1;
    color: #475569;
}
.status-value {
    color: #1e293b;
    font-weight: 500;
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

<div class="awaiting-container">
    <div class="guest-card p-4 p-md-5 text-center">
        <div class="awaiting-icon">
            <i class="fas fa-hourglass-half"></i>
        </div>

        <div class="awaiting-title">Заявка на рассмотрении</div>
        <p class="awaiting-desc">
            Email подтверждён. Ваша организация <strong><?= Html::encode($organization->name) ?></strong>
            ожидает проверки администратором.
        </p>

        <div class="info-box text-left">
            <div class="info-box-title">
                <i class="fas fa-clock"></i> Что дальше?
            </div>
            <p>
                Наша команда проверит вашу заявку в ближайшее время.
                После одобрения вы получите уведомление на email
                <strong><?= Html::encode($organization->email) ?></strong>
                и сможете войти в систему.
            </p>
        </div>

        <div class="status-box text-left">
            <div class="status-item">
                <span class="status-icon done"><i class="fas fa-check"></i></span>
                <span class="status-label">Регистрация</span>
                <span class="status-value text-success">Завершена</span>
            </div>
            <div class="status-item">
                <span class="status-icon done"><i class="fas fa-check"></i></span>
                <span class="status-label">Подтверждение email</span>
                <span class="status-value text-success">Подтверждён</span>
            </div>
            <div class="status-item">
                <span class="status-icon pending"><i class="fas fa-hourglass-half"></i></span>
                <span class="status-label">Проверка администратором</span>
                <span class="status-value text-warning">Ожидает</span>
            </div>
        </div>

        <div class="credentials text-left">
            <div class="credentials-title">Данные организации</div>
            <div class="credentials-item">
                <span class="credentials-label">Название</span>
                <span class="credentials-value"><?= Html::encode($organization->name) ?></span>
            </div>
            <div class="credentials-item">
                <span class="credentials-label">Email</span>
                <span class="credentials-value"><?= Html::encode($organization->email) ?></span>
            </div>
            <?php if ($organization->phone): ?>
            <div class="credentials-item">
                <span class="credentials-label">Телефон</span>
                <span class="credentials-value"><?= Html::encode($organization->phone) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <a href="<?= Url::to(['/']) ?>" class="btn btn-outline-primary btn-block">
            <i class="fas fa-home mr-2"></i> На главную
        </a>

        <p class="text-muted small mt-4 mb-0">
            Обычно проверка занимает от нескольких минут до 24 часов.
            <br>
            Если у вас есть вопросы, свяжитесь с нами: <a href="mailto:support@qazcrm.kz">support@qazcrm.kz</a>
        </p>
    </div>
</div>
