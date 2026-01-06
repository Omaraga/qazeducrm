<?php

use yii\db\Migration;

/**
 * Добавляет поле tags для тегов лидов (hot, vip, repeat, no_answer)
 */
class m260106_200000_add_lids_tags extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('lids', 'tags', $this->json()->null()->after('lost_reason'));

        // Добавляем комментарий к полю
        $this->execute("ALTER TABLE lids MODIFY COLUMN tags JSON COMMENT 'Теги лида: hot, vip, repeat, no_answer'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('lids', 'tags');
    }
}
