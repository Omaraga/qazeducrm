<?php

use yii\db\Migration;

/**
 * Создание таблиц для агрегации данных о выручке
 */
class m250107_100004_create_revenue_aggregate_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Ежемесячная агрегация для быстрых отчётов
        $this->createTable('{{%saas_revenue_monthly}}', [
            'id' => $this->primaryKey(),
            'year_month' => $this->string(7)->notNull()->comment('Месяц в формате YYYY-MM'),

            // Выручка
            'total_revenue' => $this->decimal(12, 2)->defaultValue(0)->comment('Общая выручка'),
            'subscription_revenue' => $this->decimal(12, 2)->defaultValue(0)->comment('От подписок'),
            'addon_revenue' => $this->decimal(12, 2)->defaultValue(0)->comment('От аддонов'),

            // Скидки
            'total_discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Общая сумма скидок'),
            'promo_discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Скидки по промокодам'),
            'volume_discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Накопительные скидки'),
            'yearly_discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Скидки за годовую оплату'),
            'individual_discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Индивидуальные скидки'),

            // Количества операций
            'new_subscriptions' => $this->integer()->defaultValue(0)->comment('Новые подписки'),
            'renewals' => $this->integer()->defaultValue(0)->comment('Продления'),
            'upgrades' => $this->integer()->defaultValue(0)->comment('Апгрейды'),
            'downgrades' => $this->integer()->defaultValue(0)->comment('Даунгрейды'),
            'cancellations' => $this->integer()->defaultValue(0)->comment('Отмены'),
            'churned_mrr' => $this->decimal(12, 2)->defaultValue(0)->comment('Потерянный MRR от оттока'),

            // MRR метрики
            'mrr_start' => $this->decimal(12, 2)->defaultValue(0)->comment('MRR на начало месяца'),
            'mrr_end' => $this->decimal(12, 2)->defaultValue(0)->comment('MRR на конец месяца'),
            'mrr_new' => $this->decimal(12, 2)->defaultValue(0)->comment('MRR от новых подписок'),
            'mrr_expansion' => $this->decimal(12, 2)->defaultValue(0)->comment('MRR от апгрейдов'),
            'mrr_contraction' => $this->decimal(12, 2)->defaultValue(0)->comment('MRR от даунгрейдов'),
            'mrr_churn' => $this->decimal(12, 2)->defaultValue(0)->comment('Потерянный MRR'),

            // Средние чеки
            'avg_subscription_value' => $this->decimal(10, 2)->defaultValue(0)->comment('Средний чек подписки'),
            'avg_addon_value' => $this->decimal(10, 2)->defaultValue(0)->comment('Средний чек аддона'),

            // Организации
            'active_organizations' => $this->integer()->defaultValue(0)->comment('Активные организации'),
            'trial_organizations' => $this->integer()->defaultValue(0)->comment('Организации на триале'),
            'paying_organizations' => $this->integer()->defaultValue(0)->comment('Платящие организации'),

            // По планам (JSON)
            'revenue_by_plan' => $this->json()->comment('Выручка по тарифам'),
            'organizations_by_plan' => $this->json()->comment('Организации по тарифам'),

            // По менеджерам
            'revenue_by_manager' => $this->json()->comment('Выручка по менеджерам'),
            'bonuses_by_manager' => $this->json()->comment('Бонусы по менеджерам'),

            // Конверсии
            'trial_to_paid_count' => $this->integer()->defaultValue(0)->comment('Конверсий триал->платный'),
            'trial_to_paid_rate' => $this->decimal(5, 2)->defaultValue(0)->comment('Процент конверсии'),

            'calculated_at' => $this->timestamp()->comment('Время расчёта'),
        ]);

        $this->createIndex('idx_revenue_monthly_year_month', '{{%saas_revenue_monthly}}', 'year_month', true);

        // Выручка по дням (для детализации)
        $this->createTable('{{%saas_revenue_daily}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull()->comment('Дата'),

            // Выручка
            'revenue' => $this->decimal(12, 2)->defaultValue(0)->comment('Выручка за день'),
            'discounts' => $this->decimal(12, 2)->defaultValue(0)->comment('Скидки за день'),
            'net_revenue' => $this->decimal(12, 2)->defaultValue(0)->comment('Чистая выручка'),

            // Количества
            'payments_count' => $this->integer()->defaultValue(0)->comment('Количество платежей'),
            'new_trials' => $this->integer()->defaultValue(0)->comment('Новые триалы'),
            'conversions' => $this->integer()->defaultValue(0)->comment('Конверсии триал->платный'),
            'new_subscriptions' => $this->integer()->defaultValue(0)->comment('Новые подписки'),
            'renewals' => $this->integer()->defaultValue(0)->comment('Продления'),
            'cancellations' => $this->integer()->defaultValue(0)->comment('Отмены'),

            // По типам платежей
            'subscription_revenue' => $this->decimal(12, 2)->defaultValue(0),
            'addon_revenue' => $this->decimal(12, 2)->defaultValue(0),

            'calculated_at' => $this->timestamp()->comment('Время расчёта'),
        ]);

        $this->createIndex('idx_revenue_daily_date', '{{%saas_revenue_daily}}', 'date', true);

        // Добавляем поля discount в organization_payment если их нет
        $tableSchema = $this->db->getTableSchema('{{%organization_payment}}');

        if (!isset($tableSchema->columns['original_amount'])) {
            $this->addColumn('{{%organization_payment}}', 'original_amount',
                $this->decimal(10, 2)->after('amount')->comment('Сумма до скидки'));
        }

        if (!isset($tableSchema->columns['discount_amount'])) {
            $this->addColumn('{{%organization_payment}}', 'discount_amount',
                $this->decimal(10, 2)->defaultValue(0)->after('original_amount')->comment('Сумма скидки'));
        }

        if (!isset($tableSchema->columns['discount_type'])) {
            $this->addColumn('{{%organization_payment}}', 'discount_type',
                $this->string(50)->after('discount_amount')->comment('Тип скидки: promo, volume, individual, yearly'));
        }

        if (!isset($tableSchema->columns['discount_details'])) {
            $this->addColumn('{{%organization_payment}}', 'discount_details',
                $this->json()->after('discount_type')->comment('Детали скидок'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем добавленные колонки
        $tableSchema = $this->db->getTableSchema('{{%organization_payment}}');

        if (isset($tableSchema->columns['discount_details'])) {
            $this->dropColumn('{{%organization_payment}}', 'discount_details');
        }
        if (isset($tableSchema->columns['discount_type'])) {
            $this->dropColumn('{{%organization_payment}}', 'discount_type');
        }
        if (isset($tableSchema->columns['discount_amount'])) {
            $this->dropColumn('{{%organization_payment}}', 'discount_amount');
        }
        if (isset($tableSchema->columns['original_amount'])) {
            $this->dropColumn('{{%organization_payment}}', 'original_amount');
        }

        $this->dropTable('{{%saas_revenue_daily}}');
        $this->dropTable('{{%saas_revenue_monthly}}');
    }
}
