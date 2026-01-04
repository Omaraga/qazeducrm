<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%organization_payment}}`.
 *
 * Таблица платежей организаций за подписки.
 * Используется для ручного биллинга (без автооплаты).
 */
class m250104_000003_create_organization_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organization_payment}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),
            'subscription_id' => $this->integer()->null()->comment('ID подписки'),

            // Сумма
            'amount' => $this->decimal(10, 2)->notNull()->comment('Сумма платежа'),
            'currency' => $this->string(3)->defaultValue('KZT')->comment('Валюта'),

            // Период оплаты
            'period_start' => $this->date()->null()->comment('Начало оплаченного периода'),
            'period_end' => $this->date()->null()->comment('Конец оплаченного периода'),

            // Статус платежа
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending, completed, failed, refunded'),

            // Метод оплаты
            'payment_method' => $this->string(50)->comment('kaspi, bank_transfer, cash, card'),
            'payment_reference' => $this->string(255)->comment('Номер транзакции/чека'),

            // Документы
            'invoice_number' => $this->string(50)->comment('Номер счёта'),
            'invoice_file' => $this->string(255)->comment('Путь к файлу счёта'),
            'receipt_file' => $this->string(255)->comment('Путь к файлу чека'),

            // Мета
            'notes' => $this->text()->comment('Заметки'),
            'processed_by' => $this->integer()->null()->comment('ID супер-админа, подтвердившего платёж'),
            'processed_at' => $this->dateTime()->null()->comment('Дата подтверждения'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Внешние ключи
        $this->addForeignKey(
            'fk-org_payment-organization_id',
            '{{%organization_payment}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_payment-subscription_id',
            '{{%organization_payment}}',
            'subscription_id',
            '{{%organization_subscription}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Индексы
        $this->createIndex('idx-org_payment-organization_id', '{{%organization_payment}}', 'organization_id');
        $this->createIndex('idx-org_payment-status', '{{%organization_payment}}', 'status');
        $this->createIndex('idx-org_payment-created_at', '{{%organization_payment}}', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-org_payment-organization_id', '{{%organization_payment}}');
        $this->dropForeignKey('fk-org_payment-subscription_id', '{{%organization_payment}}');
        $this->dropTable('{{%organization_payment}}');
    }
}
