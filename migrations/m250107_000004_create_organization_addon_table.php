<?php

use yii\db\Migration;

/**
 * Создание таблицы organization_addon для докупленных аддонов организаций.
 *
 * Аддоны позволяют организациям:
 * - Увеличивать лимиты (учеников, групп, SMS)
 * - Подключать дополнительные функции (интеграции, порталы)
 * - Использовать trial период для функций
 */
class m250107_000004_create_organization_addon_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%organization_addon}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации (головной)'),
            'feature_id' => $this->integer()->notNull()->comment('ID функции/аддона'),
            'status' => $this->string(20)->notNull()->defaultValue('active')
                ->comment('Статус: trial, active, expired, cancelled'),
            'quantity' => $this->integer()->defaultValue(1)->comment('Количество (для пакетов лимитов)'),
            'value' => $this->json()->comment('Кастомные настройки аддона'),
            'billing_period' => $this->string(20)->defaultValue('monthly')
                ->comment('Период: monthly, yearly'),
            'price' => $this->decimal(10, 2)->comment('Цена за период'),
            'started_at' => $this->dateTime()->comment('Дата активации'),
            'expires_at' => $this->dateTime()->comment('Дата истечения'),
            'trial_ends_at' => $this->dateTime()->comment('Дата окончания trial'),
            'cancelled_at' => $this->dateTime()->comment('Дата отмены'),
            'created_by' => $this->integer()->comment('Кто добавил'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // Индексы
        $this->createIndex('idx_org_addon_org', '{{%organization_addon}}', 'organization_id');
        $this->createIndex('idx_org_addon_feature', '{{%organization_addon}}', 'feature_id');
        $this->createIndex('idx_org_addon_status', '{{%organization_addon}}', 'status');
        $this->createIndex('idx_org_addon_expires', '{{%organization_addon}}', 'expires_at');

        // Уникальный индекс: одна организация - один активный аддон определённого типа
        $this->createIndex(
            'idx_org_addon_unique_active',
            '{{%organization_addon}}',
            ['organization_id', 'feature_id', 'status']
        );

        // Внешние ключи
        $this->addForeignKey(
            'fk_org_addon_organization',
            '{{%organization_addon}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_org_addon_feature',
            '{{%organization_addon}}',
            'feature_id',
            '{{%saas_feature}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        echo "    > Создана таблица organization_addon\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_org_addon_feature', '{{%organization_addon}}');
        $this->dropForeignKey('fk_org_addon_organization', '{{%organization_addon}}');
        $this->dropTable('{{%organization_addon}}');
    }
}
