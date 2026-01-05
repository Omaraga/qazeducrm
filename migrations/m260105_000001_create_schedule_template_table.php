<?php

use yii\db\Migration;

/**
 * Создание таблицы schedule_template для хранения шаблонов расписания
 */
class m260105_000001_create_schedule_template_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%schedule_template}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11)->notNull(),
            'name' => $this->string(255)->notNull()->comment('Название шаблона'),
            'description' => $this->text()->comment('Описание'),
            'color' => $this->string(7)->comment('Цвет для отображения'),
            'is_default' => $this->tinyInteger(1)->defaultValue(0)->comment('Шаблон по умолчанию'),
            'is_active' => $this->tinyInteger(1)->defaultValue(1)->comment('Активен'),
            'is_deleted' => $this->tinyInteger(1)->defaultValue(0),
            'info' => $this->text()->comment('JSON дополнительные данные'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_schedule_template_org', '{{%schedule_template}}', 'organization_id');
        $this->createIndex('idx_schedule_template_default', '{{%schedule_template}}', ['organization_id', 'is_default']);
        $this->createIndex('idx_schedule_template_active', '{{%schedule_template}}', ['organization_id', 'is_active', 'is_deleted']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%schedule_template}}');
    }
}
