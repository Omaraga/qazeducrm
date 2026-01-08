<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_change_request}}`.
 * Таблица для запросов на изменение/удаление платежей от Admin.
 * Admin создаёт запрос → Director одобряет/отклоняет.
 */
class m260108_150000_create_payment_change_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment_change_request}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11)->notNull(),
            'payment_id' => $this->integer(11)->notNull(),
            'request_type' => $this->string(20)->notNull()->comment('delete, update'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending, approved, rejected'),
            'old_values' => $this->json()->comment('Старые значения платежа в JSON'),
            'new_values' => $this->json()->comment('Новые значения платежа в JSON (для update)'),
            'reason' => $this->text()->notNull()->comment('Причина запроса'),
            'requested_by' => $this->integer(11)->notNull()->comment('ID пользователя, создавшего запрос'),
            'processed_by' => $this->integer(11)->comment('ID пользователя, обработавшего запрос'),
            'processed_at' => $this->dateTime()->comment('Дата обработки запроса'),
            'admin_comment' => $this->text()->comment('Комментарий директора при отклонении'),
            'is_deleted' => $this->integer(1)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);

        // Индексы для быстрого поиска
        $this->createIndex('idx-payment_change_request-organization_id', 'payment_change_request', 'organization_id');
        $this->createIndex('idx-payment_change_request-payment_id', 'payment_change_request', 'payment_id');
        $this->createIndex('idx-payment_change_request-status', 'payment_change_request', 'status');
        $this->createIndex('idx-payment_change_request-requested_by', 'payment_change_request', 'requested_by');

        // Внешние ключи
        $this->addForeignKey(
            'fk-payment_change_request-organization_id',
            'payment_change_request',
            'organization_id',
            'organization',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-payment_change_request-payment_id',
            'payment_change_request',
            'payment_id',
            'payment',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-payment_change_request-requested_by',
            'payment_change_request',
            'requested_by',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-payment_change_request-processed_by',
            'payment_change_request',
            'processed_by',
            'user',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-payment_change_request-processed_by', 'payment_change_request');
        $this->dropForeignKey('fk-payment_change_request-requested_by', 'payment_change_request');
        $this->dropForeignKey('fk-payment_change_request-payment_id', 'payment_change_request');
        $this->dropForeignKey('fk-payment_change_request-organization_id', 'payment_change_request');

        $this->dropIndex('idx-payment_change_request-requested_by', 'payment_change_request');
        $this->dropIndex('idx-payment_change_request-status', 'payment_change_request');
        $this->dropIndex('idx-payment_change_request-payment_id', 'payment_change_request');
        $this->dropIndex('idx-payment_change_request-organization_id', 'payment_change_request');

        $this->dropTable('{{%payment_change_request}}');
    }
}
