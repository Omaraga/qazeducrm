<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lids_subject_point}}`.
 */
class m240125_115427_create_lids_subject_point_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lids_subject_point}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'lid_id' => $this->integer(11),
            'subject_id' => $this->integer(11),
            'point' => $this->integer(11),
            'is_deleted' => $this->smallInteger()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%lids_subject_point}}');
    }
}
