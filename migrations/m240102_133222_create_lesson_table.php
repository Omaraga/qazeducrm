<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lesson}}`.
 */
class m240102_133222_create_lesson_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lesson}}', [
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
        $this->dropTable('{{%lesson}}');
    }
}
