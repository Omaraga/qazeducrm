<?php

use yii\db\Migration;

/**
 * Миграция для SMS уведомлений:
 * - sms_template - шаблоны SMS
 * - sms_log - лог отправленных SMS
 * - sms_settings - настройки SMS провайдера
 */
class m260104_220000_create_sms_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Шаблоны SMS
        $this->createTable('{{%sms_template}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull()->comment('Код шаблона: lesson_reminder, payment_due, birthday'),
            'name' => $this->string(255)->notNull(),
            'content' => $this->text()->notNull()->comment('Текст с плейсхолдерами {name}, {date}, {time}, {amount}'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-sms_template-organization', '{{%sms_template}}', 'organization_id');
        $this->createIndex('idx-sms_template-code', '{{%sms_template}}', ['organization_id', 'code']);

        $this->addForeignKey(
            'fk-sms_template-organization',
            '{{%sms_template}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        // Лог отправленных SMS
        $this->createTable('{{%sms_log}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'template_id' => $this->integer()->null(),
            'phone' => $this->string(20)->notNull(),
            'message' => $this->text()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)->comment('1=pending, 2=sent, 3=delivered, 4=failed'),
            'provider_id' => $this->string(100)->null()->comment('ID сообщения у провайдера'),
            'provider_response' => $this->text()->null(),
            'error_message' => $this->string(255)->null(),
            'recipient_type' => $this->string(20)->null()->comment('pupil, parent, teacher'),
            'recipient_id' => $this->integer()->null(),
            'sent_at' => $this->timestamp()->null(),
            'delivered_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-sms_log-organization', '{{%sms_log}}', 'organization_id');
        $this->createIndex('idx-sms_log-phone', '{{%sms_log}}', 'phone');
        $this->createIndex('idx-sms_log-status', '{{%sms_log}}', 'status');
        $this->createIndex('idx-sms_log-created', '{{%sms_log}}', 'created_at');

        $this->addForeignKey(
            'fk-sms_log-organization',
            '{{%sms_log}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        // Настройки SMS провайдера для организации
        $this->addColumn('{{%organization}}', 'sms_provider', $this->string(50)->null()->after('locale'));
        $this->addColumn('{{%organization}}', 'sms_api_key', $this->string(255)->null()->after('sms_provider'));
        $this->addColumn('{{%organization}}', 'sms_sender', $this->string(20)->null()->after('sms_api_key'));
        $this->addColumn('{{%organization}}', 'sms_balance', $this->decimal(10, 2)->null()->defaultValue(0)->after('sms_sender'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'sms_balance');
        $this->dropColumn('{{%organization}}', 'sms_sender');
        $this->dropColumn('{{%organization}}', 'sms_api_key');
        $this->dropColumn('{{%organization}}', 'sms_provider');

        $this->dropForeignKey('fk-sms_log-organization', '{{%sms_log}}');
        $this->dropTable('{{%sms_log}}');

        $this->dropForeignKey('fk-sms_template-organization', '{{%sms_template}}');
        $this->dropTable('{{%sms_template}}');
    }
}
