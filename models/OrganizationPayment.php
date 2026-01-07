<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель платежей организаций за подписки.
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $subscription_id
 * @property float $amount
 * @property float|null $original_amount
 * @property float $discount_amount
 * @property string|null $discount_type
 * @property array|null $discount_details
 * @property string $currency
 * @property string|null $period_start
 * @property string|null $period_end
 * @property string $status
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property string|null $invoice_number
 * @property string|null $invoice_file
 * @property string|null $receipt_file
 * @property string|null $notes
 * @property int|null $manager_id
 * @property float $manager_bonus_percent
 * @property float $manager_bonus_amount
 * @property string $manager_bonus_status
 * @property string|null $manager_bonus_paid_at
 * @property int|null $processed_by
 * @property string|null $processed_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property OrganizationSubscription $subscription
 * @property User $processedBy
 * @property User $manager
 */
class OrganizationPayment extends ActiveRecord
{
    // Статусы платежа
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    // Методы оплаты
    const METHOD_KASPI = 'kaspi';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';

    // Статусы бонуса менеджера
    const BONUS_PENDING = 'pending';
    const BONUS_PAID = 'paid';
    const BONUS_CANCELLED = 'cancelled';

    // Типы скидок
    const DISCOUNT_PROMO = 'promo';
    const DISCOUNT_VOLUME = 'volume';
    const DISCOUNT_INDIVIDUAL = 'individual';
    const DISCOUNT_YEARLY = 'yearly';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_payment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'amount'], 'required'],
            [['organization_id', 'subscription_id', 'processed_by', 'manager_id'], 'integer'],
            [['amount', 'original_amount', 'discount_amount', 'manager_bonus_percent', 'manager_bonus_amount'], 'number'],
            [['period_start', 'period_end', 'processed_at', 'manager_bonus_paid_at'], 'safe'],
            [['notes'], 'string'],
            [['currency'], 'string', 'max' => 3],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_REFUNDED]],
            [['payment_method'], 'string', 'max' => 50],
            [['payment_reference', 'invoice_file', 'receipt_file'], 'string', 'max' => 255],
            [['invoice_number'], 'string', 'max' => 50],
            [['discount_type'], 'string', 'max' => 50],
            [['discount_type'], 'in', 'range' => [self::DISCOUNT_PROMO, self::DISCOUNT_VOLUME, self::DISCOUNT_INDIVIDUAL, self::DISCOUNT_YEARLY]],
            [['manager_bonus_status'], 'string', 'max' => 20],
            [['manager_bonus_status'], 'in', 'range' => [self::BONUS_PENDING, self::BONUS_PAID, self::BONUS_CANCELLED]],
            [['discount_details'], 'safe'],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            [['subscription_id'], 'exist', 'targetClass' => OrganizationSubscription::class, 'targetAttribute' => 'id'],
            [['manager_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => Yii::t('main', 'Организация'),
            'subscription_id' => Yii::t('main', 'Подписка'),
            'amount' => Yii::t('main', 'Сумма'),
            'original_amount' => Yii::t('main', 'Сумма до скидки'),
            'discount_amount' => Yii::t('main', 'Скидка'),
            'discount_type' => Yii::t('main', 'Тип скидки'),
            'discount_details' => Yii::t('main', 'Детали скидки'),
            'currency' => Yii::t('main', 'Валюта'),
            'period_start' => Yii::t('main', 'Начало периода'),
            'period_end' => Yii::t('main', 'Конец периода'),
            'status' => Yii::t('main', 'Статус'),
            'payment_method' => Yii::t('main', 'Метод оплаты'),
            'payment_reference' => Yii::t('main', 'Номер транзакции'),
            'invoice_number' => Yii::t('main', 'Номер счёта'),
            'invoice_file' => Yii::t('main', 'Файл счёта'),
            'receipt_file' => Yii::t('main', 'Файл чека'),
            'notes' => Yii::t('main', 'Заметки'),
            'manager_id' => Yii::t('main', 'Менеджер'),
            'manager_bonus_percent' => Yii::t('main', '% бонуса'),
            'manager_bonus_amount' => Yii::t('main', 'Сумма бонуса'),
            'manager_bonus_status' => Yii::t('main', 'Статус бонуса'),
            'manager_bonus_paid_at' => Yii::t('main', 'Дата выплаты бонуса'),
            'processed_by' => Yii::t('main', 'Обработал'),
            'processed_at' => Yii::t('main', 'Дата обработки'),
        ];
    }

    /**
     * Связь с организацией
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Связь с подпиской
     */
    public function getSubscription()
    {
        return $this->hasOne(OrganizationSubscription::class, ['id' => 'subscription_id']);
    }

    /**
     * Связь с пользователем, обработавшим платёж
     */
    public function getProcessedBy()
    {
        return $this->hasOne(User::class, ['id' => 'processed_by']);
    }

    /**
     * Связь с менеджером продаж
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => Yii::t('main', 'Ожидает'),
            self::STATUS_COMPLETED => Yii::t('main', 'Завершён'),
            self::STATUS_FAILED => Yii::t('main', 'Ошибка'),
            self::STATUS_REFUNDED => Yii::t('main', 'Возврат'),
        ];
    }

    /**
     * Название статуса
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    /**
     * Список методов оплаты
     */
    public static function getPaymentMethodList(): array
    {
        return [
            self::METHOD_KASPI => 'Kaspi',
            self::METHOD_BANK_TRANSFER => Yii::t('main', 'Банковский перевод'),
            self::METHOD_CASH => Yii::t('main', 'Наличные'),
            self::METHOD_CARD => Yii::t('main', 'Карта'),
        ];
    }

    /**
     * Название метода оплаты
     */
    public function getPaymentMethodLabel(): string
    {
        return self::getPaymentMethodList()[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Ожидает подтверждения
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Завершён
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Подтвердить платёж
     */
    public function confirm(int $processedBy): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->processed_by = $processedBy;
        $this->processed_at = date('Y-m-d H:i:s');

        // Рассчитать бонус менеджера
        if ($this->manager_id && $this->manager_bonus_percent > 0) {
            $this->manager_bonus_amount = $this->calculateManagerBonus();
            $this->manager_bonus_status = self::BONUS_PENDING;
        }

        if ($this->save()) {
            // Активируем подписку если есть
            if ($this->subscription_id) {
                $subscription = $this->subscription;
                if ($subscription) {
                    $subscription->activate($subscription->billing_period);
                }
            }

            // Логируем событие
            OrganizationActivityLog::log(
                $this->organization_id,
                OrganizationActivityLog::ACTION_PAYMENT_RECEIVED,
                OrganizationActivityLog::CATEGORY_PAYMENT,
                sprintf('Платёж подтверждён: %s', $this->getFormattedAmount())
            );

            return true;
        }

        return false;
    }

    /**
     * Отменить/вернуть платёж
     */
    public function refund(int $processedBy): bool
    {
        $this->status = self::STATUS_REFUNDED;
        $this->processed_by = $processedBy;
        $this->processed_at = date('Y-m-d H:i:s');

        return $this->save();
    }

    /**
     * Форматированная сумма
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 0, '.', ' ') . ' ' . $this->currency;
    }

    /**
     * Сгенерировать номер счёта
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $sequence = static::find()
            ->andWhere(['LIKE', 'invoice_number', "{$prefix}-{$year}-%", false])
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    // ==================== MANAGER BONUS ====================

    /**
     * Рассчитать бонус менеджера
     */
    public function calculateManagerBonus(): float
    {
        if ($this->manager_bonus_percent <= 0) {
            return 0;
        }
        return round($this->amount * ($this->manager_bonus_percent / 100), 2);
    }

    /**
     * Выплатить бонус менеджеру
     */
    public function payBonus(): bool
    {
        if ($this->manager_bonus_status !== self::BONUS_PENDING) {
            return false;
        }

        $this->manager_bonus_status = self::BONUS_PAID;
        $this->manager_bonus_paid_at = date('Y-m-d H:i:s');

        return $this->save(false, ['manager_bonus_status', 'manager_bonus_paid_at']);
    }

    /**
     * Отменить бонус менеджера
     */
    public function cancelBonus(): bool
    {
        $this->manager_bonus_status = self::BONUS_CANCELLED;
        return $this->save(false, ['manager_bonus_status']);
    }

    /**
     * Список статусов бонуса
     */
    public static function getBonusStatusList(): array
    {
        return [
            self::BONUS_PENDING => Yii::t('main', 'Ожидает'),
            self::BONUS_PAID => Yii::t('main', 'Выплачен'),
            self::BONUS_CANCELLED => Yii::t('main', 'Отменён'),
        ];
    }

    /**
     * Название статуса бонуса
     */
    public function getBonusStatusLabel(): string
    {
        return self::getBonusStatusList()[$this->manager_bonus_status] ?? $this->manager_bonus_status;
    }

    /**
     * CSS класс для статуса бонуса
     */
    public function getBonusStatusBadgeClass(): string
    {
        return match ($this->manager_bonus_status) {
            self::BONUS_PENDING => 'badge-warning',
            self::BONUS_PAID => 'badge-success',
            self::BONUS_CANCELLED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Форматированная сумма бонуса
     */
    public function getFormattedBonusAmount(): string
    {
        return number_format($this->manager_bonus_amount, 0, '.', ' ') . ' ' . $this->currency;
    }

    // ==================== DISCOUNTS ====================

    /**
     * Список типов скидок
     */
    public static function getDiscountTypeList(): array
    {
        return [
            self::DISCOUNT_PROMO => Yii::t('main', 'Промокод'),
            self::DISCOUNT_VOLUME => Yii::t('main', 'Накопительная'),
            self::DISCOUNT_INDIVIDUAL => Yii::t('main', 'Индивидуальная'),
            self::DISCOUNT_YEARLY => Yii::t('main', 'Годовая'),
        ];
    }

    /**
     * Название типа скидки
     */
    public function getDiscountTypeLabel(): string
    {
        return self::getDiscountTypeList()[$this->discount_type] ?? $this->discount_type;
    }

    /**
     * Получить детали скидки как массив
     */
    public function getDiscountDetailsArray(): array
    {
        if (empty($this->discount_details)) {
            return [];
        }
        return is_array($this->discount_details)
            ? $this->discount_details
            : json_decode($this->discount_details, true) ?? [];
    }

    /**
     * Есть ли скидка
     */
    public function hasDiscount(): bool
    {
        return $this->discount_amount > 0;
    }

    /**
     * Форматированная скидка
     */
    public function getFormattedDiscount(): string
    {
        if (!$this->hasDiscount()) {
            return '-';
        }
        return '-' . number_format($this->discount_amount, 0, '.', ' ') . ' ' . $this->currency;
    }

    /**
     * Процент скидки
     */
    public function getDiscountPercent(): float
    {
        if (!$this->original_amount || $this->original_amount <= 0) {
            return 0;
        }
        return round(($this->discount_amount / $this->original_amount) * 100, 1);
    }

    /**
     * Применить скидку
     */
    public function applyDiscount(float $discountAmount, string $type, ?array $details = null): void
    {
        $this->original_amount = $this->original_amount ?: $this->amount;
        $this->discount_amount = $discountAmount;
        $this->discount_type = $type;
        $this->discount_details = $details;
        $this->amount = max(0, $this->original_amount - $discountAmount);
    }

    // ==================== STATIC QUERIES ====================

    /**
     * Найти платежи менеджера
     */
    public static function findByManager(int $managerId): \yii\db\ActiveQuery
    {
        return static::find()->where(['manager_id' => $managerId]);
    }

    /**
     * Найти платежи с ожидающими бонусами
     */
    public static function findPendingBonuses(): \yii\db\ActiveQuery
    {
        return static::find()
            ->where(['status' => self::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => self::BONUS_PENDING])
            ->andWhere(['>', 'manager_bonus_amount', 0]);
    }

    /**
     * Получить общую сумму продаж за период
     */
    public static function getTotalSales(string $dateFrom, string $dateTo): float
    {
        return static::find()
            ->where(['status' => self::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('amount') ?? 0;
    }

    /**
     * Получить общую сумму скидок за период
     */
    public static function getTotalDiscounts(string $dateFrom, string $dateTo): float
    {
        return static::find()
            ->where(['status' => self::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('discount_amount') ?? 0;
    }
}
