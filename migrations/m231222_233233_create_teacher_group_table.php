<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%teacher_group}}`.
 */
class m231222_233233_create_teacher_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%teacher_group}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'related_id' => $this->integer(11)->notNull()->comment('Учитель'),
            'target_id' => $this->integer(11)->notNull()->comment('Группа'),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'type' => $this->smallInteger()->defaultValue(1),
            'price' => $this->integer(11)->defaultValue(0),
            'info' => $this->text(),
        ]);
        $this->addForeignKey('fk-teacher_group-related_id', 'teacher_group', 'related_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk-teacher_group-target_id', 'teacher_group', 'target_id', 'group', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-teacher_group-related_id', 'teacher_group');
        $this->dropForeignKey('fk-teacher_group-target_id', 'teacher_group');
        $this->dropTable('{{%teacher_group}}');
    }
}
