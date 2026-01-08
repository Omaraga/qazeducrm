<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPlan[] $plans */

use app\helpers\SettingsHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'CRM для учебных центров';
$stats = SettingsHelper::getLandingStats();
?>

<!-- Hero Section -->
<section class="relative bg-white overflow-hidden">
    <!-- Background gradient -->
    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-bl from-gray-100 to-transparent pointer-events-none"></div>

    <div class="container mx-auto px-4 py-20 lg:py-28 relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 bg-gray-100 text-orange-600 px-4 py-2 rounded-full text-sm font-semibold mb-6 border border-gray-200">
                    <i class="fas fa-bolt"></i>
                    <span>Запуск за 15 минут</span>
                </div>

                <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                    CRM для <span class="text-orange-500">учебных центров</span>
                </h1>

                <p class="text-lg text-gray-600 mb-8 max-w-lg leading-relaxed">
                    Ученики, группы, расписание, платежи, лиды — всё в одной системе.
                    Освободите время для развития бизнеса.
                </p>

                <div class="flex flex-wrap gap-4 mb-10">
                    <a href="<?= Url::to(['/register']) ?>" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition-all hover:shadow-lg">
                        Попробовать бесплатно <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="<?= Url::to(['/features']) ?>" class="inline-flex items-center gap-2 border-2 border-gray-200 hover:border-orange-500 text-gray-700 hover:text-orange-500 px-6 py-3 rounded-lg font-medium transition-all">
                        Возможности
                    </a>
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap gap-4 md:gap-8 pt-8 border-t border-gray-200">
                    <?php foreach ($stats as $stat): ?>
                    <div>
                        <div class="text-xl md:text-2xl lg:text-3xl font-extrabold text-orange-500"><?= Html::encode($stat['value']) ?></div>
                        <div class="text-xs md:text-sm text-gray-500"><?= Html::encode($stat['label']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Dashboard Screenshot -->
            <div class="mt-8 lg:mt-0">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 overflow-hidden">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-dashboard.png"
                         alt="Dashboard CRM - учебный центр"
                         class="rounded-xl w-full h-auto"
                         loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By -->
<section class="bg-gray-50 py-10 border-y border-gray-100">
    <div class="container mx-auto px-4 text-center">
        <p class="text-sm text-gray-400 uppercase tracking-wider mb-6">Нам доверяют учебные центры</p>
        <div class="flex flex-wrap items-center justify-center gap-8 lg:gap-12 opacity-60">
            <span class="text-xl font-bold text-gray-500">StudyLab</span>
            <span class="text-xl font-bold text-gray-500">EduPro</span>
            <span class="text-xl font-bold text-gray-500">SmartKids</span>
            <span class="text-xl font-bold text-gray-500">LangSchool</span>
            <span class="text-xl font-bold text-gray-500">TechAcademy</span>
        </div>
    </div>
</section>

<!-- Problems Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Проблемы</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Знакомые ситуации?</h2>
            <p class="text-lg text-gray-600 max-w-xl mx-auto">Эти проблемы решает Qazaq Education CRM</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-red-300 hover:shadow-lg transition-all group">
                <div class="w-12 h-12 bg-red-100 text-red-500 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-excel"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Excel-хаос</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Десятки таблиц, которые путаются и теряются. Данные дублируются и устаревают.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-red-300 hover:shadow-lg transition-all group">
                <div class="w-12 h-12 bg-red-100 text-red-500 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Деньги уходят</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Непонятно кто должен, сколько заработали, куда тратятся деньги.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-red-300 hover:shadow-lg transition-all group">
                <div class="w-12 h-12 bg-red-100 text-red-500 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Лиды теряются</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Заявки забываются, не перезваниваем вовремя, клиенты уходят к конкурентам.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-red-300 hover:shadow-lg transition-all group">
                <div class="w-12 h-12 bg-red-100 text-red-500 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Расписание — ад</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Конфликты, накладки, преподаватель не знает когда урок.</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Возможности</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Всё что нужно для работы</h2>
            <p class="text-lg text-gray-600 max-w-xl mx-auto">Полный набор инструментов для управления учебным центром</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-users"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">База учеников</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Полная информация о каждом ученике: контакты, история, платежи, посещаемость.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-green-100 text-green-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Группы</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Создавайте группы, назначайте преподавателей, управляйте составом.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Расписание</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Визуальный календарь с drag & drop. Шаблоны и проверка конфликтов.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-purple-100 text-purple-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-funnel-dollar"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">CRM и Лиды</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Kanban-доска, воронка продаж, скрипты, автоматические напоминания.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-cyan-100 text-cyan-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Платежи</h4>
                <p class="text-gray-600 text-sm leading-relaxed">Приём оплат, контроль долгов, печать квитанций, история транзакций.</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6 hover:border-orange-300 hover:shadow-lg hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-pink-100 text-pink-500 rounded-lg flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Отчёты</h4>
                <p class="text-gray-600 text-sm leading-relaxed">10+ видов отчётов: финансы, продажи, посещаемость, эффективность.</p>
            </div>
        </div>

        <div class="text-center mt-10">
            <a href="<?= Url::to(['/features']) ?>" class="inline-flex items-center gap-2 border-2 border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white px-6 py-3 rounded-lg font-semibold transition-all">
                Все возможности <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- CRM Highlight -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">CRM</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Превращайте лидов в учеников</h2>
                <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                    Полноценная CRM-система для работы с заявками. Kanban-доска, скрипты продаж,
                    автоматические напоминания — всё для увеличения конверсии.
                </p>

                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span class="text-gray-700"><strong class="text-gray-900">Kanban-доска</strong> — перетаскивайте лидов между этапами</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span class="text-gray-700"><strong class="text-gray-900">Скрипты продаж</strong> — готовые сценарии для менеджеров</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span class="text-gray-700"><strong class="text-gray-900">Напоминания</strong> — система не даст забыть о звонке</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span class="text-gray-700"><strong class="text-gray-900">Аналитика воронки</strong> — конверсия на каждом этапе</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-green-500 mt-1"></i>
                        <span class="text-gray-700"><strong class="text-gray-900">Конвертация в 1 клик</strong> — превращайте лида в ученика</span>
                    </li>
                </ul>
            </div>

            <!-- Kanban Screenshot -->
            <div class="bg-white border border-gray-200 rounded-xl p-2 shadow-xl overflow-hidden">
                <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-kanban.png"
                     alt="CRM Kanban доска - воронка продаж"
                     class="rounded-lg w-full h-auto"
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Access Control -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Безопасность</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Гибкие права доступа</h2>
            <p class="text-lg text-gray-600">Каждый сотрудник видит только то, что ему нужно</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6">
            <div class="bg-white border border-gray-100 rounded-xl p-5 text-center hover:border-orange-300 hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-crown"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-1">Директор</h5>
                <p class="text-xs text-gray-500">Полный доступ ко всем функциям</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-5 text-center hover:border-orange-300 hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-1">Админ</h5>
                <p class="text-xs text-gray-500">Ученики, группы, расписание</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-5 text-center hover:border-orange-300 hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-headset"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-1">Менеджер</h5>
                <p class="text-xs text-gray-500">Лиды, звонки, продажи</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-5 text-center hover:border-orange-300 hover:shadow-md transition-all">
                <div class="w-14 h-14 bg-purple-100 text-purple-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-1">Учитель</h5>
                <p class="text-xs text-gray-500">Свои группы и расписание</p>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-5 text-center hover:border-orange-300 hover:shadow-md transition-all col-span-2 md:col-span-1">
                <div class="w-14 h-14 bg-yellow-100 text-yellow-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-calculator"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-1">Бухгалтер</h5>
                <p class="text-xs text-gray-500">Платежи и отчёты</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Отзывы</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Что говорят клиенты</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <div class="flex gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-700 leading-relaxed mb-6 italic">
                    "Наконец-то избавились от Excel! Теперь всё в одном месте, ничего не теряется.
                    Конверсия выросла на 40% благодаря CRM-модулю."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center font-bold">АК</div>
                    <div>
                        <h5 class="font-semibold text-gray-900">Айгуль К.</h5>
                        <p class="text-sm text-gray-500">Директор StudyLab</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <div class="flex gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-700 leading-relaxed mb-6 italic">
                    "Расписание — просто огонь! Drag & drop, проверка конфликтов. Раньше тратили
                    час на составление, теперь 10 минут."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center font-bold">МТ</div>
                    <div>
                        <h5 class="font-semibold text-gray-900">Марат Т.</h5>
                        <p class="text-sm text-gray-500">Основатель EduPro</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <div class="flex gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-700 leading-relaxed mb-6 italic">
                    "Отчёты помогают видеть реальную картину. Поняли где теряем деньги и оптимизировали
                    расходы на 25%."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center font-bold">ДС</div>
                    <div>
                        <h5 class="font-semibold text-gray-900">Динара С.</h5>
                        <p class="text-sm text-gray-500">CEO SmartKids</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Us -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Преимущества</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900">Почему выбирают нас</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Быстрый старт</h4>
                <p class="text-gray-600 text-sm">Регистрация за 2 минуты. Начните работать сразу, без настройки.</p>
            </div>

            <div class="text-center p-6">
                <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-headset"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Поддержка 24/7</h4>
                <p class="text-gray-600 text-sm">Отвечаем в Telegram и WhatsApp. Помогаем с настройкой.</p>
            </div>

            <div class="text-center p-6">
                <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-sync"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Обновления</h4>
                <p class="text-gray-600 text-sm">Регулярно добавляем новые функции по запросам клиентов.</p>
            </div>

            <div class="text-center p-6">
                <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Безопасность</h4>
                <p class="text-gray-600 text-sm">Данные защищены, ежедневные бэкапы, SSL-шифрование.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing -->
<?php if (!empty($plans)): ?>
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block bg-gray-100 text-orange-600 px-4 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Тарифы</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-4">Простые и понятные цены</h2>
            <p class="text-lg text-gray-600">14 дней бесплатно на любом тарифе</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <?php foreach ($plans as $i => $plan): ?>
            <div class="relative bg-white border-2 <?= $i === 1 ? 'border-orange-500 shadow-xl' : 'border-gray-100' ?> rounded-2xl p-8 text-center hover:border-orange-300 transition-all">
                <?php if ($i === 1): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-orange-500 text-white text-xs font-semibold px-4 py-1 rounded-full">Популярный</span>
                <?php endif; ?>
                <div class="text-xl font-bold text-gray-900 mb-2"><?= Html::encode($plan->name) ?></div>
                <div class="text-4xl font-extrabold text-orange-500 mb-1"><?= $plan->price_monthly > 0 ? number_format($plan->price_monthly, 0, '', ' ') . ' ₸' : 'Бесплатно' ?></div>
                <div class="text-sm text-gray-500 mb-6"><?= $plan->price_monthly > 0 ? 'в месяц' : '' ?></div>
                <ul class="text-left space-y-3 mb-6">
                    <li class="flex items-center gap-2 text-gray-700"><i class="fas fa-check text-green-500"></i> До <?= $plan->max_pupils ?: '∞' ?> учеников</li>
                    <li class="flex items-center gap-2 text-gray-700"><i class="fas fa-check text-green-500"></i> До <?= $plan->max_teachers ?: '∞' ?> учителей</li>
                    <li class="flex items-center gap-2 text-gray-700"><i class="fas fa-check text-green-500"></i> Все функции CRM</li>
                    <li class="flex items-center gap-2 text-gray-700"><i class="fas fa-check text-green-500"></i> Техподдержка</li>
                </ul>
                <a href="<?= Url::to(['/register']) ?>" class="block w-full py-3 rounded-lg font-semibold transition-all <?= $i === 1 ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'border-2 border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white' ?>">
                    Попробовать бесплатно
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-20 bg-gradient-to-r from-gray-900 to-gray-800 text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl lg:text-4xl font-extrabold text-white mb-4">Готовы упростить управление?</h2>
        <p class="text-lg text-gray-400 mb-8 max-w-md mx-auto">Начните бесплатный период прямо сейчас. Без привязки карты.</p>
        <a href="<?= Url::to(['/register']) ?>" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all hover:shadow-lg">
            Начать бесплатно <i class="fas fa-arrow-right"></i>
        </a>
        <div class="flex flex-wrap justify-center gap-6 mt-8">
            <span class="flex items-center gap-2 text-gray-400"><i class="fas fa-check text-green-500"></i> 14 дней бесплатно</span>
            <span class="flex items-center gap-2 text-gray-400"><i class="fas fa-check text-green-500"></i> Без привязки карты</span>
            <span class="flex items-center gap-2 text-gray-400"><i class="fas fa-check text-green-500"></i> Полный функционал</span>
        </div>
    </div>
</section>
