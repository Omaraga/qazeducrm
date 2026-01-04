<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%organization_activity_log}}`.
 *
 * Таблица логов активности организаций для супер-админки.
 * Отслеживает важные события: регистрация, платежи, смена плана, блокировки.
 */
class m250104_000005_create_organization_activity_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organization_activity_log}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),

            // Тип события
            'action' => $this->string(50)->notNull()->comment('Тип действия'),
            'category' => $this->string(30)->defaultValue('general')->comment('Категория: subscription, payment, status, auth'),

            // Детали
            'description' => $this->text()->comment('Описание события'),
            'old_value' => $this->text()->comment('Старое значение (JSON)'),
            'new_value' => $this->text()->comment('Новое значение (JSON)'),
            'metadata' => $this->json()->comment('Дополнительные данные'),

            // Кто выполнил
            'user_id' => $this->integer()->null()->comment('ID пользователя'),
            'user_type' => $this->string(20)->defaultValue('user')->comment('user, super_admin, system'),
            'ip_address' => $this->string(45)->comment('IP адрес'),
            'user_agent' => $this->text()->comment('User Agent'),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Внешние ключи
        $this->addForeignKey(
            'fk-org_activity_log-organization_id',
            '{{%organization_activity_log}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Индексы
        $this->createIndex('idx-org_activity_log-organization_id', '{{%organization_activity_log}}', 'organization_id');
        $this->createIndex('idx-org_activity_log-action', '{{%organization_activity_log}}', 'action');
        $this->createIndex('idx-org_activity_log-category', '{{%organization_activity_log}}', 'category');
        $this->createIndex('idx-org_activity_log-created_at', '{{%organization_activity_log}}', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-org_activity_log-organization_id', '{{%organization_activity_log}}');
        $this->dropTable('{{%organization_activity_log}}');
    }
}
