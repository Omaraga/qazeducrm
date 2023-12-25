<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pupil_education}}`.
 */
class m231223_224502_create_pupil_education_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%pupil_education}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'pupil_id' => $this->integer(11)->notNull(),
            'tariff_id' => $this->integer(11)->notNull(),
            'sale' => $this->integer(5)->defaultValue(0),
            'date_start' => $this->date(),
            'date_end' => $this->date(),
            'comment' => $this->text(),
            'tariff_price' => $this->double()->defaultValue(0),
            'total_price' => $this->double()->defaultValue(0),
            'is_deleted' => $this->integer(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'create_user' => $this->integer(11),
            'update_user' => $this->integer(11),
            'info' => $this->text(),
        ]);
        $this->addForeignKey('fk-pupil_eduction-pupil_id', 'pupil_education', 'pupil_id', 'pupil', 'id', 'CASCADE');
        $this->addForeignKey('fk-pupil_eduction-tariff_id', 'pupil_education', 'tariff_id', 'tariff', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pupil_eduction-pupil_id', 'pupil_education');
        $this->dropForeignKey('fk-pupil_eduction-tariff_id', 'pupil_education');
        $this->dropTable('{{%pupil_education}}');
    }
}
