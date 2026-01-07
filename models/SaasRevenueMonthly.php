<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Модель для хранения агрегированных данных о выручке за месяц
 *
 * @property int $id
 * @property string $year_month Месяц в формате YYYY-MM
 *
 * Выручка
 * @property float $total_revenue Общая выручка
 * @property float $subscription_revenue От подписок
 * @property float $addon_revenue От аддонов
 *
 * Скидки
 * @property float $total_discounts Общая сумма скидок
 * @property float $promo_discounts Скидки по промокодам
 * @property float $volume_discounts Накопительные скидки
 * @property float $yearly_discounts Скидки за годовую оплату
 * @property float $individual_discounts Индивидуальные скидки
 *
 * Операции
 * @property int $new_subscriptions Новые подписки
 * @property int $renewals Продления
 * @property int $upgrades Апгрейды
 * @property int $downgrades Даунгрейды
 * @property int $cancellations Отмены
 * @property float $churned_mrr Потерянный MRR
 *
 * MRR метрики
 * @property float $mrr_start MRR на начало месяца
 * @property float $mrr_end MRR на конец месяца
 * @property float $mrr_new MRR от новых
 * @property float $mrr_expansion MRR от апгрейдов
 * @property float $mrr_contraction MRR от даунгрейдов
 * @property float $mrr_churn Потерянный MRR
 *
 * Средние значения
 * @property float $avg_subscription_value Средний чек подписки
 * @property float $avg_addon_value Средний чек аддона
 *
 * Организации
 * @property int $active_organizations Активные организации
 * @property int $trial_organizations На триале
 * @property int $paying_organizations Платящие
 *
 * JSON данные
 * @property array|null $revenue_by_plan
 * @property array|null $organizations_by_plan
 * @property array|null $revenue_by_manager
 * @property array|null $bonuses_by_manager
 *
 * Конверсии
 * @property int $trial_to_paid_count
 * @property float $trial_to_paid_rate
 *
 * @property string|null $calculated_at
 */
