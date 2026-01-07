<?php

use yii\db\Migration;

/**
 * Создание таблицы логов уведомлений о подписках
 */
class m250107_100005_create_subscription_notification_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscription_notification_log}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'subscription_id' => $this->integer(),
            'type' => $this->string(50)->notNull()->comment('Тип уведомления'),
            'channel' => $this->string(20)->notNull()->comment('Канал: email, sms, in_app'),
            'recipient' => $this->string(255)->comment('Email или телефон'),
            'subject' => $this->string(255)->comment('Тема письма'),
            'message' => $this->text()->comment('Текст сообщения'),
            'metadata' => $this->json()->comment('Дополнительные данные'),
            'status' => $this->string(20)->defaultValue('sent')->comment('sent, failed, pending'),
            'error_message' => $this->text()->comment('Сообщение об ошибке'),
            'sent_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_notif_log_org', '{{%subscription_notification_log}}', 'organization_id');
        $this->createIndex('idx_notif_log_type', '{{%subscription_notification_log}}', 'type');
        $this->createIndex('idx_notif_log_sent', '{{%subscription_notification_log}}', 'sent_at');

        $this->addForeignKey(
            'fk_notif_log_org',
            '{{%subscription_notification_log}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Добавляем поле grace_period_ends_at в organization_subscription если его нет
        $tableSchema = $this->db->getTableSchema('{{%organization_subscription}}');

        if (!isset($tableSchema->columns['grace_period_ends_at'])) {
            $this->addColumn('{{%organization_subscription}}', 'grace_period_ends_at',
                $this->dateTime()->after('expires_at')->comment('Окончание grace периода'));
        }

        if (!isset($tableSchema->columns['access_mode'])) {
            $this->addColumn('{{%organization_subscription}}', 'access_mode',
                $this->string(20)->defaultValue('full')->after('status')->comment('Режим доступа: full, limited, read_only, blocked'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->db->getTableSchema('{{%organization_subscription}}');

        if (isset($tableSchema->columns['access_mode'])) {
            $this->dropColumn('{{%organization_subscription}}', 'access_mode');
        }

        if (isset($tableSchema->columns['grace_period_ends_at'])) {
            $this->dropColumn('{{%organization_subscription}}', 'grace_period_ends_at');
        }

        $this->dropTable('{{%subscription_notification_log}}');
    }
}
