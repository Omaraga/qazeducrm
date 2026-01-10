<?php

use yii\db\Migration;

/**
 * Миграция для исправления slugs в контенте документации
 */
class m260110_180000_fix_docs_slugs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Глава 4: Сотрудники - исправляем slug add-employee -> add-staff
        $this->update('{{%docs_section}}', [
            'content' => $this->getAddStaffContent(),
        ], ['chapter_id' => 4, 'slug' => 'add-staff']);

        // Глава 6: Посещаемость - исправляем slug marking-attendance -> mark-attendance
        $this->update('{{%docs_section}}', [
            'content' => $this->getMarkAttendanceContent(),
        ], ['chapter_id' => 6, 'slug' => 'mark-attendance']);

        // Глава 8: Зарплаты - исправляем slug salary-calculation -> calculate-salary
        $this->update('{{%docs_section}}', [
            'content' => $this->getSalaryContent(),
        ], ['chapter_id' => 8, 'slug' => 'calculate-salary']);

        // Глава 10: WhatsApp - исправляем slug connection -> connect-whatsapp
        $this->update('{{%docs_section}}', [
            'content' => $this->getWhatsappContent(),
        ], ['chapter_id' => 10, 'slug' => 'connect-whatsapp']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    protected function getAddStaffContent()
    {
        return <<<'HTML'
<p>Сотрудники — это преподаватели и администраторы вашего учебного центра. Добавление сотрудника позволяет назначать его на группы и занятия.</p>

<h2 id="list">Список сотрудников</h2>

<figure class="my-6">
    <img src="/images/docs/employees/employees-list.png" alt="Список сотрудников" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком сотрудников</figcaption>
</figure>

<h2 id="create">Добавление сотрудника</h2>

<ol>
    <li>Перейдите в раздел <strong>«Сотрудники»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Добавить сотрудника»</strong></li>
</ol>

<figure class="my-6">
    <img src="/images/docs/employees/employee-create.png" alt="Форма добавления сотрудника" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма добавления нового сотрудника</figcaption>
</figure>

<h3 id="personal">Личные данные</h3>

<ul>
    <li><strong>ФИО</strong> — полное имя сотрудника</li>
    <li><strong>Email</strong> — для входа в систему и уведомлений</li>
    <li><strong>Телефон</strong> — контактный номер</li>
    <li><strong>Должность</strong> — преподаватель, администратор и т.д.</li>
</ul>

<h3 id="role">Роль в системе</h3>

<ul>
    <li><strong>Директор</strong> — полный доступ ко всем функциям</li>
    <li><strong>Администратор</strong> — управление учениками, группами, платежами</li>
    <li><strong>Преподаватель</strong> — доступ к своим группам и расписанию</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Приглашение</div>
            <div class="text-blue-700 text-sm">После создания сотрудник получит email с приглашением и ссылкой для установки пароля.</div>
        </div>
    </div>
</div>

<h2 id="view">Карточка сотрудника</h2>

<figure class="my-6">
    <img src="/images/docs/employees/employee-view.png" alt="Карточка сотрудника" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Детальная карточка сотрудника</figcaption>
</figure>

<h2 id="salary-settings">Настройка ставок</h2>

<p>Для расчёта зарплаты настройте ставки преподавателя:</p>

<ol>
    <li>Откройте карточку сотрудника</li>
    <li>Перейдите на вкладку <strong>«Ставки»</strong></li>
    <li>Укажите ставку за занятие или процент от оплаты</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Совет</div>
            <div class="text-green-700 text-sm">Ставки можно настроить индивидуально для каждой группы — это удобно, если оплата различается по предметам.</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function getMarkAttendanceContent()
    {
        return <<<'HTML'
<p>Отметка посещаемости — важная функция для учёта посещений учеников и расчёта статистики.</p>

<h2 id="page">Страница посещаемости</h2>

<figure class="my-6">
    <img src="/images/docs/attendance/attendance-page.png" alt="Страница посещаемости" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница отметки посещаемости</figcaption>
</figure>

<h2 id="access">Доступ к отметке</h2>

<p>Отметить посещаемость можно несколькими способами:</p>

<ol>
    <li><strong>Из календаря</strong> — кликните на занятие и выберите «Отметить посещаемость»</li>
    <li><strong>Из раздела «Посещаемость»</strong> — найдите занятие в списке</li>
</ol>

<h2 id="statuses">Статусы посещения</h2>

<p>Для каждого ученика доступны следующие статусы:</p>

<ul>
    <li><strong class="text-green-600">Присутствует</strong> — ученик был на занятии</li>
    <li><strong class="text-yellow-600">Опоздал</strong> — ученик пришёл с опозданием</li>
    <li><strong class="text-red-600">Отсутствует</strong> — ученик не пришёл</li>
    <li><strong class="text-blue-600">Уважительная причина</strong> — отсутствие по уважительной причине</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Автоматическое списание</div>
            <div class="text-blue-700 text-sm">При отметке «Присутствует» с баланса ученика автоматически списывается стоимость занятия согласно тарифу.</div>
        </div>
    </div>
</div>

<h2 id="marking">Процесс отметки</h2>

<ol>
    <li>Откройте занятие для отметки посещаемости</li>
    <li>Для каждого ученика нажмите на соответствующий статус</li>
    <li>При необходимости добавьте комментарий</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Быстрая отметка</div>
            <div class="text-green-700 text-sm">Используйте кнопку «Отметить всех присутствующими» для быстрой отметки, если все ученики на месте.</div>
        </div>
    </div>
</div>

<h2 id="notifications">Уведомления родителям</h2>

<p>При отметке отсутствия система может автоматически отправить уведомление родителям через:</p>

<ul>
    <li>WhatsApp (если подключен)</li>
    <li>Email</li>
    <li>SMS (если настроено)</li>
</ul>

<h2 id="reports">Отчёты по посещаемости</h2>

<p>В разделе «Отчёты» доступна статистика посещаемости:</p>

<ul>
    <li>По ученикам — процент посещений каждого ученика</li>
    <li>По группам — общая посещаемость группы</li>
    <li>По периодам — динамика посещаемости за месяц/квартал</li>
</ul>
HTML;
    }

    protected function getSalaryContent()
    {
        return <<<'HTML'
<p>Расчёт зарплаты преподавателей основан на проведённых занятиях и настроенных ставках.</p>

<h2 id="list">Список зарплат</h2>

<figure class="my-6">
    <img src="/images/docs/salary/salary-list.png" alt="Список зарплат" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком зарплат преподавателей</figcaption>
</figure>

<h2 id="calculation">Расчёт зарплаты</h2>

<figure class="my-6">
    <img src="/images/docs/salary/salary-calculate.png" alt="Расчёт зарплаты" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма расчёта зарплаты</figcaption>
</figure>

<ol>
    <li>Перейдите в раздел <strong>«Зарплаты»</strong></li>
    <li>Нажмите <strong>«Рассчитать зарплату»</strong></li>
    <li>Выберите период (месяц)</li>
    <li>Выберите преподавателя или всех</li>
    <li>Нажмите <strong>«Рассчитать»</strong></li>
</ol>

<h2 id="salary-types">Типы расчёта</h2>

<p>Система поддерживает несколько схем оплаты:</p>

<ul>
    <li><strong>Фиксированная ставка</strong> — фиксированная сумма за занятие</li>
    <li><strong>Процент от оплаты</strong> — процент от оплаченных учениками сумм</li>
    <li><strong>Почасовая ставка</strong> — оплата за час работы</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Автоматический расчёт</div>
            <div class="text-blue-700 text-sm">Система автоматически учитывает только проведённые занятия с отмеченной посещаемостью.</div>
        </div>
    </div>
</div>

<h2 id="details">Детализация</h2>

<p>В расчёте показано:</p>

<ul>
    <li>Количество проведённых занятий</li>
    <li>Количество учеников на занятиях</li>
    <li>Ставка за занятие/час</li>
    <li>Итоговая сумма к выплате</li>
</ul>

<h2 id="payment">Выплата зарплаты</h2>

<ol>
    <li>Проверьте расчёт</li>
    <li>Нажмите <strong>«Провести выплату»</strong></li>
    <li>Укажите способ выплаты</li>
    <li>Подтвердите операцию</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">История выплат</div>
            <div class="text-green-700 text-sm">Все выплаты сохраняются в истории. Вы всегда можете посмотреть, когда и сколько было выплачено преподавателю.</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function getWhatsappContent()
    {
        return <<<'HTML'
<p>Интеграция с WhatsApp позволяет общаться с учениками и родителями прямо из CRM, отправлять уведомления и вести историю переписки.</p>

<h2 id="page">Страница WhatsApp</h2>

<figure class="my-6">
    <img src="/images/docs/whatsapp/whatsapp-index.png" alt="Страница WhatsApp" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Главная страница интеграции WhatsApp</figcaption>
</figure>

<h2 id="connection">Подключение WhatsApp</h2>

<ol>
    <li>Перейдите в раздел <strong>«WhatsApp»</strong></li>
    <li>Нажмите <strong>«Подключить WhatsApp»</strong></li>
    <li>Отсканируйте QR-код с помощью приложения WhatsApp на телефоне</li>
    <li>После успешного сканирования статус изменится на «Подключено»</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Важно</div>
            <div class="text-blue-700 text-sm">Для подключения используйте номер телефона организации. Рекомендуем использовать отдельный номер для CRM.</div>
        </div>
    </div>
</div>

<h2 id="chats">Чаты</h2>

<figure class="my-6">
    <img src="/images/docs/whatsapp/whatsapp-chats.png" alt="Список чатов WhatsApp" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Список чатов WhatsApp</figcaption>
</figure>

<p>В разделе чатов вы можете:</p>

<ul>
    <li>Просматривать все входящие сообщения</li>
    <li>Отвечать на сообщения</li>
    <li>Видеть, с каким учеником/лидом связан чат</li>
    <li>Искать по истории переписки</li>
</ul>

<h2 id="notifications">Автоматические уведомления</h2>

<p>Система может автоматически отправлять сообщения:</p>

<ul>
    <li><strong>Напоминание о занятии</strong> — за час до занятия</li>
    <li><strong>Уведомление об отсутствии</strong> — если ученик пропустил занятие</li>
    <li><strong>Напоминание об оплате</strong> — при низком балансе</li>
    <li><strong>Поздравления</strong> — с днём рождения</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Шаблоны</div>
            <div class="text-green-700 text-sm">Настройте шаблоны сообщений в разделе «Настройки» → «WhatsApp» для персонализации уведомлений.</div>
        </div>
    </div>
</div>

<h2 id="troubleshooting">Решение проблем</h2>

<p>Если WhatsApp отключился:</p>

<ol>
    <li>Проверьте, что телефон с WhatsApp включён и подключён к интернету</li>
    <li>Откройте WhatsApp на телефоне и убедитесь, что связанные устройства активны</li>
    <li>При необходимости повторно отсканируйте QR-код</li>
</ol>
HTML;
    }
}
