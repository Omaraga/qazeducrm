<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tariff_subject}}`.
 */
class m231223_191316_create_tariff_subject_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tariff_subject}}', [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer(11),
            'subject_id' => $this->integer(11),
            'lesson_amount' => $this->integer(4),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'info' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tariff_subject}}');
    }
}
