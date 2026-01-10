<?php

use yii\db\Migration;

/**
 * Добавляет контент документации для глав 10-11
 */
class m260110_190003_add_docs_content_chapters_10_11 extends Migration
{
    public function safeUp()
    {
        // Глава 10: whatsapp-chats
        $this->update('{{%docs_section}}', [
            'content' => $this->getWhatsappChatsContent(),
        ], ['chapter_id' => 10, 'slug' => 'whatsapp-chats']);

        // Глава 10: whatsapp-notifications
        $this->update('{{%docs_section}}', [
            'content' => $this->getWhatsappNotificationsContent(),
        ], ['chapter_id' => 10, 'slug' => 'whatsapp-notifications']);

        // Глава 11: rooms
        $this->update('{{%docs_section}}', [
            'content' => $this->getRoomsContent(),
        ], ['chapter_id' => 11, 'slug' => 'rooms']);

        // Глава 11: payment-methods-settings
        $this->update('{{%docs_section}}', [
            'content' => $this->getPaymentMethodsSettingsContent(),
        ], ['chapter_id' => 11, 'slug' => 'payment-methods-settings']);

        // Глава 11: access-rights
        $this->update('{{%docs_section}}', [
            'content' => $this->getAccessRightsContent(),
        ], ['chapter_id' => 11, 'slug' => 'access-rights']);
    }

    public function safeDown()
    {
        $sections = [
            ['chapter_id' => 10, 'slug' => 'whatsapp-chats'],
            ['chapter_id' => 10, 'slug' => 'whatsapp-notifications'],
            ['chapter_id' => 11, 'slug' => 'rooms'],
            ['chapter_id' => 11, 'slug' => 'payment-methods-settings'],
            ['chapter_id' => 11, 'slug' => 'access-rights'],
        ];
        foreach ($sections as $s) {
            $this->update('{{%docs_section}}', ['content' => null], $s);
        }
    }

    private function getWhatsappChatsContent(): string
    {
        return <<<HTML
<h2 id="chats">Работа с чатами WhatsApp</h2>
<p>После подключения WhatsApp вы можете общаться с клиентами прямо из CRM.</p>

<h3 id="open-chats">Открытие чатов</h3>
<ol>
    <li>Перейдите в раздел <strong>WhatsApp</strong></li>
    <li>Нажмите <strong>«Чаты»</strong></li>
    <li>Отобразится список всех диалогов</li>
</ol>

<h3 id="chat-list">Список чатов</h3>
<p>Для каждого чата отображается:</p>
<ul>
    <li><strong>Имя контакта</strong> — из телефонной книги WhatsApp</li>
    <li><strong>Последнее сообщение</strong> — превью текста</li>
    <li><strong>Время</strong> — когда было последнее сообщение</li>
    <li><strong>Непрочитанные</strong> — счётчик новых сообщений</li>
    <li><strong>Привязка к лиду</strong> — если чат связан с лидом</li>
</ul>

<h3 id="send-message">Отправка сообщения</h3>
<ol>
    <li>Выберите чат из списка</li>
    <li>Введите текст сообщения</li>
    <li>Нажмите <strong>Enter</strong> или кнопку отправки</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-paperclip text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Отправка файлов</div>
            <div class="text-blue-700 text-sm mt-1">Можно отправлять изображения, документы и аудиосообщения. Нажмите на иконку скрепки для выбора файла.</div>
        </div>
    </div>
</div>

<h3 id="link-to-lead">Привязка чата к лиду</h3>
<ol>
    <li>Откройте чат</li>
    <li>Нажмите <strong>«Привязать к лиду»</strong></li>
    <li>Выберите существующего лида или создайте нового</li>
</ol>

<h3 id="create-lead-from-chat">Создание лида из чата</h3>
<ol>
    <li>В чате нажмите <strong>«Создать лида»</strong></li>
    <li>Данные (телефон, имя) заполнятся автоматически</li>
    <li>Дополните информацию и сохраните</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-link text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Связанная история</div>
            <div class="text-green-700 text-sm mt-1">После привязки все сообщения отображаются в истории взаимодействий с лидом.</div>
        </div>
    </div>
</div>

<h3 id="search-chats">Поиск в чатах</h3>
<p>Используйте поиск для быстрого нахождения нужного диалога по имени или номеру телефона.</p>
HTML;
    }

