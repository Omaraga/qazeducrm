<?php

use yii\db\Migration;

/**
 * Оптимизация индексов таблицы whatsapp_chat
 *
 * Добавляем составные индексы:
 * 1. (session_id, is_deleted, is_archived, last_message_at) - список чатов
 * 2. (organization_id, is_deleted, unread_count) - подсчёт непрочитанных
 *
 * Удаляем бесполезные индексы:
 * - session_id (избыточен - уже есть в UNIQUE idx-whatsapp_chat-remote_jid)
 * - unread_count (низкая кардинальность, теперь в составном индексе)
 * - last_message_at (теперь в составном индексе)
 */
class m260109_151000_optimize_whatsapp_chat_indexes extends Migration
{
    public function safeUp()
    {
        // 1. Составной индекс для списка чатов (самый частый запрос)
        // Запрос: WHERE session_id = ? AND is_deleted = 0 AND is_archived = 0 ORDER BY last_message_at DESC
        $this->createIndex(
            'idx-whatsapp_chat-chat_list',
            '{{%whatsapp_chat}}',
            ['session_id', 'is_deleted', 'is_archived', 'last_message_at']
        );

        // 2. Составной индекс для подсчёта непрочитанных по организации
        // Запрос: WHERE organization_id = ? AND is_deleted = 0 AND unread_count > 0
        $this->createIndex(
            'idx-whatsapp_chat-unread_by_org',
            '{{%whatsapp_chat}}',
            ['organization_id', 'is_deleted', 'unread_count']
        );

        // Удаляем избыточные одиночные индексы

        // session_id - уже покрывается UNIQUE (session_id, remote_jid) и новым chat_list
        $this->dropIndex('idx-whatsapp_chat-session_id', '{{%whatsapp_chat}}');

        // unread_count - низкая кардинальность, теперь в составном unread_by_org
        $this->dropIndex('idx-whatsapp_chat-unread_count', '{{%whatsapp_chat}}');

        // last_message_at - теперь в составном chat_list
        $this->dropIndex('idx-whatsapp_chat-last_message_at', '{{%whatsapp_chat}}');
    }

    public function safeDown()
    {
        // Восстанавливаем удалённые индексы
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

        $this->createIndex(
            'idx-whatsapp_chat-session_id',
            '{{%whatsapp_chat}}',
            'session_id'
        );

        // Удаляем составные индексы
        $this->dropIndex('idx-whatsapp_chat-unread_by_org', '{{%whatsapp_chat}}');
        $this->dropIndex('idx-whatsapp_chat-chat_list', '{{%whatsapp_chat}}');
    }
}
