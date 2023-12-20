<?php

use yii\db\Migration;

/**
 * Class m231220_203613_add_admin_user
 */
class m231220_203613_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $user = new \app\models\User();
        $user->email = 'admin@admin.kz';
        $user->username = 'admin@admin.kz';
        $user->fio = 'Админ';
        $user->setPassword('123456789');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231220_203613_add_admin_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231220_203613_add_admin_user cannot be reverted.\n";

        return false;
    }
    */
}
