<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sales_script}}`.
 * Скрипты продаж для работы с лидами
 */
class m260106_230000_create_sales_script_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if table exists (from partial migration)
        $tableExists = $this->db->getTableSchema('{{%sales_script}}', true) !== null;

        if (!$tableExists) {
            $this->createTable('{{%sales_script}}', [
                'id' => $this->primaryKey(),
                'organization_id' => $this->integer()->notNull(),
                'status' => $this->string(50)->notNull()->comment('Статус лида для которого скрипт'),
                'title' => $this->string(255)->notNull()->comment('Заголовок скрипта'),
                'content' => $this->text()->comment('Содержимое скрипта'),
                'objections' => $this->text()->comment('JSON массив возражений и ответов'),
                'tips' => $this->text()->comment('JSON массив советов'),
                'sort_order' => $this->integer()->defaultValue(0),
                'is_active' => $this->boolean()->defaultValue(true),
                'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            $this->createIndex('idx-sales_script-organization_id', '{{%sales_script}}', 'organization_id');
            $this->createIndex('idx-sales_script-status', '{{%sales_script}}', 'status');
            $this->createIndex('idx-sales_script-is_active', '{{%sales_script}}', 'is_active');
        }

        // Skip FK constraint as organizations table may have different engine/charset
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sales_script}}');
    }
}
