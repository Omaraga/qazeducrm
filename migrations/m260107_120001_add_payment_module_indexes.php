<?php

use yii\db\Migration;

/**
 * Добавление индексов для оптимизации модуля платежей
 */
class m260107_120001_add_payment_module_indexes extends Migration
{
    /**
     * Безопасное создание индекса (игнорирует ошибку если индекс существует)
     */
    private function safeCreateIndex($name, $table, $columns, $unique = false)
    {
        try {
            $this->createIndex($name, $table, $columns, $unique);
            echo "    > created index {$name} on {$table}\n";
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate key name') !== false) {
                echo "    > index {$name} already exists, skipping\n";
            } elseif (strpos($msg, 'Duplicate entry') !== false && $unique) {
                echo "    > WARNING: duplicate data found, creating non-unique index instead\n";
                try {
                    $this->createIndex($name, $table, $columns, false);
                    echo "    > created non-unique index {$name} on {$table}\n";
                } catch (\Exception $e2) {
                    echo "    > failed to create index: " . $e2->getMessage() . "\n";
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * Безопасное удаление индекса
     */
    private function safeDropIndex($name, $table)
    {
        try {
            $this->dropIndex($name, $table);
            echo "    > dropped index {$name} from {$table}\n";
        } catch (\Exception $e) {
            echo "    > index {$name} does not exist, skipping\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // ========== payment ==========
        // Индекс на pupil_id для быстрого поиска платежей ученика
        $this->safeCreateIndex(
            'idx_payment_pupil_id',
            'payment',
            'pupil_id'
        );

        // Индекс на organization_id для мультитенансности
        $this->safeCreateIndex(
            'idx_payment_organization_id',
            'payment',
            'organization_id'
        );

        // Индекс на date для сортировки и фильтрации по дате
        $this->safeCreateIndex(
            'idx_payment_date',
            'payment',
            'date'
        );

        // Индекс на type для фильтрации по типу платежа
        $this->safeCreateIndex(
            'idx_payment_type',
            'payment',
            'type'
        );

        // Индекс на method_id для JOIN с методами оплаты
        $this->safeCreateIndex(
            'idx_payment_method_id',
            'payment',
            'method_id'
        );

        // Индекс на is_deleted для фильтрации удалённых
        $this->safeCreateIndex(
            'idx_payment_is_deleted',
            'payment',
            'is_deleted'
        );

        // Составной индекс для частых запросов по организации и дате
        $this->safeCreateIndex(
            'idx_payment_org_date',
            'payment',
            ['organization_id', 'date']
        );

        // Составной индекс для запросов по ученику и дате
        $this->safeCreateIndex(
            'idx_payment_pupil_date',
            'payment',
            ['pupil_id', 'date']
        );

        // ========== pay_method ==========
        // Индекс на organization_id для мультитенансности
        $this->safeCreateIndex(
            'idx_pay_method_organization_id',
            'pay_method',
            'organization_id'
        );

        // Индекс на is_deleted для фильтрации удалённых
        $this->safeCreateIndex(
            'idx_pay_method_is_deleted',
            'pay_method',
            'is_deleted'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // payment
        $this->safeDropIndex('idx_payment_pupil_id', 'payment');
        $this->safeDropIndex('idx_payment_organization_id', 'payment');
        $this->safeDropIndex('idx_payment_date', 'payment');
        $this->safeDropIndex('idx_payment_type', 'payment');
        $this->safeDropIndex('idx_payment_method_id', 'payment');
        $this->safeDropIndex('idx_payment_is_deleted', 'payment');
        $this->safeDropIndex('idx_payment_org_date', 'payment');
        $this->safeDropIndex('idx_payment_pupil_date', 'payment');

        // pay_method
        $this->safeDropIndex('idx_pay_method_organization_id', 'pay_method');
        $this->safeDropIndex('idx_pay_method_is_deleted', 'pay_method');

        return true;
    }
}
