<?php

use yii\db\Migration;

/**
 * Class m231222_004223_modify_pupil_table
 */
class m231222_004223_modify_pupil_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('pupil', 'is_deleted', $this->smallInteger()->defaultValue(0));
        $this->addColumn('pupil', 'organization_id', $this->integer(11));
        $this->alterColumn('pupil', 'status', $this->smallInteger()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pupil', 'is_deleted');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231222_004223_modify_pupil_table cannot be reverted.\n";

        return false;
    }
    */
}
