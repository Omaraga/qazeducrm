<?php

/** @var yii\web\View $this */

use app\helpers\SettingsHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Возможности';
$features = SettingsHelper::getFeatures();
?>

<!-- Hero -->
<section class="relative bg-gradient-to-br from-gray-900 to-gray-800 py-24 text-center overflow-hidden">
    <!-- Pattern -->
    <div class="absolute inset-0 opacity-5" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
    <div class="container mx-auto px-4 relative">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">
            <?= Html::encode($features['hero_title']) ?>
        </h1>
        <p class="text-xl text-white/70 max-w-2xl mx-auto">
            <?= Html::encode($features['hero_subtitle']) ?>
        </p>
    </div>
</section>

<!-- Quick Navigation -->
<nav class="bg-white py-4 border-b border-gray-200 sticky top-16 z-40 hidden md:block">
    <div class="container mx-auto px-4">
        <ul class="flex flex-wrap gap-2 justify-center">
            <li><a href="#pupils" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Ученики</a></li>
            <li><a href="#groups" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Группы</a></li>
            <li><a href="#schedule" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Расписание</a></li>
            <li><a href="#crm" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">CRM и Лиды</a></li>
            <li><a href="#finance" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Финансы</a></li>
            <li><a href="#reports" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Отчёты</a></li>
            <li><a href="#access" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Права доступа</a></li>
            <li><a href="#more" class="inline-block px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-orange-500 hover:text-white transition-all">Ещё</a></li>
        </ul>
    </div>
</nav>

<!-- Feature 1: Pupils -->
<section class="py-20 bg-white" id="pupils">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Ученики</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Полная база учеников под контролем</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Храните всю информацию о каждом ученике в одном месте: контакты, история обучения, платежи, посещаемость. Находите нужного ученика за секунды.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Карточка ученика</strong>ФИО, контакты, родители, адрес, документы - вся информация в профиле</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">История обучения</strong>Какие группы посещал, когда записался, сколько занятий прошёл</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Финансы ученика</strong>Баланс, история платежей, задолженности - всё видно сразу</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Быстрый поиск</strong>Найдите ученика по имени, телефону или номеру за секунды</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Экспорт в Excel</strong>Выгружайте списки учеников для отчётов и рассылок</span>
                    </li>
                </ul>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-pupils.png" alt="База учеников CRM" class="w-full h-auto">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 2: Groups -->
<section class="py-20 bg-gray-50" id="groups">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-groups.png" alt="Управление группами CRM" class="w-full h-auto">
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Группы</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Гибкое управление группами</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Создавайте группы по уровням, предметам и направлениям. Назначайте преподавателей, управляйте составом учеников в пару кликов.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Групповые и индивидуальные</strong>Поддержка любого формата занятий</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Назначение преподавателей</strong>Один учитель на группу или несколько - как удобно</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Управление составом</strong>Добавляйте и переводите учеников между группами</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Цветовая кодировка</strong>Визуально различайте группы в расписании</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Feature 3: Schedule -->
<section class="py-20 bg-white" id="schedule">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-gray-100 text-orange-600 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Расписание</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Визуальное расписание с drag & drop</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Создавайте и редактируйте расписание в удобном календаре. Перетаскивайте занятия мышкой, система сама проверит конфликты.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Недельный и месячный вид</strong>Выбирайте удобный формат просмотра</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Drag & drop</strong>Перетаскивайте занятия для изменения времени</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Проверка конфликтов</strong>Система предупредит о пересечениях</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Шаблоны расписания</strong>Создайте шаблон и применяйте на любой период</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Фильтры</strong>По группам, преподавателям, кабинетам</span>
                    </li>
                </ul>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-schedule-2.png" alt="Расписание занятий CRM" class="w-full h-auto">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 4: CRM/Leads -->
