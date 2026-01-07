<?php

use yii\db\Migration;

/**
 * Добавляет связь платежа с заявкой на подписку
 */
class m260108_010000_add_request_id_to_payment extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization_payment}}', 'subscription_request_id', $this->integer()->null()->after('subscription_id'));

        $this->addForeignKey(
            'fk-organization_payment-subscription_request',
            '{{%organization_payment}}',
            'subscription_request_id',
            '{{%organization_subscription_request}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->createIndex(
            'idx-organization_payment-subscription_request_id',
            '{{%organization_payment}}',
            'subscription_request_id'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-organization_payment-subscription_request', '{{%organization_payment}}');
        $this->dropIndex('idx-organization_payment-subscription_request_id', '{{%organization_payment}}');
        $this->dropColumn('{{%organization_payment}}', 'subscription_request_id');
    }
}
