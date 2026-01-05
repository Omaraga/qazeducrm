<?php

use yii\db\Migration;

/**
 * Добавление поля template_id в таблицу typical_schedule
 */
class m260105_000002_add_template_id_to_typical_schedule extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%typical_schedule}}', 'template_id', $this->integer(11)->after('organization_id'));
        $this->createIndex('idx_typical_schedule_template', '{{%typical_schedule}}', 'template_id');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_typical_schedule_template', '{{%typical_schedule}}');
        $this->dropColumn('{{%typical_schedule}}', 'template_id');
    }
}
