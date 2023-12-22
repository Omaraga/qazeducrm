<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%pupil}}', [
            'id' => $this->primaryKey(),
            'iin' => $this->string()->notNull(),
            'email' => $this->string(),
            'phone' => $this->string(),
            'home_phone' => $this->string(),
            'address' => $this->string(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'middle_name' => $this->string(),
            'parent_fio' => $this->string(),
            'parent_phone' => $this->string(),
            'sex' => $this->integer(1)->defaultValue(1),
            'birth_date' => $this->string(),
            'school_name' => $this->string(),
            'info' => $this->text(),
            'class_id' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%pupil}}');
    }
}
