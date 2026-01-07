<?php

use yii\db\Migration;

/**
 * Добавление полей менеджера и скидок в таблицу organization_payment.
 *
 * Поля менеджера:
 * - manager_id: ID менеджера продаж
 * - manager_bonus_percent: Процент бонуса менеджера
 * - manager_bonus_amount: Сумма бонуса
 * - manager_bonus_status: Статус выплаты бонуса
 * - manager_bonus_paid_at: Дата выплаты бонуса
 *
 * Поля скидок:
 * - original_amount: Сумма до скидки
 * - discount_amount: Сумма скидки
 * - discount_type: Тип скидки (promo, volume, individual, yearly)
 * - discount_details: Детали скидки (JSON)
 */
class m250107_100001_add_manager_and_discount_fields_to_payment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Поля менеджера
        $this->addColumn('{{%organization_payment}}', 'manager_id', $this->integer()->comment('ID менеджера продаж'));
        $this->addColumn('{{%organization_payment}}', 'manager_bonus_percent', $this->decimal(5, 2)->defaultValue(0)->comment('Процент бонуса менеджера'));
        $this->addColumn('{{%organization_payment}}', 'manager_bonus_amount', $this->decimal(10, 2)->defaultValue(0)->comment('Сумма бонуса'));
        $this->addColumn('{{%organization_payment}}', 'manager_bonus_status', $this->string(20)->defaultValue('pending')->comment('Статус: pending, paid, cancelled'));
        $this->addColumn('{{%organization_payment}}', 'manager_bonus_paid_at', $this->dateTime()->comment('Дата выплаты бонуса'));

        // Поля скидок
        $this->addColumn('{{%organization_payment}}', 'original_amount', $this->decimal(10, 2)->comment('Сумма до скидки'));
        $this->addColumn('{{%organization_payment}}', 'discount_amount', $this->decimal(10, 2)->defaultValue(0)->comment('Сумма скидки'));
        $this->addColumn('{{%organization_payment}}', 'discount_type', $this->string(50)->comment('Тип скидки: promo, volume, individual, yearly'));
        $this->addColumn('{{%organization_payment}}', 'discount_details', $this->json()->comment('Детали скидки'));

        // Индексы
        $this->createIndex('idx_org_payment_manager', '{{%organization_payment}}', 'manager_id');
        $this->createIndex('idx_org_payment_bonus_status', '{{%organization_payment}}', 'manager_bonus_status');

        // Внешний ключ на менеджера
        $this->addForeignKey(
            'fk_org_payment_manager',
            '{{%organization_payment}}',
            'manager_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        echo "    > Добавлены поля менеджера и скидок в organization_payment\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_org_payment_manager', '{{%organization_payment}}');
        $this->dropIndex('idx_org_payment_bonus_status', '{{%organization_payment}}');
        $this->dropIndex('idx_org_payment_manager', '{{%organization_payment}}');

        $this->dropColumn('{{%organization_payment}}', 'discount_details');
        $this->dropColumn('{{%organization_payment}}', 'discount_type');
        $this->dropColumn('{{%organization_payment}}', 'discount_amount');
        $this->dropColumn('{{%organization_payment}}', 'original_amount');

        $this->dropColumn('{{%organization_payment}}', 'manager_bonus_paid_at');
        $this->dropColumn('{{%organization_payment}}', 'manager_bonus_status');
        $this->dropColumn('{{%organization_payment}}', 'manager_bonus_amount');
        $this->dropColumn('{{%organization_payment}}', 'manager_bonus_percent');
        $this->dropColumn('{{%organization_payment}}', 'manager_id');
    }
}
