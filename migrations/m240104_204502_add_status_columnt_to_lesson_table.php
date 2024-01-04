<?php

use yii\db\Migration;

/**
 * Class m240104_204502_add_status_columnt_to_lesson_table
 */
class m240104_204502_add_status_columnt_to_lesson_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('lesson', 'status', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('lesson', 'status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240104_204502_add_status_columnt_to_lesson_table cannot be reverted.\n";

        return false;
    }
    */
}
