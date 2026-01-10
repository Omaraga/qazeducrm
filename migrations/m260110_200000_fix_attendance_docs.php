<?php

use yii\db\Migration;

/**
 * Исправляет контент документации раздела "Посещаемость"
 */
class m260110_200000_fix_attendance_docs extends Migration
{
    public function safeUp()
    {
        // Глава 6: mark-attendance - основной раздел
        $this->update('{{%docs_section}}', [
            'content' => $this->getMarkAttendanceContent(),
        ], ['chapter_id' => 6, 'slug' => 'mark-attendance']);

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
        // Откат не требуется
    }

    private function getMarkAttendanceContent(): string
    {
        return <<<HTML
<h2 id="how-to-mark">Как отметить посещаемость</h2>
<p>Посещаемость отмечается прямо в расписании через модальное окно занятия.</p>

<figure class="my-6">
    <img src="/images/docs/attendance/attendance-page.png"
         alt="Информационная страница посещаемости"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Информационная страница с типами посещаемости
    </figcaption>
</figure>

<h3 id="workflow">Процесс отметки посещаемости</h3>
<ol>
    <li>Перейдите в раздел <strong>Расписание</strong></li>
    <li>Найдите нужное занятие в календаре</li>
    <li>Кликните на карточку занятия — откроется модальное окно</li>
    <li>В модальном окне вы увидите список учеников группы</li>
    <li>Для каждого ученика кликните на соответствующую кнопку статуса</li>
</ol>

<figure class="my-6">
    <img src="/images/docs/attendance/attendance-lesson.png"
         alt="Модальное окно занятия с посещаемостью"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Модальное окно занятия с отметкой посещаемости
    </figcaption>
</figure>

<h3 id="quick-actions">Быстрые действия</h3>
<p>В модальном окне есть кнопки для быстрой отметки:</p>
<ul>
    <li><strong>Все +</strong> — отметить всех учеников как присутствующих</li>
    <li><strong>Все −</strong> — отметить всех как отсутствующих</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-check-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Автосохранение</div>
            <div class="text-green-700 text-sm mt-1">Каждый клик по статусу мгновенно сохраняется. Не нужно нажимать отдельную кнопку сохранения.</div>
        </div>
    </div>
</div>

<h3 id="full-page">Полная страница посещаемости</h3>
<p>Для более удобной работы с большими группами можно открыть посещаемость в отдельной вкладке:</p>
<ol>
    <li>В модальном окне занятия рядом с заголовком <strong>«Посещаемость»</strong> есть иконка внешней ссылки</li>
    <li>Кликните на неё — откроется полноценная страница посещаемости в новой вкладке</li>
</ol>

<figure class="my-6">
    <img src="/images/docs/attendance/attendance-full-page.png"
         alt="Полная страница посещаемости занятия"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Полная страница посещаемости с расширенными возможностями
    </figcaption>
</figure>

<p>На полной странице посещаемости доступны:</p>
<ul>
    <li>Информация о занятии: группа, преподаватель, дата, время</li>
    <li>Полный список учеников с радио-кнопками для выбора статуса</li>
    <li>Кнопки «Все присутствовали» и «Все отсутствовали»</li>
    <li>Подробная справка по типам посещаемости</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Когда использовать полную страницу</div>
            <div class="text-blue-700 text-sm mt-1">Полная страница удобна для больших групп (более 10 учеников) или когда нужно видеть подробную информацию о занятии.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getAttendanceStatusesContent(): string
    {
        return <<<HTML
<h2 id="statuses">Типы посещаемости</h2>
<p>Для каждого ученика на занятии устанавливается один из четырёх статусов посещения.</p>

<h3 id="status-types">Доступные статусы</h3>
<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-3 text-left">Статус</th>
            <th class="border p-3 text-left">Описание</th>
            <th class="border p-3 text-left">Влияние на оплату</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="border p-3"><span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 font-medium">Посещение</span></td>
            <td class="border p-3">Ученик был на уроке</td>
            <td class="border p-3">Преподаватель получает оплату за ученика</td>
        </tr>
        <tr>
            <td class="border p-3"><span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-800 font-medium">Пропуск (с оплатой)</span></td>
            <td class="border p-3">Ученика не было, но урок состоялся</td>
            <td class="border p-3">Преподаватель получает оплату. Используется для индивидуальных занятий</td>
        </tr>
        <tr>
            <td class="border p-3"><span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 font-medium">Пропуск (без оплаты)</span></td>
            <td class="border p-3">Ученик не пришёл на занятие</td>
            <td class="border p-3">Преподаватель не получает оплату за этого ученика. Используется для групповых занятий</td>
        </tr>
        <tr>
            <td class="border p-3"><span class="inline-flex items-center px-2 py-1 rounded bg-yellow-100 text-yellow-800 font-medium">Уваж. причина</span></td>
            <td class="border p-3">Пропуск по уважительной причине</td>
            <td class="border p-3">Урок ученика переносится на другую дату, оплата не сгорает</td>
        </tr>
    </tbody>
</table>

<h3 id="status-buttons">Кнопки в модальном окне</h3>
<p>В модальном окне занятия статусы отображаются как иконки-кнопки:</p>
<ul>
    <li><strong>✓</strong> (зелёная галочка) — Посещение</li>
    <li><strong>₮</strong> (синяя) — Пропуск с оплатой</li>
    <li><strong>✕</strong> (красный крестик) — Пропуск без оплаты</li>
    <li><strong>⊘</strong> (серая) — Уважительная причина</li>
</ul>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Групповые vs индивидуальные занятия</div>
            <div class="text-yellow-700 text-sm mt-1">
                <p><strong>Групповые занятия:</strong> обычно используйте «Пропуск (без оплаты)» — занятие состоялось, но ученик не пришёл.</p>
                <p class="mt-2"><strong>Индивидуальные занятия:</strong> используйте «Пропуск (с оплатой)» — преподаватель ждал ученика, время потрачено.</p>
            </div>
        </div>
    </div>
</div>

<h3 id="change-status">Изменение статуса</h3>
<p>Статус можно изменить в любой момент:</p>
<ol>
    <li>Откройте занятие в расписании</li>
    <li>В модальном окне кликните на новый статус для ученика</li>
    <li>Изменение сохранится автоматически</li>
</ol>
<p>При изменении статуса зарплата преподавателя и баланс ученика пересчитываются автоматически.</p>

<h3 id="legend">Легенда статусов</h3>
<p>Внизу модального окна всегда отображается легенда с расшифровкой всех статусов и их значений.</p>
HTML;
    }

    private function getAttendanceReportsContent(): string
    {
        return <<<HTML
<h2 id="reports">Отчёты по посещаемости</h2>
<p>Система предоставляет различные способы анализа посещаемости учеников.</p>

<h3 id="info-page">Информационная страница</h3>
<p>На странице <strong>Посещаемость</strong> (в меню) отображается:</p>
<ul>
    <li>Инструкция по отметке посещаемости</li>
    <li>Описание всех типов посещаемости</li>
    <li>Кнопка быстрого перехода в расписание</li>
</ul>

<h3 id="pupil-history">История ученика</h3>
<p>Чтобы посмотреть историю посещаемости конкретного ученика:</p>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Перейдите на вкладку <strong>«Посещаемость»</strong></li>
    <li>Вы увидите список всех занятий с отметками</li>
</ol>

<h3 id="group-attendance">Посещаемость группы</h3>
<p>Для просмотра посещаемости по группе:</p>
<ol>
    <li>Откройте карточку группы</li>
    <li>Перейдите на вкладку <strong>«Занятия»</strong></li>
    <li>В списке занятий видны статусы посещения каждого ученика</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-chart-bar text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Зарплата и посещаемость</div>
            <div class="text-blue-700 text-sm mt-1">Посещаемость напрямую влияет на расчёт зарплаты преподавателя. Отметки автоматически учитываются при начислении зарплаты в разделе «Зарплаты».</div>
        </div>
    </div>
</div>

<h3 id="salary-impact">Влияние на зарплату</h3>
<p>При расчёте зарплаты преподавателя учитываются:</p>
<ul>
    <li>Количество учеников со статусом «Посещение»</li>
    <li>Количество учеников со статусом «Пропуск (с оплатой)»</li>
    <li>Ставка преподавателя за занятие</li>
</ul>
<p>Ученики со статусом «Пропуск (без оплаты)» и «Уваж. причина» не учитываются в зарплате преподавателя.</p>

<h3 id="balance-impact">Влияние на баланс ученика</h3>
<p>При отметке посещаемости с баланса ученика списывается стоимость занятия:</p>
<ul>
    <li><strong>Посещение</strong> — списывается стоимость занятия</li>
    <li><strong>Пропуск (с оплатой)</strong> — списывается стоимость занятия</li>
    <li><strong>Пропуск (без оплаты)</strong> — ничего не списывается</li>
    <li><strong>Уваж. причина</strong> — урок переносится, баланс не меняется</li>
</ul>
HTML;
    }
}
