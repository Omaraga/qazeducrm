<?php

use yii\db\Migration;

/**
 * Добавляет секцию "Настройка справочников" в главу "Начало работы"
 * и заполняет контент для секций Предметы и Тарифы
 */
class m260110_210000_add_settings_to_getting_started extends Migration
{
    public function safeUp()
    {
        // Добавляем новую секцию "Настройка справочников" в главу 1 (Начало работы)
        $this->insert('{{%docs_section}}', [
            'chapter_id' => 1,
            'slug' => 'initial-setup',
            'title' => 'Настройка справочников',
            'excerpt' => 'Первоначальная настройка предметов, тарифов и кабинетов',
            'content' => $this->getInitialSetupContent(),
            'sort_order' => 50, // После organization-setup
            'is_active' => 1,
            'created_at' => new \yii\db\Expression('NOW()'),
            'updated_at' => new \yii\db\Expression('NOW()'),
        ]);

        // Обновляем контент для секции Предметы (глава 11)
        $this->update('{{%docs_section}}', [
            'content' => $this->getSubjectsContent(),
        ], ['chapter_id' => 11, 'slug' => 'subjects']);

        // Обновляем контент для секции Тарифы (глава 11)
        $this->update('{{%docs_section}}', [
            'content' => $this->getTariffsContent(),
        ], ['chapter_id' => 11, 'slug' => 'tariffs']);
    }

