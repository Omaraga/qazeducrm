<?php

use yii\db\Migration;

/**
 * Добавляет поле profile_picture_url в таблицу whatsapp_chat
 * для хранения URL аватарки контакта WhatsApp
 */
class m260109_100000_add_profile_picture_to_whatsapp_chat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%whatsapp_chat}}', 'profile_picture_url', $this->string(512)->null()->after('remote_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%whatsapp_chat}}', 'profile_picture_url');
    }
}