<section class="py-20 bg-gray-50" id="crm">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-kanban.png" alt="Kanban-доска CRM" class="w-full h-auto">
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <span class="inline-block px-3 py-1 bg-gray-100 text-orange-600 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">CRM</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Воронка продаж и управление лидами</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Не теряйте ни одного клиента. Kanban-доска для управления заявками, скрипты продаж для менеджеров, автоматические напоминания.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Kanban-доска</strong>Визуальное управление воронкой с drag & drop</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Скрипты продаж</strong>Готовые сценарии разговоров для менеджеров</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Напоминания</strong>Автоматические уведомления о звонках и задачах</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Теги и сегментация</strong>Маркируйте лидов для лучшей аналитики</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Конвертация в 1 клик</strong>Превращайте лида в ученика мгновенно</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Аналитика воронки</strong>Конверсия, источники, эффективность менеджеров</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Feature 5: Finance -->
<section class="py-20 bg-white" id="finance">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Финансы</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Полный контроль над деньгами</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Приём платежей, контроль задолженностей, расчёт зарплат преподавателей. Всё прозрачно и под контролем.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Приём платежей</strong>Наличные, Kaspi, банковский перевод - любой способ</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Контроль долгов</strong>Видите кто и сколько должен в реальном времени</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Печать квитанций</strong>Выдавайте чеки клиентам</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Зарплаты учителей</strong>Расчёт по ставкам и проведённым занятиям</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Финансовые отчёты</strong>Доходы, расходы, прибыль по периодам</span>
                    </li>
                </ul>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-payments.png" alt="Бухгалтерия CRM" class="w-full h-auto">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature 6: Reports -->
<section class="py-20 bg-gray-50" id="reports">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-reports.png" alt="Отчёты CRM" class="w-full h-auto">
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <span class="inline-block px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Отчёты</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">10+ видов отчётов для принятия решений</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Принимайте решения на основе данных. Финансы, посещаемость, продажи, эффективность - всё в наглядных отчётах.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Финансовые отчёты</strong>Доходы, расходы, задолженности</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Отчёты по продажам</strong>Воронка, источники лидов, менеджеры</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Посещаемость</strong>По ученикам, группам, преподавателям</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Эффективность учителей</strong>Нагрузка, зарплаты, отработанные часы</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-white rounded-lg border border-gray-200">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Экспорт</strong>Выгрузка в Excel для дальнейшего анализа</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Feature 7: Access Control -->
<section class="py-20 bg-white" id="access">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">Безопасность</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Гибкие права доступа</h2>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">Каждый сотрудник видит только то, что ему нужно. Настройте права для директора, админа, менеджера, учителя отдельно.</p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">5 ролей</strong>Директор, Админ, Менеджер, Учитель, Бухгалтер</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Скрытие финансов</strong>Учителя не видят зарплаты коллег и платежи</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Ограничение удаления</strong>Только директор может удалять записи</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">Мои группы</strong>Учитель видит только свои группы и учеников</span>
                    </li>
                    <li class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="w-7 h-7 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check text-orange-500 text-xs"></i>
                        </span>
                        <span class="text-gray-600"><strong class="text-gray-900 block mb-1">История действий</strong>Кто, когда и что изменил - всё логируется</span>
                    </li>
                </ul>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/screenshots/crm-access.png" alt="Настройки доступа CRM" class="w-full h-auto">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- All Other Features -->
<section class="py-20 bg-gray-50" id="more">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">И это ещё не всё</h2>
            <p class="text-lg text-gray-500">Дополнительные возможности для вашего роста</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-sms text-blue-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">SMS уведомления</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Автоматические напоминания о занятиях, платежах и задолженностях. Шаблоны сообщений.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-clipboard-check text-green-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Посещаемость</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Отметка присутствия в 2 клика. История посещений по каждому ученику.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-building text-orange-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Филиалы</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Управляйте несколькими филиалами из одного аккаунта. Отдельная статистика по каждому.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-book-open text-purple-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">База знаний</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Документация и обучающие материалы для команды. Всё в одном месте.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-bell text-blue-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Уведомления</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Система внутренних уведомлений о важных событиях и задачах.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-tags text-green-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Тарифы и предметы</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Гибкая настройка тарифных планов и учебных предметов.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-door-open text-orange-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Кабинеты</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Управление помещениями. Проверка доступности при составлении расписания.</p>
            </div>

            <div class="bg-white rounded-xl p-6 hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="w-11 h-11 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-friends text-purple-500 text-lg"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Сотрудники</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Управление пользователями системы. Сброс паролей, назначение ролей.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-gradient-to-br from-gray-800 to-gray-900 text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">Готовы попробовать?</h2>
        <p class="text-xl text-white/70 mb-8">14 дней бесплатно, полный функционал, без привязки карты</p>
        <a href="<?= Url::to(['/register']) ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50">
            Начать бесплатно
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
