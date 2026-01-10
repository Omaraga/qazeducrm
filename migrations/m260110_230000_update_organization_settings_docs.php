<?php

use yii\db\Migration;

/**
 * Обновляет документацию раздела "Настройка организации" с новыми настройками
 */
class m260110_230000_update_organization_settings_docs extends Migration
{
    public function safeUp()
    {
        // Обновляем контент для секции organization-setup в главе 1
        $this->update('{{%docs_section}}', [
            'content' => $this->getOrganizationSetupContent(),
        ], ['chapter_id' => 1, 'slug' => 'organization-setup']);
    }

    public function safeDown()
    {
        // Возвращаем старый контент
        $this->update('{{%docs_section}}', [
            'content' => $this->getOldContent(),
        ], ['chapter_id' => 1, 'slug' => 'organization-setup']);
    }

    private function getOrganizationSetupContent(): string
    {
        return <<<HTML
<h2 id="setup">Настройка организации</h2>
<p>После первого входа рекомендуется настроить базовые параметры организации для корректной работы системы.</p>

<figure class="my-6">
    <img src="/images/docs/settings/organization-settings.png"
         alt="Настройки организации"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Страница настроек организации
    </figcaption>
</figure>

<h3 id="how-to-open">Как открыть настройки</h3>
<ol>
    <li>В левом меню раскройте раздел <strong>«Управление»</strong></li>
    <li>Нажмите <strong>«Настройки»</strong></li>
    <li>Откроется страница с вкладками: <strong>Организация</strong> и <strong>Права доступа</strong></li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Автосохранение</div>
            <div class="text-blue-700 text-sm mt-1">Все изменения сохраняются автоматически при редактировании каждого поля. Вы увидите индикатор «Сохранено» в правом верхнем углу.</div>
        </div>
    </div>
</div>

<h3 id="basic-settings">Основные данные</h3>
<p>Первая группа настроек содержит базовую информацию об организации:</p>

<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Поле</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Логотип</strong></td><td class="border p-2">Загрузите изображение для брендирования. Рекомендуемый размер: 200x200 px. Форматы: PNG, JPG, GIF, WebP. Максимум 2MB.</td></tr>
        <tr><td class="border p-2"><strong>Название организации</strong></td><td class="border p-2">Отображается в интерфейсе, документах и уведомлениях</td></tr>
        <tr><td class="border p-2"><strong>Юридическое название</strong></td><td class="border p-2">Полное юридическое название для договоров и официальных документов</td></tr>
        <tr><td class="border p-2"><strong>БИН</strong></td><td class="border p-2">Бизнес-идентификационный номер организации (12 цифр)</td></tr>
    </tbody>
</table>

<h3 id="contact-settings">Контактные данные</h3>
<p>Вторая группа настроек содержит контактную информацию:</p>

<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Поле</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Телефон</strong></td><td class="border p-2">Основной номер для связи с организацией</td></tr>
        <tr><td class="border p-2"><strong>Email</strong></td><td class="border p-2">Email для уведомлений и связи</td></tr>
        <tr><td class="border p-2"><strong>Адрес</strong></td><td class="border p-2">Физический адрес организации</td></tr>
        <tr><td class="border p-2"><strong>Instagram</strong></td><td class="border p-2">Ссылка или имя аккаунта (без @)</td></tr>
        <tr><td class="border p-2"><strong>WhatsApp</strong></td><td class="border p-2">Номер телефона для WhatsApp</td></tr>
        <tr><td class="border p-2"><strong>Telegram</strong></td><td class="border p-2">Ссылка на канал или username</td></tr>
    </tbody>
</table>

<h3 id="regional-settings">Региональные настройки</h3>
<p>Третья группа настроек определяет формат отображения данных:</p>

<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Поле</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Часовой пояс</strong></td><td class="border p-2">Влияет на отображение времени занятий в расписании</td></tr>
        <tr><td class="border p-2"><strong>Язык интерфейса</strong></td><td class="border p-2">Язык по умолчанию для новых пользователей (Русский, Қазақша, English)</td></tr>
        <tr><td class="border p-2"><strong>Валюта</strong></td><td class="border p-2">Валюта для отображения цен (Тенге, Рубль, Доллар)</td></tr>
        <tr><td class="border p-2"><strong>Формат даты</strong></td><td class="border p-2">Как отображаются даты в системе (31.12.2024 или 2024-12-31)</td></tr>
    </tbody>
</table>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно: Часовой пояс</div>
            <div class="text-yellow-700 text-sm mt-1">Правильный часовой пояс критически важен для корректного отображения времени занятий. Убедитесь, что выбран пояс вашего города.</div>
        </div>
    </div>
</div>

<h3 id="schedule-settings">Рабочее время</h3>
<p>Четвёртая группа настроек определяет график работы организации:</p>

<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Поле</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Начало рабочего дня</strong></td><td class="border p-2">Время открытия организации (например, 09:00)</td></tr>
        <tr><td class="border p-2"><strong>Конец рабочего дня</strong></td><td class="border p-2">Время закрытия организации (например, 18:00)</td></tr>
        <tr><td class="border p-2"><strong>Рабочие дни</strong></td><td class="border p-2">Выберите дни недели, когда организация работает</td></tr>
        <tr><td class="border p-2"><strong>Первый день недели</strong></td><td class="border p-2">Определяет, с какого дня начинается неделя в календаре (Понедельник или Воскресенье)</td></tr>
    </tbody>
</table>

<h3 id="lesson-settings">Настройки занятий</h3>
<p>Пятая группа настроек содержит параметры по умолчанию для занятий:</p>

<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Поле</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Продолжительность по умолчанию</strong></td><td class="border p-2">Стандартная длительность занятия при создании (30, 45, 60, 90 или 120 минут)</td></tr>
        <tr><td class="border p-2"><strong>Автоматическое списание</strong></td><td class="border p-2">Если включено — оплата за занятие автоматически списывается с баланса ученика после отметки посещения</td></tr>
        <tr><td class="border p-2"><strong>Уведомления о занятиях</strong></td><td class="border p-2">Если включено — ученики и родители получают напоминания о предстоящих занятиях</td></tr>
    </tbody>
</table>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Рекомендация</div>
            <div class="text-green-700 text-sm mt-1">Включите автоматическое списание, чтобы система сама рассчитывала баланс учеников после каждого занятия. Это значительно упростит финансовый учёт.</div>
        </div>
    </div>
</div>

<h3 id="access-settings">Права доступа</h3>
<p>Вторая вкладка <strong>«Права доступа»</strong> позволяет настроить, какие действия могут выполнять администраторы и преподаватели:</p>

<figure class="my-6">
    <img src="/images/docs/settings/access-settings.png"
         alt="Настройки прав доступа"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Страница настроек прав доступа
    </figcaption>
</figure>

<h4>Права администратора:</h4>
<ul>
    <li><strong>Редактирование платежей</strong> — может ли админ редактировать платежи напрямую</li>
    <li><strong>Удаление платежей</strong> — может ли админ удалять платежи</li>
    <li><strong>Удаление учеников</strong> — может ли админ удалять учеников</li>
    <li><strong>Просмотр баланса</strong> — может ли админ видеть баланс учеников</li>
    <li><strong>Просмотр зарплат</strong> — может ли админ видеть зарплаты преподавателей</li>
</ul>

<h4>Права преподавателя:</h4>
<ul>
    <li><strong>Создание занятий</strong> — может ли преподаватель создавать занятия</li>
    <li><strong>Редактирование занятий</strong> — может ли преподаватель редактировать свои занятия</li>
    <li><strong>Удаление занятий</strong> — может ли преподаватель удалять свои занятия</li>
    <li><strong>Просмотр контактов</strong> — может ли преподаватель видеть контакты учеников</li>
    <li><strong>Просмотр своей зарплаты</strong> — может ли преподаватель видеть свою зарплату</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Права директора</div>
            <div class="text-blue-700 text-sm mt-1">Директор и генеральный директор всегда имеют полный доступ ко всем функциям. Эти настройки применяются только к администраторам и преподавателям.</div>
        </div>
    </div>
</div>

<h3 id="next-steps">Следующие шаги</h3>
<p>После настройки организации рекомендуем:</p>
<ol>
    <li><a href="/docs/getting-started/initial-setup">Настроить справочники</a> — предметы, тарифы, кабинеты</li>
    <li>Добавить сотрудников (преподавателей)</li>
    <li>Создать учебные группы</li>
    <li>Добавить учеников</li>
</ol>
HTML;
    }

