<?php

use yii\db\Migration;

/**
 * Добавляет тип шаблона и WhatsApp шаблоны для лидов
 */
class m260106_210000_add_whatsapp_templates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Добавляем поле type для различения SMS и WhatsApp шаблонов
        $this->addColumn('sms_template', 'type', $this->string(20)->defaultValue('sms')->after('code'));

        // Добавляем комментарий
        $this->execute("ALTER TABLE sms_template MODIFY COLUMN type VARCHAR(20) DEFAULT 'sms' COMMENT 'Тип шаблона: sms, whatsapp'");

        // Добавляем индекс для быстрого поиска
        $this->createIndex('idx-sms_template-type', 'sms_template', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-sms_template-type', 'sms_template');
        $this->dropColumn('sms_template', 'type');
    }
}
