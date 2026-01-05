<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%room}}` and adding room_id to schedule tables.
 */
class m260104_300000_create_room_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create room table
        $this->createTable('{{%room}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11)->notNull(),
            'name' => $this->string(100)->notNull(),
            'code' => $this->string(20),
            'capacity' => $this->integer()->defaultValue(0),
            'color' => $this->string(7)->defaultValue('#6366f1'),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'info' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()'))
        ]);

        // Add index for organization_id
        $this->createIndex(
            'idx-room-organization_id',
            '{{%room}}',
            'organization_id'
        );

        // Add room_id to lesson table (nullable - room is optional)
        $this->addColumn('{{%lesson}}', 'room_id', $this->integer()->null()->after('teacher_id'));

        // Add typical_schedule_id to lesson (to track origin)
        $this->addColumn('{{%lesson}}', 'typical_schedule_id', $this->integer()->null()->after('room_id'));

        // Add room_id to typical_schedule table (nullable)
        $this->addColumn('{{%typical_schedule}}', 'room_id', $this->integer()->null()->after('teacher_id'));

        // Add indexes
        $this->createIndex(
            'idx-lesson-room_id',
            '{{%lesson}}',
            'room_id'
        );

        $this->createIndex(
            'idx-lesson-typical_schedule_id',
            '{{%lesson}}',
            'typical_schedule_id'
        );

        $this->createIndex(
            'idx-typical_schedule-room_id',
            '{{%typical_schedule}}',
            'room_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes
        $this->dropIndex('idx-typical_schedule-room_id', '{{%typical_schedule}}');
        $this->dropIndex('idx-lesson-typical_schedule_id', '{{%lesson}}');
        $this->dropIndex('idx-lesson-room_id', '{{%lesson}}');

        // Drop columns
        $this->dropColumn('{{%typical_schedule}}', 'room_id');
        $this->dropColumn('{{%lesson}}', 'typical_schedule_id');
        $this->dropColumn('{{%lesson}}', 'room_id');

        // Drop room table
        $this->dropIndex('idx-room-organization_id', '{{%room}}');
        $this->dropTable('{{%room}}');
    }
}
