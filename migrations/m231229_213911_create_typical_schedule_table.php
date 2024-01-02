<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%typical_schedule}}`.
 */
class m231229_213911_create_typical_schedule_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%typical_schedule}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'group_id' => $this->integer(11),
            'teacher_id' => $this->integer(11),
            'week' => $this->smallInteger(),
            'start_time' => $this->time(),
            'end_time' => $this->time(),
            'date' => $this->date(),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'info' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()'))
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%typical_schedule}}');
    }
}
