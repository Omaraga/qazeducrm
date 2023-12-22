<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pupil}}`.
 */
class m231221_165921_add_column_to_pupil_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pupil', 'balance', $this->double());
        $this->addColumn('pupil', 'fio', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pupil', 'balance');
        $this->dropColumn('pupil', 'fio');
    }
}
