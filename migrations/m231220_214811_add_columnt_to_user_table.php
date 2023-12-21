<?php

use yii\db\Migration;

/**
 * Class m231220_214811_add_columnt_to_user_table
 */
class m231220_214811_add_columnt_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'system_role', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'system_role');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231220_214811_add_columnt_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
