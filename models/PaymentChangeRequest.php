<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель запросов на изменение/удаление платежей.
 * Admin создаёт запрос → Director одобряет/отклоняет.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $payment_id
 * @property string $request_type delete|update
 * @property string $status pending|approved|rejected
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string $reason
 * @property int $requested_by
 * @property int|null $processed_by
 * @property string|null $processed_at
 * @property string|null $admin_comment
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Payment $payment
 * @property User $requestedByUser
 * @property User $processedByUser
 * @property Organizations $organization
 */
class PaymentChangeRequest extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Типы запросов
    const TYPE_DELETE = 'delete';
    const TYPE_UPDATE = 'update';

    // Статусы запросов
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_change_request';
    }

    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'payment_id', 'request_type', 'reason', 'requested_by'], 'required'],
            [['organization_id', 'payment_id', 'requested_by', 'processed_by', 'is_deleted'], 'integer'],
            [['old_values', 'new_values'], 'safe'],
            [['reason', 'admin_comment'], 'string'],
            [['processed_at', 'created_at', 'updated_at'], 'safe'],
            [['request_type'], 'string', 'max' => 20],
            [['request_type'], 'in', 'range' => [self::TYPE_DELETE, self::TYPE_UPDATE]],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payment::class, 'targetAttribute' => ['payment_id' => 'id']],
            [['requested_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['requested_by' => 'id']],
            [['processed_by'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => User::class, 'targetAttribute' => ['processed_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'payment_id' => 'Платёж',
            'request_type' => 'Тип запроса',
            'status' => 'Статус',
            'old_values' => 'Старые значения',
            'new_values' => 'Новые значения',
            'reason' => 'Причина',
            'requested_by' => 'Запросил',
            'processed_by' => 'Обработал',
            'processed_at' => 'Дата обработки',
            'admin_comment' => 'Комментарий',
            'is_deleted' => 'Удалён',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::class, ['id' => 'payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequestedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'requested_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProcessedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'processed_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Список типов запросов
     * @return array
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_DELETE => 'Удаление',
            self::TYPE_UPDATE => 'Изменение',
        ];
    }

    /**
     * Список статусов
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_APPROVED => 'Одобрен',
            self::STATUS_REJECTED => 'Отклонён',
        ];
    }

    /**
     * Получить метку типа запроса
     * @return string
     */
    public function getTypeLabel(): string
    {
        return self::getTypeList()[$this->request_type] ?? $this->request_type;
    }

    /**
     * Получить метку статуса
     * @return string
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    /**
     * Получить CSS-класс для бейджа статуса
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Проверка - в ожидании
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Проверка - одобрен
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Проверка - отклонён
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Одобрить запрос
     * Применяет изменения к платежу
     *
     * @param string|null $comment Комментарий
     * @return bool
     */
    public function approve(?string $comment = null): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->status = self::STATUS_APPROVED;
            $this->processed_by = Yii::$app->user->id;
            $this->processed_at = date('Y-m-d H:i:s');
            $this->admin_comment = $comment;

            if (!$this->save(false)) {
                throw new \Exception('Не удалось сохранить запрос');
            }

            $payment = $this->payment;

            if ($this->request_type === self::TYPE_DELETE) {
                // Удаление платежа
                if (!$payment->delete()) {
                    throw new \Exception('Не удалось удалить платёж');
                }
            } elseif ($this->request_type === self::TYPE_UPDATE) {
                // Обновление платежа
                $newValues = $this->new_values;
                if (is_string($newValues)) {
                    $newValues = json_decode($newValues, true);
                }

                if (!empty($newValues) && is_array($newValues)) {
                    $payment->setAttributes($newValues, false);
                    if (!$payment->save(false)) {
                        throw new \Exception('Не удалось обновить платёж');
                    }
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка одобрения запроса: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Отклонить запрос
     *
     * @param string $comment Комментарий (обязательный)
     * @return bool
     */
    public function reject(string $comment): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->processed_by = Yii::$app->user->id;
        $this->processed_at = date('Y-m-d H:i:s');
        $this->admin_comment = $comment;

        return $this->save(false);
    }

    /**
     * Создать запрос на удаление платежа
     *
     * @param Payment $payment
     * @param string $reason
     * @return PaymentChangeRequest|null
     */
    public static function createDeleteRequest(Payment $payment, string $reason): ?PaymentChangeRequest
    {
        $request = new self();
        $request->organization_id = $payment->organization_id;
        $request->payment_id = $payment->id;
        $request->request_type = self::TYPE_DELETE;
        $request->reason = $reason;
        $request->requested_by = Yii::$app->user->id;
        $request->old_values = json_encode([
            'pupil_id' => $payment->pupil_id,
            'pupil_name' => $payment->pupil->fio ?? null,
            'amount' => $payment->amount,
            'date' => $payment->date,
            'type' => $payment->type,
            'method_id' => $payment->method_id,
            'method_name' => $payment->method->name ?? null,
            'purpose_id' => $payment->purpose_id,
            'comment' => $payment->comment,
        ]);

        if ($request->save()) {
            return $request;
        }

        return null;
    }

    /**
     * Создать запрос на изменение платежа
     *
     * @param Payment $payment
     * @param array $newValues Новые значения
     * @param string $reason
     * @return PaymentChangeRequest|null
     */
    public static function createUpdateRequest(Payment $payment, array $newValues, string $reason): ?PaymentChangeRequest
    {
        $request = new self();
        $request->organization_id = $payment->organization_id;
        $request->payment_id = $payment->id;
        $request->request_type = self::TYPE_UPDATE;
        $request->reason = $reason;
        $request->requested_by = Yii::$app->user->id;
        $request->old_values = json_encode([
            'pupil_id' => $payment->pupil_id,
            'pupil_name' => $payment->pupil->fio ?? null,
            'amount' => $payment->amount,
            'date' => $payment->date,
            'type' => $payment->type,
            'method_id' => $payment->method_id,
            'method_name' => $payment->method->name ?? null,
            'purpose_id' => $payment->purpose_id,
            'comment' => $payment->comment,
        ]);
        $request->new_values = json_encode($newValues);

        if ($request->save()) {
            return $request;
        }

        return null;
    }

    /**
     * Получить количество ожидающих запросов для текущей организации
     * @return int
     */
    public static function getPendingCount(): int
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['status' => self::STATUS_PENDING])
            ->count();
    }

    /**
     * Проверить, есть ли ожидающий запрос для платежа
     * @param int $paymentId
     * @return bool
     */
    public static function hasPendingRequest(int $paymentId): bool
    {
        return self::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['payment_id' => $paymentId, 'status' => self::STATUS_PENDING])
            ->exists();
    }

    /**
     * Получить изменённые поля для отображения
     * @return array ['field' => ['old' => value, 'new' => value], ...]
     */
    public function getChangedFields(): array
    {
        if ($this->request_type !== self::TYPE_UPDATE) {
            return [];
        }

        $oldValues = is_string($this->old_values) ? json_decode($this->old_values, true) : $this->old_values;
        $newValues = is_string($this->new_values) ? json_decode($this->new_values, true) : $this->new_values;

        if (!is_array($oldValues) || !is_array($newValues)) {
            return [];
        }

        $changes = [];
        $labels = [
            'amount' => 'Сумма',
            'date' => 'Дата',
            'method_id' => 'Способ оплаты',
            'purpose_id' => 'Назначение',
            'comment' => 'Комментарий',
        ];

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'label' => $labels[$field] ?? $field,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
