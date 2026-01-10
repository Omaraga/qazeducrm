<?php

use yii\db\Migration;

/**
 * Добавляет контент документации для глав 1-3
 */
class m260110_190000_add_docs_content_chapters_1_3 extends Migration
{
    public function safeUp()
    {
        // Глава 1: email-verification
        $this->update('{{%docs_section}}', [
            'content' => $this->getEmailVerificationContent(),
        ], ['chapter_id' => 1, 'slug' => 'email-verification']);

        // Глава 1: organization-setup
        $this->update('{{%docs_section}}', [
            'content' => $this->getOrganizationSetupContent(),
        ], ['chapter_id' => 1, 'slug' => 'organization-setup']);

        // Глава 2: edit-pupil
        $this->update('{{%docs_section}}', [
            'content' => $this->getEditPupilContent(),
        ], ['chapter_id' => 2, 'slug' => 'edit-pupil']);

        // Глава 2: add-to-group
        $this->update('{{%docs_section}}', [
            'content' => $this->getAddToGroupContent(),
        ], ['chapter_id' => 2, 'slug' => 'add-to-group']);

        // Глава 2: pupil-payments
        $this->update('{{%docs_section}}', [
            'content' => $this->getPupilPaymentsContent(),
        ], ['chapter_id' => 2, 'slug' => 'pupil-payments']);

        // Глава 2: delete-pupil
        $this->update('{{%docs_section}}', [
            'content' => $this->getDeletePupilContent(),
        ], ['chapter_id' => 2, 'slug' => 'delete-pupil']);

        // Глава 3: add-pupils-to-group
        $this->update('{{%docs_section}}', [
            'content' => $this->getAddPupilsToGroupContent(),
        ], ['chapter_id' => 3, 'slug' => 'add-pupils-to-group']);

        // Глава 3: assign-teacher
        $this->update('{{%docs_section}}', [
            'content' => $this->getAssignTeacherContent(),
        ], ['chapter_id' => 3, 'slug' => 'assign-teacher']);

        // Глава 3: group-tariffs
        $this->update('{{%docs_section}}', [
            'content' => $this->getGroupTariffsContent(),
        ], ['chapter_id' => 3, 'slug' => 'group-tariffs']);
    }

