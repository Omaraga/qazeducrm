<?php

use yii\db\Migration;

/**
 * Создание таблицы saas_feature для Feature Flags системы.
 *
 * Таблица хранит справочник всех функций системы, которые могут быть:
 * - Включены/выключены по тарифу
 * - Докуплены как аддоны
 * - Иметь trial период
 */
class m250107_000001_create_saas_feature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%saas_feature}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(100)->notNull()->unique()->comment('Уникальный код функции'),
            'name' => $this->string(255)->notNull()->comment('Название функции'),
            'description' => $this->text()->comment('Описание функции'),
            'category' => $this->string(50)->notNull()->comment('Категория: core, crm, integration, analytics, portal'),
            'type' => "ENUM('boolean', 'limit', 'config') DEFAULT 'boolean' COMMENT 'Тип: boolean (вкл/выкл), limit (числовой лимит), config (настройка)'",
            'default_value' => $this->text()->comment('Значение по умолчанию (JSON для config)'),
            'is_addon' => $this->tinyInteger(1)->defaultValue(0)->comment('Можно докупить отдельно?'),
            'addon_price_monthly' => $this->decimal(10, 2)->comment('Цена аддона в месяц'),
            'addon_price_yearly' => $this->decimal(10, 2)->comment('Цена аддона в год'),
            'trial_available' => $this->tinyInteger(1)->defaultValue(0)->comment('Доступен trial?'),
            'trial_days' => $this->integer()->defaultValue(7)->comment('Длительность trial в днях'),
            'dependencies' => $this->json()->comment('Зависимости от других функций'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Порядок сортировки'),
            'is_active' => $this->tinyInteger(1)->defaultValue(1)->comment('Активна?'),
            'is_deleted' => $this->tinyInteger(1)->defaultValue(0)->comment('Удалена?'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Индексы
        $this->createIndex('idx_saas_feature_code', '{{%saas_feature}}', 'code', true);
        $this->createIndex('idx_saas_feature_category', '{{%saas_feature}}', 'category');
        $this->createIndex('idx_saas_feature_is_addon', '{{%saas_feature}}', 'is_addon');
        $this->createIndex('idx_saas_feature_active', '{{%saas_feature}}', ['is_active', 'is_deleted']);

        // Начальные данные: Базовые функции системы
        $this->batchInsert('{{%saas_feature}}',
            ['code', 'name', 'description', 'category', 'type', 'is_addon', 'addon_price_monthly', 'trial_available', 'sort_order'],
            [
                // Core функции (всегда включены)
                ['pupils', 'Управление учениками', 'Добавление и управление учениками', 'core', 'boolean', 0, null, 0, 10],
                ['groups', 'Управление группами', 'Создание и управление группами', 'core', 'boolean', 0, null, 0, 20],
                ['schedule', 'Расписание', 'Управление расписанием занятий', 'core', 'boolean', 0, null, 0, 30],
                ['attendance', 'Посещаемость', 'Отметка посещаемости', 'core', 'boolean', 0, null, 0, 40],

                // CRM функции
                ['crm_lids', 'CRM Лиды', 'Воронка продаж, канбан лидов', 'crm', 'boolean', 0, null, 0, 100],
                ['crm_analytics', 'Аналитика лидов', 'Статистика по лидам и конверсии', 'crm', 'boolean', 0, null, 0, 110],

                // SMS
                ['sms_notifications', 'SMS уведомления', 'Отправка SMS уведомлений', 'integration', 'limit', 0, null, 0, 200],
                ['sms_monthly_limit', 'Лимит SMS в месяц', 'Количество SMS в месяц', 'integration', 'limit', 0, null, 0, 201],

                // Интеграции (аддоны)
                ['kaspi_pay', 'Kaspi Pay', 'Интеграция с Kaspi для приёма платежей', 'integration', 'boolean', 1, 4990, 1, 300],
                ['whatsapp', 'WhatsApp Business', 'Рассылки и уведомления через WhatsApp', 'integration', 'boolean', 1, 9990, 1, 310],
                ['telegram_bot', 'Telegram Bot', 'Бот для записи и уведомлений', 'integration', 'boolean', 1, 3990, 1, 320],
                ['ip_telephony', 'IP-телефония', 'Интеграция с АТС', 'integration', 'boolean', 1, 5990, 1, 330],

                // Порталы
                ['parent_portal', 'Портал для родителей', 'Расписание, платежи, прогресс ребёнка', 'portal', 'boolean', 1, 7990, 1, 400],
                ['online_booking', 'Онлайн-запись', 'Виджет для записи на сайт', 'portal', 'boolean', 1, 5990, 1, 410],

                // Аналитика
                ['advanced_analytics', 'Расширенная аналитика', 'LTV, прогноз оттока, когорты', 'analytics', 'boolean', 1, 6990, 1, 500],
                ['reports_export', 'Экспорт отчётов', 'Экспорт в Excel/PDF', 'analytics', 'boolean', 0, null, 0, 510],

                // Дополнительные функции
                ['homework', 'Домашние задания', 'Задания и проверка', 'feature', 'boolean', 1, 4990, 1, 600],
                ['testing', 'Тестирование', 'Онлайн-тесты с автопроверкой', 'feature', 'boolean', 1, 5990, 1, 610],
                ['documents', 'Документооборот', 'Договоры, ЭЦП', 'feature', 'boolean', 1, 6990, 1, 620],
                ['knowledge_base', 'База знаний', 'Внутренняя база знаний', 'feature', 'boolean', 0, null, 0, 630],

                // Филиалы
                ['branches', 'Филиалы', 'Управление филиалами', 'core', 'limit', 0, null, 0, 700],

                // Зарплата
                ['salary', 'Зарплата учителей', 'Расчёт зарплаты учителей', 'feature', 'boolean', 0, null, 0, 800],

                // API
                ['api_access', 'API доступ', 'Доступ к API для интеграций', 'integration', 'boolean', 0, null, 0, 900],
            ]
        );

        echo "    > Создана таблица saas_feature с " . count($this->db->createCommand('SELECT * FROM {{%saas_feature}}')->queryAll()) . " записями\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%saas_feature}}');
    }
}
