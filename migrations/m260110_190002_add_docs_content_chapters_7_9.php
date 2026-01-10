<?php

use yii\db\Migration;

/**
 * Добавляет контент документации для глав 7-9
 */
class m260110_190002_add_docs_content_chapters_7_9 extends Migration
{
    public function safeUp()
    {
        // Глава 7: payment-methods
        $this->update('{{%docs_section}}', [
            'content' => $this->getPaymentMethodsContent(),
        ], ['chapter_id' => 7, 'slug' => 'payment-methods']);

        // Глава 7: payment-history
        $this->update('{{%docs_section}}', [
            'content' => $this->getPaymentHistoryContent(),
        ], ['chapter_id' => 7, 'slug' => 'payment-history']);

        // Глава 7: income-reports
        $this->update('{{%docs_section}}', [
            'content' => $this->getIncomeReportsContent(),
        ], ['chapter_id' => 7, 'slug' => 'income-reports']);

        // Глава 8: setup-rates
        $this->update('{{%docs_section}}', [
            'content' => $this->getSetupRatesContent(),
        ], ['chapter_id' => 8, 'slug' => 'setup-rates']);

        // Глава 8: pay-salary
        $this->update('{{%docs_section}}', [
            'content' => $this->getPaySalaryContent(),
        ], ['chapter_id' => 8, 'slug' => 'pay-salary']);

        // Глава 8: salary-history
        $this->update('{{%docs_section}}', [
            'content' => $this->getSalaryHistoryContent(),
        ], ['chapter_id' => 8, 'slug' => 'salary-history']);

        // Глава 9: create-lead
        $this->update('{{%docs_section}}', [
            'content' => $this->getCreateLeadContent(),
        ], ['chapter_id' => 9, 'slug' => 'create-lead']);

        // Глава 9: funnel-stages
        $this->update('{{%docs_section}}', [
            'content' => $this->getFunnelStagesContent(),
        ], ['chapter_id' => 9, 'slug' => 'funnel-stages']);

        // Глава 9: convert-to-pupil
        $this->update('{{%docs_section}}', [
            'content' => $this->getConvertToPupilContent(),
        ], ['chapter_id' => 9, 'slug' => 'convert-to-pupil']);

        // Глава 9: lead-analytics
        $this->update('{{%docs_section}}', [
            'content' => $this->getLeadAnalyticsContent(),
        ], ['chapter_id' => 9, 'slug' => 'lead-analytics']);
    }

    public function safeDown()
    {
        $sections = [
            ['chapter_id' => 7, 'slug' => 'payment-methods'],
            ['chapter_id' => 7, 'slug' => 'payment-history'],
            ['chapter_id' => 7, 'slug' => 'income-reports'],
            ['chapter_id' => 8, 'slug' => 'setup-rates'],
            ['chapter_id' => 8, 'slug' => 'pay-salary'],
            ['chapter_id' => 8, 'slug' => 'salary-history'],
            ['chapter_id' => 9, 'slug' => 'create-lead'],
            ['chapter_id' => 9, 'slug' => 'funnel-stages'],
            ['chapter_id' => 9, 'slug' => 'convert-to-pupil'],
            ['chapter_id' => 9, 'slug' => 'lead-analytics'],
        ];
        foreach ($sections as $s) {
            $this->update('{{%docs_section}}', ['content' => null], $s);
        }
    }

    private function getPaymentMethodsContent(): string
    {
        return <<<HTML
<h2 id="methods">Способы оплаты</h2>
<p>При приёме платежа выбирается способ оплаты для корректного учёта и отчётности.</p>

<h3 id="default-methods">Стандартные способы</h3>
<ul>
    <li><strong>Наличные</strong> — оплата наличными в кассу</li>
    <li><strong>Карта</strong> — оплата банковской картой через терминал</li>
    <li><strong>Перевод</strong> — банковский перевод на счёт организации</li>
    <li><strong>Kaspi</strong> — перевод через Kaspi Bank (популярно в Казахстане)</li>
</ul>

<h3 id="select-method">Выбор способа при оплате</h3>
<ol>
    <li>При создании платежа найдите поле <strong>«Способ оплаты»</strong></li>
    <li>Выберите соответствующий метод из списка</li>
    <li>Способ сохраняется в истории платежа</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Отчётность</div>
            <div class="text-blue-700 text-sm mt-1">Способ оплаты используется в финансовых отчётах для разделения наличных и безналичных поступлений.</div>
        </div>
    </div>
</div>

<h3 id="custom-methods">Свои способы оплаты</h3>
<p>Добавление собственного способа:</p>
<ol>
    <li>Перейдите в <strong>Настройки → Способы оплаты</strong></li>
    <li>Нажмите <strong>«Добавить»</strong></li>
    <li>Введите название способа</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="disable-method">Отключение способа</h3>
<p>Неиспользуемые способы можно отключить:</p>
<ol>
    <li>В списке способов найдите нужный</li>
    <li>Нажмите переключатель для деактивации</li>
    <li>Способ исчезнет из выпадающего списка при оплате</li>
</ol>
HTML;
    }

