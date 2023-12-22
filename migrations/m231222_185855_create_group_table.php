<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group}}`.
 */
class m231222_185855_create_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group}}', [
            'id' => $this->primaryKey(),
            'subject_id' => $this->integer(11),
            'code' => $this->string(255)->notNull(),
            'name' => $this->string(255),
            'category_id' => $this->smallInteger(),
            'type' => $this->smallInteger(),
            'color' => $this->string(255),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'info' => $this->text(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group}}');
    }
}
