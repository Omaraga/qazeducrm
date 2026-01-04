<?php

/** @var yii\web\View $this */
/** @var bool $success */
/** @var bool $alreadyVerified */
/** @var app\models\Organizations $organization */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $success ? 'Email подтверждён' : 'Ошибка';
?>

<style>
.verify-container {
    max-width: 480px;
    margin: 0 auto;
}
.verify-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}
.verify-icon.success {
    background: #dcfce7;
}
.verify-icon.success i {
    font-size: 28px;
    color: #16a34a;
}
.verify-icon.error {
    background: #fee2e2;
}
.verify-icon.error i {
    font-size: 28px;
    color: #dc2626;
}
.verify-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}
.verify-desc {
    color: #64748b;
    margin-bottom: 24px;
}
.trial-box {
    background: #fff7eb;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.trial-box i {
    color: #FE8D00;
    font-size: 20px;
}
.trial-box span {
    color: #9a3412;
    font-size: 0.9rem;
}
</style>

<div class="verify-container">
    <div class="guest-card p-4 p-md-5 text-center">
        <?php if ($success): ?>
            <div class="verify-icon success">
                <i class="fas fa-check"></i>
            </div>

            <?php if ($alreadyVerified): ?>
                <div class="verify-title">Email уже подтверждён</div>
                <p class="verify-desc">
                    Аккаунт организации <strong><?= Html::encode($organization->name) ?></strong> уже активен
                </p>
            <?php else: ?>
                <div class="verify-title">Email подтверждён!</div>
                <p class="verify-desc">
                    Организация <strong><?= Html::encode($organization->name) ?></strong> активирована
                </p>

                <div class="trial-box">
                    <i class="fas fa-gift"></i>
                    <span>Пробный период начался. Пользуйтесь всеми возможностями бесплатно!</span>
                </div>
            <?php endif; ?>

            <a href="<?= Url::to(['/login']) ?>" class="btn btn-primary btn-block">
                Войти в систему
            </a>

        <?php else: ?>
            <div class="verify-icon error">
                <i class="fas fa-times"></i>
            </div>

            <div class="verify-title">Ошибка подтверждения</div>
            <p class="verify-desc">
                Ссылка недействительна или устарела
            </p>

            <div class="d-flex gap-2 justify-content-center">
                <a href="<?= Url::to(['/register']) ?>" class="btn btn-primary">
                    Зарегистрироваться
                </a>
                <a href="<?= Url::to(['/login']) ?>" class="btn btn-outline-secondary ml-2">
                    Войти
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
