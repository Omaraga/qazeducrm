<?php

use yii\db\Migration;

/**
 * Добавляет индекс для запроса подсчёта непрочитанных по сессии
 *
 * Запрос: WHERE is_deleted = 0 AND session_id = ? AND unread_count > 0
 * (is_deleted добавляется автоматически через WhatsappChat::find())
 */
class m260109_152000_add_whatsapp_chat_session_unread_index extends Migration
{
    public function safeUp()
    {
        // Индекс для подсчёта непрочитанных по сессии
        // Используется в WhatsappSession::getUnreadCount()
        $this->createIndex(
            'idx-whatsapp_chat-session_unread',
            '{{%whatsapp_chat}}',
            ['session_id', 'is_deleted', 'unread_count']
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx-whatsapp_chat-session_unread', '{{%whatsapp_chat}}');
    }
}
