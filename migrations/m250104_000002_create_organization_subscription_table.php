<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%organization_subscription}}`.
 *
 * Таблица подписок организаций на SaaS планы.
 * Отслеживает активную подписку, даты и кастомные лимиты.
 */
class m250104_000002_create_organization_subscription_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organization_subscription}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации (головной)'),
            'saas_plan_id' => $this->integer()->notNull()->comment('ID тарифного плана'),

            // Статус подписки
            'status' => $this->string(20)->notNull()->defaultValue('trial')->comment('trial, active, expired, suspended, cancelled'),

            // Периоды
            'billing_period' => $this->string(20)->defaultValue('monthly')->comment('monthly, yearly'),
            'started_at' => $this->dateTime()->null()->comment('Начало подписки'),
            'expires_at' => $this->dateTime()->null()->comment('Окончание подписки'),
            'trial_ends_at' => $this->dateTime()->null()->comment('Окончание пробного периода'),
            'cancelled_at' => $this->dateTime()->null()->comment('Дата отмены'),

            // Кастомные лимиты (переопределяют план)
            'custom_limits' => $this->json()->comment('JSON с кастомными лимитами'),

            // Мета
            'notes' => $this->text()->comment('Заметки от супер-админа'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Внешние ключи
        $this->addForeignKey(
            'fk-org_subscription-organization_id',
            '{{%organization_subscription}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_subscription-saas_plan_id',
            '{{%organization_subscription}}',
            'saas_plan_id',
            '{{%saas_plan}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Индексы
        $this->createIndex('idx-org_subscription-organization_id', '{{%organization_subscription}}', 'organization_id');
        $this->createIndex('idx-org_subscription-status', '{{%organization_subscription}}', 'status');
        $this->createIndex('idx-org_subscription-expires_at', '{{%organization_subscription}}', 'expires_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-org_subscription-organization_id', '{{%organization_subscription}}');
        $this->dropForeignKey('fk-org_subscription-saas_plan_id', '{{%organization_subscription}}');
        $this->dropTable('{{%organization_subscription}}');
    }
}
