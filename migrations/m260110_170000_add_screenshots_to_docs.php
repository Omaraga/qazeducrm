<?php

use yii\db\Migration;

/**
 * Миграция для добавления скриншотов в документацию
 */
class m260110_170000_add_screenshots_to_docs extends Migration
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

        // Глава 4: Сотрудники - Добавление сотрудника
        $this->update('{{%docs_section}}', [
            'content' => $this->getAddEmployeeContent(),
        ], ['chapter_id' => 4, 'slug' => 'add-employee']);

        // Глава 5: Обзор календаря
        $this->update('{{%docs_section}}', [
            'content' => $this->getCalendarOverviewContent(),
        ], ['chapter_id' => 5, 'slug' => 'calendar-overview']);

        // Глава 6: Посещаемость
        $this->update('{{%docs_section}}', [
            'content' => $this->getAttendanceMarkingContent(),
        ], ['chapter_id' => 6, 'slug' => 'marking-attendance']);

        // Глава 7: Приём платежа
        $this->update('{{%docs_section}}', [
            'content' => $this->getReceivePaymentContent(),
        ], ['chapter_id' => 7, 'slug' => 'receive-payment']);

        // Глава 8: Зарплаты - Расчёт зарплаты
        $this->update('{{%docs_section}}', [
            'content' => $this->getSalaryCalculationContent(),
        ], ['chapter_id' => 8, 'slug' => 'salary-calculation']);

        // Глава 9: Kanban-доска
        $this->update('{{%docs_section}}', [
            'content' => $this->getKanbanContent(),
        ], ['chapter_id' => 9, 'slug' => 'kanban-board']);

        // Глава 10: WhatsApp
        $this->update('{{%docs_section}}', [
            'content' => $this->getWhatsappConnectionContent(),
        ], ['chapter_id' => 10, 'slug' => 'connection']);

        // Глава 11: Настройки - Предметы
        $this->update('{{%docs_section}}', [
            'content' => $this->getSubjectsContent(),
        ], ['chapter_id' => 11, 'slug' => 'subjects']);

        // Глава 11: Настройки - Тарифы
        $this->update('{{%docs_section}}', [
            'content' => $this->getTariffsContent(),
        ], ['chapter_id' => 11, 'slug' => 'tariffs']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Откат не требуется - контент обновляется
        return true;
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

<figure class="my-6">
    <img src="/images/docs/getting-started/registration-page.png" alt="Форма регистрации организации" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма регистрации новой организации</figcaption>
</figure>

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

<figure class="my-6">
    <img src="/images/docs/getting-started/login-page.png" alt="Страница входа в систему" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница входа в QazEduCRM</figcaption>
</figure>

<ol>
    <li>Перейдите на страницу входа: нажмите <strong>«Войти»</strong> на главной странице</li>
    <li>Введите ваш <strong>Email</strong>, указанный при регистрации</li>
    <li>Введите <strong>пароль</strong></li>
    <li>Нажмите кнопку <strong>«Войти»</strong></li>
</ol>

<figure class="my-6">
    <img src="/images/docs/getting-started/login-form-highlighted.png" alt="Форма входа с выделенными полями" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Поля формы входа: 1 - Email, 2 - Пароль, 3 - Кнопка входа</figcaption>
</figure>

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

<figure class="my-6">
    <img src="/images/docs/getting-started/dashboard.png" alt="Главная страница Dashboard" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Главная страница CRM с основной статистикой</figcaption>
</figure>

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

<figure class="my-6">
    <img src="/images/docs/getting-started/dashboard-sidebar.png" alt="Боковое меню системы" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Главное боковое меню навигации</figcaption>
</figure>

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

<figure class="my-6">
    <img src="/images/docs/pupils/pupils-list.png" alt="Список учеников" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком учеников</figcaption>
</figure>

<ol>
    <li>Перейдите в раздел <strong>«Ученики»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Добавить ученика»</strong> (оранжевая кнопка справа вверху)</li>
</ol>

<h2 id="fill-data">Заполнение данных ученика</h2>

<figure class="my-6">
    <img src="/images/docs/pupils/pupil-create-form.png" alt="Форма создания ученика" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма добавления нового ученика</figcaption>
</figure>

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

<h2 id="list">Список групп</h2>

<figure class="my-6">
    <img src="/images/docs/groups/groups-list.png" alt="Список групп" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком учебных групп</figcaption>
</figure>

<h2 id="create">Создание группы</h2>

<ol>
    <li>Перейдите в раздел <strong>«Группы»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Создать группу»</strong></li>
</ol>

<figure class="my-6">
    <img src="/images/docs/groups/group-create.png" alt="Форма создания группы" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма создания новой группы</figcaption>
</figure>

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

<h2 id="view">Просмотр группы</h2>

<figure class="my-6">
    <img src="/images/docs/groups/group-view.png" alt="Карточка группы" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Детальная карточка группы</figcaption>
</figure>

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

    protected function getAddEmployeeContent()
    {
        return <<<'HTML'
<p>Сотрудники — это преподаватели и администраторы вашего учебного центра. Добавление сотрудника позволяет назначать его на группы и занятия.</p>

<h2 id="list">Список сотрудников</h2>

<figure class="my-6">
    <img src="/images/docs/employees/employees-list.png" alt="Список сотрудников" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком сотрудников</figcaption>
</figure>

<h2 id="create">Добавление сотрудника</h2>

<ol>
    <li>Перейдите в раздел <strong>«Сотрудники»</strong> в боковом меню</li>
    <li>Нажмите кнопку <strong>«Добавить сотрудника»</strong></li>
</ol>

<figure class="my-6">
    <img src="/images/docs/employees/employee-create.png" alt="Форма добавления сотрудника" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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
    <img src="/images/docs/employees/employee-view.png" alt="Карточка сотрудника" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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

    protected function getCalendarOverviewContent()
    {
        return <<<'HTML'
<p>Календарь расписания — центральный инструмент для планирования и отслеживания занятий.</p>

<h2 id="calendar">Обзор календаря</h2>

<figure class="my-6">
    <img src="/images/docs/schedule/schedule-calendar.png" alt="Календарь расписания" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Календарь расписания занятий</figcaption>
</figure>

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

<figure class="my-6">
    <img src="/images/docs/schedule/schedule-create-lesson.png" alt="Форма создания занятия" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма создания нового занятия</figcaption>
</figure>

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

    protected function getAttendanceMarkingContent()
    {
        return <<<'HTML'
<p>Отметка посещаемости — важная функция для учёта посещений учеников и расчёта статистики.</p>

<h2 id="page">Страница посещаемости</h2>

<figure class="my-6">
    <img src="/images/docs/attendance/attendance-page.png" alt="Страница посещаемости" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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

    protected function getReceivePaymentContent()
    {
        return <<<'HTML'
<p>Приём платежей от учеников — важная функция для учёта финансов организации.</p>

<h2 id="list">Список платежей</h2>

<figure class="my-6">
    <img src="/images/docs/payments/payments-list.png" alt="Список платежей" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком платежей</figcaption>
</figure>

<h2 id="methods">Способы создания платежа</h2>

<p>Создать платёж можно несколькими способами:</p>

<ol>
    <li><strong>Из раздела «Платежи»</strong> — кнопка «Создать платёж»</li>
    <li><strong>Из карточки ученика</strong> — вкладка «Платежи» → «Добавить платёж»</li>
</ol>

<h2 id="form">Заполнение формы платежа</h2>

<figure class="my-6">
    <img src="/images/docs/payments/payment-create.png" alt="Форма создания платежа" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма приёма платежа</figcaption>
</figure>

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

    protected function getSalaryCalculationContent()
    {
        return <<<'HTML'
<p>Расчёт зарплаты преподавателей основан на проведённых занятиях и настроенных ставках.</p>

<h2 id="list">Список зарплат</h2>

<figure class="my-6">
    <img src="/images/docs/salary/salary-list.png" alt="Список зарплат" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком зарплат преподавателей</figcaption>
</figure>

<h2 id="calculation">Расчёт зарплаты</h2>

<figure class="my-6">
    <img src="/images/docs/salary/salary-calculate.png" alt="Расчёт зарплаты" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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

    protected function getKanbanContent()
    {
        return <<<'HTML'
<p>Kanban-доска — наглядный инструмент для управления лидами через воронку продаж.</p>

<h2 id="list">Список лидов</h2>

<figure class="my-6">
    <img src="/images/docs/lids/lids-list.png" alt="Список лидов" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Табличный вид списка лидов</figcaption>
</figure>

<h2 id="kanban">Kanban-доска</h2>

<figure class="my-6">
    <img src="/images/docs/lids/lids-kanban.png" alt="Kanban-доска лидов" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Kanban-доска для управления лидами</figcaption>
</figure>

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

<h2 id="create-lead">Создание лида</h2>

<figure class="my-6">
    <img src="/images/docs/lids/lid-create.png" alt="Форма создания лида" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма создания нового лида</figcaption>
</figure>

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

    protected function getWhatsappConnectionContent()
    {
        return <<<'HTML'
<p>Интеграция с WhatsApp позволяет общаться с учениками и родителями прямо из CRM, отправлять уведомления и вести историю переписки.</p>

<h2 id="page">Страница WhatsApp</h2>

<figure class="my-6">
    <img src="/images/docs/whatsapp/whatsapp-index.png" alt="Страница WhatsApp" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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
    <img src="/images/docs/whatsapp/whatsapp-chats.png" alt="Список чатов WhatsApp" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
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

    protected function getSubjectsContent()
    {
        return <<<'HTML'
<p>Предметы — это дисциплины, которые преподаются в вашем учебном центре. Настройка предметов необходима для создания групп и тарифов.</p>

<h2 id="list">Список предметов</h2>

<figure class="my-6">
    <img src="/images/docs/settings/subjects-list.png" alt="Список предметов" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком предметов</figcaption>
</figure>

<h2 id="create">Создание предмета</h2>

<figure class="my-6">
    <img src="/images/docs/settings/subject-create.png" alt="Создание предмета" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма создания предмета</figcaption>
</figure>

<ol>
    <li>Перейдите в <strong>«Настройки»</strong> → <strong>«Предметы»</strong></li>
    <li>Нажмите <strong>«Добавить предмет»</strong></li>
    <li>Введите название предмета</li>
    <li>При необходимости добавьте описание</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-semibold text-blue-800">Примеры предметов</div>
            <div class="text-blue-700 text-sm">Математика, Английский язык, Физика, Программирование, Подготовка к ЕНТ, Шахматы и т.д.</div>
        </div>
    </div>
</div>

<h2 id="usage">Использование</h2>

<p>Предметы используются в:</p>

<ul>
    <li><strong>Группах</strong> — каждая группа привязана к предмету</li>
    <li><strong>Тарифах</strong> — можно создать тарифы для конкретных предметов</li>
    <li><strong>Отчётах</strong> — фильтрация статистики по предметам</li>
</ul>
HTML;
    }

    protected function getTariffsContent()
    {
        return <<<'HTML'
<p>Тарифы определяют стоимость обучения и условия оплаты для учеников.</p>

<h2 id="list">Список тарифов</h2>

<figure class="my-6">
    <img src="/images/docs/settings/tariffs-list.png" alt="Список тарифов" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Страница со списком тарифов</figcaption>
</figure>

<h2 id="create">Создание тарифа</h2>

<figure class="my-6">
    <img src="/images/docs/settings/tariff-create.png" alt="Создание тарифа" class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity" onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">Форма создания тарифа</figcaption>
</figure>

<ol>
    <li>Перейдите в <strong>«Настройки»</strong> → <strong>«Тарифы»</strong></li>
    <li>Нажмите <strong>«Добавить тариф»</strong></li>
    <li>Заполните параметры тарифа</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h2 id="settings">Параметры тарифа</h2>

<ul>
    <li><strong>Название</strong> — например, «Стандарт», «Премиум», «Индивидуальные занятия»</li>
    <li><strong>Тип оплаты</strong> — за занятие, за месяц, за курс</li>
    <li><strong>Стоимость</strong> — цена в тенге</li>
    <li><strong>Количество занятий</strong> — для абонементов</li>
    <li><strong>Срок действия</strong> — период использования абонемента</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-semibold text-green-800">Гибкость</div>
            <div class="text-green-700 text-sm">Создавайте разные тарифы для групповых и индивидуальных занятий, для разных предметов или уровней подготовки.</div>
        </div>
    </div>
</div>

<h2 id="discount">Скидки</h2>

<p>К тарифам можно применять скидки:</p>

<ul>
    <li><strong>Семейная скидка</strong> — для нескольких детей из одной семьи</li>
    <li><strong>Скидка за предоплату</strong> — при оплате за несколько месяцев</li>
    <li><strong>Индивидуальная скидка</strong> — персональные условия для ученика</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-4">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-semibold text-yellow-800">Изменение тарифа</div>
            <div class="text-yellow-700 text-sm">Изменение тарифа не влияет на уже записанных учеников. Новые условия применяются только к новым записям.</div>
        </div>
    </div>
</div>
HTML;
    }
}
