<?php

use yii\db\Migration;

/**
 * Добавляет контент документации для глав 4-6
 */
class m260110_190001_add_docs_content_chapters_4_6 extends Migration
{
    public function safeUp()
    {
        // Глава 4: assign-roles
        $this->update('{{%docs_section}}', [
            'content' => $this->getAssignRolesContent(),
        ], ['chapter_id' => 4, 'slug' => 'assign-roles']);

        // Глава 4: link-to-groups
        $this->update('{{%docs_section}}', [
            'content' => $this->getLinkToGroupsContent(),
        ], ['chapter_id' => 4, 'slug' => 'link-to-groups']);

        // Глава 4: teacher-rates
        $this->update('{{%docs_section}}', [
            'content' => $this->getTeacherRatesContent(),
        ], ['chapter_id' => 4, 'slug' => 'teacher-rates']);

        // Глава 5: create-lesson
        $this->update('{{%docs_section}}', [
            'content' => $this->getCreateLessonContent(),
        ], ['chapter_id' => 5, 'slug' => 'create-lesson']);

        // Глава 5: schedule-templates
        $this->update('{{%docs_section}}', [
            'content' => $this->getScheduleTemplatesContent(),
        ], ['chapter_id' => 5, 'slug' => 'schedule-templates']);

        // Глава 5: edit-cancel-lesson
        $this->update('{{%docs_section}}', [
            'content' => $this->getEditCancelLessonContent(),
        ], ['chapter_id' => 5, 'slug' => 'edit-cancel-lesson']);

        // Глава 6: attendance-statuses
        $this->update('{{%docs_section}}', [
            'content' => $this->getAttendanceStatusesContent(),
        ], ['chapter_id' => 6, 'slug' => 'attendance-statuses']);

        // Глава 6: attendance-reports
        $this->update('{{%docs_section}}', [
            'content' => $this->getAttendanceReportsContent(),
        ], ['chapter_id' => 6, 'slug' => 'attendance-reports']);
    }

