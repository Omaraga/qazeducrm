<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan[] $plans */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'CRM для учебных центров';
?>

<style>
/* ========================================
   HERO SECTION
   ======================================== */
.hero {
    background: linear-gradient(135deg, var(--bg-white) 0%, var(--bg-light) 100%);
    padding: 7rem 0 5rem;
    position: relative;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 70%;
    height: 200%;
    background: radial-gradient(circle, var(--primary-light) 0%, transparent 70%);
    opacity: 0.5;
}
.hero-content {
    position: relative;
    z-index: 1;
}
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-light);
    color: var(--primary);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
}
.hero-badge i {
    font-size: 0.75rem;
}
.hero-title {
    font-size: 3.25rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.15;
    margin-bottom: 1.5rem;
}
.hero-title span {
    color: var(--primary);
}
.hero-subtitle {
    font-size: 1.25rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
    max-width: 520px;
    line-height: 1.6;
}
.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 3rem;
}
.hero-buttons .btn {
    padding: 0.875rem 1.75rem;
    font-size: 1rem;
}
.hero-stats {
    display: flex;
    gap: 3rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border);
}
.hero-stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}
.hero-stat-label {
    color: var(--text-muted);
    font-size: 0.875rem;
}

/* ========================================
   FEATURES SECTION
   ======================================== */
.features-section {
    padding: 6rem 0;
    background: var(--bg-white);
}
.feature-card {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
    height: 100%;
    transition: all var(--transition);
}
.feature-card:hover {
    box-shadow: var(--shadow-lg);
    border-color: transparent;
    transform: translateY(-4px);
}
.feature-icon {
    width: 56px;
    height: 56px;
    background: var(--primary-light);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.feature-icon i {
    font-size: 1.5rem;
    color: var(--primary);
}
.feature-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
}
.feature-desc {
    color: var(--text-muted);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

/* ========================================
   PRICING SECTION
   ======================================== */
.pricing-section {
    padding: 6rem 0;
    background: var(--bg-light);
}
.plan-card {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
    height: 100%;
    transition: all var(--transition);
    position: relative;
}
.plan-card:hover {
    box-shadow: var(--shadow-lg);
}
.plan-card.popular {
    border: 2px solid var(--primary);
    box-shadow: var(--shadow-xl);
}
.plan-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--primary);
    color: var(--text-white);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.35rem 1rem;
    border-radius: var(--radius-full);
}
.plan-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.plan-price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 0.25rem;
}
.plan-price span {
    font-size: 1rem;
    font-weight: 400;
    color: var(--text-muted);
}
.plan-trial {
    color: var(--success);
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}
.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem;
}
.plan-features li {
    padding: 0.5rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.plan-features li i {
    color: var(--success);
    font-size: 0.85rem;
}

/* ========================================
   CTA SECTION
   ======================================== */
.cta-section {
    padding: 5rem 0;
    background: var(--bg-white);
    text-align: center;
}
.cta-card {
    background: var(--dark);
    border-radius: var(--radius-xl);
    padding: 4rem;
    position: relative;
    overflow: hidden;
}
.cta-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 40%;
    height: 100%;
    background: radial-gradient(circle at 100% 0%, var(--primary) 0%, transparent 50%);
    opacity: 0.15;
}
.cta-content {
    position: relative;
    z-index: 1;
}
.cta-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-white);
    margin-bottom: 1rem;
}
.cta-subtitle {
    color: var(--dark-text-muted);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
    .hero {
        padding: 5rem 0 3rem;
    }
    .hero-title {
        font-size: 2.25rem;
    }
    .hero-stats {
        gap: 2rem;
    }
    .hero-stat-value {
        font-size: 1.5rem;
    }
    .cta-card {
        padding: 2.5rem 1.5rem;
    }
}
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-bolt"></i>
                        Попробуйте бесплатно 14 дней
                    </div>
                    <h1 class="hero-title">
                        Управляйте учебным центром <span>эффективно</span>
                    </h1>
                    <p class="hero-subtitle">
                        Автоматизируйте учёт учеников, расписание, платежи и отчётность. Всё в одной системе, созданной для учебных центров Казахстана.
                    </p>
                    <div class="hero-buttons">
                        <a href="<?= Url::to(['/register']) ?>" class="btn btn-primary btn-lg">
                            Начать бесплатно <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="<?= Url::to(['/features']) ?>" class="btn btn-light btn-lg">
                            Узнать больше
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-value">200+</div>
                            <div class="hero-stat-label">Учебных центров</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">15K+</div>
                            <div class="hero-stat-label">Учеников</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">99%</div>
                            <div class="hero-stat-label">Довольных клиентов</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <!-- Illustration placeholder -->
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Всё для управления учебным центром</h2>
            <p class="section-subtitle">Забудьте о таблицах Excel и бумажных журналах</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="feature-title">Учёт учеников</h3>
                    <p class="feature-desc">Полная база учеников с контактами, историей обучения и платежей. Поиск и фильтрация в один клик.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="feature-title">Расписание</h3>
                    <p class="feature-desc">Удобное расписание занятий по группам и преподавателям. Отметка посещаемости в пару кликов.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="feature-title">Финансы</h3>
                    <p class="feature-desc">Приём платежей, контроль задолженностей, автоматические напоминания. Интеграция с Kaspi.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Группы</h3>
                    <p class="feature-desc">Создание групп по уровням и направлениям. Гибкое управление составом и расписанием.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Отчёты</h3>
                    <p class="feature-desc">Наглядные отчёты по посещаемости, финансам и эффективности. Принимайте решения на основе данных.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">SMS уведомления</h3>
                    <p class="feature-desc">Автоматические SMS напоминания о занятиях и платежах. Экономьте время администраторов.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?= Url::to(['/features']) ?>" class="btn btn-outline-primary btn-lg">
                Все возможности <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Pricing Preview -->