    private function getPaymentHistoryContent(): string
    {
        return <<<HTML
<h2 id="history">История платежей</h2>
<p>Полная история всех платежей организации доступна в разделе «Платежи».</p>

<h3 id="view-all">Просмотр всех платежей</h3>
<ol>
    <li>Перейдите в раздел <strong>Платежи</strong></li>
    <li>Отобразится список всех операций</li>
</ol>

<h3 id="filters">Фильтрация</h3>
<p>Используйте фильтры для поиска нужных платежей:</p>
<ul>
    <li><strong>Период</strong> — выберите даты начала и окончания</li>
    <li><strong>Тип</strong> — оплата, возврат, списание</li>
    <li><strong>Способ</strong> — наличные, карта, перевод</li>
    <li><strong>Ученик</strong> — платежи конкретного ученика</li>
</ul>

<h3 id="payment-details">Детали платежа</h3>
<p>Для каждого платежа отображается:</p>
<ul>
    <li>Дата и время операции</li>
    <li>ФИО ученика</li>
    <li>Сумма (положительная — оплата, отрицательная — возврат)</li>
    <li>Способ оплаты</li>
    <li>Кто принял платёж</li>
    <li>Комментарий (если есть)</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-search text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Быстрый поиск</div>
            <div class="text-green-700 text-sm mt-1">Введите имя ученика или сумму в поле поиска для быстрой фильтрации списка.</div>
        </div>
    </div>
</div>

<h3 id="edit-payment">Редактирование платежа</h3>
<p>Для изменения платежа:</p>
<ol>
    <li>Найдите платёж в списке</li>
    <li>Нажмите на строку для открытия деталей</li>
    <li>Нажмите <strong>«Редактировать»</strong></li>
    <li>Внесите изменения и сохраните</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-lock text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Ограничения</div>
            <div class="text-yellow-700 text-sm mt-1">Редактирование и удаление платежей может требовать подтверждения директора (зависит от настроек прав доступа).</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getIncomeReportsContent(): string
    {
        return <<<HTML
<h2 id="income">Отчёты по доходам</h2>
<p>Финансовые отчёты помогают анализировать поступления и контролировать доходы организации.</p>

<h3 id="daily-report">Отчёт за день</h3>
<p>Показывает все поступления за выбранный день:</p>
<ol>
    <li>Перейдите в <strong>Платежи</strong></li>
    <li>Установите фильтр по дате</li>
    <li>Просмотрите итоговую сумму внизу списка</li>
</ol>

<h3 id="period-report">Отчёт за период</h3>
<p>Для анализа за произвольный период:</p>
<ol>
    <li>Укажите даты начала и окончания</li>
    <li>Примените фильтр</li>
    <li>Система покажет все платежи и итоговую сумму</li>
</ol>

<h3 id="by-method">Разбивка по способам оплаты</h3>
<p>Отчёт показывает суммы по каждому способу:</p>
<ul>
    <li>Наличные: X ₸</li>
    <li>Карта: Y ₸</li>
    <li>Перевод: Z ₸</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-chart-pie text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Визуализация</div>
            <div class="text-blue-700 text-sm mt-1">На главной странице (Dashboard) отображается график поступлений за последние 7 дней.</div>
        </div>
    </div>
</div>

<h3 id="export-report">Экспорт отчёта</h3>
<ol>
    <li>Сформируйте нужный отчёт с фильтрами</li>
    <li>Нажмите кнопку <strong>«Экспорт»</strong></li>
    <li>Выберите формат: Excel или PDF</li>
    <li>Скачайте файл</li>
</ol>

<h3 id="refunds">Учёт возвратов</h3>
<p>Возвраты отображаются отдельно:</p>
<ul>
    <li>В списке платежей выделены красным цветом</li>
    <li>Вычитаются из общей суммы дохода</li>
    <li>Можно отфильтровать только возвраты</li>
</ul>
HTML;
    }

    private function getSetupRatesContent(): string
    {
        return <<<HTML
<h2 id="setup">Настройка ставок преподавателей</h2>
<p>Перед расчётом зарплаты необходимо настроить ставки для каждого преподавателя.</p>

<h3 id="open-rates">Открытие раздела ставок</h3>
<ol>
    <li>Перейдите в <strong>Зарплаты → Ставки</strong></li>
    <li>Отобразится список всех настроенных ставок</li>
</ol>

<h3 id="rate-types">Типы ставок</h3>
<ul>
    <li><strong>Фиксированная за занятие</strong> — одинаковая сумма за каждое занятие</li>
    <li><strong>Процент от оплаты</strong> — доля от суммы, оплаченной учениками группы</li>
    <li><strong>Почасовая</strong> — оплата за фактическое время (сумма × часы)</li>
</ul>

<h3 id="create-rate">Создание ставки</h3>
<ol>
    <li>Нажмите <strong>«Добавить ставку»</strong></li>
    <li>Выберите преподавателя</li>
    <li>Выберите тип ставки</li>
    <li>Введите размер (сумма или процент)</li>
    <li>При необходимости укажите группу для индивидуальной ставки</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Разные ставки для групп</div>
            <div class="text-green-700 text-sm mt-1">Можно задать разные ставки для разных групп одного преподавателя. Например, для VIP-группы — выше.</div>
        </div>
    </div>
</div>

<h3 id="edit-rate">Редактирование ставки</h3>
<ol>
    <li>Найдите ставку в списке</li>
    <li>Нажмите <strong>«Редактировать»</strong></li>
    <li>Измените параметры</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Изменение ставки влияет только на будущие расчёты. Уже рассчитанные зарплаты не пересчитываются автоматически.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getPaySalaryContent(): string
    {
        return <<<HTML
<h2 id="pay">Выплата зарплаты</h2>
<p>После расчёта и утверждения зарплату можно отметить как выплаченную.</p>

<h3 id="workflow">Процесс выплаты</h3>
<ol>
    <li><strong>Расчёт</strong> — система рассчитывает сумму на основе занятий и ставки</li>
    <li><strong>Проверка</strong> — администратор проверяет корректность расчёта</li>
    <li><strong>Утверждение</strong> — директор утверждает зарплату</li>
    <li><strong>Выплата</strong> — отметка о фактической выплате</li>
</ol>

<h3 id="approve">Утверждение зарплаты</h3>
<ol>
    <li>Откройте рассчитанную зарплату</li>
    <li>Проверьте данные: количество занятий, ставку, сумму</li>
    <li>При необходимости добавьте бонус или удержание</li>
    <li>Нажмите <strong>«Утвердить»</strong></li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Права доступа</div>
            <div class="text-blue-700 text-sm mt-1">Утверждение и выплата зарплаты доступны только директору или генеральному директору.</div>
        </div>
    </div>
</div>

<h3 id="mark-paid">Отметка о выплате</h3>
<ol>
    <li>После утверждения нажмите <strong>«Выплатить»</strong></li>
    <li>Подтвердите действие</li>
    <li>Статус изменится на «Выплачено»</li>
</ol>

<h3 id="bonus-deduction">Бонусы и удержания</h3>
<p>Перед утверждением можно добавить:</p>
<ul>
    <li><strong>Бонус</strong> — дополнительная сумма (премия, надбавка)</li>
    <li><strong>Удержание</strong> — вычет из зарплаты</li>
    <li><strong>Комментарий</strong> — пояснение к корректировке</li>
</ul>

<h3 id="statuses">Статусы зарплаты</h3>
<ul>
    <li><strong>Черновик</strong> — можно редактировать и пересчитывать</li>
    <li><strong>Утверждено</strong> — готово к выплате, редактирование заблокировано</li>
    <li><strong>Выплачено</strong> — финальный статус</li>
</ul>
HTML;
    }

    private function getSalaryHistoryContent(): string
    {
        return <<<HTML
<h2 id="history">История выплат</h2>
<p>В разделе «Зарплаты» хранится полная история всех начислений и выплат.</p>

<h3 id="view-history">Просмотр истории</h3>
<ol>
    <li>Перейдите в раздел <strong>Зарплаты</strong></li>
    <li>Используйте фильтры для поиска:
        <ul>
            <li>Преподаватель</li>
            <li>Период</li>
            <li>Статус (черновик, утверждено, выплачено)</li>
        </ul>
    </li>
</ol>

<h3 id="salary-details">Детали зарплаты</h3>
<p>Для каждой записи отображается:</p>
<ul>
    <li><strong>Преподаватель</strong> — ФИО сотрудника</li>
    <li><strong>Период</strong> — за какой месяц начислено</li>
    <li><strong>Занятий</strong> — количество проведённых занятий</li>
    <li><strong>Сумма</strong> — итоговая сумма к выплате</li>
    <li><strong>Статус</strong> — текущее состояние</li>
</ul>

<h3 id="breakdown">Детализация начисления</h3>
<p>При открытии зарплаты видна полная детализация:</p>
<ul>
    <li>Список всех проведённых занятий</li>
    <li>Ставка по каждому занятию</li>
    <li>Расчёт суммы</li>
    <li>Бонусы и удержания</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-file-export text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Экспорт</div>
            <div class="text-green-700 text-sm mt-1">Историю выплат можно экспортировать в Excel для бухгалтерского учёта.</div>
        </div>
    </div>
</div>

<h3 id="teacher-view">Просмотр преподавателем</h3>
<p>Преподаватели могут видеть только свои зарплаты:</p>
<ul>
    <li>Историю всех начислений</li>
    <li>Детализацию по занятиям</li>
    <li>Статус выплаты</li>
</ul>
HTML;
    }

    private function getCreateLeadContent(): string
    {
        return <<<HTML
<h2 id="create">Создание лида</h2>
<p>Лид — это потенциальный клиент, который проявил интерес к обучению, но ещё не стал учеником.</p>

<h3 id="when-create">Когда создавать лида</h3>
<ul>
    <li>Поступил звонок с вопросом об обучении</li>
    <li>Оставлена заявка на сайте</li>
    <li>Пришёл запрос в WhatsApp</li>
    <li>Рекомендация от текущих учеников</li>
</ul>

<h3 id="create-form">Создание через форму</h3>
<ol>
    <li>Перейдите в раздел <strong>Лиды</strong></li>
    <li>Нажмите <strong>«Добавить лида»</strong></li>
    <li>Заполните форму:
        <ul>
            <li><strong>ФИО</strong> — имя потенциального ученика</li>
            <li><strong>Телефон</strong> — контактный номер</li>
            <li><strong>Источник</strong> — откуда пришёл (сайт, Instagram, рекомендация)</li>
            <li><strong>Интересующий предмет</strong></li>
            <li><strong>Комментарий</strong> — дополнительная информация</li>
        </ul>
    </li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-comments text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Из WhatsApp</div>
            <div class="text-green-700 text-sm mt-1">При получении сообщения в WhatsApp можно создать лида прямо из чата одним кликом.</div>
        </div>
    </div>
</div>

<h3 id="quick-create">Быстрое создание</h3>
<p>На Kanban-доске:</p>
<ol>
    <li>Нажмите <strong>«+ Добавить»</strong> в колонке «Новый»</li>
    <li>Введите имя и телефон</li>
    <li>Лид появится на доске</li>
</ol>

<h3 id="after-create">После создания</h3>
<p>Новый лид автоматически:</p>
<ul>
    <li>Получает статус «Новый»</li>
    <li>Появляется на Kanban-доске</li>
    <li>Назначается ответственному менеджеру (если настроено)</li>
</ul>
HTML;
    }

    private function getFunnelStagesContent(): string
    {
        return <<<HTML
<h2 id="stages">Этапы воронки продаж</h2>
<p>Воронка продаж помогает отслеживать путь лида от первого контакта до становления учеником.</p>

<h3 id="default-stages">Стандартные этапы</h3>
<ul>
    <li><strong>Новый</strong> — только что поступившая заявка</li>
    <li><strong>В работе</strong> — ведётся первичный контакт</li>
    <li><strong>Интерес</strong> — клиент заинтересован, обсуждаются условия</li>
    <li><strong>Предложение</strong> — отправлено коммерческое предложение</li>
    <li><strong>Переговоры</strong> — обсуждение деталей, возражений</li>
    <li><strong>Оплата</strong> — ожидается оплата</li>
    <li><strong>Сконвертирован</strong> — стал учеником</li>
</ul>

<h3 id="move-stages">Перемещение по этапам</h3>
<p>На Kanban-доске:</p>
<ol>
    <li>Найдите карточку лида</li>
    <li>Перетащите в нужную колонку</li>
    <li>Или откройте карточку и выберите новый статус</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-arrows-alt text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Drag & Drop</div>
            <div class="text-blue-700 text-sm mt-1">Перетаскивайте карточки между колонками для быстрого изменения статуса.</div>
        </div>
    </div>
</div>

<h3 id="lost">Нецелевые лиды</h3>
<p>Если лид не станет клиентом:</p>
<ol>
    <li>Откройте карточку лида</li>
    <li>Нажмите <strong>«Не целевой»</strong></li>
    <li>Укажите причину отказа</li>
    <li>Лид переместится в архив</li>
</ol>

<h3 id="history">История изменений</h3>
<p>Для каждого лида сохраняется история:</p>
<ul>
    <li>Все смены статусов с датами</li>
    <li>Комментарии менеджеров</li>
    <li>Взаимодействия (звонки, сообщения)</li>
</ul>
HTML;
    }

    private function getConvertToPupilContent(): string
    {
        return <<<HTML
<h2 id="convert">Конверсия лида в ученика</h2>
<p>Когда лид готов начать обучение, его можно конвертировать в полноценного ученика.</p>

<h3 id="when-convert">Когда конвертировать</h3>
<ul>
    <li>Клиент внёс оплату</li>
    <li>Подписан договор на обучение</li>
    <li>Согласованы все условия</li>
</ul>

<h3 id="convert-process">Процесс конверсии</h3>
<ol>
    <li>Откройте карточку лида</li>
    <li>Нажмите <strong>«Конвертировать в ученика»</strong></li>
    <li>Проверьте и дополните данные:
        <ul>
            <li>ФИО ученика</li>
            <li>Контактные данные</li>
            <li>Данные родителя (для несовершеннолетних)</li>
        </ul>
    </li>
    <li>Выберите группу для записи</li>
    <li>Выберите тариф</li>
    <li>Нажмите <strong>«Создать ученика»</strong></li>
</ol>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-magic text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Автоматический перенос данных</div>
            <div class="text-green-700 text-sm mt-1">Все данные из карточки лида автоматически переносятся в карточку ученика. Вам остаётся только дополнить недостающую информацию.</div>
        </div>
    </div>
</div>

<h3 id="after-convert">После конверсии</h3>
<ul>
    <li>Создаётся карточка ученика</li>
    <li>Ученик добавляется в выбранную группу</li>
    <li>Лид получает статус «Сконвертирован»</li>
    <li>Связь между лидом и учеником сохраняется</li>
</ul>

<h3 id="link-existing">Привязка к существующему ученику</h3>
<p>Если ученик уже есть в системе (например, записывается на второй предмет):</p>
<ol>
    <li>Откройте карточку лида</li>
    <li>Нажмите <strong>«Привязать к ученику»</strong></li>
    <li>Найдите ученика по имени или телефону</li>
    <li>Подтвердите привязку</li>
</ol>
HTML;
    }

    private function getLeadAnalyticsContent(): string
    {
        return <<<HTML
<h2 id="analytics">Аналитика воронки продаж</h2>
<p>Раздел аналитики помогает оценить эффективность работы с лидами и найти узкие места в воронке.</p>

<h3 id="open-analytics">Открытие аналитики</h3>
<ol>
    <li>Перейдите в <strong>Лиды → Аналитика</strong></li>
    <li>Выберите период для анализа</li>
</ol>

<h3 id="key-metrics">Ключевые метрики</h3>
<ul>
    <li><strong>Всего лидов</strong> — общее количество за период</li>
    <li><strong>Конверсия</strong> — процент лидов, ставших учениками</li>
    <li><strong>Средний цикл</strong> — время от создания до конверсии</li>
    <li><strong>По источникам</strong> — откуда приходят лиды</li>
</ul>

<h3 id="funnel-chart">Воронка конверсии</h3>
<p>График показывает количество лидов на каждом этапе:</p>
<ul>
    <li>Видно, где «отваливается» больше всего клиентов</li>
    <li>Помогает найти проблемные этапы</li>
    <li>Можно сравнить с предыдущим периодом</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-chart-line text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Тренды</div>
            <div class="text-blue-700 text-sm mt-1">Отслеживайте динамику конверсии по месяцам, чтобы оценить эффективность маркетинга и продаж.</div>
        </div>
    </div>
</div>

<h3 id="by-source">Анализ по источникам</h3>
<p>Какие каналы приводят больше клиентов:</p>
<ul>
    <li>Instagram</li>
    <li>Сайт</li>
    <li>Рекомендации</li>
    <li>Звонки</li>
</ul>

<h3 id="by-manager">Анализ по менеджерам</h3>
<p>Эффективность работы сотрудников:</p>
<ul>
    <li>Количество обработанных лидов</li>
    <li>Конверсия в учеников</li>
    <li>Среднее время ответа</li>
</ul>

<h3 id="lost-reasons">Причины потерь</h3>
<p>Статистика по причинам отказов помогает улучшить предложение и работу менеджеров.</p>
HTML;
    }
}
