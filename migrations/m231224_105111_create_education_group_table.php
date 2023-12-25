<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%education_group}}`.
 */
class m231224_105111_create_education_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%education_group}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'pupil_id' => $this->integer(11),
            'education_id' => $this->integer(11)->notNull(),
            'group_id' => $this->integer(11)->notNull(),
            'subject_id' => $this->integer(11)->defaultValue(-1),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'info' => $this->text(),
        ]);
        $this->addForeignKey('fk-education_group-education_id', 'education_group', 'education_id', 'pupil_education', 'id', 'CASCADE');
        $this->addForeignKey('fk-education_group-group_id', 'education_group', 'group_id', 'group', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-education_group-education_id', 'education_group');
        $this->dropForeignKey('fk-education_group-group_id', 'education_group');
        $this->dropTable('{{%education_group}}');
    }
}
