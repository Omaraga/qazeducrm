<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%saas_plan}}`.
 *
 * Таблица тарифных планов SaaS платформы.
 * Определяет лимиты и цены для каждого уровня подписки.
 */
class m250104_000001_create_saas_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%saas_plan}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull()->unique()->comment('Код плана: free, basic, pro, enterprise'),
            'name' => $this->string(255)->notNull()->comment('Название плана'),
            'description' => $this->text()->comment('Описание плана'),

            // Лимиты
            'max_pupils' => $this->integer()->defaultValue(0)->comment('Макс. учеников (0 = безлимит)'),
            'max_teachers' => $this->integer()->defaultValue(0)->comment('Макс. учителей (0 = безлимит)'),
            'max_groups' => $this->integer()->defaultValue(0)->comment('Макс. групп (0 = безлимит)'),
            'max_admins' => $this->integer()->defaultValue(0)->comment('Макс. админов (0 = безлимит)'),
            'max_branches' => $this->integer()->defaultValue(0)->comment('Макс. филиалов (0 = безлимит)'),

            // Цены
            'price_monthly' => $this->decimal(10, 2)->defaultValue(0)->comment('Цена за месяц (KZT)'),
            'price_yearly' => $this->decimal(10, 2)->defaultValue(0)->comment('Цена за год (KZT)'),
            'trial_days' => $this->integer()->defaultValue(14)->comment('Дней пробного периода'),

            // Функции (JSON)
            'features' => $this->json()->comment('JSON с включенными функциями'),

            // Мета
            'sort_order' => $this->integer()->defaultValue(0)->comment('Порядок сортировки'),
            'is_active' => $this->smallInteger(1)->defaultValue(1)->comment('Активен ли план'),
            'is_deleted' => $this->smallInteger(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Индексы
        $this->createIndex('idx-saas_plan-code', '{{%saas_plan}}', 'code');
        $this->createIndex('idx-saas_plan-is_active', '{{%saas_plan}}', 'is_active');

        // Вставляем базовые планы
        $this->batchInsert('{{%saas_plan}}',
            ['code', 'name', 'description', 'max_pupils', 'max_teachers', 'max_groups', 'max_admins', 'max_branches', 'price_monthly', 'price_yearly', 'trial_days', 'features', 'sort_order'],
            [
                ['free', 'Free', 'Бесплатный план для небольших центров', 10, 2, 3, 1, 0, 0, 0, 14, json_encode(['crm_basic' => true]), 1],
                ['basic', 'Basic', 'Базовый план с SMS и отчётами', 50, 5, 10, 2, 1, 9990, 99900, 14, json_encode(['crm_basic' => true, 'sms' => true, 'reports' => true]), 2],
                ['pro', 'Pro', 'Профессиональный план с API и лидами', 200, 20, 50, 5, 3, 29990, 299900, 14, json_encode(['crm_basic' => true, 'sms' => true, 'reports' => true, 'api' => true, 'leads' => true]), 3],
                ['enterprise', 'Enterprise', 'Корпоративный план без ограничений', 0, 0, 0, 0, 0, 99990, 999900, 30, json_encode(['crm_basic' => true, 'sms' => true, 'reports' => true, 'api' => true, 'leads' => true, 'custom' => true, 'priority_support' => true]), 4],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%saas_plan}}');
    }
}
