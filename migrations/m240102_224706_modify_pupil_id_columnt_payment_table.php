<?php

use yii\db\Migration;

/**
 * Class m240102_224706_modify_pupil_id_columnt_payment_table
 */
class m240102_224706_modify_pupil_id_columnt_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('payment', 'pupil_id', $this->integer(11)->null());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240102_224706_modify_pupil_id_columnt_payment_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240102_224706_modify_pupil_id_columnt_payment_table cannot be reverted.\n";

        return false;
    }
    */
}
