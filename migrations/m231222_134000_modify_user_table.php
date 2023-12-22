<?php

use yii\db\Migration;

/**
 * Class m231222_134000_modify_user_table
 */
class m231222_134000_modify_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'first_name', $this->string(255));
        $this->addColumn('user', 'last_name', $this->string(255));
        $this->addColumn('user', 'middle_name', $this->string(255));
        $this->addColumn('user', 'iin', $this->string(255));
        $this->addColumn('user', 'phone', $this->string(255));
        $this->addColumn('user', 'home_phone', $this->string(255));
        $this->addColumn('user', 'birth_date', $this->string(255));
        $this->alterColumn('user', 'created_at', $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression("NOW()")));
        $this->alterColumn('user', 'updated_at', $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression("NOW()")));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231222_134000_modify_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231222_134000_modify_user_table cannot be reverted.\n";

        return false;
    }
    */
}
