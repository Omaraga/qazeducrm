<?php

use yii\db\Migration;

/**
 * Создаёт таблицу настроек автоматических уведомлений
 * Хранит конфигурацию напоминаний для каждой организации
 */
class m260109_120000_create_notification_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification_setting}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull()->comment('lesson_reminder, payment_due, birthday'),
            'channel' => $this->string(20)->notNull()->defaultValue('whatsapp')->comment('sms, whatsapp'),
            'is_active' => $this->boolean()->notNull()->defaultValue(false),
            'hours_before' => $this->integer()->comment('За сколько часов до события (для lesson_reminder)'),
            'frequency' => $this->string(20)->comment('daily, weekly (для payment_due)'),
            'template_id' => $this->integer()->comment('ID шаблона сообщения'),
            'last_run_at' => $this->dateTime()->comment('Время последнего запуска'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime(),
        ]);

        // Индексы
        $this->createIndex('idx-notification_setting-org', 'notification_setting', 'organization_id');
        $this->createIndex('idx-notification_setting-org_type', 'notification_setting', ['organization_id', 'type'], true);

        // Внешние ключи
        $this->addForeignKey(
            'fk-notification_setting-organization',
            'notification_setting',
            'organization_id',
            'organization',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-notification_setting-template',
            'notification_setting',
            'template_id',
            'sms_template',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-notification_setting-template', 'notification_setting');
        $this->dropForeignKey('fk-notification_setting-organization', 'notification_setting');
        $this->dropTable('{{%notification_setting}}');
    }
}
