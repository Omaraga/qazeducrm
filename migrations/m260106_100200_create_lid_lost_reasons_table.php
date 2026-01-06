<?php

use yii\db\Migration;

/**
 * Создание справочника причин потери лидов
 */
class m260106_100200_create_lid_lost_reasons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lid_lost_reasons}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->null(), // null = глобальная причина для всех организаций
            'name' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);

        // Индексы
        $this->createIndex('idx-lid_lost_reasons-organization_id', '{{%lid_lost_reasons}}', 'organization_id');
        $this->createIndex('idx-lid_lost_reasons-sort_order', '{{%lid_lost_reasons}}', 'sort_order');

        // Вставка стандартных причин (для всех организаций)
        $this->batchInsert('{{%lid_lost_reasons}}', ['organization_id', 'name', 'sort_order'], [
            [null, 'Дорого', 1],
            [null, 'Далеко', 2],
            [null, 'Не устроило расписание', 3],
            [null, 'Выбрали конкурента', 4],
            [null, 'Передумали', 5],
            [null, 'Не дозвонились', 6],
            [null, 'Ребёнок отказался', 7],
            [null, 'Другое', 99],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%lid_lost_reasons}}');
    }
}
