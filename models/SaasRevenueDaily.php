<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Модель для хранения агрегированных данных о выручке за день
 *
 * @property int $id
 * @property string $date Дата
 * @property float $revenue Выручка за день
 * @property float $discounts Скидки за день
 * @property float $net_revenue Чистая выручка
 * @property int $payments_count Количество платежей
 * @property int $new_trials Новые триалы
 * @property int $conversions Конверсии триал->платный
 * @property int $new_subscriptions Новые подписки
 * @property int $renewals Продления
 * @property int $cancellations Отмены
 * @property float $subscription_revenue Выручка от подписок
 * @property float $addon_revenue Выручка от аддонов
 * @property string|null $calculated_at Время расчёта
 */
class SaasRevenueDaily extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_revenue_daily}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['date'], 'unique'],

            [[
                'revenue', 'discounts', 'net_revenue',
                'subscription_revenue', 'addon_revenue'
            ], 'number'],

            [[
                'payments_count', 'new_trials', 'conversions',
                'new_subscriptions', 'renewals', 'cancellations'
            ], 'integer'],

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
            'date' => 'Дата',
            'revenue' => 'Выручка',
            'discounts' => 'Скидки',
            'net_revenue' => 'Чистая выручка',
            'payments_count' => 'Платежей',
            'new_trials' => 'Новых триалов',
            'conversions' => 'Конверсий',
            'new_subscriptions' => 'Новых подписок',
            'renewals' => 'Продлений',
            'cancellations' => 'Отмен',
            'subscription_revenue' => 'От подписок',
            'addon_revenue' => 'От аддонов',
            'calculated_at' => 'Рассчитано',
        ];
    }

    /**
     * Получить или создать запись за день
     */
    public static function getOrCreate(string $date): self
    {
        $model = self::findOne(['date' => $date]);
        if (!$model) {
            $model = new self();
            $model->date = $date;
        }
        return $model;
    }

    /**
     * Получить данные за последние N дней
     */
    public static function getLastDays(int $days = 30): array
    {
        return self::find()
            ->orderBy(['date' => SORT_DESC])
            ->limit($days)
            ->all();
    }

    /**
     * Получить данные за период
     */
    public static function getByPeriod(string $from, string $to): array
    {
        return self::find()
            ->andWhere(['>=', 'date', $from])
            ->andWhere(['<=', 'date', $to])
            ->orderBy(['date' => SORT_ASC])
            ->all();
    }

    /**
     * Получить данные за месяц
     */
    public static function getByMonth(string $yearMonth): array
    {
        return self::find()
            ->andWhere(['LIKE', 'date', $yearMonth . '%', false])
            ->orderBy(['date' => SORT_ASC])
            ->all();
    }

    /**
     * Сумма выручки за период
     */
    public static function getTotalRevenue(string $from, string $to): float
    {
        return (float) self::find()
            ->andWhere(['>=', 'date', $from])
            ->andWhere(['<=', 'date', $to])
            ->sum('revenue');
    }

    /**
     * Среднедневная выручка за период
     */
    public static function getAverageRevenue(string $from, string $to): float
    {
        return (float) self::find()
            ->andWhere(['>=', 'date', $from])
            ->andWhere(['<=', 'date', $to])
            ->average('revenue');
    }

    /**
     * День недели
     */
    public function getDayOfWeek(): string
    {
        $days = [
            'Sunday' => 'Вс',
            'Monday' => 'Пн',
            'Tuesday' => 'Вт',
            'Wednesday' => 'Ср',
            'Thursday' => 'Чт',
            'Friday' => 'Пт',
            'Saturday' => 'Сб',
        ];
        $day = date('l', strtotime($this->date));
        return $days[$day] ?? $day;
    }

    /**
     * Форматированная дата
     */
    public function getFormattedDate(): string
    {
        return date('d.m.Y', strtotime($this->date));
    }

    /**
     * Получить данные для графика за последние N дней
     */
    public static function getChartData(int $days = 30): array
    {
        $data = self::getLastDays($days);
        $data = array_reverse($data);

        $labels = [];
        $revenues = [];
        $payments = [];

        foreach ($data as $item) {
            $labels[] = date('d.m', strtotime($item->date));
            $revenues[] = (float) $item->revenue;
            $payments[] = (int) $item->payments_count;
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'payments' => $payments,
        ];
    }

    /**
     * Сравнение с предыдущим днём
     */
    public function getComparisonWithPrevious(): array
    {
        $prevDate = date('Y-m-d', strtotime($this->date . ' -1 day'));
        $prev = self::findOne(['date' => $prevDate]);

        if (!$prev) {
            return [];
        }

        return [
            'revenue_change' => $this->revenue - $prev->revenue,
            'revenue_change_percent' => $prev->revenue > 0
                ? round((($this->revenue - $prev->revenue) / $prev->revenue) * 100, 1)
                : null,
            'payments_change' => $this->payments_count - $prev->payments_count,
        ];
    }

    /**
     * Сравнение с тем же днём неделю назад
     */
    public function getComparisonWithLastWeek(): array
    {
        $prevDate = date('Y-m-d', strtotime($this->date . ' -7 days'));
        $prev = self::findOne(['date' => $prevDate]);

        if (!$prev) {
            return [];
        }

        return [
            'revenue_change' => $this->revenue - $prev->revenue,
            'revenue_change_percent' => $prev->revenue > 0
                ? round((($this->revenue - $prev->revenue) / $prev->revenue) * 100, 1)
                : null,
        ];
    }
}
