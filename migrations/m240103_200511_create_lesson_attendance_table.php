<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lesson_attendance}}`.
 */
class m240103_200511_create_lesson_attendance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lesson_attendance}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'pupil_id' => $this->integer(11)->notNull(),
            'lesson_id' => $this->integer(11)->notNull(),
            'teacher_id' => $this->integer(11)->notNull(),
            'status' => $this->integer(11),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'info' => $this->text(),
        ]);
        $this->addForeignKey('fk-lesson_attendance-pupil_id', 'lesson_attendance', 'pupil_id', 'pupil', 'id', 'CASCADE');
        $this->addForeignKey('fk-lesson_attendance-lesson_id', 'lesson_attendance', 'lesson_id', 'lesson', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-lesson_attendance-pupil_id', 'lesson_attendance');
        $this->dropForeignKey('fk-lesson_attendance-lesson_id', 'lesson_attendance');
        $this->dropTable('{{%lesson_attendance}}');
    }
}
