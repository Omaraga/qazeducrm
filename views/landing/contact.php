<?php

/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Контакты';
?>

<style>
.contact-hero {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
    padding: 6rem 0 4rem;
    text-align: center;
}
.contact-hero h1 {
    color: var(--text-white);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
.contact-hero p {
    color: var(--dark-text-muted);
    font-size: 1.1rem;
}

.contact-section {
    padding: 5rem 0;
    background: var(--bg-white);
}

.contact-card {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
    height: 100%;
    text-align: center;
    transition: all var(--transition);
}
.contact-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}
.contact-card-icon {
    width: 64px;
    height: 64px;
    background: var(--primary-light);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.contact-card-icon i {
    font-size: 1.5rem;
    color: var(--primary);
}
.contact-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.contact-card p {
    color: var(--text-muted);
    margin-bottom: 1rem;
    font-size: 0.95rem;
}
.contact-card a {
    color: var(--primary);
    font-weight: 500;
    text-decoration: none;
}
.contact-card a:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.contact-form-section {
    background: var(--bg-light);
    padding: 5rem 0;
}
.contact-form {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: var(--shadow-lg);
}
.contact-form h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.contact-form .lead {
    color: var(--text-muted);
    margin-bottom: 2rem;
}
.form-label {
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}
.form-control {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    border: 1px solid var(--border-dark);
}
.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(254, 141, 0, 0.15);
}
textarea.form-control {
    min-height: 120px;
}

.office-section {
    padding: 5rem 0;
    background: var(--bg-white);
}
.office-info {
    background: var(--dark);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    color: var(--text-white);
    height: 100%;
}
.office-info h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}
.office-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.office-item i {
    color: var(--primary);
    font-size: 1.25rem;
    width: 24px;
}
.office-item div {
    color: var(--dark-text-muted);
}
.office-item strong {
    color: var(--text-white);
    display: block;
    margin-bottom: 0.25rem;
}
.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}
.social-links a {
    width: 44px;
    height: 44px;
    background: var(--dark-light);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-white);
    font-size: 1.25rem;
    transition: all var(--transition);
}
.social-links a:hover {
    background: var(--primary);
}
.map-placeholder {
    background: var(--border);
    border-radius: var(--radius-lg);
    height: 100%;
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
}
</style>

<!-- Hero -->
<section class="contact-hero">
    <div class="container">
        <h1>Свяжитесь с нами</h1>
        <p>Мы всегда рады помочь и ответить на ваши вопросы</p>
    </div>
</section>

<!-- Contact Cards -->
<section class="contact-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p>Напишите нам, мы ответим в течение 24 часов</p>
                    <a href="mailto:info@qazaqedu.kz">info@qazaqedu.kz</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Телефон</h3>
                    <p>Пн-Пт с 9:00 до 18:00 по времени Астаны</p>
                    <a href="tel:+77001234567">+7 700 123 45 67</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fab fa-telegram"></i>
                    </div>
                    <h3>Telegram</h3>
                    <p>Быстрые ответы на ваши вопросы</p>
                    <a href="#">@qazaqedu_support</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="contact-form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact-form">
                    <div class="text-center mb-4">
                        <h2>Оставить заявку</h2>
                        <p class="lead">Заполните форму и мы свяжемся с вами</p>
                    </div>

                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Имя *</label>
                                <input type="text" class="form-control" placeholder="Ваше имя" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Телефон *</label>
                                <input type="tel" class="form-control" placeholder="+7 777 123 4567" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="email@example.kz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Название организации</label>
                                <input type="text" class="form-control" placeholder="Ваш учебный центр">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Сообщение</label>
                                <textarea class="form-control" placeholder="Расскажите о вашем учебном центре и задачах..."></textarea>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Отправить заявку
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office -->
<section class="office-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="office-info">
                    <h3>Наш офис</h3>

                    <div class="office-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Адрес</strong>
                            г. Алматы, ул. Примерная 123, офис 456
                        </div>
                    </div>

                    <div class="office-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Время работы</strong>
                            Пн-Пт: 9:00 - 18:00<br>
                            Сб-Вс: выходной
                        </div>
                    </div>

                    <div class="office-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Телефон</strong>
                            +7 700 123 45 67
                        </div>
                    </div>

                    <div class="office-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            info@qazaqedu.kz
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="#"><i class="fab fa-telegram"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <!-- Map placeholder -->
                <div class="map-placeholder">
                    <i class="fas fa-map-marked-alt fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</section>
