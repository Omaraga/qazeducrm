<?php

use yii\db\Migration;

/**
 * Class m220923_134450_add_static_pages_table
 */
class m220923_134450_add_static_pages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('static_pages', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'title' => $this->text(),
            'description' => 'longtext',
            'created_at' => $this->integer(11),
            'info' => 'mediumtext',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('static_pages');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220923_134450_add_static_pages_table cannot be reverted.\n";

        return false;
    }
    */
}