    private function getOldContent(): string
    {
        return <<<HTML
<h2 id="setup">Настройка организации</h2>
<p>После первого входа рекомендуется настроить базовые параметры организации для корректной работы системы.</p>

<h3 id="basic-settings">Основные настройки</h3>
<p>Перейдите в раздел <strong>Настройки → Организация</strong> для редактирования:</p>
<ul>
    <li><strong>Название организации</strong> — отображается в интерфейсе и документах</li>
    <li><strong>Логотип</strong> — загрузите изображение для брендирования</li>
    <li><strong>Контактные данные</strong> — телефон, адрес, email для связи</li>
    <li><strong>Часовой пояс</strong> — важно для корректного отображения времени занятий</li>
</ul>

<h3 id="subjects-setup">Настройка предметов</h3>
<p>Добавьте предметы, которые преподаются в вашей организации:</p>
<ol>
    <li>Перейдите в <strong>Настройки → Предметы</strong></li>
    <li>Нажмите <strong>«Добавить предмет»</strong></li>
    <li>Введите название предмета</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="tariffs-setup">Настройка тарифов</h3>
<p>Создайте тарифные планы для оплаты обучения:</p>
<ol>
    <li>Перейдите в <strong>Настройки → Тарифы</strong></li>
    <li>Нажмите <strong>«Добавить тариф»</strong></li>
    <li>Укажите название, стоимость и количество занятий</li>
    <li>Выберите предметы, к которым применяется тариф</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Рекомендация</div>
            <div class="text-green-700 text-sm mt-1">Создайте несколько тарифов с разным количеством занятий — это упростит работу с платежами и позволит предложить клиентам гибкие условия.</div>
        </div>
    </div>
</div>

<h3 id="rooms-setup">Настройка кабинетов</h3>
<p>Добавьте кабинеты для проведения занятий:</p>
<ol>
    <li>Перейдите в <strong>Настройки → Кабинеты</strong></li>
    <li>Нажмите <strong>«Добавить кабинет»</strong></li>
    <li>Введите название и выберите цвет для календаря</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="payment-methods">Способы оплаты</h3>
<p>Настройте доступные способы приёма платежей:</p>
<ol>
    <li>Перейдите в <strong>Настройки → Способы оплаты</strong></li>
    <li>Активируйте нужные методы: наличные, карта, перевод</li>
    <li>При необходимости добавьте свои способы оплаты</li>
</ol>
HTML;
    }
}
