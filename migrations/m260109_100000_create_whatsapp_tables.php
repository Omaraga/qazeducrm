<?php

use yii\db\Migration;

/**
 * Создание таблиц для WhatsApp интеграции
 */
class m260109_100000_create_whatsapp_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица сессий WhatsApp (по одной на организацию)
        $this->createTable('{{%whatsapp_session}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'instance_name' => $this->string(100)->notNull()->comment('Имя инстанса в Evolution API'),
            'phone_number' => $this->string(20)->null()->comment('Подключенный номер телефона'),
            'status' => $this->string(20)->notNull()->defaultValue('disconnected')->comment('disconnected, connecting, connected'),
            'qr_code' => $this->text()->null()->comment('Base64 QR код для сканирования'),
            'qr_code_updated_at' => $this->timestamp()->null(),
            'connected_at' => $this->timestamp()->null(),
            'disconnected_at' => $this->timestamp()->null(),
            'webhook_url' => $this->string(500)->null()->comment('Webhook URL для этой сессии'),
            'info' => $this->json()->null()->comment('Дополнительные данные от API'),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Индексы для whatsapp_session
        $this->createIndex(
            'idx-whatsapp_session-organization_id',
            '{{%whatsapp_session}}',
            'organization_id'
        );
        $this->createIndex(
            'idx-whatsapp_session-instance_name',
            '{{%whatsapp_session}}',
            'instance_name',
            true // unique
        );
        $this->createIndex(
            'idx-whatsapp_session-status',
            '{{%whatsapp_session}}',
            'status'
        );

        // Foreign key
        $this->addForeignKey(
            'fk-whatsapp_session-organization_id',
            '{{%whatsapp_session}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Таблица сообщений WhatsApp
        $this->createTable('{{%whatsapp_message}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'session_id' => $this->integer()->notNull(),
            'lid_id' => $this->integer()->null()->comment('Связанный лид (если найден)'),
            'remote_jid' => $this->string(100)->notNull()->comment('WhatsApp ID собеседника (phone@s.whatsapp.net)'),
            'remote_phone' => $this->string(20)->null()->comment('Номер телефона собеседника'),
            'remote_name' => $this->string(255)->null()->comment('Имя собеседника (pushName)'),
            'direction' => $this->string(10)->notNull()->comment('incoming или outgoing'),
            'message_type' => $this->string(20)->notNull()->defaultValue('text')->comment('text, image, video, audio, document, sticker'),
            'content' => $this->text()->null()->comment('Текст сообщения'),
            'media_url' => $this->string(500)->null()->comment('URL медиа файла'),
            'media_mimetype' => $this->string(100)->null(),
            'media_filename' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('sent')->comment('sent, delivered, read, failed'),
            'whatsapp_id' => $this->string(100)->null()->comment('ID сообщения в WhatsApp'),
            'whatsapp_timestamp' => $this->timestamp()->null()->comment('Время сообщения в WhatsApp'),
            'is_from_me' => $this->boolean()->notNull()->defaultValue(false),
            'is_read' => $this->boolean()->notNull()->defaultValue(false)->comment('Прочитано ли в CRM'),
            'read_at' => $this->timestamp()->null(),
            'read_by' => $this->integer()->null()->comment('Кто прочитал'),
            'info' => $this->json()->null()->comment('Полные данные сообщения от API'),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Индексы для whatsapp_message
        $this->createIndex(
            'idx-whatsapp_message-organization_id',
            '{{%whatsapp_message}}',
            'organization_id'
        );
        $this->createIndex(
            'idx-whatsapp_message-session_id',
            '{{%whatsapp_message}}',
            'session_id'
        );
        $this->createIndex(
            'idx-whatsapp_message-lid_id',
            '{{%whatsapp_message}}',
            'lid_id'
        );
        $this->createIndex(
            'idx-whatsapp_message-remote_jid',
            '{{%whatsapp_message}}',
            'remote_jid'
        );
        $this->createIndex(
            'idx-whatsapp_message-remote_phone',
            '{{%whatsapp_message}}',
            'remote_phone'
        );
        $this->createIndex(
            'idx-whatsapp_message-direction',
            '{{%whatsapp_message}}',
            'direction'
        );
        $this->createIndex(
            'idx-whatsapp_message-is_read',
            '{{%whatsapp_message}}',
            'is_read'
        );
        $this->createIndex(
            'idx-whatsapp_message-created_at',
            '{{%whatsapp_message}}',
            'created_at'
        );
        $this->createIndex(
            'idx-whatsapp_message-whatsapp_id',
            '{{%whatsapp_message}}',
            'whatsapp_id'
        );

        // Foreign keys для whatsapp_message
        $this->addForeignKey(
            'fk-whatsapp_message-organization_id',
            '{{%whatsapp_message}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-whatsapp_message-session_id',
            '{{%whatsapp_message}}',
            'session_id',
            '{{%whatsapp_session}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-whatsapp_message-lid_id',
            '{{%whatsapp_message}}',
            'lid_id',
            '{{%lids}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Таблица чатов (группировка сообщений по собеседникам)
        $this->createTable('{{%whatsapp_chat}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'session_id' => $this->integer()->notNull(),
            'lid_id' => $this->integer()->null(),
            'remote_jid' => $this->string(100)->notNull(),
            'remote_phone' => $this->string(20)->null(),
            'remote_name' => $this->string(255)->null(),
            'last_message_id' => $this->integer()->null(),
            'last_message_at' => $this->timestamp()->null(),
            'unread_count' => $this->integer()->notNull()->defaultValue(0),
            'is_archived' => $this->boolean()->notNull()->defaultValue(false),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Индексы для whatsapp_chat
        $this->createIndex(
            'idx-whatsapp_chat-organization_id',
            '{{%whatsapp_chat}}',
            'organization_id'
        );
        $this->createIndex(
            'idx-whatsapp_chat-session_id',
            '{{%whatsapp_chat}}',
            'session_id'
        );
        $this->createIndex(
            'idx-whatsapp_chat-lid_id',
            '{{%whatsapp_chat}}',
            'lid_id'
        );
        $this->createIndex(
            'idx-whatsapp_chat-remote_jid',
            '{{%whatsapp_chat}}',
            ['session_id', 'remote_jid'],
            true // unique per session
        );
        $this->createIndex(
            'idx-whatsapp_chat-last_message_at',
            '{{%whatsapp_chat}}',
            'last_message_at'
        );
        $this->createIndex(
            'idx-whatsapp_chat-unread_count',
            '{{%whatsapp_chat}}',
            'unread_count'
        );

        // Foreign keys для whatsapp_chat
        $this->addForeignKey(
            'fk-whatsapp_chat-organization_id',
            '{{%whatsapp_chat}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-whatsapp_chat-session_id',
            '{{%whatsapp_chat}}',
            'session_id',
            '{{%whatsapp_session}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-whatsapp_chat-lid_id',
            '{{%whatsapp_chat}}',
            'lid_id',
            '{{%lids}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%whatsapp_chat}}');
        $this->dropTable('{{%whatsapp_message}}');
        $this->dropTable('{{%whatsapp_session}}');
    }
}
