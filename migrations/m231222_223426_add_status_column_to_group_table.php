<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%group}}`.
 */
class m231222_223426_add_status_column_to_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group', 'status', $this->integer(1)->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('group', 'status');
    }
}
