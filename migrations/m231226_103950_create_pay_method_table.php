<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pay_method}}`.
 */
class m231226_103950_create_pay_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%pay_method}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'organization_id' => $this->integer(11),
            'is_deleted' => $this->integer(1)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'info' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%pay_method}}');
    }
}
