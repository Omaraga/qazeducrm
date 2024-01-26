<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lids}}`.
 */
class m240125_114126_create_lids_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lids}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'fio' => $this->text(),
            'phone' => $this->string(255),
            'class_id' => $this->smallInteger(),
            'school' => $this->string(255),
            'date' => $this->date(),
            'manager_name' => $this->string(255),
            'comment' => $this->text(),
            'sale' => $this->integer(11),
            'total_sum' => $this->integer(11),
            'total_point' => $this->integer(11),
            'is_deleted' => $this->smallInteger()->defaultValue(0),
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
        $this->dropTable('{{%lids}}');
    }
}
