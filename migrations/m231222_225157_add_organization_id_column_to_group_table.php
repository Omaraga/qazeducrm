<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%group}}`.
 */
class m231222_225157_add_organization_id_column_to_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group', 'organization_id', $this->integer(11));
        $this->addColumn('group', 'editor_id', $this->integer(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('group', 'organization_id');
        $this->dropColumn('group', 'editor_id');
    }
}
