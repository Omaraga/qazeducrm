<?php

use yii\db\Migration;

/**
 * Создание таблицы для запросов на изменение подписки от организаций.
 *
 * Запросы создаются когда организация хочет:
 * - Продлить подписку (renewal)
 * - Повысить тариф (upgrade)
 * - Понизить тариф (downgrade)
 * - Конвертировать trial в платную (trial_convert)
 * - Докупить аддон (addon)
 */
class m260107_200001_create_organization_subscription_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organization_subscription_request}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull()->comment('ID организации'),
            'request_type' => $this->string(20)->notNull()->comment('Тип запроса: renewal, upgrade, downgrade, trial_convert, addon'),
            'current_plan_id' => $this->integer()->comment('Текущий план'),
            'requested_plan_id' => $this->integer()->comment('Запрашиваемый план'),
            'billing_period' => $this->string(10)->comment('Период оплаты: monthly, yearly'),
            'addon_id' => $this->integer()->comment('ID аддона (для типа addon)'),
            'comment' => $this->text()->comment('Комментарий от организации'),
            'contact_phone' => $this->string(20)->comment('Контактный телефон'),
            'contact_name' => $this->string(100)->comment('Контактное имя'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('Статус: pending, approved, rejected, completed'),
            'admin_comment' => $this->text()->comment('Комментарий администратора'),
            'processed_by' => $this->integer()->comment('Кем обработан'),
            'processed_at' => $this->dateTime()->comment('Когда обработан'),
            'created_by' => $this->integer()->comment('Кем создан'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Индексы
        $this->createIndex(
            'idx-org_subscription_request-organization_id',
            '{{%organization_subscription_request}}',
            'organization_id'
        );

        $this->createIndex(
            'idx-org_subscription_request-status',
            '{{%organization_subscription_request}}',
            'status'
        );

        $this->createIndex(
            'idx-org_subscription_request-request_type',
            '{{%organization_subscription_request}}',
            'request_type'
        );

        $this->createIndex(
            'idx-org_subscription_request-created_at',
            '{{%organization_subscription_request}}',
            'created_at'
        );

        // Внешние ключи
        $this->addForeignKey(
            'fk-org_subscription_request-organization_id',
            '{{%organization_subscription_request}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_subscription_request-current_plan_id',
            '{{%organization_subscription_request}}',
            'current_plan_id',
            '{{%saas_plan}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_subscription_request-requested_plan_id',
            '{{%organization_subscription_request}}',
            'requested_plan_id',
            '{{%saas_plan}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_subscription_request-processed_by',
            '{{%organization_subscription_request}}',
            'processed_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-org_subscription_request-created_by',
            '{{%organization_subscription_request}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-org_subscription_request-created_by', '{{%organization_subscription_request}}');
        $this->dropForeignKey('fk-org_subscription_request-processed_by', '{{%organization_subscription_request}}');
        $this->dropForeignKey('fk-org_subscription_request-requested_plan_id', '{{%organization_subscription_request}}');
        $this->dropForeignKey('fk-org_subscription_request-current_plan_id', '{{%organization_subscription_request}}');
        $this->dropForeignKey('fk-org_subscription_request-organization_id', '{{%organization_subscription_request}}');

        $this->dropTable('{{%organization_subscription_request}}');
    }
}
