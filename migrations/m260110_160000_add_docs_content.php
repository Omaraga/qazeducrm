<?php

use yii\db\Migration;

/**
 * Миграция для добавления начального контента в документацию
 */
class m260110_160000_add_docs_content extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Глава 1: Начало работы - Регистрация организации
        $this->update('{{%docs_section}}', [
            'content' => $this->getRegistrationContent(),
        ], ['chapter_id' => 1, 'slug' => 'registration']);

        // Глава 1: Первый вход в систему
        $this->update('{{%docs_section}}', [
            'content' => $this->getFirstLoginContent(),
        ], ['chapter_id' => 1, 'slug' => 'first-login']);

        // Глава 1: Обзор интерфейса
        $this->update('{{%docs_section}}', [
            'content' => $this->getInterfaceOverviewContent(),
        ], ['chapter_id' => 1, 'slug' => 'interface-overview']);

        // Глава 2: Добавление ученика
        $this->update('{{%docs_section}}', [
            'content' => $this->getAddPupilContent(),
        ], ['chapter_id' => 2, 'slug' => 'add-pupil']);

        // Глава 3: Создание группы
        $this->update('{{%docs_section}}', [
            'content' => $this->getCreateGroupContent(),
        ], ['chapter_id' => 3, 'slug' => 'create-group']);

        // Глава 5: Обзор календаря
        $this->update('{{%docs_section}}', [
            'content' => $this->getCalendarOverviewContent(),
        ], ['chapter_id' => 5, 'slug' => 'calendar-overview']);

        // Глава 7: Приём платежа
        $this->update('{{%docs_section}}', [
            'content' => $this->getReceivePaymentContent(),
        ], ['chapter_id' => 7, 'slug' => 'receive-payment']);

        // Глава 9: Kanban-доска
        $this->update('{{%docs_section}}', [
            'content' => $this->getKanbanContent(),
        ], ['chapter_id' => 9, 'slug' => 'kanban-board']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Очищаем контент
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => [1, 2, 3, 5, 7, 9]]);
    }

    protected function getRegistrationContent()
    {
        return <<<'HTML'
<p>Для начала работы с системой QazEduCRM необходимо зарегистрировать вашу организацию. Процесс регистрации занимает всего несколько минут.</p>

<h2 id="start">Начало регистрации</h2>

<p>Перейдите на главную страницу системы и нажмите кнопку <strong>«Попробовать бесплатно»</strong> или <strong>«Регистрация»</strong>.</p>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Информация</div>
            <div class="text-blue-700 text-sm">Первые 14 дней использования системы — бесплатно. Банковская карта не требуется.</div>
        </div>
    </div>
</div>

<h2 id="fill-form">Заполнение формы регистрации</h2>

<p>В форме регистрации необходимо указать:</p>

<ol>
    <li><strong>Название организации</strong> — официальное название вашего учебного центра</li>
    <li><strong>Email</strong> — электронная почта для входа и получения уведомлений</li>
    <li><strong>Телефон</strong> — контактный номер телефона</li>
    <li><strong>Пароль</strong> — придумайте надёжный пароль (минимум 6 символов)</li>
    <li><strong>Тарифный план</strong> — выберите подходящий тариф</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-semibold text-yellow-800">Важно</div>
            <div class="text-yellow-700 text-sm">Используйте реальный email — на него придёт письмо для подтверждения регистрации.</div>
        </div>
    </div>
</div>

<h2 id="confirm">Подтверждение email</h2>

<p>После заполнения формы:</p>

<ol>
    <li>Нажмите кнопку <strong>«Зарегистрироваться»</strong></li>
    <li>Проверьте вашу электронную почту</li>
    <li>Найдите письмо от QazEduCRM и нажмите на ссылку подтверждения</li>
    <li>После подтверждения email ваша заявка будет рассмотрена администратором</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Совет</div>
            <div class="text-green-700 text-sm">Если письмо не пришло, проверьте папку «Спам» или «Промоакции».</div>
        </div>
    </div>
</div>

<h2 id="next-steps">Следующие шаги</h2>

<p>После одобрения заявки вы получите уведомление на email и сможете войти в систему. Далее рекомендуем:</p>

<ul>
    <li>Настроить основные параметры организации</li>
    <li>Добавить предметы и тарифы</li>
    <li>Создать учебные группы</li>
    <li>Добавить преподавателей и учеников</li>
</ul>
HTML;
    }

    protected function getFirstLoginContent()
    {
        return <<<'HTML'
<p>После подтверждения регистрации вы можете войти в систему и начать работу.</p>

<h2 id="login">Вход в систему</h2>

<ol>
    <li>Перейдите на страницу входа: нажмите <strong>«Войти»</strong> на главной странице</li>
    <li>Введите ваш <strong>Email</strong>, указанный при регистрации</li>
    <li>Введите <strong>пароль</strong></li>
    <li>Нажмите кнопку <strong>«Войти»</strong></li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Автоматический вход</div>
            <div class="text-blue-700 text-sm">Система запоминает ваш вход на 30 дней. Для выхода используйте кнопку «Выйти» в меню.</div>
        </div>
    </div>
</div>

<h2 id="dashboard">Главная страница (Dashboard)</h2>

<p>После входа вы попадаете на главную страницу CRM — <strong>Dashboard</strong>. Здесь отображается:</p>

<ul>
    <li><strong>Статистика</strong> — количество учеников, групп, платежей</li>
    <li><strong>Ближайшие занятия</strong> — расписание на сегодня</li>
    <li><strong>Последние действия</strong> — история активности</li>
</ul>

<h2 id="password-reset">Восстановление пароля</h2>

<p>Если вы забыли пароль:</p>

<ol>
    <li>На странице входа нажмите <strong>«Забыли пароль?»</strong></li>
    <li>Введите email, указанный при регистрации</li>
    <li>Проверьте почту и перейдите по ссылке восстановления</li>
    <li>Придумайте новый пароль</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-semibold text-yellow-800">Безопасность</div>
            <div class="text-yellow-700 text-sm">Используйте надёжный пароль и не передавайте его третьим лицам. Рекомендуем менять пароль каждые 3 месяца.</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function getInterfaceOverviewContent()
    {
        return <<<'HTML'
<p>Интерфейс QazEduCRM состоит из нескольких основных областей, которые помогают быстро находить нужные функции.</p>

<h2 id="sidebar">Боковое меню (Sidebar)</h2>

<p>Слева находится главное меню навигации. Оно содержит разделы:</p>

<ul>
    <li><strong>Dashboard</strong> — главная страница со статистикой</li>
    <li><strong>Расписание</strong> — календарь занятий</li>
    <li><strong>Лиды</strong> — работа с потенциальными клиентами</li>
    <li><strong>Ученики</strong> — база учеников</li>
    <li><strong>Группы</strong> — учебные группы</li>
    <li><strong>Платежи</strong> — финансы и оплаты</li>
    <li><strong>Зарплаты</strong> — расчёт зарплат преподавателей</li>
    <li><strong>Настройки</strong> — параметры системы</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Совет</div>
            <div class="text-green-700 text-sm">На маленьких экранах меню можно свернуть, нажав на иконку «гамбургер» слева вверху.</div>
        </div>
    </div>
</div>

<h2 id="header">Верхняя панель</h2>

<p>В верхней части экрана расположены:</p>

<ul>
    <li><strong>Название организации</strong> — текущая организация</li>
    <li><strong>Профиль пользователя</strong> — меню с настройками аккаунта</li>
    <li><strong>Уведомления</strong> — системные оповещения</li>
</ul>

<h2 id="content-area">Рабочая область</h2>

<p>Центральная часть экрана — это рабочая область, где отображается содержимое выбранного раздела:</p>

<ul>
    <li>Списки данных (таблицы) с возможностью сортировки и фильтрации</li>
    <li>Формы создания и редактирования</li>
    <li>Карточки с детальной информацией</li>
    <li>Календарь и графики</li>
</ul>

<h2 id="navigation">Навигация по данным</h2>

<p>В большинстве разделов доступны:</p>

<ul>
    <li><strong>Поиск</strong> — быстрый поиск по имени, телефону, email</li>
    <li><strong>Фильтры</strong> — фильтрация по статусу, дате, группе</li>
    <li><strong>Сортировка</strong> — сортировка по столбцам таблицы</li>
    <li><strong>Пагинация</strong> — постраничная навигация</li>
</ul>
HTML;
    }

    protected function getAddPupilContent()
    {
        return <<<'HTML'
<p>Добавление нового ученика — одна из основных операций в системе. Следуйте инструкции ниже.</p>

<h2 id="open-form">Открытие формы</h2>

<ol>
    <li>Перейдите в раздел <strong>«Ученики»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Добавить ученика»</strong> (оранжевая кнопка справа вверху)</li>
</ol>

<h2 id="fill-data">Заполнение данных ученика</h2>

<p>Форма содержит несколько блоков информации:</p>

<h3 id="personal">Личные данные</h3>

<ul>
    <li><strong>ФИО ученика</strong> — полное имя (обязательное поле)</li>
    <li><strong>ИИН</strong> — индивидуальный идентификационный номер</li>
    <li><strong>Дата рождения</strong> — для расчёта возраста</li>
    <li><strong>Телефон</strong> — контактный номер ученика</li>
</ul>

<h3 id="parent">Данные родителя/представителя</h3>

<ul>
    <li><strong>ФИО родителя</strong> — имя законного представителя</li>
    <li><strong>Телефон родителя</strong> — для связи по вопросам обучения</li>
    <li><strong>Email</strong> — для отправки уведомлений</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">WhatsApp интеграция</div>
            <div class="text-blue-700 text-sm">Если подключена интеграция с WhatsApp, уведомления будут автоматически отправляться на указанный номер телефона.</div>
        </div>
    </div>
</div>

<h3 id="education">Информация об обучении</h3>

<ul>
    <li><strong>Школа</strong> — учебное заведение ученика</li>
    <li><strong>Класс</strong> — текущий класс обучения</li>
    <li><strong>Примечания</strong> — дополнительная информация</li>
</ul>

<h2 id="save">Сохранение</h2>

<ol>
    <li>Проверьте введённые данные</li>
    <li>Нажмите кнопку <strong>«Сохранить»</strong></li>
    <li>Система создаст карточку ученика и перенаправит вас на страницу просмотра</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Следующий шаг</div>
            <div class="text-green-700 text-sm">После создания ученика добавьте его в учебную группу — это можно сделать на странице ученика в разделе «Обучение».</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function getCreateGroupContent()
    {
        return <<<'HTML'
<p>Учебные группы — это основа организации учебного процесса. В группу записываются ученики, назначаются преподаватели и создаётся расписание.</p>

<h2 id="create">Создание группы</h2>

<ol>
    <li>Перейдите в раздел <strong>«Группы»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Создать группу»</strong></li>
</ol>

<h2 id="settings">Настройки группы</h2>

<h3 id="basic">Основная информация</h3>

<ul>
    <li><strong>Название группы</strong> — например, «Математика 9 класс» или «Английский начальный»</li>
    <li><strong>Код группы</strong> — краткий идентификатор (MAT-9, ENG-B1)</li>
    <li><strong>Предмет</strong> — выберите из списка предметов</li>
    <li><strong>Тип</strong> — групповые или индивидуальные занятия</li>
</ul>

<h3 id="visual">Визуальные настройки</h3>

<ul>
    <li><strong>Цвет</strong> — цвет группы для отображения в календаре</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Совет</div>
            <div class="text-green-700 text-sm">Используйте разные цвета для разных предметов — так расписание будет нагляднее.</div>
        </div>
    </div>
</div>

<h2 id="add-teacher">Добавление преподавателя</h2>

<p>После создания группы добавьте преподавателя:</p>

<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Преподаватели»</strong></li>
    <li>Нажмите <strong>«Добавить преподавателя»</strong></li>
    <li>Выберите преподавателя из списка</li>
</ol>

<h2 id="add-pupils">Добавление учеников</h2>

<p>Ученики могут быть добавлены в группу двумя способами:</p>

<ul>
    <li><strong>Из карточки ученика</strong> — раздел «Обучение» → «Добавить в группу»</li>
    <li><strong>Из карточки группы</strong> — вкладка «Ученики» → «Добавить ученика»</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-semibold text-yellow-800">Важно</div>
            <div class="text-yellow-700 text-sm">При добавлении ученика в группу укажите тариф и период обучения — это необходимо для корректного расчёта баланса.</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function getCalendarOverviewContent()
    {
        return <<<'HTML'
<p>Календарь расписания — центральный инструмент для планирования и отслеживания занятий.</p>

<h2 id="views">Режимы просмотра</h2>

<p>Календарь поддерживает несколько режимов отображения:</p>

<ul>
    <li><strong>День</strong> — подробное расписание на один день</li>
    <li><strong>Неделя</strong> — обзор всей недели (рекомендуется)</li>
    <li><strong>Месяц</strong> — общий вид на месяц</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Навигация</div>
            <div class="text-blue-700 text-sm">Используйте стрелки «‹» и «›» для перехода между периодами. Кнопка «Сегодня» вернёт к текущей дате.</div>
        </div>
    </div>
</div>

<h2 id="filters">Фильтры</h2>

<p>Над календарём расположены фильтры:</p>

<ul>
    <li><strong>По группам</strong> — показать занятия выбранных групп</li>
    <li><strong>По преподавателям</strong> — занятия конкретного преподавателя</li>
    <li><strong>По кабинетам</strong> — занятия в определённом кабинете</li>
</ul>

<h2 id="lesson-card">Карточка занятия</h2>

<p>Каждое занятие в календаре отображается карточкой, которая показывает:</p>

<ul>
    <li>Название группы</li>
    <li>Время начала и окончания</li>
    <li>Преподаватель</li>
    <li>Кабинет</li>
    <li>Статус (запланировано/проведено/отменено)</li>
</ul>

<h2 id="create-lesson">Создание занятия</h2>

<p>Создать новое занятие можно несколькими способами:</p>

<ol>
    <li><strong>Кнопка «Добавить»</strong> — откроет форму создания занятия</li>
    <li><strong>Клик по пустой ячейке</strong> — создаст занятие на выбранное время</li>
    <li><strong>Drag & Drop</strong> — перетащите существующее занятие на новое время</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Совет</div>
            <div class="text-green-700 text-sm">Используйте шаблоны расписания для быстрого создания повторяющихся занятий на всю неделю или месяц.</div>
        </div>
    </div>
</div>

<h2 id="conflicts">Проверка конфликтов</h2>

<p>Система автоматически проверяет конфликты расписания:</p>

<ul>
    <li>Занятость преподавателя в это время</li>
    <li>Занятость кабинета</li>
    <li>Пересечение с другими занятиями группы</li>
</ul>

<p>При обнаружении конфликта система покажет предупреждение и не позволит сохранить занятие до устранения конфликта.</p>
HTML;
    }

    protected function getReceivePaymentContent()
    {
        return <<<'HTML'
<p>Приём платежей от учеников — важная функция для учёта финансов организации.</p>

<h2 id="methods">Способы создания платежа</h2>

<p>Создать платёж можно несколькими способами:</p>

<ol>
    <li><strong>Из раздела «Платежи»</strong> — кнопка «Создать платёж»</li>
    <li><strong>Из карточки ученика</strong> — вкладка «Платежи» → «Добавить платёж»</li>
</ol>

<h2 id="form">Заполнение формы платежа</h2>

<h3 id="required">Обязательные поля</h3>

<ul>
    <li><strong>Ученик</strong> — выберите ученика из списка</li>
    <li><strong>Сумма</strong> — введите сумму платежа в тенге</li>
    <li><strong>Дата</strong> — дата получения платежа</li>
    <li><strong>Способ оплаты</strong> — наличные, карта, перевод и т.д.</li>
</ul>

<h3 id="optional">Дополнительные поля</h3>

<ul>
    <li><strong>Комментарий</strong> — примечание к платежу</li>
    <li><strong>Обучение</strong> — за какой период/группу оплата</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Автоматический расчёт</div>
            <div class="text-blue-700 text-sm">При создании платежа баланс ученика автоматически пересчитывается. Вы всегда видите актуальный остаток.</div>
        </div>
    </div>
</div>

<h2 id="receipt">Квитанция</h2>

<p>После создания платежа можно распечатать квитанцию:</p>

<ol>
    <li>Откройте карточку платежа</li>
    <li>Нажмите кнопку <strong>«Распечатать квитанцию»</strong></li>
    <li>Квитанция откроется в новом окне для печати</li>
</ol>

<h2 id="edit-delete">Редактирование и удаление</h2>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-semibold text-yellow-800">Права доступа</div>
            <div class="text-yellow-700 text-sm">Редактирование и удаление платежей может быть ограничено настройками прав доступа. Если у вас нет прав, вы можете отправить запрос директору.</div>
        </div>
    </div>
</div>

<p>Для запроса на изменение:</p>

<ol>
    <li>Откройте карточку платежа</li>
    <li>Нажмите <strong>«Запросить изменение»</strong> или <strong>«Запросить удаление»</strong></li>
    <li>Укажите причину запроса</li>
    <li>Директор получит уведомление и сможет одобрить или отклонить запрос</li>
</ol>
HTML;
    }

    protected function getKanbanContent()
    {
        return <<<'HTML'
<p>Kanban-доска — наглядный инструмент для управления лидами через воронку продаж.</p>

<h2 id="structure">Структура доски</h2>

<p>Доска состоит из колонок, соответствующих этапам воронки:</p>

<ul>
    <li><strong>Новый</strong> — только что поступившие заявки</li>
    <li><strong>В работе</strong> — ведётся первичный контакт</li>
    <li><strong>Интерес</strong> — клиент заинтересован</li>
    <li><strong>Предложение</strong> — отправлено коммерческое предложение</li>
    <li><strong>Переговоры</strong> — обсуждение условий</li>
    <li><strong>Оплата</strong> — ожидание оплаты</li>
    <li><strong>Сконвертирован</strong> — стал учеником</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Drag & Drop</div>
            <div class="text-blue-700 text-sm">Перетаскивайте карточки между колонками для изменения статуса лида. Изменения сохраняются автоматически.</div>
        </div>
    </div>
</div>

<h2 id="lead-card">Карточка лида</h2>

<p>Каждая карточка на доске показывает:</p>

<ul>
    <li>Имя потенциального клиента</li>
    <li>Контактный телефон</li>
    <li>Источник (откуда пришёл)</li>
    <li>Дата создания</li>
    <li>Теги для категоризации</li>
</ul>

<h2 id="quick-actions">Быстрые действия</h2>

<p>При наведении на карточку доступны кнопки:</p>

<ul>
    <li><strong>WhatsApp</strong> — открыть чат с клиентом</li>
    <li><strong>Позвонить</strong> — инициировать звонок</li>
    <li><strong>Просмотр</strong> — открыть полную карточку лида</li>
</ul>

<h2 id="filters">Фильтрация</h2>

<p>Над доской расположены фильтры:</p>

<ul>
    <li><strong>По источнику</strong> — Instagram, сайт, рекомендация и т.д.</li>
    <li><strong>По менеджеру</strong> — кто ведёт лида</li>
    <li><strong>По дате</strong> — за определённый период</li>
    <li><strong>По тегам</strong> — категории лидов</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Аналитика</div>
            <div class="text-green-700 text-sm">Перейдите в раздел «Аналитика» для просмотра конверсии воронки, среднего времени на каждом этапе и эффективности источников.</div>
        </div>
    </div>
</div>

<h2 id="conversion">Конверсия в ученика</h2>

<p>Когда лид готов стать учеником:</p>

<ol>
    <li>Откройте карточку лида</li>
    <li>Нажмите <strong>«Конвертировать в ученика»</strong></li>
    <li>Заполните недостающие данные</li>
    <li>Система создаст ученика и свяжет его с лидом</li>
</ol>

<p>После конверсии лид перемещается в статус «Сконвертирован» и связывается с карточкой ученика.</p>
HTML;
    }
}