    public function safeDown()
    {
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 1, 'slug' => 'email-verification']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 1, 'slug' => 'organization-setup']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 2, 'slug' => 'edit-pupil']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 2, 'slug' => 'add-to-group']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 2, 'slug' => 'pupil-payments']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 2, 'slug' => 'delete-pupil']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 3, 'slug' => 'add-pupils-to-group']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 3, 'slug' => 'assign-teacher']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 3, 'slug' => 'group-tariffs']);
    }

    private function getEmailVerificationContent(): string
    {
        return <<<HTML
<h2 id="verification">Подтверждение email</h2>
<p>После регистрации организации на указанный email отправляется письмо с ссылкой для подтверждения. Этот шаг необходим для активации вашего аккаунта.</p>

<h3 id="check-email">Проверка почты</h3>
<ol>
    <li>Откройте почтовый ящик, указанный при регистрации</li>
    <li>Найдите письмо от <strong>QazEduCRM</strong> с темой «Подтверждение регистрации»</li>
    <li>Если письма нет во входящих, проверьте папку <strong>«Спам»</strong></li>
    <li>Нажмите на кнопку <strong>«Подтвердить email»</strong> в письме</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Срок действия ссылки</div>
            <div class="text-blue-700 text-sm mt-1">Ссылка для подтверждения действительна в течение 24 часов. Если срок истёк, запросите новое письмо на странице входа.</div>
        </div>
    </div>
</div>

<h3 id="resend">Повторная отправка письма</h3>
<p>Если письмо не пришло или ссылка устарела:</p>
<ol>
    <li>Перейдите на страницу входа</li>
    <li>Нажмите <strong>«Отправить письмо повторно»</strong></li>
    <li>Введите email, указанный при регистрации</li>
    <li>Нажмите <strong>«Отправить»</strong></li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Проблемы с получением письма</div>
            <div class="text-yellow-700 text-sm mt-1">Если письмо не приходит даже после повторной отправки, проверьте правильность указанного email или свяжитесь с поддержкой.</div>
        </div>
    </div>
</div>

<h3 id="after-verification">После подтверждения</h3>
<p>После успешного подтверждения email:</p>
<ul>
    <li>Вы будете автоматически перенаправлены на страницу входа</li>
    <li>Появится сообщение об успешном подтверждении</li>
    <li>Теперь можно войти в систему, используя email и пароль</li>
</ul>
HTML;
    }

    private function getOrganizationSetupContent(): string
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

    private function getEditPupilContent(): string
    {
        return <<<HTML
<h2 id="edit">Редактирование данных ученика</h2>
<p>Вы можете изменить информацию об ученике в любой момент через карточку ученика.</p>

<h3 id="open-edit">Открытие формы редактирования</h3>
<ol>
    <li>Перейдите в раздел <strong>Ученики</strong></li>
    <li>Найдите нужного ученика в списке или воспользуйтесь поиском</li>
    <li>Нажмите на имя ученика для открытия карточки</li>
    <li>Нажмите кнопку <strong>«Редактировать»</strong></li>
</ol>

<h3 id="editable-fields">Редактируемые поля</h3>
<p>В форме редактирования доступны следующие поля:</p>
<ul>
    <li><strong>ФИО ученика</strong> — имя, фамилия, отчество</li>
    <li><strong>Дата рождения</strong> — для расчёта возраста</li>
    <li><strong>Пол</strong> — мужской или женский</li>
    <li><strong>Телефон</strong> — контактный номер ученика</li>
    <li><strong>Email</strong> — электронная почта</li>
    <li><strong>Адрес</strong> — место проживания</li>
    <li><strong>Примечание</strong> — дополнительная информация</li>
</ul>

<h3 id="parent-info">Данные родителя</h3>
<p>Для несовершеннолетних учеников важно указать контакты родителя:</p>
<ul>
    <li><strong>ФИО родителя</strong></li>
    <li><strong>Телефон родителя</strong> — для связи и уведомлений</li>
    <li><strong>Email родителя</strong></li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">WhatsApp уведомления</div>
            <div class="text-blue-700 text-sm mt-1">Телефон родителя используется для отправки уведомлений в WhatsApp. Убедитесь, что номер указан в международном формате.</div>
        </div>
    </div>
</div>

<h3 id="save-changes">Сохранение изменений</h3>
<ol>
    <li>Внесите необходимые изменения в форму</li>
    <li>Проверьте корректность данных</li>
    <li>Нажмите кнопку <strong>«Сохранить»</strong></li>
</ol>
<p>После сохранения вы будете перенаправлены на карточку ученика с обновлёнными данными.</p>
HTML;
    }

    private function getAddToGroupContent(): string
    {
        return <<<HTML
<h2 id="enroll">Добавление ученика в группу</h2>
<p>Для того чтобы ученик мог посещать занятия и его посещаемость учитывалась, необходимо добавить его в учебную группу.</p>

<h3 id="from-pupil-card">Добавление из карточки ученика</h3>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Перейдите на вкладку <strong>«Обучение»</strong></li>
    <li>Нажмите кнопку <strong>«Добавить в группу»</strong></li>
    <li>Выберите группу из списка</li>
    <li>Укажите дату начала обучения</li>
    <li>Выберите тариф для расчёта стоимости</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="select-tariff">Выбор тарифа</h3>
<p>При добавлении в группу необходимо выбрать тариф:</p>
<ul>
    <li>Тариф определяет стоимость обучения</li>
    <li>Количество занятий в абонементе</li>
    <li>Период действия (если ограничен)</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Несколько групп</div>
            <div class="text-green-700 text-sm mt-1">Один ученик может быть записан в несколько групп одновременно. Например, на математику и английский язык.</div>
        </div>
    </div>
</div>

<h3 id="copy-education">Копирование обучения</h3>
<p>Если нужно записать ученика в те же группы, что и другой ученик:</p>
<ol>
    <li>На вкладке <strong>«Обучение»</strong> нажмите <strong>«Копировать»</strong></li>
    <li>Выберите ученика, чьё обучение хотите скопировать</li>
    <li>Подтвердите копирование</li>
</ol>

<h3 id="end-education">Завершение обучения</h3>
<p>Чтобы завершить обучение ученика в группе:</p>
<ol>
    <li>Откройте вкладку <strong>«Обучение»</strong> в карточке ученика</li>
    <li>Найдите нужную группу</li>
    <li>Нажмите <strong>«Завершить»</strong> или укажите дату окончания</li>
</ol>
HTML;
    }

    private function getPupilPaymentsContent(): string
    {
        return <<<HTML
<h2 id="payments-history">История платежей ученика</h2>
<p>В карточке ученика доступна полная история всех платежей и текущий баланс.</p>

<h3 id="view-payments">Просмотр платежей</h3>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Перейдите на вкладку <strong>«Платежи»</strong></li>
    <li>Просмотрите список всех операций</li>
</ol>

<h3 id="payment-info">Информация о платеже</h3>
<p>Для каждого платежа отображается:</p>
<ul>
    <li><strong>Дата и время</strong> — когда был совершён платёж</li>
    <li><strong>Сумма</strong> — размер платежа</li>
    <li><strong>Тип</strong> — оплата, возврат или списание</li>
    <li><strong>Способ оплаты</strong> — наличные, карта, перевод</li>
    <li><strong>Комментарий</strong> — дополнительная информация</li>
</ul>

<h3 id="balance">Баланс ученика</h3>
<p>В верхней части вкладки отображается текущий баланс:</p>
<ul>
    <li><strong>Положительный баланс</strong> — у ученика есть предоплата</li>
    <li><strong>Отрицательный баланс</strong> — ученик должен за занятия</li>
    <li><strong>Нулевой баланс</strong> — все оплачено</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-calculator text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Автоматический расчёт</div>
            <div class="text-blue-700 text-sm mt-1">Баланс автоматически пересчитывается при каждом платеже и после каждого посещённого занятия.</div>
        </div>
    </div>
</div>

<h3 id="add-payment">Добавление платежа</h3>
<ol>
    <li>На вкладке <strong>«Платежи»</strong> нажмите <strong>«Принять платёж»</strong></li>
    <li>Введите сумму платежа</li>
    <li>Выберите способ оплаты</li>
    <li>При необходимости добавьте комментарий</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="print-receipt">Печать квитанции</h3>
<p>Для печати квитанции об оплате:</p>
<ol>
    <li>Найдите нужный платёж в списке</li>
    <li>Нажмите на иконку принтера</li>
    <li>Распечатайте открывшуюся квитанцию</li>
</ol>
HTML;
    }

    private function getDeletePupilContent(): string
    {
        return <<<HTML
<h2 id="delete">Удаление ученика</h2>
<p>При необходимости ученика можно удалить из системы. Удаление является «мягким» — данные сохраняются в базе, но скрываются из интерфейса.</p>

<h3 id="before-delete">Перед удалением</h3>
<p>Убедитесь, что:</p>
<ul>
    <li>У ученика нулевой или положительный баланс</li>
    <li>Ученик исключён из всех групп</li>
    <li>Нет незавершённых платежей</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Удаление ученика доступно только пользователям с правами администратора или директора.</div>
        </div>
    </div>
</div>

<h3 id="delete-process">Процесс удаления</h3>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Нажмите кнопку <strong>«Удалить»</strong></li>
    <li>Подтвердите действие в диалоговом окне</li>
</ol>

<h3 id="after-delete">После удаления</h3>
<p>После удаления:</p>
<ul>
    <li>Ученик исчезнет из списка учеников</li>
    <li>История посещений и платежей сохранится в системе</li>
    <li>Данные можно восстановить при необходимости (через поддержку)</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Альтернатива удалению</div>
            <div class="text-green-700 text-sm mt-1">Вместо удаления рекомендуется завершить обучение ученика во всех группах. Так данные останутся доступными для отчётов.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getAddPupilsToGroupContent(): string
    {
        return <<<HTML
<h2 id="add-pupils">Добавление учеников в группу</h2>
<p>Добавить учеников в группу можно из карточки группы.</p>

<h3 id="open-group">Открытие группы</h3>
<ol>
    <li>Перейдите в раздел <strong>Группы</strong></li>
    <li>Найдите нужную группу в списке</li>
    <li>Нажмите на название группы</li>
    <li>Перейдите на вкладку <strong>«Ученики»</strong></li>
</ol>

<h3 id="add-single">Добавление одного ученика</h3>
<ol>
    <li>На вкладке «Ученики» нажмите <strong>«Добавить ученика»</strong></li>
    <li>Выберите ученика из списка или найдите по имени</li>
    <li>Укажите дату начала обучения</li>
    <li>Выберите тариф</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="group-info">Информация в списке</h3>
<p>Для каждого ученика в группе отображается:</p>
<ul>
    <li><strong>ФИО</strong> — имя ученика (ссылка на карточку)</li>
    <li><strong>Телефон</strong> — контактный номер</li>
    <li><strong>Баланс</strong> — текущий баланс ученика</li>
    <li><strong>Дата начала</strong> — когда начал обучение</li>
    <li><strong>Тариф</strong> — выбранный тарифный план</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Цветовая индикация баланса</div>
            <div class="text-blue-700 text-sm mt-1">Зелёный — положительный баланс, красный — отрицательный (задолженность), серый — нулевой баланс.</div>
        </div>
    </div>
</div>

<h3 id="remove-pupil">Исключение из группы</h3>
<ol>
    <li>Найдите ученика в списке группы</li>
    <li>Нажмите кнопку <strong>«Исключить»</strong></li>
    <li>Подтвердите действие</li>
</ol>
HTML;
    }

    private function getAssignTeacherContent(): string
    {
        return <<<HTML
<h2 id="assign">Назначение преподавателя</h2>
<p>К каждой группе можно назначить одного или нескольких преподавателей.</p>

<h3 id="open-teachers">Открытие списка преподавателей</h3>
<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Преподаватели»</strong></li>
</ol>

<h3 id="add-teacher">Добавление преподавателя</h3>
<ol>
    <li>Нажмите кнопку <strong>«Назначить преподавателя»</strong></li>
    <li>Выберите преподавателя из списка сотрудников</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Несколько преподавателей</div>
            <div class="text-green-700 text-sm mt-1">Группа может иметь несколько преподавателей. Это удобно для занятий с разными предметами или для подмены.</div>
        </div>
    </div>
</div>

<h3 id="teacher-access">Доступ преподавателя</h3>
<p>После назначения преподаватель получает доступ к:</p>
<ul>
    <li>Просмотру расписания группы</li>
    <li>Созданию занятий для группы</li>
    <li>Отметке посещаемости учеников</li>
    <li>Просмотру списка учеников группы</li>
</ul>

<h3 id="remove-teacher">Удаление преподавателя из группы</h3>
<ol>
    <li>На вкладке «Преподаватели» найдите нужного</li>
    <li>Нажмите кнопку <strong>«Удалить»</strong></li>
    <li>Подтвердите действие</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Внимание</div>
            <div class="text-yellow-700 text-sm mt-1">При удалении преподавателя из группы он теряет доступ к её расписанию и ученикам, но ранее проведённые занятия сохраняются.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getGroupTariffsContent(): string
    {
        return <<<HTML
<h2 id="tariffs">Настройка тарифов группы</h2>
<p>Тарифы определяют стоимость обучения и условия оплаты для учеников группы.</p>

<h3 id="default-tariff">Тариф по умолчанию</h3>
<p>При создании группы можно указать тариф по умолчанию:</p>
<ul>
    <li>Этот тариф будет предлагаться при добавлении учеников</li>
    <li>Упрощает работу при массовом добавлении</li>
    <li>Ученики могут иметь индивидуальный тариф</li>
</ul>

<h3 id="individual-tariff">Индивидуальный тариф</h3>
<p>Каждому ученику в группе можно назначить свой тариф:</p>
<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Ученики»</strong></li>
    <li>Нажмите на иконку редактирования у ученика</li>
    <li>Выберите другой тариф</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="tariff-calculation">Расчёт стоимости</h3>
<p>Система автоматически рассчитывает:</p>
<ul>
    <li><strong>Стоимость занятия</strong> — цена тарифа / количество занятий</li>
    <li><strong>Списание за посещение</strong> — вычитается из баланса ученика</li>
    <li><strong>Остаток занятий</strong> — сколько занятий осталось по абонементу</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-calculator text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Пример расчёта</div>
            <div class="text-blue-700 text-sm mt-1">Тариф «8 занятий» стоит 24 000 ₸. Стоимость одного занятия: 24 000 / 8 = 3 000 ₸. После каждого посещения с баланса списывается 3 000 ₸.</div>
        </div>
    </div>
</div>

<h3 id="change-tariff">Смена тарифа</h3>
<p>При смене тарифа:</p>
<ul>
    <li>Новый тариф применяется к будущим занятиям</li>
    <li>Прошлые списания не пересчитываются</li>
    <li>Баланс ученика не изменяется автоматически</li>
</ul>
HTML;
    }
}
