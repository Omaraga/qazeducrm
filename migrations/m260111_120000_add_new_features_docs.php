<?php

use yii\db\Migration;

/**
 * Добавляет документацию для новых функций:
 * - Пробные занятия
 * - Домашние задания
 * - Аналитика и KPI
 */
class m260111_120000_add_new_features_docs extends Migration
{
    public function safeUp()
    {
        // Создаём новую главу "Новые функции"
        $this->insert('{{%docs_chapter}}', [
            'slug' => 'new-features',
            'title' => 'Новые функции',
            'description' => 'Пробные занятия, домашние задания и расширенная аналитика',
            'icon' => 'sparkles',
            'sort_order' => 12,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $chapterId = $this->db->getLastInsertID();

        // Секция 1: Пробные занятия
        $this->insert('{{%docs_section}}', [
            'chapter_id' => $chapterId,
            'slug' => 'trial-lessons',
            'title' => 'Пробные занятия',
            'excerpt' => 'Управление пробными уроками: запись, отслеживание, конверсия в учеников',
            'content' => $this->getTrialLessonsContent(),
            'screenshots' => json_encode(['uploads/docs/trial-index.png', 'uploads/docs/trial-create.png']),
            'sort_order' => 1,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Секция 2: Домашние задания
        $this->insert('{{%docs_section}}', [
            'chapter_id' => $chapterId,
            'slug' => 'homework',
            'title' => 'Домашние задания',
            'excerpt' => 'Создание, назначение и проверка домашних заданий для групп',
            'content' => $this->getHomeworkContent(),
            'screenshots' => json_encode(['uploads/docs/homework-index.png', 'uploads/docs/homework-create.png']),
            'sort_order' => 2,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Секция 3: Аналитика и KPI
        $this->insert('{{%docs_section}}', [
            'chapter_id' => $chapterId,
            'slug' => 'analytics-kpi',
            'title' => 'Аналитика и KPI',
            'excerpt' => 'Расширенные метрики: LTV, конверсия, посещаемость, финансовые показатели',
            'content' => $this->getAnalyticsContent(),
            'screenshots' => json_encode(['uploads/docs/analytics-dashboard.png', 'uploads/docs/analytics-dashboard-full.png']),
            'sort_order' => 3,
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown()
    {
        // Удаляем секции
        $chapter = $this->db->createCommand("SELECT id FROM {{%docs_chapter}} WHERE slug = 'new-features'")->queryScalar();
        if ($chapter) {
            $this->delete('{{%docs_section}}', ['chapter_id' => $chapter]);
            $this->delete('{{%docs_chapter}}', ['id' => $chapter]);
        }
    }

    private function getTrialLessonsContent(): string
    {
        return <<<'HTML'
<p>Модуль <strong>«Пробные занятия»</strong> позволяет эффективно управлять пробными уроками — от записи потенциального ученика до отслеживания конверсии в постоянного клиента.</p>

<h2 id="overview">Обзор модуля</h2>

<p>Пробные занятия — важный инструмент привлечения новых учеников. Модуль помогает:</p>

<ul>
    <li>Записывать лидов на пробные занятия</li>
    <li>Отслеживать статус каждого пробного урока</li>
    <li>Анализировать конверсию «пробное → оплата»</li>
    <li>Автоматически напоминать о предстоящих пробных</li>
</ul>

<figure class="my-6">
    <img src="/uploads/docs/trial-index.png" alt="Список пробных занятий" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Список пробных занятий со статистикой</figcaption>
</figure>

<h2 id="access">Доступ к модулю</h2>

<p>Модуль доступен в боковом меню:</p>

<ol>
    <li>Найдите пункт <strong>«Пробные занятия»</strong> с иконкой звезды</li>
    <li>Кликните для перехода к списку пробных занятий</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Права доступа</div>
            <div class="text-blue-700 text-sm mt-1">Модуль доступен пользователям с ролью Администратор и выше. Учителя видят только пробные занятия своих групп.</div>
        </div>
    </div>
</div>

<h2 id="statistics">Статистика</h2>

<p>В верхней части страницы отображается статистика:</p>

<ul>
    <li><strong>Всего</strong> — общее количество пробных занятий</li>
    <li><strong>Запланировано</strong> — предстоящие пробные уроки</li>
    <li><strong>Проведено</strong> — состоявшиеся пробные</li>
    <li><strong>Конвертировано</strong> — пробные, которые привели к оплате</li>
</ul>

<h2 id="create">Создание пробного занятия</h2>

<p>Для записи на пробное занятие:</p>

<ol>
    <li>Нажмите кнопку <strong>«Записать на пробное»</strong></li>
    <li>Заполните форму:</li>
</ol>

<figure class="my-6">
    <img src="/uploads/docs/trial-create.png" alt="Форма записи на пробное занятие" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Форма записи на пробное занятие</figcaption>
</figure>

<h3 id="form-fields">Поля формы</h3>

<ul>
    <li><strong>Лид</strong> — выберите потенциального клиента из базы лидов (обязательно)</li>
    <li><strong>Группа</strong> — группа для пробного занятия (обязательно)</li>
    <li><strong>Дата</strong> — дата пробного урока</li>
    <li><strong>Время</strong> — время начала занятия</li>
    <li><strong>Комментарий</strong> — дополнительная информация</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Совет</div>
            <div class="text-green-700 text-sm mt-1">Записывайте пробное на время обычного занятия группы — так потенциальный ученик увидит реальный учебный процесс.</div>
        </div>
    </div>
</div>

<h2 id="statuses">Статусы пробных занятий</h2>

<p>Каждое пробное занятие имеет один из статусов:</p>

<table class="min-w-full divide-y divide-gray-200 my-4">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Описание</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">Запланировано</span></td>
            <td class="px-4 py-2 text-sm">Пробное занятие назначено на будущую дату</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Не пришёл</span></td>
            <td class="px-4 py-2 text-sm">Клиент не явился на пробное занятие</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Проведено</span></td>
            <td class="px-4 py-2 text-sm">Пробное занятие состоялось</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">Конвертировано</span></td>
            <td class="px-4 py-2 text-sm">Клиент оплатил и стал учеником</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">Отменено</span></td>
            <td class="px-4 py-2 text-sm">Пробное занятие отменено</td>
        </tr>
    </tbody>
</table>

<h2 id="filters">Фильтрация</h2>

<p>Используйте фильтры для поиска пробных занятий:</p>

<ul>
    <li><strong>По статусу</strong> — показать только запланированные, проведённые и т.д.</li>
    <li><strong>По дате</strong> — пробные за определённый период</li>
    <li><strong>По группе</strong> — пробные для конкретной группы</li>
</ul>

<h2 id="workflow">Рабочий процесс</h2>

<p>Типичный workflow работы с пробными:</p>

<ol>
    <li><strong>Создание лида</strong> — поступает заявка от потенциального клиента</li>
    <li><strong>Запись на пробное</strong> — назначается дата и группа</li>
    <li><strong>Напоминание</strong> — система отправляет SMS/WhatsApp за день до пробного</li>
    <li><strong>Проведение</strong> — преподаватель проводит пробный урок</li>
    <li><strong>Обратная связь</strong> — заполняется результат пробного</li>
    <li><strong>Конверсия</strong> — при оплате статус меняется на «Конвертировано»</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Не забывайте обновлять статус пробного занятия — это влияет на статистику конверсии и помогает анализировать эффективность.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getHomeworkContent(): string
    {
        return <<<'HTML'
<p>Модуль <strong>«Домашние задания»</strong> позволяет преподавателям создавать и отслеживать выполнение домашних заданий учениками.</p>

<h2 id="overview">Обзор модуля</h2>

<p>Домашние задания — неотъемлемая часть учебного процесса. Модуль обеспечивает:</p>

<ul>
    <li>Создание заданий для групп</li>
    <li>Прикрепление файлов и материалов</li>
    <li>Отслеживание сдачи работ</li>
    <li>Проверку и оценивание</li>
</ul>

<figure class="my-6">
    <img src="/uploads/docs/homework-index.png" alt="Список домашних заданий" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Список домашних заданий с информацией о сдаче</figcaption>
</figure>

<h2 id="access">Доступ к модулю</h2>

<p>Модуль находится в боковом меню:</p>

<ol>
    <li>Найдите пункт <strong>«Домашние задания»</strong> с иконкой документа</li>
    <li>Кликните для перехода к списку заданий</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Права доступа</div>
            <div class="text-blue-700 text-sm mt-1">Преподаватели видят задания только своих групп. Администраторы и директора видят все задания организации.</div>
        </div>
    </div>
</div>

<h2 id="create">Создание задания</h2>

<p>Для создания нового домашнего задания:</p>

<ol>
    <li>Нажмите кнопку <strong>«Создать задание»</strong></li>
    <li>Заполните форму задания</li>
</ol>

<figure class="my-6">
    <img src="/uploads/docs/homework-create.png" alt="Форма создания домашнего задания" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Форма создания домашнего задания</figcaption>
</figure>

<h3 id="form-fields">Поля формы</h3>

<ul>
    <li><strong>Группа</strong> — выберите группу для задания (обязательно)</li>
    <li><strong>Название</strong> — краткое название задания, например «Выучить слова Unit 5» (обязательно)</li>
    <li><strong>Срок сдачи</strong> — дата, до которой нужно сдать задание (обязательно)</li>
    <li><strong>Описание</strong> — подробное описание задания</li>
    <li><strong>Статус</strong> — активное или архивное задание</li>
    <li><strong>Файлы</strong> — прикрепите материалы (PDF, Word, Excel, изображения)</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Совет</div>
            <div class="text-green-700 text-sm mt-1">Давайте заданиям понятные названия — это поможет ученикам и родителям быстро понять суть задания.</div>
        </div>
    </div>
</div>

<h2 id="statuses">Статусы заданий</h2>

<table class="min-w-full divide-y divide-gray-200 my-4">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Описание</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Активное</span></td>
            <td class="px-4 py-2 text-sm">Задание доступно для выполнения</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">Архивное</span></td>
            <td class="px-4 py-2 text-sm">Задание завершено или скрыто</td>
        </tr>
    </tbody>
</table>

<h2 id="tracking">Отслеживание сдачи</h2>

<p>В списке заданий отображается информация о сдаче:</p>

<ul>
    <li><strong>Сдано X/Y</strong> — сколько учеников сдали из общего количества в группе</li>
    <li><strong>Z проверено</strong> — сколько работ уже проверено преподавателем</li>
    <li><strong>Просрочено</strong> — отметка красным, если срок сдачи истёк</li>
</ul>

<h2 id="view">Просмотр задания</h2>

<p>При клике на задание открывается детальная страница:</p>

<ul>
    <li>Полное описание и прикреплённые файлы</li>
    <li>Список учеников группы с их статусами</li>
    <li>Возможность отметить сдачу и выставить оценку</li>
</ul>

<h3 id="submission-statuses">Статусы сдачи</h3>

<table class="min-w-full divide-y divide-gray-200 my-4">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Описание</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">Не сдано</span></td>
            <td class="px-4 py-2 text-sm">Ученик ещё не сдал задание</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">На проверке</span></td>
            <td class="px-4 py-2 text-sm">Работа сдана, ожидает проверки</td>
        </tr>
        <tr>
            <td class="px-4 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Проверено</span></td>
            <td class="px-4 py-2 text-sm">Преподаватель проверил работу</td>
        </tr>
    </tbody>
</table>

<h2 id="grading">Оценивание работ</h2>

<p>Для проверки работы ученика:</p>

<ol>
    <li>Откройте страницу задания</li>
    <li>Найдите ученика в списке</li>
    <li>Измените статус на <strong>«Проверено»</strong></li>
    <li>Выставьте оценку (опционально)</li>
    <li>Добавьте комментарий (опционально)</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Регулярно проверяйте сданные работы — это мотивирует учеников выполнять задания вовремя.</div>
        </div>
    </div>
</div>

<h2 id="filters">Фильтрация</h2>

<p>Используйте фильтры для поиска заданий:</p>

<ul>
    <li><strong>По статусу</strong> — активные или архивные</li>
    <li><strong>По сроку</strong> — задания за определённый период</li>
    <li><strong>По названию</strong> — поиск по тексту</li>
</ul>
HTML;
    }

    private function getAnalyticsContent(): string
    {
        return <<<'HTML'
<p>Страница <strong>«Аналитика и KPI»</strong> предоставляет расширенные метрики для принятия управленческих решений.</p>

<h2 id="overview">Обзор</h2>

<p>KPI Dashboard — инструмент для директоров и руководителей. Он показывает ключевые показатели эффективности учебного центра:</p>

<ul>
    <li>Финансовые метрики</li>
    <li>Показатели работы с лидами</li>
    <li>Статистика посещаемости</li>
    <li>Эффективность преподавателей</li>
</ul>

<figure class="my-6">
    <img src="/uploads/docs/analytics-dashboard.png" alt="KPI Dashboard" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Страница аналитики с ключевыми метриками</figcaption>
</figure>

<h2 id="access">Доступ</h2>

<p>Страница аналитики доступна в меню <strong>«Отчёты» → «KPI Dashboard»</strong>.</p>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Права доступа</div>
            <div class="text-blue-700 text-sm mt-1">Страница аналитики доступна только пользователям с ролью Директор и выше.</div>
        </div>
    </div>
</div>

<h2 id="financial-metrics">Финансовые метрики</h2>

<h3 id="ltv">LTV (Lifetime Value)</h3>

<p><strong>Пожизненная ценность клиента</strong> — средняя сумма, которую приносит один ученик за всё время обучения.</p>

<ul>
    <li><strong>Средний LTV</strong> — средняя сумма платежей на одного ученика</li>
    <li><strong>Топ учеников</strong> — ученики с наибольшим LTV</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Применение</div>
            <div class="text-green-700 text-sm mt-1">LTV помогает оценить, сколько можно потратить на привлечение одного клиента. Если LTV = 200,000₸, а стоимость привлечения = 20,000₸, это хороший показатель.</div>
        </div>
    </div>
</div>

<h3 id="revenue">Доходы</h3>

<ul>
    <li><strong>Доход за период</strong> — сумма всех платежей за выбранный период</li>
    <li><strong>Средний чек</strong> — средняя сумма одного платежа</li>
    <li><strong>Доход по группам</strong> — распределение дохода по учебным группам</li>
</ul>

<h3 id="debt">Задолженность</h3>

<ul>
    <li><strong>Общая задолженность</strong> — сумма отрицательных балансов учеников</li>
    <li><strong>Количество должников</strong> — ученики с отрицательным балансом</li>
</ul>

<h2 id="leads-metrics">Метрики лидов</h2>

<h3 id="conversion">Конверсия воронки</h3>

<p>Показывает эффективность работы с потенциальными клиентами:</p>

<ul>
    <li><strong>Общая конверсия</strong> — % лидов, ставших учениками</li>
    <li><strong>По этапам</strong> — конверсия на каждом этапе воронки</li>
    <li><strong>По источникам</strong> — какие каналы приносят больше конверсий</li>
</ul>

<h3 id="source-efficiency">Эффективность источников</h3>

<table class="min-w-full divide-y divide-gray-200 my-4">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Источник</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Лидов</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Конверсия</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr>
            <td class="px-4 py-2 text-sm">Instagram</td>
            <td class="px-4 py-2 text-sm">150</td>
            <td class="px-4 py-2 text-sm">12%</td>
        </tr>
        <tr>
            <td class="px-4 py-2 text-sm">Рекомендации</td>
            <td class="px-4 py-2 text-sm">80</td>
            <td class="px-4 py-2 text-sm">35%</td>
        </tr>
        <tr>
            <td class="px-4 py-2 text-sm">Сайт</td>
            <td class="px-4 py-2 text-sm">60</td>
            <td class="px-4 py-2 text-sm">20%</td>
        </tr>
    </tbody>
</table>

<h2 id="attendance-metrics">Метрики посещаемости</h2>

<h3 id="attendance-rate">Средняя посещаемость</h3>

<p>Показывает % посещений занятий:</p>

<ul>
    <li><strong>По организации</strong> — общая посещаемость</li>
    <li><strong>По группам</strong> — посещаемость каждой группы</li>
    <li><strong>По периодам</strong> — динамика посещаемости</li>
</ul>

<h3 id="churn">Отток учеников (Churn Rate)</h3>

<p>Показывает количество учеников, которые:</p>

<ul>
    <li>Не посещали занятия более 30 дней</li>
    <li>Отчислены или завершили обучение</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">На что обратить внимание</div>
            <div class="text-yellow-700 text-sm mt-1">Высокий Churn Rate (более 10% в месяц) сигнализирует о проблемах — возможно, нужно улучшить качество обучения или коммуникацию с родителями.</div>
        </div>
    </div>
</div>

<h2 id="teacher-metrics">Эффективность преподавателей</h2>

<p>Метрики работы преподавателей:</p>

<ul>
    <li><strong>Посещаемость групп</strong> — средняя посещаемость по группам преподавателя</li>
    <li><strong>Количество учеников</strong> — сколько учеников у каждого преподавателя</li>
    <li><strong>Нагрузка</strong> — количество часов в неделю</li>
</ul>

<h2 id="period-filter">Фильтр по периоду</h2>

<p>Все метрики можно посмотреть за разные периоды:</p>

<ul>
    <li>Сегодня</li>
    <li>Эта неделя</li>
    <li>Этот месяц</li>
    <li>Этот год</li>
    <li>Произвольный период</li>
</ul>

<h2 id="export">Экспорт данных</h2>

<p>Данные можно экспортировать для дальнейшего анализа:</p>

<ul>
    <li><strong>Excel</strong> — для работы в таблицах</li>
    <li><strong>PDF</strong> — для отчётов и презентаций</li>
</ul>

<figure class="my-6">
    <img src="/uploads/docs/analytics-dashboard-full.png" alt="Полная страница аналитики" class="rounded-lg border shadow-sm w-full">
    <figcaption class="text-sm text-gray-500 mt-2 text-center">Полная страница аналитики со всеми метриками</figcaption>
</figure>
HTML;
    }
}
