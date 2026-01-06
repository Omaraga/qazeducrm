<?php

use yii\db\Migration;

/**
 * Расширяет таблицу notification для системы напоминаний и уведомлений
 */
class m260106_220000_extend_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Добавляем organization_id
        $this->addColumn('notification', 'organization_id', $this->integer()->after('id'));

        // Добавляем title
        $this->addColumn('notification', 'title', $this->string(255)->after('type'));

        // Добавляем entity_type и entity_id для связи с сущностями
        $this->addColumn('notification', 'entity_type', $this->string(50)->after('message'));
        $this->addColumn('notification', 'entity_id', $this->integer()->after('entity_type'));

        // Добавляем link для быстрого перехода
        $this->addColumn('notification', 'link', $this->string(500)->after('entity_id'));

        // Добавляем is_read для отслеживания прочитанности
        $this->addColumn('notification', 'is_read', $this->boolean()->defaultValue(false)->after('link'));

        // Добавляем scheduled_at для напоминаний
        $this->addColumn('notification', 'scheduled_at', $this->dateTime()->after('is_read'));

        // Добавляем sent_at для отслеживания отправки
        $this->addColumn('notification', 'sent_at', $this->dateTime()->after('scheduled_at'));

        // Изменяем created_at на datetime
        $this->alterColumn('notification', 'created_at', $this->dateTime());

        // Индексы для быстрого поиска
        $this->createIndex('idx-notification-org_user', 'notification', ['organization_id', 'user_id']);
        $this->createIndex('idx-notification-is_read', 'notification', ['user_id', 'is_read', 'is_deleted']);
        $this->createIndex('idx-notification-scheduled', 'notification', ['scheduled_at', 'sent_at']);
        $this->createIndex('idx-notification-entity', 'notification', ['entity_type', 'entity_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-notification-entity', 'notification');
        $this->dropIndex('idx-notification-scheduled', 'notification');
        $this->dropIndex('idx-notification-is_read', 'notification');
        $this->dropIndex('idx-notification-org_user', 'notification');

        $this->dropColumn('notification', 'sent_at');
        $this->dropColumn('notification', 'scheduled_at');
        $this->dropColumn('notification', 'is_read');
        $this->dropColumn('notification', 'link');
        $this->dropColumn('notification', 'entity_id');
        $this->dropColumn('notification', 'entity_type');
        $this->dropColumn('notification', 'title');
        $this->dropColumn('notification', 'organization_id');

        $this->alterColumn('notification', 'created_at', $this->integer(11));
    }
}
