<?php

use yii\db\Migration;

/**
 * Class m260104_100000_create_super_admin
 */
class m260104_100000_create_super_admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $user = new \app\models\User();
        $user->email = 'super@qazedu.kz';
        $user->username = 'superadmin';
        $user->first_name = 'Super';
        $user->last_name = 'Admin';
        $user->fio = 'Super Admin';
        $user->system_role = 'SUPER';
        $user->status = \app\models\User::STATUS_ACTIVE;
        $user->setPassword('SuperAdmin2024!');
        $user->generateAuthKey();
        $user->save(false);

        echo "Super admin created:\n";
        echo "Login: superadmin\n";
        echo "Password: SuperAdmin2024!\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%user}}', ['username' => 'superadmin']);
    }
}
