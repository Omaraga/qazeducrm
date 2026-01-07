<?php

use yii\db\Migration;

/**
 * Создание таблиц для накопительных и индивидуальных скидок.
 *
 * Таблицы:
 * - saas_volume_discount: накопительные скидки за лояльность
 * - organization_discount: индивидуальные скидки организаций
 */
class m250107_100003_create_discount_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица накопительных скидок (за лояльность)
        $this->createTable('{{%saas_volume_discount}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'description' => $this->text()->comment('Описание'),
            'min_months' => $this->integer()->notNull()->comment('Минимум месяцев подряд'),
            'discount_percent' => $this->decimal(5, 2)->notNull()->comment('Процент скидки'),
            'applies_to' => "ENUM('renewal', 'all') DEFAULT 'renewal' COMMENT 'Применяется к: renewal=продление, all=все'",
            'is_active' => $this->boolean()->defaultValue(true)->comment('Активна'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Порядок сортировки'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Индексы
        $this->createIndex('idx_volume_discount_active', '{{%saas_volume_discount}}', 'is_active');
        $this->createIndex('idx_volume_discount_months', '{{%saas_volume_discount}}', 'min_months');

        // Начальные данные накопительных скидок
        $this->batchInsert('{{%saas_volume_discount}}', [
            'name', 'description', 'min_months', 'discount_percent', 'applies_to', 'is_active', 'sort_order'
        ], [
            ['Лояльный клиент', '6 месяцев подряд - 5% скидка на продление', 6, 5.00, 'renewal', 1, 1],
            ['Постоянный клиент', '12 месяцев подряд - 10% скидка на продление', 12, 10.00, 'renewal', 1, 2],
            ['VIP клиент', '24 месяца подряд - 15% скидка на продление', 24, 15.00, 'renewal', 1, 3],
        ]);

        // Таблица индивидуальных скидок организаций
        $this->createTable('{{%organization_discount}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),
            'discount_type' => "ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent' COMMENT 'Тип скидки'",
            'discount_value' => $this->decimal(10, 2)->notNull()->comment('Значение скидки'),
            'reason' => $this->string(255)->comment('Причина скидки'),
            'valid_from' => $this->dateTime()->comment('Действует с'),
            'valid_until' => $this->dateTime()->comment('Действует до (null=бессрочно)'),
            'applies_to' => "ENUM('subscription', 'addon', 'all') DEFAULT 'all' COMMENT 'К чему применяется'",
            'created_by' => $this->integer()->comment('Кто создал'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Активна'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Индексы для индивидуальных скидок
        $this->createIndex('idx_org_discount_org', '{{%organization_discount}}', 'organization_id');
        $this->createIndex('idx_org_discount_active', '{{%organization_discount}}', 'is_active');
        $this->createIndex('idx_org_discount_valid', '{{%organization_discount}}', ['valid_from', 'valid_until']);

        // Внешние ключи
        $this->addForeignKey(
            'fk_org_discount_org',
            '{{%organization_discount}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_org_discount_created_by',
            '{{%organization_discount}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        echo "    > Созданы таблицы накопительных и индивидуальных скидок\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_org_discount_created_by', '{{%organization_discount}}');
        $this->dropForeignKey('fk_org_discount_org', '{{%organization_discount}}');
        $this->dropTable('{{%organization_discount}}');

        $this->dropTable('{{%saas_volume_discount}}');
    }
}
