<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Возможности';
?>

<style>
.features-hero {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
    padding: 6rem 0 4rem;
    text-align: center;
}
.features-hero h1 {
    color: var(--text-white);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
.features-hero p {
    color: var(--dark-text-muted);
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.feature-section {
    padding: 5rem 0;
    background: var(--bg-white);
}
.feature-section:nth-child(even) {
    background: var(--bg-light);
}
.feature-section .section-label {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}
.feature-section h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}
.feature-section .lead {
    color: var(--text-muted);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}
.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.feature-list li {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.feature-list li i {
    width: 24px;
    height: 24px;
    background: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 0.75rem;
    flex-shrink: 0;
    margin-top: 2px;
}
.feature-list li span {
    color: var(--text-secondary);
    font-size: 1rem;
}

.feature-image {
    background: var(--border);
    border-radius: var(--radius-lg);
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
}

/* Stats */
.stats-section {
    background: var(--primary);
    padding: 4rem 0;
}
.stat-item {
    text-align: center;
}
.stat-value {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text-white);
}
.stat-label {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
}

/* Additional Features */
.addon-icon {
    width: 48px;
    height: 48px;
    background: var(--primary-light);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}
.addon-icon i {
    color: var(--primary);
}
.addon-title {
    font-weight: 600;
    color: var(--text-primary);
}

/* CTA */
.cta-section {
    padding: 5rem 0;
    text-align: center;
    background: var(--bg-white);
}
.cta-section h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}
.cta-section p {
    color: var(--text-muted);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}
</style>

<!-- Hero -->
<section class="features-hero">
    <div class="container">
        <h1>Всё для эффективного управления</h1>
        <p>Комплексное решение для автоматизации учебных центров, языковых школ и курсов</p>
    </div>
</section>

<!-- Feature 1: Pupils -->
<section class="feature-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="section-label">Ученики</div>
                <h2>Полный контроль над базой учеников</h2>
                <p class="lead">Вся информация о учениках в одном месте. Быстрый поиск, удобные фильтры, история обучения.</p>
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Карточка ученика с контактами и историей</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Связь с родителями и несколько контактов</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>История посещений и платежей</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Быстрый поиск и фильтрация</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Экспорт данных в Excel</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="feature-image">
                    <i class="fas fa-user-graduate fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 2: Groups & Schedule -->
<section class="feature-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <div class="section-label">Группы и расписание</div>
                <h2>Гибкое управление группами</h2>
                <p class="lead">Создавайте группы, назначайте преподавателей, управляйте расписанием в удобном интерфейсе.</p>
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Группы по уровням и направлениям</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Визуальное расписание на неделю</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Отметка посещаемости в два клика</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Перенос и отмена занятий</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Конфликты расписания автоматически</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="feature-image">
                    <i class="fas fa-calendar-alt fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 3: Payments -->
<section class="feature-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="section-label">Финансы</div>
                <h2>Прозрачный учёт финансов</h2>
                <p class="lead">Контролируйте оплаты, отслеживайте задолженности, получайте финансовые отчёты.</p>
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Приём платежей наличными и Kaspi</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Автоматический расчёт баланса</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Контроль задолженностей</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>SMS напоминания о платежах</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Финансовые отчёты по периодам</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="feature-image">
                    <i class="fas fa-money-bill-wave fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 4: Reports -->
<section class="feature-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <div class="section-label">Аналитика</div>
                <h2>Принимайте решения на основе данных</h2>
                <p class="lead">Наглядные отчёты и дашборды помогут понять эффективность вашего центра.</p>
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Дашборд с ключевыми метриками</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Отчёт по посещаемости</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Финансовая отчётность</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Эффективность преподавателей</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Экспорт отчётов в PDF и Excel</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="feature-image">
                    <i class="fas fa-chart-line fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-value">200+</div>
                    <div class="stat-label">Учебных центров</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-value">15K+</div>
                    <div class="stat-label">Учеников</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-value">50K+</div>
                    <div class="stat-label">Занятий в месяц</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-value">99%</div>
                    <div class="stat-label">Uptime</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- More Features -->
<section class="feature-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>И это ещё не всё</h2>
            <p class="text-muted">Дополнительные возможности для вашего роста</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-sms"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">SMS уведомления</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Автоматические напоминания о занятиях и платежах</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-building"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">Филиалы</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Управляйте несколькими филиалами из одного аккаунта</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-funnel-dollar"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">Воронка лидов</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Отслеживайте заявки от первого контакта до оплаты</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-users-cog"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">Роли и доступы</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Гибкое управление правами сотрудников</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-code"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">API</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Интеграция с вашими системами через REST API</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="addon-icon"><i class="fas fa-headset"></i></div>
                    </div>
                    <div>
                        <h5 class="addon-title">Поддержка</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Техническая поддержка и обучение персонала</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Готовы попробовать?</h2>
        <p>Начните бесплатный пробный период прямо сейчас</p>
        <a href="<?= Url::to(['/register']) ?>" class="btn btn-primary btn-lg">
            Попробовать бесплатно <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</section>
