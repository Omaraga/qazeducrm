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
 * @property int|null $processed_by
 * @property string|null $processed_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property OrganizationSubscription $subscription
 * @property User $processedBy
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
            [['organization_id', 'subscription_id', 'processed_by'], 'integer'],
            [['amount'], 'number'],
            [['period_start', 'period_end', 'processed_at'], 'safe'],
            [['notes'], 'string'],
            [['currency'], 'string', 'max' => 3],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_REFUNDED]],
            [['payment_method'], 'string', 'max' => 50],
            [['payment_reference', 'invoice_file', 'receipt_file'], 'string', 'max' => 255],
            [['invoice_number'], 'string', 'max' => 50],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
            [['subscription_id'], 'exist', 'targetClass' => OrganizationSubscription::class, 'targetAttribute' => 'id'],
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

        if ($this->save()) {
            // Активируем подписку если есть
            if ($this->subscription_id) {
                $subscription = $this->subscription;
                if ($subscription) {
                    $subscription->activate($subscription->billing_period);
                }
            }
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
}