<section class="pricing-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Прозрачные тарифы</h2>
            <p class="section-subtitle">Начните бесплатно, масштабируйтесь по мере роста</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $index => $plan): ?>
            <div class="col-md-6 col-lg-4">
                <div class="plan-card <?= $index === 1 ? 'popular' : '' ?>">
                    <?php if ($index === 1): ?>
                    <div class="plan-badge">Популярный</div>
                    <?php endif; ?>
                    <div class="plan-name"><?= Html::encode($plan->name) ?></div>
                    <div class="plan-price">
                        <?= $plan->getFormattedPriceMonthly() ?>
                        <?php if ($plan->price_monthly > 0): ?>
                        <span>/мес</span>
                        <?php endif; ?>
                    </div>
                    <div class="plan-trial">
                        <i class="fas fa-gift mr-1"></i> <?= $plan->trial_days ?> дней бесплатно
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> <?= $plan->max_pupils ?: '∞' ?> учеников</li>
                        <li><i class="fas fa-check"></i> <?= $plan->max_teachers ?: '∞' ?> преподавателей</li>
                        <li><i class="fas fa-check"></i> <?= $plan->max_groups ?: '∞' ?> групп</li>
                        <?php if ($plan->hasFeature('sms')): ?>
                        <li><i class="fas fa-check"></i> SMS уведомления</li>
                        <?php endif; ?>
                        <?php if ($plan->hasFeature('reports')): ?>
                        <li><i class="fas fa-check"></i> Расширенные отчёты</li>
                        <?php endif; ?>
                    </ul>
                    <a href="<?= Url::to(['/register']) ?>" class="btn <?= $index === 1 ? 'btn-primary' : 'btn-outline-primary' ?> btn-block">
                        Начать бесплатно
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= Url::to(['/pricing']) ?>" class="text-muted">
                Сравнить все тарифы <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <div class="cta-content">
                <h2 class="cta-title">Готовы автоматизировать ваш учебный центр?</h2>
                <p class="cta-subtitle">Присоединяйтесь к сотням учебных центров Казахстана</p>
                <a href="<?= Url::to(['/register']) ?>" class="btn btn-primary btn-lg">
                    Попробовать бесплатно <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
