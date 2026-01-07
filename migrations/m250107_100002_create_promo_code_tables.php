<?php

use yii\db\Migration;

/**
 * Создание таблиц для промокодов.
 *
 * Таблицы:
 * - saas_promo_code: справочник промокодов
 * - saas_promo_code_usage: использование промокодов
 */
class m250107_100002_create_promo_code_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица промокодов
        $this->createTable('{{%saas_promo_code}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull()->unique()->comment('Код промокода (WELCOME2025)'),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'description' => $this->text()->comment('Описание'),

            // Тип и значение скидки
            'discount_type' => "ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent' COMMENT 'Тип: процент или фиксированная сумма'",
            'discount_value' => $this->decimal(10, 2)->notNull()->comment('Значение скидки (10% или 5000 KZT)'),

            // Применимость
            'applies_to' => "ENUM('subscription', 'addon', 'all') DEFAULT 'subscription' COMMENT 'К чему применяется'",
            'applicable_plans' => $this->json()->comment('JSON массив кодов планов или null=все'),
            'applicable_addons' => $this->json()->comment('JSON массив кодов аддонов или null=все'),

            // Ограничения
            'min_amount' => $this->decimal(10, 2)->defaultValue(0)->comment('Минимальная сумма заказа'),
            'max_discount' => $this->decimal(10, 2)->comment('Максимальная сумма скидки (для процентных)'),
            'usage_limit' => $this->integer()->comment('Общий лимит использований'),
            'usage_per_org' => $this->integer()->defaultValue(1)->comment('Лимит на организацию'),

            // Период действия
            'valid_from' => $this->dateTime()->comment('Действует с'),
            'valid_until' => $this->dateTime()->comment('Действует до'),

            // Условия
            'first_payment_only' => $this->boolean()->defaultValue(false)->comment('Только первый платёж'),
            'new_customers_only' => $this->boolean()->defaultValue(false)->comment('Только новые клиенты'),

            // Метаданные
            'created_by' => $this->integer()->comment('Кто создал'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Активен'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Индексы для промокодов
        $this->createIndex('idx_promo_code_code', '{{%saas_promo_code}}', 'code');
        $this->createIndex('idx_promo_code_active', '{{%saas_promo_code}}', 'is_active');
        $this->createIndex('idx_promo_code_valid', '{{%saas_promo_code}}', ['valid_from', 'valid_until']);

        // Внешний ключ на создателя
        $this->addForeignKey(
            'fk_promo_code_created_by',
            '{{%saas_promo_code}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Таблица использования промокодов
        $this->createTable('{{%saas_promo_code_usage}}', [
            'id' => $this->primaryKey(),
            'promo_code_id' => $this->integer()->notNull()->comment('ID промокода'),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),
            'payment_id' => $this->integer()->comment('ID платежа'),
            'discount_amount' => $this->decimal(10, 2)->notNull()->comment('Фактическая сумма скидки'),
            'used_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата использования'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Индексы для использования
        $this->createIndex('idx_promo_usage_code', '{{%saas_promo_code_usage}}', 'promo_code_id');
        $this->createIndex('idx_promo_usage_org', '{{%saas_promo_code_usage}}', 'organization_id');
        $this->createIndex('idx_promo_usage_payment', '{{%saas_promo_code_usage}}', 'payment_id');

        // Внешние ключи для использования
        $this->addForeignKey(
            'fk_promo_usage_code',
            '{{%saas_promo_code_usage}}',
            'promo_code_id',
            '{{%saas_promo_code}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_promo_usage_org',
            '{{%saas_promo_code_usage}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_promo_usage_payment',
            '{{%saas_promo_code_usage}}',
            'payment_id',
            '{{%organization_payment}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Добавляем поле promo_code_id в organization_payment если ещё нет
        $tableSchema = $this->db->schema->getTableSchema('{{%organization_payment}}');
        if (!isset($tableSchema->columns['promo_code_id'])) {
            $this->addColumn('{{%organization_payment}}', 'promo_code_id', $this->integer()->comment('ID использованного промокода'));
            $this->createIndex('idx_org_payment_promo', '{{%organization_payment}}', 'promo_code_id');
            $this->addForeignKey(
                'fk_org_payment_promo',
                '{{%organization_payment}}',
                'promo_code_id',
                '{{%saas_promo_code}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        echo "    > Созданы таблицы промокодов\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем поле из organization_payment
        $tableSchema = $this->db->schema->getTableSchema('{{%organization_payment}}');
        if (isset($tableSchema->columns['promo_code_id'])) {
            $this->dropForeignKey('fk_org_payment_promo', '{{%organization_payment}}');
            $this->dropIndex('idx_org_payment_promo', '{{%organization_payment}}');
            $this->dropColumn('{{%organization_payment}}', 'promo_code_id');
        }

        $this->dropForeignKey('fk_promo_usage_payment', '{{%saas_promo_code_usage}}');
        $this->dropForeignKey('fk_promo_usage_org', '{{%saas_promo_code_usage}}');
        $this->dropForeignKey('fk_promo_usage_code', '{{%saas_promo_code_usage}}');
        $this->dropTable('{{%saas_promo_code_usage}}');

        $this->dropForeignKey('fk_promo_code_created_by', '{{%saas_promo_code}}');
        $this->dropTable('{{%saas_promo_code}}');
    }
}
