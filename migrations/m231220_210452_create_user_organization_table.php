<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_organization}}`.
 */
class m231220_210452_create_user_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_organization}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'related_id' => $this->integer(11),
            'target_id' => $this->integer(11),
            'state' => $this->smallInteger()->defaultValue(1),
            'is_deleted' => $this->smallInteger()->defaultValue(0),
            'role' => $this->string(255),
            'ts' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression("NOW()")),
            'info'=> $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_organization}}');
    }
}
