<?php

use yii\db\Migration;

/**
 * Миграция для создания таблиц пользовательских тегов лидов
 */
class m260107_010000_create_lid_tags_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Проверяем существование таблицы
        if ($this->db->getTableSchema('{{%lid_tags}}', true) !== null) {
            echo "Таблица lid_tags уже существует, пропускаем\n";
            return true;
        }

        // Справочник тегов организации
        $this->createTable('{{%lid_tags}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull()->comment('Название тега'),
            'color' => $this->string(20)->defaultValue('gray')->comment('Цвет для Tailwind'),
            'icon' => $this->string(50)->defaultValue('tag')->comment('Иконка HeroIcons'),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_system' => $this->boolean()->defaultValue(false)->comment('Системный тег (нельзя удалить)'),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Индексы
        $this->createIndex('idx-lid_tags-organization_id', '{{%lid_tags}}', 'organization_id');
        $this->createIndex('ux-lid_tags-org_name', '{{%lid_tags}}', ['organization_id', 'name'], true);

        // Связь лиды ↔ теги (many-to-many)
        $this->createTable('{{%lid_tag_relations}}', [
            'id' => $this->primaryKey(),
            'lid_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Индексы для связи
        $this->createIndex('idx-lid_tag_relations-lid_id', '{{%lid_tag_relations}}', 'lid_id');
        $this->createIndex('idx-lid_tag_relations-tag_id', '{{%lid_tag_relations}}', 'tag_id');
        $this->createIndex('ux-lid_tag_relations-lid_tag', '{{%lid_tag_relations}}', ['lid_id', 'tag_id'], true);

        // Foreign keys
        try {
            $this->addForeignKey(
                'fk-lid_tag_relations-lid_id',
                '{{%lid_tag_relations}}',
                'lid_id',
                '{{%lids}}',
                'id',
                'CASCADE'
            );
        } catch (\Exception $e) {
            echo "FK lid_id: " . $e->getMessage() . " (пропускаем)\n";
        }

        try {
            $this->addForeignKey(
                'fk-lid_tag_relations-tag_id',
                '{{%lid_tag_relations}}',
                'tag_id',
                '{{%lid_tags}}',
                'id',
                'CASCADE'
            );
        } catch (\Exception $e) {
            echo "FK tag_id: " . $e->getMessage() . " (пропускаем)\n";
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем FK
        try { $this->dropForeignKey('fk-lid_tag_relations-tag_id', '{{%lid_tag_relations}}'); } catch (\Exception $e) {}
        try { $this->dropForeignKey('fk-lid_tag_relations-lid_id', '{{%lid_tag_relations}}'); } catch (\Exception $e) {}

        // Удаляем таблицы
        $this->dropTable('{{%lid_tag_relations}}');
        $this->dropTable('{{%lid_tags}}');

        return true;
    }
}