class SaasRevenueMonthly extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_revenue_monthly}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year_month'], 'required'],
            [['year_month'], 'string', 'max' => 7],
            [['year_month'], 'unique'],
            [['year_month'], 'match', 'pattern' => '/^\d{4}-\d{2}$/'],

            // Числовые поля
            [[
                'total_revenue', 'subscription_revenue', 'addon_revenue',
                'total_discounts', 'promo_discounts', 'volume_discounts', 'yearly_discounts', 'individual_discounts',
                'churned_mrr', 'mrr_start', 'mrr_end', 'mrr_new', 'mrr_expansion', 'mrr_contraction', 'mrr_churn',
                'avg_subscription_value', 'avg_addon_value', 'trial_to_paid_rate'
            ], 'number'],

            [[
                'new_subscriptions', 'renewals', 'upgrades', 'downgrades', 'cancellations',
                'active_organizations', 'trial_organizations', 'paying_organizations', 'trial_to_paid_count'
            ], 'integer'],

            // JSON поля
            [['revenue_by_plan', 'organizations_by_plan', 'revenue_by_manager', 'bonuses_by_manager'], 'safe'],

            [['calculated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'year_month' => 'Месяц',
            'total_revenue' => 'Общая выручка',
            'subscription_revenue' => 'Выручка от подписок',
            'addon_revenue' => 'Выручка от аддонов',
            'total_discounts' => 'Скидки',
            'promo_discounts' => 'Скидки по промокодам',
            'volume_discounts' => 'Накопительные скидки',
            'yearly_discounts' => 'Скидки за год. оплату',
            'individual_discounts' => 'Индивидуальные скидки',
            'new_subscriptions' => 'Новые подписки',
            'renewals' => 'Продления',
            'upgrades' => 'Апгрейды',
            'downgrades' => 'Даунгрейды',
            'cancellations' => 'Отмены',
            'churned_mrr' => 'Потерянный MRR',
            'mrr_start' => 'MRR (начало)',
            'mrr_end' => 'MRR (конец)',
            'mrr_new' => 'Новый MRR',
            'mrr_expansion' => 'MRR от апгрейдов',
            'mrr_contraction' => 'MRR от даунгрейдов',
            'mrr_churn' => 'Отток MRR',
            'avg_subscription_value' => 'Ср. чек подписки',
            'avg_addon_value' => 'Ср. чек аддона',
            'active_organizations' => 'Активные организации',
            'trial_organizations' => 'На триале',
            'paying_organizations' => 'Платящие',
            'trial_to_paid_count' => 'Конверсий',
            'trial_to_paid_rate' => '% конверсии',
            'calculated_at' => 'Рассчитано',
        ];
    }

    /**
     * Получить или создать запись за месяц
     */
    public static function getOrCreate(string $yearMonth): self
    {
        $model = self::findOne(['year_month' => $yearMonth]);
        if (!$model) {
            $model = new self();
            $model->year_month = $yearMonth;
        }
        return $model;
    }

    /**
     * Получить данные за последние N месяцев
     */
    public static function getLastMonths(int $months = 12): array
    {
        return self::find()
            ->orderBy(['year_month' => SORT_DESC])
            ->limit($months)
            ->all();
    }

    /**
     * Получить данные за период
     */
    public static function getByPeriod(string $from, string $to): array
    {
        return self::find()
            ->andWhere(['>=', 'year_month', $from])
            ->andWhere(['<=', 'year_month', $to])
            ->orderBy(['year_month' => SORT_ASC])
            ->all();
    }

    /**
     * Рост MRR в процентах
     */
    public function getMrrGrowthPercent(): ?float
    {
        if ($this->mrr_start <= 0) {
            return null;
        }
        return round((($this->mrr_end - $this->mrr_start) / $this->mrr_start) * 100, 2);
    }

    /**
     * Net Revenue Retention (NRR)
     * (MRR_start + Expansion - Contraction - Churn) / MRR_start * 100
     */
    public function getNRR(): ?float
    {
        if ($this->mrr_start <= 0) {
            return null;
        }
        $nrr = ($this->mrr_start + $this->mrr_expansion - $this->mrr_contraction - $this->mrr_churn)
            / $this->mrr_start * 100;
        return round($nrr, 1);
    }

    /**
     * Quick Ratio = (New MRR + Expansion) / (Churn + Contraction)
     * > 4 = отлично, > 2 = хорошо, < 1 = проблема
     */
    public function getQuickRatio(): ?float
    {
        $loss = $this->mrr_churn + $this->mrr_contraction;
        if ($loss <= 0) {
            return null; // Нет оттока - отлично
        }
        $growth = $this->mrr_new + $this->mrr_expansion;
        return round($growth / $loss, 2);
    }

    /**
     * Churn Rate в процентах
     */
    public function getChurnRate(): ?float
    {
        if ($this->mrr_start <= 0) {
            return null;
        }
        return round(($this->mrr_churn / $this->mrr_start) * 100, 2);
    }

    /**
     * Получить выручку по планам как массив
     */
    public function getRevenueByPlanArray(): array
    {
        if (empty($this->revenue_by_plan)) {
            return [];
        }
        return is_array($this->revenue_by_plan)
            ? $this->revenue_by_plan
            : json_decode($this->revenue_by_plan, true) ?? [];
    }

    /**
     * Получить организации по планам как массив
     */
    public function getOrganizationsByPlanArray(): array
    {
        if (empty($this->organizations_by_plan)) {
            return [];
        }
        return is_array($this->organizations_by_plan)
            ? $this->organizations_by_plan
            : json_decode($this->organizations_by_plan, true) ?? [];
    }

    /**
     * Получить выручку по менеджерам как массив
     */
    public function getRevenueByManagerArray(): array
    {
        if (empty($this->revenue_by_manager)) {
            return [];
        }
        return is_array($this->revenue_by_manager)
            ? $this->revenue_by_manager
            : json_decode($this->revenue_by_manager, true) ?? [];
    }

    /**
     * Форматирование месяца для отображения
     */
    public function getFormattedMonth(): string
    {
        $months = [
            '01' => 'Январь', '02' => 'Февраль', '03' => 'Март',
            '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь',
            '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
            '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь',
        ];

        $parts = explode('-', $this->year_month);
        if (count($parts) !== 2) {
            return $this->year_month;
        }

        return ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    }

    /**
     * Получить предыдущий месяц
     */
    public function getPreviousMonth(): ?self
    {
        $prevMonth = date('Y-m', strtotime($this->year_month . '-01 -1 month'));
        return self::findOne(['year_month' => $prevMonth]);
    }

    /**
     * Сравнение с предыдущим месяцем
     */
    public function getComparisonWithPrevious(): array
    {
        $prev = $this->getPreviousMonth();
        if (!$prev) {
            return [];
        }

        return [
            'revenue_change' => $this->total_revenue - $prev->total_revenue,
            'revenue_change_percent' => $prev->total_revenue > 0
                ? round((($this->total_revenue - $prev->total_revenue) / $prev->total_revenue) * 100, 1)
                : null,
            'mrr_change' => $this->mrr_end - $prev->mrr_end,
            'mrr_change_percent' => $prev->mrr_end > 0
                ? round((($this->mrr_end - $prev->mrr_end) / $prev->mrr_end) * 100, 1)
                : null,
            'subscriptions_change' => $this->new_subscriptions - $prev->new_subscriptions,
        ];
    }
}