    private function getWhatsappNotificationsContent(): string
    {
        return <<<HTML
<h2 id="notifications">Уведомления WhatsApp</h2>
<p>Система может автоматически отправлять уведомления клиентам через WhatsApp.</p>

<h3 id="notification-types">Типы уведомлений</h3>
<ul>
    <li><strong>Напоминание о занятии</strong> — за день или за час до урока</li>
    <li><strong>Информация о балансе</strong> — при низком или отрицательном балансе</li>
    <li><strong>Пропуск занятия</strong> — уведомление родителям об отсутствии</li>
    <li><strong>Подтверждение оплаты</strong> — после приёма платежа</li>
</ul>

<h3 id="setup-notifications">Настройка уведомлений</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Уведомления</strong></li>
    <li>Включите нужные типы уведомлений</li>
    <li>Настройте время отправки напоминаний</li>
    <li>Сохраните настройки</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Требуется подключение</div>
            <div class="text-yellow-700 text-sm mt-1">Для отправки уведомлений WhatsApp должен быть подключён и активен. Проверьте статус подключения в разделе WhatsApp.</div>
        </div>
    </div>
</div>

<h3 id="templates">Шаблоны сообщений</h3>
<p>Можно настроить тексты уведомлений:</p>
<ul>
    <li>Используйте переменные: {имя}, {дата}, {время}, {группа}</li>
    <li>Переменные автоматически заменяются на реальные данные</li>
</ul>

<h3 id="manual-send">Ручная отправка</h3>
<p>Помимо автоматических, можно отправлять сообщения вручную:</p>
<ol>
    <li>Откройте карточку ученика или лида</li>
    <li>Нажмите на иконку WhatsApp</li>
    <li>Напишите сообщение</li>
    <li>Отправьте</li>
</ol>

<h3 id="delivery-status">Статус доставки</h3>
<p>Для каждого сообщения отображается статус:</p>
<ul>
    <li><strong>Отправлено</strong> — сообщение ушло с сервера</li>
    <li><strong>Доставлено</strong> — получено устройством клиента</li>
    <li><strong>Прочитано</strong> — клиент открыл сообщение</li>
</ul>
HTML;
    }

    private function getRoomsContent(): string
    {
        return <<<HTML
<h2 id="rooms">Управление кабинетами</h2>
<p>Кабинеты (аудитории) используются для планирования занятий и контроля загрузки помещений.</p>

<h3 id="open-rooms">Открытие списка кабинетов</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Кабинеты</strong></li>
    <li>Отобразится список всех помещений</li>
</ol>

<h3 id="create-room">Добавление кабинета</h3>
<ol>
    <li>Нажмите <strong>«Добавить кабинет»</strong></li>
    <li>Введите название (например, «Кабинет 101» или «Актовый зал»)</li>
    <li>Выберите цвет для отображения в календаре</li>
    <li>При необходимости добавьте описание</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="room-color">Цвет кабинета</h3>
<p>Цвет помогает быстро различать кабинеты в расписании:</p>
<ul>
    <li>Занятия в кабинете окрашиваются в выбранный цвет</li>
    <li>Удобно при работе с несколькими помещениями</li>
    <li>Рекомендуется выбирать контрастные цвета</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-palette text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Палитра цветов</div>
            <div class="text-blue-700 text-sm mt-1">Доступно 12 предустановленных цветов. Выбирайте контрастные оттенки для разных кабинетов.</div>
        </div>
    </div>
</div>

<h3 id="edit-room">Редактирование кабинета</h3>
<ol>
    <li>Найдите кабинет в списке</li>
    <li>Нажмите <strong>«Редактировать»</strong></li>
    <li>Измените данные</li>
    <li>Сохраните изменения</li>
</ol>

<h3 id="delete-room">Удаление кабинета</h3>
<ol>
    <li>Нажмите <strong>«Удалить»</strong> напротив кабинета</li>
    <li>Подтвердите действие</li>
</ol>
<p>Ранее созданные занятия в этом кабинете сохранятся.</p>

<h3 id="sort-rooms">Сортировка</h3>
<p>Порядок кабинетов можно изменить перетаскиванием. Этот порядок используется в выпадающих списках.</p>
HTML;
    }

