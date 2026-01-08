<?php

use yii\db\Migration;

/**
 * Class m260108_234104_update_whatsapp_tables_to_utf8mb4
 *
 * Converts WhatsApp tables to utf8mb4 charset to support emojis (4-byte characters)
 */
class m260108_234104_update_whatsapp_tables_to_utf8mb4 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Convert whatsapp_message table
        $this->execute("ALTER TABLE {{%whatsapp_message}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Convert whatsapp_chat table
        $this->execute("ALTER TABLE {{%whatsapp_chat}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Convert whatsapp_session table
        $this->execute("ALTER TABLE {{%whatsapp_session}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Convert back to utf8 (will lose 4-byte characters like emojis)
        $this->execute("ALTER TABLE {{%whatsapp_message}} CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->execute("ALTER TABLE {{%whatsapp_chat}} CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->execute("ALTER TABLE {{%whatsapp_session}} CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    }
}
