<?php

use yii\db\Migration;

/**
 * Миграция для создания таблиц документации
 */
class m260110_150000_create_docs_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица глав документации
        $this->createTable('{{%docs_chapter}}', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(100)->notNull()->unique(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'icon' => $this->string(50)->defaultValue('book'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'is_deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Таблица секций документации
        $this->createTable('{{%docs_section}}', [
            'id' => $this->primaryKey(),
            'chapter_id' => $this->integer()->notNull(),
            'slug' => $this->string(100)->notNull(),
            'title' => $this->string(255)->notNull(),
            'content' => 'LONGTEXT',
            'excerpt' => $this->string(500),
            'screenshots' => $this->json(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'is_deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Внешний ключ
        $this->addForeignKey(
            'fk_docs_section_chapter',
            '{{%docs_section}}',
            'chapter_id',
            '{{%docs_chapter}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Уникальный индекс для slug внутри главы
        $this->createIndex(
            'idx_docs_section_chapter_slug',
            '{{%docs_section}}',
            ['chapter_id', 'slug'],
            true
        );

        // Полнотекстовый индекс для поиска
        $this->execute('ALTER TABLE {{%docs_section}} ADD FULLTEXT INDEX idx_docs_section_fulltext (title, content)');

        // Индекс для сортировки
        $this->createIndex('idx_docs_chapter_sort', '{{%docs_chapter}}', ['sort_order', 'is_active', 'is_deleted']);
        $this->createIndex('idx_docs_section_sort', '{{%docs_section}}', ['chapter_id', 'sort_order', 'is_active', 'is_deleted']);

        // Наполнение начальными данными - главы
        $this->batchInsert('{{%docs_chapter}}', ['slug', 'title', 'description', 'icon', 'sort_order'], [
            ['getting-started', 'Начало работы', 'Регистрация, первый вход и обзор системы', 'rocket', 1],
            ['pupils', 'Ученики', 'Управление учениками и их обучением', 'users', 2],
            ['groups', 'Группы', 'Создание и настройка учебных групп', 'user-group', 3],
            ['staff', 'Сотрудники', 'Управление преподавателями и персоналом', 'user-tie', 4],
            ['schedule', 'Расписание', 'Работа с календарём и занятиями', 'calendar', 5],
            ['attendance', 'Посещаемость', 'Отметка и контроль посещаемости', 'clipboard-check', 6],
            ['payments', 'Платежи', 'Приём оплат и финансовый учёт', 'credit-card', 7],
            ['salary', 'Зарплаты', 'Расчёт и выплата зарплат преподавателям', 'money-bill', 8],
            ['leads', 'Лиды и воронка', 'CRM для работы с потенциальными клиентами', 'funnel', 9],
            ['whatsapp', 'WhatsApp', 'Интеграция с WhatsApp для общения', 'comments', 10],
            ['settings', 'Настройки', 'Настройка организации и системы', 'cog', 11],
        ]);

        // Наполнение начальными данными - секции для главы "Начало работы"
        $this->batchInsert('{{%docs_section}}', ['chapter_id', 'slug', 'title', 'excerpt', 'sort_order'], [
            // Глава 1: Начало работы
            [1, 'registration', 'Регистрация организации', 'Как зарегистрировать новую организацию в системе', 1],
            [1, 'email-verification', 'Подтверждение email', 'Подтверждение email адреса после регистрации', 2],
            [1, 'first-login', 'Первый вход в систему', 'Вход в систему и начальная настройка', 3],
            [1, 'interface-overview', 'Обзор интерфейса', 'Знакомство с интерфейсом CRM системы', 4],
            [1, 'organization-setup', 'Настройка организации', 'Базовые настройки вашей организации', 5],

            // Глава 2: Ученики
            [2, 'add-pupil', 'Добавление ученика', 'Как добавить нового ученика в систему', 1],
            [2, 'edit-pupil', 'Редактирование данных', 'Изменение информации об ученике', 2],
            [2, 'add-to-group', 'Добавление в группы', 'Как записать ученика в учебную группу', 3],
            [2, 'pupil-payments', 'История платежей ученика', 'Просмотр платежей и баланса ученика', 4],
            [2, 'delete-pupil', 'Удаление ученика', 'Архивация или удаление ученика', 5],

            // Глава 3: Группы
            [3, 'create-group', 'Создание группы', 'Как создать новую учебную группу', 1],
            [3, 'add-pupils-to-group', 'Добавление учеников', 'Запись учеников в группу', 2],
            [3, 'assign-teacher', 'Назначение преподавателя', 'Привязка преподавателя к группе', 3],
            [3, 'group-tariffs', 'Настройка тарифов', 'Установка стоимости обучения в группе', 4],

            // Глава 4: Сотрудники
            [4, 'add-staff', 'Добавление сотрудника', 'Как добавить нового сотрудника', 1],
            [4, 'assign-roles', 'Назначение ролей', 'Настройка прав доступа сотрудника', 2],
            [4, 'link-to-groups', 'Привязка к группам', 'Назначение преподавателя на группы', 3],
            [4, 'teacher-rates', 'Настройка ставок', 'Установка ставок для расчёта зарплаты', 4],

            // Глава 5: Расписание
            [5, 'calendar-overview', 'Обзор календаря', 'Как работать с календарём расписания', 1],
            [5, 'create-lesson', 'Создание занятия', 'Добавление урока в расписание', 2],
            [5, 'schedule-templates', 'Шаблоны расписания', 'Использование типовых расписаний', 3],
            [5, 'edit-cancel-lesson', 'Редактирование и отмена', 'Изменение и отмена занятий', 4],

            // Глава 6: Посещаемость
            [6, 'mark-attendance', 'Отметка посещаемости', 'Как отмечать посещаемость на уроке', 1],
            [6, 'attendance-statuses', 'Статусы посещения', 'Виды статусов и их значение', 2],
            [6, 'attendance-reports', 'Отчёты', 'Отчёты по посещаемости учеников', 3],

            // Глава 7: Платежи
            [7, 'receive-payment', 'Приём платежа', 'Как принять оплату от ученика', 1],
            [7, 'payment-methods', 'Способы оплаты', 'Настройка способов оплаты', 2],
            [7, 'payment-history', 'История платежей', 'Просмотр истории всех платежей', 3],
            [7, 'income-reports', 'Отчёты по доходам', 'Финансовая отчётность', 4],

            // Глава 8: Зарплаты
            [8, 'setup-rates', 'Настройка ставок', 'Установка ставок преподавателей', 1],
            [8, 'calculate-salary', 'Расчёт зарплаты', 'Как рассчитать зарплату за период', 2],
            [8, 'pay-salary', 'Выплата', 'Отметка о выплате зарплаты', 3],
            [8, 'salary-history', 'История выплат', 'Просмотр истории зарплат', 4],

            // Глава 9: Лиды и воронка
            [9, 'create-lead', 'Создание лида', 'Как добавить нового потенциального клиента', 1],
            [9, 'kanban-board', 'Kanban-доска', 'Работа с воронкой продаж', 2],
            [9, 'funnel-stages', 'Этапы воронки', 'Настройка этапов воронки', 3],
            [9, 'convert-to-pupil', 'Конверсия в ученика', 'Превращение лида в ученика', 4],
            [9, 'lead-analytics', 'Аналитика', 'Отчёты по воронке продаж', 5],

            // Глава 10: WhatsApp
            [10, 'connect-whatsapp', 'Подключение', 'Как подключить WhatsApp к системе', 1],
            [10, 'whatsapp-chats', 'Чаты', 'Работа с чатами клиентов', 2],
            [10, 'whatsapp-notifications', 'Уведомления', 'Автоматические уведомления', 3],

            // Глава 11: Настройки
            [11, 'subjects', 'Предметы', 'Настройка списка предметов', 1],
            [11, 'tariffs', 'Тарифы', 'Создание и редактирование тарифов', 2],
            [11, 'rooms', 'Кабинеты', 'Управление кабинетами', 3],
            [11, 'payment-methods-settings', 'Способы оплаты', 'Настройка методов оплаты', 4],
            [11, 'access-rights', 'Права доступа', 'Настройка прав для ролей', 5],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%docs_section}}');
        $this->dropTable('{{%docs_chapter}}');
    }
}
