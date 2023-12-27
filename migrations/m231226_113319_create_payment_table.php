<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment}}`.
 */
class m231226_113319_create_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11),
            'pupil_id' => $this->integer(11)->notNull(),
            'purpose_id' => $this->integer(2),
            'method_id' => $this->integer(11),
            'type' => $this->integer(2)->notNull(),
            'number' => $this->string(255),
            'amount' => $this->double()->defaultValue(0),
            'date' => $this->dateTime()->notNull(),
            'is_deleted' => $this->integer(1)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'comment' => $this->text(),
        ]);
        $this->addForeignKey('fk-payment-pupil_id', 'payment', 'pupil_id', 'pupil', 'id', 'CASCADE');
        $this->addForeignKey('fk-payment-method_id', 'payment', 'method_id', 'pay_method', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-payment-pupil_id', 'payment');
        $this->dropForeignKey('fk-payment-method_id', 'payment');
        $this->dropTable('{{%payment}}');
    }
}
