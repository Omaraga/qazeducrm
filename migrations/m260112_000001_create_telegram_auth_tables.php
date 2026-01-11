<?php

use yii\db\Migration;

/**
 * Миграция для таблиц авторизации через Telegram
 */
class m260112_000001_create_telegram_auth_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Глобальная таблица пользователей Telegram (один бот на платформу)
        $this->createTable('{{%telegram_user}}', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->string(50)->notNull()->unique(),
            'phone' => $this->string(20)->notNull(),
            'username' => $this->string(100)->null(),
            'first_name' => $this->string(100)->null(),
            'last_name' => $this->string(100)->null(),
            'is_active' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ]);

        $this->createIndex('idx-telegram_user-phone', '{{%telegram_user}}', 'phone');

        // Коды верификации (привязаны к организации)
        $this->createTable('{{%verification_code}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'code' => $this->string(6)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'attempts' => $this->smallInteger()->notNull()->defaultValue(0),
            'expires_at' => $this->dateTime()->notNull(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-verification_code-phone-code', '{{%verification_code}}', ['phone', 'code']);
        $this->createIndex('idx-verification_code-org-phone', '{{%verification_code}}', ['organization_id', 'phone']);
        $this->createIndex('idx-verification_code-status', '{{%verification_code}}', 'status');
        $this->createIndex('idx-verification_code-expires', '{{%verification_code}}', 'expires_at');

        $this->addForeignKey(
            'fk-verification_code-organization',
            '{{%verification_code}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-verification_code-organization', '{{%verification_code}}');
        $this->dropTable('{{%verification_code}}');
        $this->dropTable('{{%telegram_user}}');
    }
}