    public function safeDown()
    {
        $this->delete('{{%docs_section}}', ['chapter_id' => 1, 'slug' => 'initial-setup']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 11, 'slug' => 'subjects']);
        $this->update('{{%docs_section}}', ['content' => null], ['chapter_id' => 11, 'slug' => 'tariffs']);
    }

    private function getInitialSetupContent(): string
    {
        return <<<HTML
<h2 id="overview">Первоначальная настройка</h2>
<p>Прежде чем начать работу с системой, необходимо настроить справочники. Без них вы не сможете создавать группы, тарифы и расписание.</p>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно!</div>
            <div class="text-yellow-700 text-sm mt-1">Настройте справочники в указанном порядке. Без предметов нельзя создать тарифы, без тарифов — группы.</div>
        </div>
    </div>
</div>

<h3 id="settings-menu">Где находятся настройки</h3>
<p>Все справочники находятся в разделе <strong>«Настройки»</strong> в левом меню. Раскройте этот раздел, чтобы увидеть все пункты:</p>

<figure class="my-6">
    <img src="/images/docs/settings/settings-menu.png"
         alt="Меню настроек"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Раздел настроек в боковом меню
    </figcaption>
</figure>

<h3 id="setup-order">Порядок настройки</h3>
<p>Рекомендуемый порядок настройки справочников:</p>

<ol class="space-y-4 my-6">
    <li class="flex items-start gap-3">
        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">1</span>
        <div>
            <strong>Предметы</strong>
            <p class="text-gray-600 text-sm mt-1">Создайте список предметов, которые преподаются в вашей организации: Математика, Английский язык, Программирование и т.д.</p>
        </div>
    </li>
    <li class="flex items-start gap-3">
        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">2</span>
        <div>
            <strong>Кабинеты</strong>
            <p class="text-gray-600 text-sm mt-1">Добавьте кабинеты и аудитории для отслеживания загрузки помещений в расписании.</p>
        </div>
    </li>
    <li class="flex items-start gap-3">
        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">3</span>
        <div>
            <strong>Тарифы</strong>
            <p class="text-gray-600 text-sm mt-1">Создайте тарифные планы с ценами и количеством занятий. Тарифы привязываются к предметам.</p>
        </div>
    </li>
    <li class="flex items-start gap-3">
        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">4</span>
        <div>
            <strong>Способы оплаты</strong>
            <p class="text-gray-600 text-sm mt-1">Настройте способы приёма платежей: наличные, Kaspi, банковский перевод и т.д.</p>
        </div>
    </li>
</ol>

<h3 id="subjects-quick">Предметы</h3>
<p>Перейдите в <strong>Настройки → Предметы</strong> и добавьте предметы:</p>

<figure class="my-6">
    <img src="/images/docs/settings/subjects-list.png"
         alt="Список предметов"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Страница справочника предметов
    </figcaption>
</figure>

<p>Нажмите <strong>«Добавить предмет»</strong> и введите название. Примеры предметов:</p>
<ul>
    <li>Математика</li>
    <li>Английский язык</li>
    <li>Подготовка к ЕНТ</li>
    <li>Программирование</li>
    <li>Робототехника</li>
</ul>

<h3 id="rooms-quick">Кабинеты</h3>
<p>Перейдите в <strong>Настройки → Кабинеты</strong> и добавьте помещения:</p>

<figure class="my-6">
    <img src="/images/docs/settings/rooms-list.png"
         alt="Список кабинетов"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Страница справочника кабинетов
    </figcaption>
</figure>

<p>Для каждого кабинета укажите:</p>
<ul>
    <li><strong>Название</strong> — например, «Главный зал», «Кабинет математики»</li>
    <li><strong>Код</strong> — короткий код для расписания (101, 102, А1)</li>
    <li><strong>Вместимость</strong> — максимальное количество учеников</li>
    <li><strong>Цвет</strong> — для визуального различия в календаре</li>
</ul>

<h3 id="tariffs-quick">Тарифы</h3>
<p>Перейдите в <strong>Настройки → Тарифы</strong> и создайте тарифные планы:</p>

<figure class="my-6">
    <img src="/images/docs/settings/tariffs-list.png"
         alt="Список тарифов"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Страница справочника тарифов
    </figcaption>
</figure>

<p>Примеры тарифов:</p>
<ul>
    <li><strong>Групповые (8 занятий)</strong> — 20 000 ₸</li>
    <li><strong>Групповые (12 занятий)</strong> — 28 000 ₸</li>
    <li><strong>Индивидуальные (4 занятия)</strong> — 24 000 ₸</li>
    <li><strong>Интенсив ЕНТ (20 занятий)</strong> — 45 000 ₸</li>
</ul>

<div class="bg-green-50 border border-green-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-green-500"><i class="fas fa-check-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-green-900">Готово!</div>
            <div class="text-green-700 text-sm mt-1">После настройки справочников вы можете создавать группы, добавлять учеников и формировать расписание.</div>
        </div>
    </div>
</div>

<h3 id="next-steps">Следующие шаги</h3>
<p>После настройки справочников рекомендуем:</p>
<ol>
    <li>Добавить сотрудников (преподавателей)</li>
    <li>Создать учебные группы</li>
    <li>Добавить учеников в группы</li>
    <li>Сформировать расписание занятий</li>
</ol>
HTML;
    }

    private function getSubjectsContent(): string
    {
        return <<<HTML
<h2 id="overview">Справочник предметов</h2>
<p>Предметы — это дисциплины, которые преподаются в вашей организации. Справочник предметов используется при создании тарифов и групп.</p>

<figure class="my-6">
    <img src="/images/docs/settings/subjects-list.png"
         alt="Список предметов"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Справочник предметов
    </figcaption>
</figure>

<h3 id="add-subject">Добавление предмета</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Предметы</strong></li>
    <li>Нажмите кнопку <strong>«+ Добавить предмет»</strong></li>
    <li>Введите название предмета</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<figure class="my-6">
    <img src="/images/docs/settings/subject-create.png"
         alt="Форма добавления предмета"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Форма добавления нового предмета
    </figcaption>
</figure>

<h3 id="examples">Примеры предметов</h3>
<ul>
    <li>Математика</li>
    <li>Английский язык</li>
    <li>Казахский язык</li>
    <li>Физика</li>
    <li>Химия</li>
    <li>Подготовка к ЕНТ</li>
    <li>Программирование</li>
    <li>Робототехника</li>
    <li>Музыка</li>
    <li>Рисование</li>
</ul>

<h3 id="edit-subject">Редактирование</h3>
<ol>
    <li>В списке предметов найдите нужный</li>
    <li>Нажмите на иконку редактирования (карандаш)</li>
    <li>Измените название</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h3 id="sort-subjects">Сортировка</h3>
<p>Предметы можно сортировать перетаскиванием:</p>
<ol>
    <li>Наведите курсор на иконку перетаскивания (три горизонтальные линии) слева от названия</li>
    <li>Зажмите левую кнопку мыши</li>
    <li>Перетащите предмет на нужную позицию</li>
    <li>Отпустите кнопку — порядок сохранится автоматически</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-info-circle text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Порядок отображения</div>
            <div class="text-blue-700 text-sm mt-1">Порядок предметов в справочнике влияет на порядок их отображения в выпадающих списках при создании тарифов и групп.</div>
        </div>
    </div>
</div>

<h3 id="delete-subject">Удаление</h3>
<ol>
    <li>Нажмите на иконку удаления (корзина)</li>
    <li>Подтвердите удаление</li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Ограничение удаления</div>
            <div class="text-yellow-700 text-sm mt-1">Нельзя удалить предмет, который используется в тарифах или группах. Сначала нужно удалить или изменить связанные тарифы и группы.</div>
        </div>
    </div>
</div>

<h3 id="usage">Где используются предметы</h3>
<ul>
    <li><strong>Тарифы</strong> — при создании тарифа можно привязать его к предметам</li>
    <li><strong>Группы</strong> — группа может быть привязана к предмету для фильтрации</li>
    <li><strong>Отчёты</strong> — для анализа по предметам</li>
</ul>
HTML;
    }

    private function getTariffsContent(): string
    {
        return <<<HTML
<h2 id="overview">Справочник тарифов</h2>
<p>Тарифы определяют стоимость обучения и количество занятий в абонементе. При добавлении ученика в группу выбирается тариф, по которому будет рассчитываться оплата.</p>

<figure class="my-6">
    <img src="/images/docs/settings/tariffs-list.png"
         alt="Список тарифов"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Справочник тарифов организации
    </figcaption>
</figure>

<h3 id="tariff-info">Информация в списке</h3>
<table class="w-full text-sm border-collapse my-4">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">Колонка</th>
            <th class="border p-2 text-left">Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="border p-2"><strong>Название</strong></td><td class="border p-2">Название тарифа и количество занятий</td></tr>
        <tr><td class="border p-2"><strong>Длительность</strong></td><td class="border p-2">Тип абонемента (фиксированное количество занятий)</td></tr>
        <tr><td class="border p-2"><strong>Цена</strong></td><td class="border p-2">Стоимость тарифа в тенге</td></tr>
        <tr><td class="border p-2"><strong>Предметы</strong></td><td class="border p-2">К каким предметам привязан тариф</td></tr>
        <tr><td class="border p-2"><strong>Тип</strong></td><td class="border p-2">Категория тарифа (оплата за обучение)</td></tr>
        <tr><td class="border p-2"><strong>Статус</strong></td><td class="border p-2">Активный или архивный</td></tr>
    </tbody>
</table>

<h3 id="create-tariff">Создание тарифа</h3>
<ol>
    <li>Перейдите в <strong>Настройки → Тарифы</strong></li>
    <li>Нажмите <strong>«+ Создать тариф»</strong></li>
    <li>Заполните форму:</li>
</ol>

<figure class="my-6">
    <img src="/images/docs/settings/tariff-create.png"
         alt="Форма создания тарифа"
         class="rounded-lg shadow-lg border cursor-pointer hover:opacity-90 transition-opacity"
         onclick="openLightbox(this.src, this.alt)">
    <figcaption class="text-center text-sm text-gray-500 mt-2">
        Форма создания нового тарифа
    </figcaption>
</figure>

<h4>Поля формы:</h4>
<ul>
    <li><strong>Название</strong> — понятное название тарифа (например, «Групповые 8 занятий»)</li>
    <li><strong>Длительность</strong> — тип абонемента:
        <ul>
            <li><em>Фиксированное количество занятий</em> — указываете точное число занятий</li>
        </ul>
    </li>
    <li><strong>Количество занятий</strong> — сколько занятий входит в абонемент</li>
    <li><strong>Цена</strong> — стоимость тарифа</li>
    <li><strong>Предметы</strong> — к каким предметам применим тариф (можно выбрать несколько или оставить пустым для универсального тарифа)</li>
    <li><strong>Описание</strong> — дополнительная информация о тарифе</li>
</ul>

<h3 id="tariff-examples">Примеры тарифов</h3>

<div class="bg-gray-50 rounded-lg p-4 my-6">
    <h4 class="font-semibold mb-3">Групповые занятия:</h4>
    <ul class="space-y-2">
        <li><strong>Групповые (8 занятий)</strong> — 20 000 ₸ — базовый месячный абонемент</li>
        <li><strong>Групповые (12 занятий)</strong> — 28 000 ₸ — расширенный абонемент (3 раза в неделю)</li>
        <li><strong>Групповые (4 занятия)</strong> — 12 000 ₸ — пробный абонемент</li>
    </ul>
</div>

<div class="bg-gray-50 rounded-lg p-4 my-6">
    <h4 class="font-semibold mb-3">Индивидуальные занятия:</h4>
    <ul class="space-y-2">
        <li><strong>Индивидуальные (4 занятия)</strong> — 24 000 ₸</li>
        <li><strong>Индивидуальные (8 занятий)</strong> — 44 000 ₸</li>
    </ul>
</div>

<div class="bg-gray-50 rounded-lg p-4 my-6">
    <h4 class="font-semibold mb-3">Специальные программы:</h4>
    <ul class="space-y-2">
        <li><strong>Интенсив ЕНТ (20 занятий)</strong> — 45 000 ₸</li>
        <li><strong>VIP подготовка (10 занятий)</strong> — 60 000 ₸</li>
        <li><strong>Летний лагерь (24 занятия)</strong> — 35 000 ₸</li>
    </ul>
</div>

<h3 id="edit-tariff">Редактирование тарифа</h3>
<ol>
    <li>В списке тарифов нажмите на название или иконку редактирования</li>
    <li>Внесите изменения</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Изменение цены</div>
            <div class="text-yellow-700 text-sm mt-1">При изменении цены тарифа, уже привязанные к ученикам абонементы сохранят старую цену. Новая цена применится только к новым записям.</div>
        </div>
    </div>
</div>

<h3 id="archive-tariff">Архивирование</h3>
<p>Если тариф больше не актуален, но использовался ранее:</p>
<ol>
    <li>Откройте тариф на редактирование</li>
    <li>Измените статус на <strong>«Архивный»</strong></li>
    <li>Сохраните изменения</li>
</ol>
<p>Архивные тарифы не показываются в списках выбора, но сохраняют историю платежей.</p>

<h3 id="delete-tariff">Удаление тарифа</h3>
<ol>
    <li>Нажмите на иконку удаления (корзина)</li>
    <li>Подтвердите действие</li>
</ol>

<div class="bg-red-50 border border-red-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-red-500"><i class="fas fa-ban text-xl"></i></div>
        <div>
            <div class="font-medium text-red-900">Ограничение удаления</div>
            <div class="text-red-700 text-sm mt-1">Нельзя удалить тариф, который использовался для оплат. Вместо удаления переведите его в архив.</div>
        </div>
    </div>
</div>

<h3 id="price-per-lesson">Стоимость занятия</h3>
<p>Система автоматически рассчитывает стоимость одного занятия:</p>
<p><code>Стоимость занятия = Цена тарифа / Количество занятий</code></p>
<p>Например: 20 000 ₸ / 8 занятий = 2 500 ₸ за занятие</p>
<p>Эта сумма используется при:</p>
<ul>
    <li>Расчёте зарплаты преподавателя</li>
    <li>Списании с баланса ученика при отметке посещаемости</li>
</ul>

<h3 id="usage">Где используются тарифы</h3>
<ul>
    <li><strong>Группы</strong> — при создании группы указывается тариф по умолчанию</li>
    <li><strong>Ученики</strong> — при добавлении ученика в группу выбирается тариф</li>
    <li><strong>Платежи</strong> — при приёме оплаты выбирается тариф</li>
    <li><strong>Зарплаты</strong> — для расчёта оплаты преподавателям</li>
</ul>
HTML;
    }
}
