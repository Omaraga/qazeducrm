<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%tariff}}`.
 */
class m231221_105653_add_organization_id_column_to_tariff_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tariff', 'organization_id', $this->integer(11));
        $this->addColumn('tariff', 'is_deleted', $this->integer(1)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tariff', 'is_deleted');
        $this->dropColumn('tariff', 'organization_id');
    }
}
