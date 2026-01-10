<?php

use yii\db\Migration;

/**
 * Перемещает главу "Настройки" на позицию 2 (сразу после "Начало работы")
 * и упрощает секцию initial-setup
 */
class m260110_220000_reorder_settings_chapter extends Migration
{
    public function safeUp()
    {
        // 1. Сдвигаем все главы начиная с позиции 2 на +1
        $this->execute("UPDATE docs_chapter SET sort_order = sort_order + 1 WHERE sort_order >= 2 AND id != 11");

        // 2. Перемещаем Настройки на позицию 2
        $this->update('{{%docs_chapter}}', ['sort_order' => 2], ['id' => 11]);

        // 3. Удаляем избыточную секцию initial-setup из главы 1 (теперь есть отдельная глава)
        $this->delete('{{%docs_section}}', ['chapter_id' => 1, 'slug' => 'initial-setup']);

        // 4. Обновляем секцию organization-setup чтобы добавить ссылку на следующую главу
        $this->update('{{%docs_section}}', [
            'content' => $this->getOrganizationSetupContent(),
        ], ['chapter_id' => 1, 'slug' => 'organization-setup']);
    }

    public function safeDown()
    {
        // Восстанавливаем порядок
        $this->update('{{%docs_chapter}}', ['sort_order' => 11], ['id' => 11]);
        $this->execute("UPDATE docs_chapter SET sort_order = sort_order - 1 WHERE sort_order >= 2 AND id != 11");
    }

    private function getOrganizationSetupContent(): string
    {
        return <<<HTML
<h2 id="organization-setup">Настройка организации</h2>
<p>После первого входа рекомендуем заполнить основные данные организации.</p>

<h3 id="profile">Профиль организации</h3>
<p>Перейдите в <strong>Настройки → Профиль организации</strong> и заполните:</p>
<ul>
    <li><strong>Название</strong> — официальное название вашей организации</li>
    <li><strong>Адрес</strong> — физический адрес</li>
    <li><strong>Телефон</strong> — контактный номер</li>
    <li><strong>Email</strong> — электронная почта для связи</li>
</ul>

<h3 id="next-step">Следующий шаг</h3>
<p>Прежде чем начать работу с учениками и группами, необходимо настроить справочники системы.</p>

<div class="bg-primary-50 border border-primary-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-primary-500"><i class="fas fa-arrow-right text-xl"></i></div>
        <div>
            <div class="font-medium text-primary-900">Перейдите к следующей главе</div>
            <div class="text-primary-700 text-sm mt-1">
                <a href="/docs/settings" class="text-primary-600 hover:text-primary-800 font-medium">
                    Настройки →
                </a>
                — создайте предметы, тарифы и кабинеты для начала работы.
            </div>
        </div>
    </div>
</div>

<h3 id="quick-checklist">Чек-лист быстрого старта</h3>
<ol class="space-y-2">
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-sm">✓</span>
        <span>Регистрация организации</span>
    </li>
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-sm">✓</span>
        <span>Подтверждение email</span>
    </li>
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-sm">✓</span>
        <span>Первый вход в систему</span>
    </li>
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-sm">○</span>
        <span>Настройка справочников (предметы, тарифы, кабинеты)</span>
    </li>
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-sm">○</span>
        <span>Добавление сотрудников</span>
    </li>
    <li class="flex items-center gap-2">
        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-sm">○</span>
        <span>Создание групп и добавление учеников</span>
    </li>
</ol>
HTML;
    }
}
