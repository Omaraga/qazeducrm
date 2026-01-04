<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan[] $plans */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Тарифы';
?>

<style>
/* Hero */
.pricing-hero {
    background: var(--bg-light);
    padding: 5rem 0 4rem;
    text-align: center;
}
.pricing-hero h1 {
    color: var(--text-primary);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
.pricing-hero p {
    color: var(--text-muted);
    font-size: 1.1rem;
}

/* Pricing Content */
.pricing-content {
    padding: 4rem 0;
    background: var(--bg-white);
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
    transform: scale(1.02);
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
.plan-desc {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}
.plan-price {
    font-size: 2.5rem;
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.plan-limits {
    background: var(--bg-light);
    border-radius: var(--radius);
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.plan-limit {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
}
.plan-limit:not(:last-child) {
    border-bottom: 1px solid var(--border);
}
.plan-limit strong {
    color: var(--text-primary);
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
.plan-features li i.fa-check { color: var(--success); }
.plan-features li i.fa-times { color: var(--border-dark); }

/* FAQ */
.faq-section {
    padding: 4rem 0;
    background: var(--bg-light);
}
.faq-item {
    background: var(--bg-white);
    border-radius: var(--radius-md);
    margin-bottom: 1rem;
    border: 1px solid var(--border);
    overflow: hidden;
}
.faq-question {
    padding: 1.25rem 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: color var(--transition-fast);
}
.faq-question:hover { color: var(--primary); }
.faq-answer {
    padding: 0 1.5rem 1.25rem;
    color: var(--text-muted);
    display: none;
}
.faq-item.active .faq-answer { display: block; }
.faq-item.active .faq-question i { transform: rotate(180deg); }
</style>

<!-- Hero -->
<section class="pricing-hero">
    <div class="container">
        <h1>Выберите подходящий тариф</h1>
        <p>Начните бесплатно, масштабируйтесь по мере роста вашего центра</p>
    </div>
</section>

<!-- Pricing Cards -->
<section class="pricing-content">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php foreach ($plans as $index => $plan): ?>
            <div class="col-md-6 col-lg-3">
                <div class="plan-card <?= $index === 1 ? 'popular' : '' ?>">
                    <?php if ($index === 1): ?>
                    <div class="plan-badge">Популярный</div>
                    <?php endif; ?>

                    <div class="text-center">
                        <div class="plan-name"><?= Html::encode($plan->name) ?></div>
                        <div class="plan-desc"><?= Html::encode($plan->description) ?></div>
                        <div class="plan-price">
                            <?= $plan->getFormattedPriceMonthly() ?>
                            <?php if ($plan->price_monthly > 0): ?><span>/мес</span><?php endif; ?>
                        </div>
                        <div class="plan-trial">
                            <i class="fas fa-gift"></i> <?= $plan->trial_days ?> дней бесплатно
                        </div>
                    </div>

                    <div class="plan-limits">
                        <div class="plan-limit"><span>Ученики</span><strong><?= $plan->max_pupils ?: '∞' ?></strong></div>
                        <div class="plan-limit"><span>Преподаватели</span><strong><?= $plan->max_teachers ?: '∞' ?></strong></div>
                        <div class="plan-limit"><span>Группы</span><strong><?= $plan->max_groups ?: '∞' ?></strong></div>
                    </div>

                    <ul class="plan-features">
                        <li><i class="fas <?= $plan->hasFeature('crm_basic') ? 'fa-check' : 'fa-times' ?>"></i> Базовый CRM</li>
                        <li><i class="fas <?= $plan->hasFeature('sms') ? 'fa-check' : 'fa-times' ?>"></i> SMS уведомления</li>
                        <li><i class="fas <?= $plan->hasFeature('reports') ? 'fa-check' : 'fa-times' ?>"></i> Отчёты</li>
                        <li><i class="fas <?= $plan->hasFeature('api') ? 'fa-check' : 'fa-times' ?>"></i> API</li>
                        <li><i class="fas <?= $plan->hasFeature('priority_support') ? 'fa-check' : 'fa-times' ?>"></i> Приоритетная поддержка</li>
                    </ul>

                    <a href="<?= Url::to(['/register']) ?>" class="btn <?= $index === 1 ? 'btn-primary' : 'btn-outline-primary' ?> btn-block">
                        Начать бесплатно
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="faq-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Часто задаваемые вопросы</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="faq-item active">
                    <div class="faq-question">Могу ли я сменить тариф? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-answer">Да, вы можете изменить тариф в любой момент. При переходе на более дорогой тариф изменения вступят в силу сразу.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Как работает пробный период? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-answer">После регистрации вы получаете полный доступ ко всем функциям на период пробного периода. Карта не требуется.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Какие способы оплаты? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-answer">Мы принимаем Kaspi, банковские карты, банковский перевод для юридических лиц.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Есть ли скидка за год? <i class="fas fa-chevron-down"></i></div>
                    <div class="faq-answer">Да, при оплате за год вы экономите 2 месяца — это 17% экономии.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.faq-question').forEach(function(item) {
    item.addEventListener('click', function() {
        var parent = this.parentElement;
        document.querySelectorAll('.faq-item').forEach(function(faq) {
            if (faq !== parent) faq.classList.remove('active');
        });
        parent.classList.toggle('active');
    });
});
</script>