    private function getPaymentMethodsSettingsContent(): string
    {
        return <<<HTML
<h2 id="payment-settings">Настройка способов оплаты</h2>
<p>Настройте доступные способы приёма платежей в вашей организации.</p>

<h3 id="open-settings">Открытие настроек</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Способы оплаты</strong></li>
    <li>Отобразится список всех способов</li>
</ol>

<h3 id="default-methods">Стандартные способы</h3>
<p>По умолчанию доступны:</p>
<ul>
    <li><strong>Наличные</strong> — оплата наличными деньгами</li>
    <li><strong>Банковская карта</strong> — оплата через терминал</li>
    <li><strong>Банковский перевод</strong> — на расчётный счёт</li>
    <li><strong>Kaspi перевод</strong> — через Kaspi Bank</li>
</ul>

<h3 id="add-method">Добавление способа</h3>
<ol>
    <li>Нажмите <strong>«Добавить способ»</strong></li>
    <li>Введите название</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Примеры</div>
            <div class="text-green-700 text-sm mt-1">Halyk Bank, Jusan Pay, оплата сертификатом, рассрочка — добавьте любые способы, актуальные для вашей организации.</div>
        </div>
    </div>
</div>

<h3 id="toggle-method">Включение/отключение</h3>
<p>Неиспользуемые способы можно отключить:</p>
<ol>
    <li>Найдите способ в списке</li>
    <li>Переключите тумблер «Активен»</li>
</ol>
<p>Отключённые способы не отображаются при приёме платежей.</p>

<h3 id="edit-method">Редактирование</h3>
<ol>
    <li>Нажмите на название способа</li>
    <li>Измените название</li>
    <li>Сохраните</li>
</ol>

<h3 id="delete-method">Удаление</h3>
<p>Удалить можно только способы, которые не использовались в платежах. Если способ уже использовался, его можно только отключить.</p>
HTML;
    }

    private function getAccessRightsContent(): string
    {
        return <<<HTML
<h2 id="access">Настройка прав доступа</h2>
<p>Права доступа определяют, какие действия доступны пользователям с разными ролями.</p>

<h3 id="roles-overview">Обзор ролей</h3>
<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Роль</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="border p-2 font-medium">Генеральный директор</td>
            <td class="border p-2">Полный доступ. Может всё, включая настройки организации и управление ролями.</td>
        </tr>
        <tr>
            <td class="border p-2 font-medium">Директор</td>
            <td class="border p-2">Управление персоналом, финансами, утверждение зарплат. Нет доступа к настройкам организации.</td>
        </tr>
        <tr>
            <td class="border p-2 font-medium">Администратор</td>
            <td class="border p-2">Работа с учениками, группами, расписанием, платежами. Ограниченный доступ к финансам.</td>
        </tr>
        <tr>
            <td class="border p-2 font-medium">Преподаватель</td>
            <td class="border p-2">Только свои группы: расписание, посещаемость. Нет доступа к финансам и другим группам.</td>
        </tr>
    </tbody>
</table>

<h3 id="detailed-permissions">Детальные права</h3>
<p>Для тонкой настройки доступны дополнительные параметры:</p>

<h4>Ученики</h4>
<ul>
    <li>Просмотр списка учеников</li>
    <li>Создание учеников</li>
    <li>Редактирование учеников</li>
    <li>Удаление учеников</li>
</ul>

<h4>Платежи</h4>
<ul>
    <li>Просмотр платежей</li>
    <li>Приём платежей</li>
    <li>Редактирование платежей</li>
    <li>Удаление платежей</li>
    <li>Необходимость подтверждения изменений</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-shield-alt text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Безопасность</div>
            <div class="text-yellow-700 text-sm mt-1">Рекомендуется включить подтверждение для редактирования и удаления платежей. Это предотвращает случайные или несанкционированные изменения.</div>
        </div>
    </div>
</div>

<h3 id="approval-workflow">Система подтверждений</h3>
<p>Для критичных операций можно включить подтверждение директором:</p>
<ul>
    <li><strong>Редактирование платежа</strong> — администратор отправляет запрос, директор подтверждает</li>
    <li><strong>Удаление платежа</strong> — требуется одобрение директора</li>
</ul>

<h3 id="configure-rights">Настройка прав</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Права доступа</strong></li>
    <li>Выберите роль для настройки</li>
    <li>Отметьте нужные разрешения</li>
    <li>Сохраните изменения</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Мгновенное применение</div>
            <div class="text-blue-700 text-sm mt-1">Изменения прав применяются сразу. Сотрудникам не нужно перезаходить в систему.</div>
        </div>
    </div>
</div>
HTML;
    }
}
