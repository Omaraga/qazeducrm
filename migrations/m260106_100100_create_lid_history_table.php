<?php

use yii\db\Migration;

/**
 * Создание таблицы истории взаимодействий с лидами
 */
class m260106_100100_create_lid_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lid_history}}', [
            'id' => $this->primaryKey(),
            'lid_id' => $this->integer()->notNull(),
            'organization_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->null(),
            'type' => $this->string(50)->notNull(), // call, message, whatsapp, note, status_change, meeting, created
            'status_from' => $this->smallInteger()->null(),
            'status_to' => $this->smallInteger()->null(),
            'comment' => $this->text()->null(),
            'call_duration' => $this->integer()->null(), // длительность звонка в секундах
            'next_contact_date' => $this->date()->null(),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);

        // Индексы
        $this->createIndex('idx-lid_history-lid_id', '{{%lid_history}}', 'lid_id');
        $this->createIndex('idx-lid_history-organization_id', '{{%lid_history}}', 'organization_id');
        $this->createIndex('idx-lid_history-user_id', '{{%lid_history}}', 'user_id');
        $this->createIndex('idx-lid_history-type', '{{%lid_history}}', 'type');
        $this->createIndex('idx-lid_history-created_at', '{{%lid_history}}', 'created_at');

        // Внешние ключи
        $this->addForeignKey(
            'fk-lid_history-lid_id',
            '{{%lid_history}}',
            'lid_id',
            '{{%lids}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-lid_history-user_id',
            '{{%lid_history}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-lid_history-user_id', '{{%lid_history}}');
        $this->dropForeignKey('fk-lid_history-lid_id', '{{%lid_history}}');

        $this->dropTable('{{%lid_history}}');
    }
}
