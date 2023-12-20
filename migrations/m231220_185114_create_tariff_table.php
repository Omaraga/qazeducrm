<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tariff}}`.
 */
class m231220_185114_create_tariff_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tariff}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text(),
            'status' => $this->integer(1)->defaultValue(1),
            'duration' => $this->integer(2)->notNull(),
            'lesson_amount' => $this->integer(11),
            'type' => $this->integer(2)->notNull(),
            'price' => $this->integer(11),
            'description' => $this->text(),
            'info' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tariff}}');
    }
}
