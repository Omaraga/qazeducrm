<?php

use yii\db\Migration;

/**
 * Миграция для добавления полей системы филиалов с отдельными подписками
 *
 * Добавляет:
 * - billing_mode в organization (pooled/isolated)
 * - parent_subscription_id в organization_subscription
 * - поля скидок в organization_payment
 */
class m260108_100000_add_branch_subscription_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Добавить режим биллинга в организацию
        $this->addColumn('{{%organization}}', 'billing_mode',
            $this->string(20)->defaultValue('pooled')
                ->comment('pooled=общая подписка, isolated=отдельные подписки')
                ->after('type')
        );

        // 2. Добавить связь с родительской подпиской
        $this->addColumn('{{%organization_subscription}}', 'parent_subscription_id',
            $this->integer()->null()
                ->comment('ID родительской подписки (для филиалов)')
                ->after('organization_id')
        );

        // 3. Добавить индекс для быстрого поиска дочерних подписок
        $this->createIndex(
            'idx-organization_subscription-parent_subscription_id',
            '{{%organization_subscription}}',
            'parent_subscription_id'
        );

        // 4. Добавить внешний ключ
        $this->addForeignKey(
            'fk-organization_subscription-parent_subscription_id',
            '{{%organization_subscription}}',
            'parent_subscription_id',
            '{{%organization_subscription}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // 5. Проверяем существование таблицы organization_payment
        $tableSchema = $this->db->schema->getTableSchema('{{%organization_payment}}');
        if ($tableSchema !== null) {
            // Добавить поля скидок в платежи (если их нет)
            if (!isset($tableSchema->columns['discount_percent'])) {
                $this->addColumn('{{%organization_payment}}', 'discount_percent',
                    $this->decimal(5, 2)->defaultValue(0)
                        ->comment('Процент скидки')
                );
            }

            if (!isset($tableSchema->columns['discount_amount'])) {
                $this->addColumn('{{%organization_payment}}', 'discount_amount',
                    $this->decimal(10, 2)->defaultValue(0)
                        ->comment('Сумма скидки')
                );
            }

            if (!isset($tableSchema->columns['discount_reason'])) {
                $this->addColumn('{{%organization_payment}}', 'discount_reason',
                    $this->string(255)->null()
                        ->comment('Причина скидки')
                );
            }
        }

        // 6. Установить billing_mode = 'pooled' для всех существующих организаций
        $this->update('{{%organization}}', ['billing_mode' => 'pooled']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем внешний ключ
        $this->dropForeignKey(
            'fk-organization_subscription-parent_subscription_id',
            '{{%organization_subscription}}'
        );

        // Удаляем индекс
        $this->dropIndex(
            'idx-organization_subscription-parent_subscription_id',
            '{{%organization_subscription}}'
        );

        // Удаляем колонки
        $this->dropColumn('{{%organization_subscription}}', 'parent_subscription_id');
        $this->dropColumn('{{%organization}}', 'billing_mode');

        // Удаляем колонки скидок из платежей
        $tableSchema = $this->db->schema->getTableSchema('{{%organization_payment}}');
        if ($tableSchema !== null) {
            if (isset($tableSchema->columns['discount_percent'])) {
                $this->dropColumn('{{%organization_payment}}', 'discount_percent');
            }
            if (isset($tableSchema->columns['discount_amount'])) {
                $this->dropColumn('{{%organization_payment}}', 'discount_amount');
            }
            if (isset($tableSchema->columns['discount_reason'])) {
                $this->dropColumn('{{%organization_payment}}', 'discount_reason');
            }
        }
    }
}
