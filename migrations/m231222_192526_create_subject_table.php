<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subject}}`.
 */
class m231222_192526_create_subject_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subject}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'is_deleted' => $this->integer(1)->notNull()->defaultValue(0),
            'order_col' => $this->smallInteger()->defaultValue(0),
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
        $this->dropTable('{{%subject}}');
    }
}