    public function safeDown()
    {
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 4, 'slug' => 'assign-roles']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 4, 'slug' => 'link-to-groups']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 4, 'slug' => 'teacher-rates']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 5, 'slug' => 'create-lesson']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 5, 'slug' => 'schedule-templates']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 5, 'slug' => 'edit-cancel-lesson']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 6, 'slug' => 'attendance-statuses']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 6, 'slug' => 'attendance-reports']);
    }

    private function getAssignRolesContent(): string
    {
        return <<<HTML
<h2 id="roles">Назначение ролей сотрудникам</h2>
<p>Каждому сотруднику назначается роль, определяющая его права доступа в системе.</p>

<h3 id="available-roles">Доступные роли</h3>
<ul>
    <li><strong>Генеральный директор</strong> — полный доступ ко всем функциям, включая финансы и настройки</li>
    <li><strong>Директор</strong> — управление сотрудниками, группами, учениками, просмотр финансов</li>
    <li><strong>Администратор</strong> — работа с учениками, группами, расписанием, приём платежей</li>
    <li><strong>Преподаватель</strong> — доступ к своим группам, расписанию, отметка посещаемости</li>
</ul>

<h3 id="assign-role">Назначение роли</h3>
<ol>
    <li>Перейдите в раздел <strong>Сотрудники</strong></li>
    <li>Откройте карточку сотрудника</li>
    <li>Нажмите <strong>«Редактировать»</strong></li>
    <li>Выберите роль из выпадающего списка</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Изменение роли вступает в силу сразу. Сотрудник получит новые права при следующем действии в системе.</div>
        </div>
    </div>
</div>

<h3 id="role-permissions">Права по ролям</h3>
<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Функция</th>
            <th class="border p-2 text-center">Ген. директор</th>
            <th class="border p-2 text-center">Директор</th>
            <th class="border p-2 text-center">Админ</th>
            <th class="border p-2 text-center">Препод.</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2">Настройки организации</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td><td class="border p-2 text-center">—</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Управление сотрудниками</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Финансовые отчёты</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Приём платежей</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Работа с учениками</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Расписание (все)</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">—</td></tr>
        <tr><td class="border p-2">Расписание (свои группы)</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td></tr>
        <tr><td class="border p-2">Отметка посещаемости</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td><td class="border p-2 text-center">✓</td></tr>
    </tbody>
</table>
HTML;
    }

    private function getLinkToGroupsContent(): string
    {
        return <<<HTML
<h2 id="link">Привязка сотрудника к группам</h2>
<p>Преподавателей необходимо привязать к группам, чтобы они могли вести занятия и отмечать посещаемость.</p>

<h3 id="why-link">Зачем привязывать</h3>
<ul>
    <li>Преподаватель видит только свои группы в расписании</li>
    <li>Может создавать занятия для своих групп</li>
    <li>Имеет доступ к списку учеников группы</li>
    <li>Может отмечать посещаемость на занятиях</li>
</ul>

<h3 id="link-process">Привязка к группе</h3>
<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Преподаватели»</strong></li>
    <li>Нажмите <strong>«Назначить преподавателя»</strong></li>
    <li>Выберите сотрудника из списка</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Совет</div>
            <div class="text-green-700 text-sm mt-1">Один преподаватель может быть привязан к нескольким группам, а одна группа может иметь нескольких преподавателей.</div>
        </div>
    </div>
</div>

<h3 id="view-groups">Просмотр групп сотрудника</h3>
<p>Чтобы увидеть все группы преподавателя:</p>
<ol>
    <li>Откройте карточку сотрудника</li>
    <li>Просмотрите раздел <strong>«Группы»</strong></li>
</ol>

<h3 id="unlink">Отвязка от группы</h3>
<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Преподаватели»</strong></li>
    <li>Нажмите <strong>«Удалить»</strong> напротив преподавателя</li>
    <li>Подтвердите действие</li>
</ol>
HTML;
    }

    private function getTeacherRatesContent(): string
    {
        return <<<HTML
<h2 id="rates">Настройка ставок преподавателей</h2>
<p>Ставки определяют размер оплаты преподавателя за проведённые занятия.</p>

<h3 id="rate-types">Типы ставок</h3>
<ul>
    <li><strong>За занятие</strong> — фиксированная сумма за каждое проведённое занятие</li>
    <li><strong>Процент от оплаты</strong> — процент от суммы, оплаченной учениками</li>
    <li><strong>Почасовая</strong> — оплата за фактическое время занятия</li>
</ul>

<h3 id="create-rate">Создание ставки</h3>
<ol>
    <li>Перейдите в раздел <strong>Зарплаты → Ставки</strong></li>
    <li>Нажмите <strong>«Добавить ставку»</strong></li>
    <li>Выберите преподавателя</li>
    <li>Укажите тип ставки и размер</li>
    <li>При необходимости укажите группу (для индивидуальных ставок)</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Приоритет ставок</div>
            <div class="text-blue-700 text-sm mt-1">Если у преподавателя несколько ставок, система использует наиболее специфичную: сначала для конкретной группы, затем общую.</div>
        </div>
    </div>
</div>

<h3 id="edit-rate">Редактирование ставки</h3>
<ol>
    <li>В списке ставок найдите нужную</li>
    <li>Нажмите <strong>«Редактировать»</strong></li>
    <li>Внесите изменения</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="rate-calculation">Расчёт зарплаты</h3>
<p>При расчёте зарплаты система учитывает:</p>
<ul>
    <li>Количество проведённых занятий</li>
    <li>Ставку преподавателя для каждого занятия</li>
    <li>Бонусы и удержания (если указаны)</li>
</ul>
HTML;
    }

    private function getCreateLessonContent(): string
    {
        return <<<HTML
<h2 id="create">Создание занятия</h2>
<p>Занятия можно создавать как через интерфейс календаря, так и через форму создания.</p>

<h3 id="quick-create">Быстрое создание (клик по календарю)</h3>
<ol>
    <li>Откройте раздел <strong>Расписание</strong></li>
    <li>Кликните на нужный временной слот в календаре</li>
    <li>Откроется форма с предзаполненными датой и временем</li>
    <li>Заполните остальные поля</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="form-create">Создание через форму</h3>
<ol>
    <li>На странице расписания нажмите <strong>«Добавить занятие»</strong></li>
    <li>Заполните форму:</li>
</ol>
<ul>
    <li><strong>Группа</strong> — выберите учебную группу</li>
    <li><strong>Преподаватель</strong> — кто ведёт занятие</li>
    <li><strong>Дата</strong> — день проведения</li>
    <li><strong>Время начала</strong> — во сколько начинается</li>
    <li><strong>Длительность</strong> — продолжительность в минутах</li>
    <li><strong>Кабинет</strong> — где проводится (опционально)</li>
    <li><strong>Тема</strong> — тема занятия (опционально)</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Проверка конфликтов</div>
            <div class="text-yellow-700 text-sm mt-1">Система автоматически проверяет, нет ли пересечений с другими занятиями по группе, преподавателю или кабинету.</div>
        </div>
    </div>
</div>

<h3 id="recurring">Повторяющиеся занятия</h3>
<p>Для создания регулярных занятий:</p>
<ol>
    <li>Создайте первое занятие</li>
    <li>Отметьте опцию <strong>«Повторять»</strong></li>
    <li>Укажите периодичность: ежедневно, еженедельно</li>
    <li>Укажите дату окончания повторений</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Совет</div>
            <div class="text-green-700 text-sm mt-1">Используйте шаблоны расписания для быстрого создания типовых занятий на неделю.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getScheduleTemplatesContent(): string
    {
        return <<<HTML
<h2 id="templates">Шаблоны расписания</h2>
<p>Шаблоны позволяют быстро создавать типовое расписание на неделю или месяц.</p>

<h3 id="what-is-template">Что такое шаблон</h3>
<p>Шаблон расписания — это набор занятий с указанием:</p>
<ul>
    <li>Дня недели (понедельник, вторник и т.д.)</li>
    <li>Времени начала и окончания</li>
    <li>Группы и преподавателя</li>
    <li>Кабинета (опционально)</li>
</ul>

<h3 id="create-template">Создание шаблона</h3>
<ol>
    <li>Перейдите в <strong>Расписание → Шаблоны</strong></li>
    <li>Нажмите <strong>«Создать шаблон»</strong></li>
    <li>Введите название шаблона</li>
    <li>Добавьте занятия на каждый день недели</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="apply-template">Применение шаблона</h3>
<ol>
    <li>Откройте нужный шаблон</li>
    <li>Нажмите <strong>«Применить»</strong></li>
    <li>Выберите период: дата начала и окончания</li>
    <li>Подтвердите создание занятий</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Автоматическая проверка</div>
            <div class="text-blue-700 text-sm mt-1">При применении шаблона система пропустит даты, где уже есть занятия для той же группы/преподавателя.</div>
        </div>
    </div>
</div>

<h3 id="edit-template">Редактирование шаблона</h3>
<p>Шаблон можно редактировать в любой момент. Изменения не затрагивают уже созданные занятия.</p>

<h3 id="delete-template">Удаление шаблона</h3>
<p>При удалении шаблона ранее созданные по нему занятия сохраняются.</p>
HTML;
    }

    private function getEditCancelLessonContent(): string
    {
        return <<<HTML
<h2 id="edit">Редактирование занятия</h2>
<p>Созданные занятия можно редактировать или отменять при необходимости.</p>

<h3 id="open-lesson">Открытие занятия</h3>
<ol>
    <li>Найдите занятие в календаре</li>
    <li>Кликните на карточку занятия</li>
    <li>Откроется модальное окно с деталями</li>
</ol>

<h3 id="edit-lesson">Редактирование</h3>
<ol>
    <li>В модальном окне нажмите <strong>«Редактировать»</strong></li>
    <li>Измените нужные поля: время, дату, группу, преподавателя</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="drag-drop">Перенос перетаскиванием</h3>
<p>Для быстрого переноса занятия:</p>
<ol>
    <li>Зажмите карточку занятия в календаре</li>
    <li>Перетащите на новое время или дату</li>
    <li>Отпустите — занятие переместится</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-arrows-alt text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Drag & Drop</div>
            <div class="text-blue-700 text-sm mt-1">Перетаскивание работает в недельном и дневном режимах просмотра. При конфликте система предупредит и отменит перенос.</div>
        </div>
    </div>
</div>

<h3 id="cancel-lesson">Отмена занятия</h3>
<ol>
    <li>Откройте карточку занятия</li>
    <li>Нажмите <strong>«Удалить»</strong> или <strong>«Отменить»</strong></li>
    <li>Подтвердите действие</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Внимание</div>
            <div class="text-yellow-700 text-sm mt-1">Если посещаемость уже отмечена, отмена занятия невозможна. Сначала удалите записи о посещении.</div>
        </div>
    </div>
</div>

<h3 id="cancel-recurring">Отмена повторяющихся занятий</h3>
<p>При отмене повторяющегося занятия:</p>
<ul>
    <li><strong>«Только это»</strong> — отменяется только выбранное занятие</li>
    <li><strong>«Это и следующие»</strong> — отменяются все будущие занятия серии</li>
</ul>
HTML;
    }

    private function getAttendanceStatusesContent(): string
    {
        return <<<HTML
<h2 id="statuses">Статусы посещения</h2>
<p>Для каждого ученика на занятии устанавливается статус посещения.</p>

<h3 id="available-statuses">Доступные статусы</h3>
<ul>
    <li><strong class="text-green-600">Присутствовал</strong> — ученик был на занятии</li>
    <li><strong class="text-yellow-600">Опоздал</strong> — пришёл с опозданием</li>
    <li><strong class="text-red-600">Отсутствовал</strong> — не пришёл на занятие</li>
    <li><strong class="text-blue-600">Болел</strong> — отсутствие по болезни</li>
    <li><strong class="text-gray-600">Не отмечен</strong> — статус ещё не установлен</li>
</ul>

<h3 id="set-status">Установка статуса</h3>
<ol>
    <li>Откройте занятие в календаре</li>
    <li>Нажмите <strong>«Отметить посещаемость»</strong></li>
    <li>Для каждого ученика выберите статус, кликнув на соответствующую кнопку</li>
    <li>Изменения сохраняются автоматически</li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-check-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Автосохранение</div>
            <div class="text-green-700 text-sm mt-1">Каждый клик по статусу мгновенно сохраняется. Не нужно нажимать отдельную кнопку сохранения.</div>
        </div>
    </div>
</div>

<h3 id="billing-impact">Влияние на баланс</h3>
<p>Статус посещения влияет на списание с баланса ученика:</p>
<ul>
    <li><strong>Присутствовал / Опоздал</strong> — списывается стоимость занятия</li>
    <li><strong>Отсутствовал</strong> — настраивается в параметрах организации</li>
    <li><strong>Болел</strong> — обычно не списывается (настраивается)</li>
</ul>

<h3 id="change-status">Изменение статуса</h3>
<p>Статус можно изменить в любой момент:</p>
<ol>
    <li>Откройте занятие</li>
    <li>Нажмите <strong>«Посещаемость»</strong></li>
    <li>Кликните на новый статус для ученика</li>
</ol>
<p>При изменении статуса баланс ученика пересчитывается автоматически.</p>
HTML;
    }

    private function getAttendanceReportsContent(): string
    {
        return <<<HTML
<h2 id="reports">Отчёты по посещаемости</h2>
<p>Система предоставляет различные отчёты для анализа посещаемости учеников.</p>

<h3 id="group-report">Отчёт по группе</h3>
<p>Показывает посещаемость всех учеников группы за период:</p>
<ol>
    <li>Перейдите в раздел <strong>Посещаемость</strong></li>
    <li>Выберите группу</li>
    <li>Укажите период (месяц, неделя или произвольный)</li>
    <li>Нажмите <strong>«Сформировать»</strong></li>
</ol>

<h3 id="report-data">Данные в отчёте</h3>
<ul>
    <li><strong>Всего занятий</strong> — количество проведённых занятий</li>
    <li><strong>Посещено</strong> — сколько занятий посетил ученик</li>
    <li><strong>Пропущено</strong> — количество пропусков</li>
    <li><strong>Процент посещаемости</strong> — доля посещённых занятий</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-chart-bar text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Визуализация</div>
            <div class="text-blue-700 text-sm mt-1">Отчёт включает графики для наглядного представления посещаемости по дням и ученикам.</div>
        </div>
    </div>
</div>

<h3 id="pupil-report">Отчёт по ученику</h3>
<p>Индивидуальный отчёт посещаемости ученика:</p>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Перейдите на вкладку <strong>«Посещаемость»</strong></li>
    <li>Просмотрите историю посещений по всем группам</li>
</ol>

<h3 id="export">Экспорт отчёта</h3>
<p>Отчёт можно экспортировать:</p>
<ul>
    <li><strong>Excel</strong> — для дальнейшей обработки</li>
    <li><strong>PDF</strong> — для печати или отправки родителям</li>
</ul>

<h3 id="notifications">Уведомления о пропусках</h3>
<p>Система может автоматически уведомлять родителей о пропусках через WhatsApp (при настроенной интеграции).</p>
HTML;
    }
}
