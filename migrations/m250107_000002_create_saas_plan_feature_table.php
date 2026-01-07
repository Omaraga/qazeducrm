<?php

use yii\db\Migration;

/**
 * Создание таблицы saas_plan_feature для связи планов с функциями.
 *
 * Таблица определяет, какие функции включены в каждый тарифный план
 * и с какими настройками (лимитами).
 */
class m250107_000002_create_saas_plan_feature_table extends Migration
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

        $this->createTable('{{%saas_plan_feature}}', [
            'id' => $this->primaryKey(),
            'saas_plan_id' => $this->integer()->notNull()->comment('ID тарифного плана'),
            'feature_id' => $this->integer()->notNull()->comment('ID функции'),
            'enabled' => $this->tinyInteger(1)->defaultValue(1)->comment('Включена ли функция'),
            'value' => $this->json()->comment('Значение/настройки функции'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Уникальный индекс на связь план-функция
        $this->createIndex('idx_saas_plan_feature_unique', '{{%saas_plan_feature}}', ['saas_plan_id', 'feature_id'], true);

        // Индексы
        $this->createIndex('idx_saas_plan_feature_plan', '{{%saas_plan_feature}}', 'saas_plan_id');
        $this->createIndex('idx_saas_plan_feature_feature', '{{%saas_plan_feature}}', 'feature_id');

        // Внешние ключи
        $this->addForeignKey(
            'fk_saas_plan_feature_plan',
            '{{%saas_plan_feature}}',
            'saas_plan_id',
            '{{%saas_plan}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_saas_plan_feature_feature',
            '{{%saas_plan_feature}}',
            'feature_id',
            '{{%saas_feature}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Заполняем функции для существующих планов
        $this->insertPlanFeatures();

        echo "    > Создана таблица saas_plan_feature\n";
    }

    /**
     * Заполнить функции для планов
     */
    private function insertPlanFeatures()
    {
        // Получаем планы
        $plans = $this->db->createCommand('SELECT id, code FROM {{%saas_plan}} WHERE is_deleted = 0')->queryAll();
        $features = $this->db->createCommand('SELECT id, code FROM {{%saas_feature}}')->queryAll();

        $planMap = array_column($plans, 'id', 'code');
        $featureMap = array_column($features, 'id', 'code');

        // Конфигурация функций по планам
        // Format: 'feature_code' => ['plan_code' => value или true для включения]
        $config = [
            // Core - всегда включены
            'pupils' => ['free' => true, 'basic' => true, 'pro' => true, 'enterprise' => true],
            'groups' => ['free' => true, 'basic' => true, 'pro' => true, 'enterprise' => true],
            'schedule' => ['free' => true, 'basic' => true, 'pro' => true, 'enterprise' => true],
            'attendance' => ['free' => true, 'basic' => true, 'pro' => true, 'enterprise' => true],

            // CRM
            'crm_lids' => ['basic' => true, 'pro' => true, 'enterprise' => true],
            'crm_analytics' => ['pro' => true, 'enterprise' => true],

            // SMS лимиты
            'sms_notifications' => ['basic' => true, 'pro' => true, 'enterprise' => true],
            'sms_monthly_limit' => ['basic' => 50, 'pro' => 200, 'enterprise' => 0], // 0 = безлимит

            // Филиалы
            'branches' => ['pro' => 1, 'enterprise' => 0], // 0 = безлимит

            // Зарплата
            'salary' => ['basic' => true, 'pro' => true, 'enterprise' => true],

            // Отчёты
            'reports_export' => ['pro' => true, 'enterprise' => true],

            // База знаний
            'knowledge_base' => ['pro' => true, 'enterprise' => true],

            // API
            'api_access' => ['pro' => true, 'enterprise' => true],
        ];

        $rows = [];
        foreach ($config as $featureCode => $planValues) {
            if (!isset($featureMap[$featureCode])) {
                continue;
            }
            $featureId = $featureMap[$featureCode];

            foreach ($planValues as $planCode => $value) {
                if (!isset($planMap[$planCode])) {
                    continue;
                }
                $planId = $planMap[$planCode];

                $rows[] = [
                    'saas_plan_id' => $planId,
                    'feature_id' => $featureId,
                    'enabled' => 1,
                    'value' => is_bool($value) ? null : json_encode(['limit' => $value]),
                ];
            }
        }

        if (!empty($rows)) {
            $this->batchInsert('{{%saas_plan_feature}}',
                ['saas_plan_id', 'feature_id', 'enabled', 'value'],
                $rows
            );
        }

        echo "    > Добавлено " . count($rows) . " связей план-функция\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_saas_plan_feature_feature', '{{%saas_plan_feature}}');
        $this->dropForeignKey('fk_saas_plan_feature_plan', '{{%saas_plan_feature}}');
        $this->dropTable('{{%saas_plan_feature}}');
    }
}
