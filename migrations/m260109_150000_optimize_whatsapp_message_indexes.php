<?php

use yii\db\Migration;

/**
 * Оптимизация индексов таблицы whatsapp_message
 *
 * Добавляем составные индексы для часто используемых запросов:
 * 1. (session_id, whatsapp_id) - проверка дубликатов, обновление статуса
 * 2. (session_id, remote_jid, is_deleted, created_at) - загрузка сообщений чата
 * 3. (session_id, remote_jid, is_read) - подсчёт непрочитанных
 *
 * Удаляем бесполезные индексы с низкой кардинальностью:
 * - direction (только 2 значения, никогда не используется в WHERE отдельно)
 * - is_read (только 2 значения, используется только в составе других условий)
 */
class m260109_150000_optimize_whatsapp_message_indexes extends Migration
{
    public function safeUp()
    {
        // 1. Составной индекс для проверки дубликатов и обновления статуса
        // Запрос: WHERE whatsapp_id = ? AND session_id = ?
        $this->createIndex(
            'idx-whatsapp_message-session_whatsapp',
            '{{%whatsapp_message}}',
            ['session_id', 'whatsapp_id']
        );

        // 2. Составной индекс для загрузки сообщений чата (самый частый запрос)
        // Запрос: WHERE session_id = ? AND remote_jid = ? AND is_deleted = 0 ORDER BY created_at
        $this->createIndex(
            'idx-whatsapp_message-chat_messages',
            '{{%whatsapp_message}}',
            ['session_id', 'remote_jid', 'is_deleted', 'created_at']
        );

        // 3. Составной индекс для подсчёта непрочитанных
        // Запрос: WHERE session_id = ? AND remote_jid = ? AND is_read = 0
        $this->createIndex(
            'idx-whatsapp_message-unread',
            '{{%whatsapp_message}}',
            ['session_id', 'remote_jid', 'is_read']
        );

        // Удаляем бесполезные одиночные индексы
        // direction - низкая кардинальность, не используется отдельно
        $this->dropIndex('idx-whatsapp_message-direction', '{{%whatsapp_message}}');

        // is_read - низкая кардинальность, теперь есть в составном индексе
        $this->dropIndex('idx-whatsapp_message-is_read', '{{%whatsapp_message}}');

        // remote_jid - теперь покрывается составным индексом idx-whatsapp_message-chat_messages
        $this->dropIndex('idx-whatsapp_message-remote_jid', '{{%whatsapp_message}}');
    }

    public function safeDown()
    {
        // Восстанавливаем удалённые индексы
        $this->createIndex(
            'idx-whatsapp_message-remote_jid',
            '{{%whatsapp_message}}',
            'remote_jid'
        );

        $this->createIndex(
            'idx-whatsapp_message-is_read',
            '{{%whatsapp_message}}',
            'is_read'
        );

        $this->createIndex(
            'idx-whatsapp_message-direction',
            '{{%whatsapp_message}}',
            'direction'
        );

        // Удаляем составные индексы
        $this->dropIndex('idx-whatsapp_message-unread', '{{%whatsapp_message}}');
        $this->dropIndex('idx-whatsapp_message-chat_messages', '{{%whatsapp_message}}');
        $this->dropIndex('idx-whatsapp_message-session_whatsapp', '{{%whatsapp_message}}');
    }
}
