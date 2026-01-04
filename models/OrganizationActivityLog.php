<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель логов активности организаций.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $action
 * @property string $category
 * @property string|null $description
 * @property string|null $old_value
 * @property string|null $new_value
 * @property array|null $metadata
 * @property int|null $user_id
 * @property string $user_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property Organizations $organization
 * @property User $user
 */
class OrganizationActivityLog extends ActiveRecord
{
    // Категории
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_ORGANIZATION = 'organization';
    const CATEGORY_SUBSCRIPTION = 'subscription';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_STATUS = 'status';
    const CATEGORY_AUTH = 'auth';

    // Типы пользователей
    const USER_TYPE_USER = 'user';
    const USER_TYPE_SUPER_ADMIN = 'super_admin';
    const USER_TYPE_SYSTEM = 'system';

    // Действия
    const ACTION_ORGANIZATION_CREATED = 'organization_created';
    const ACTION_REGISTERED = 'registered';
    const ACTION_EMAIL_VERIFIED = 'email_verified';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_PLAN_CHANGED = 'plan_changed';
    const ACTION_SUBSCRIPTION_CREATED = 'subscription_created';
    const ACTION_SUBSCRIPTION_ACTIVATED = 'subscription_activated';
    const ACTION_SUBSCRIPTION_EXPIRED = 'subscription_expired';
    const ACTION_SUBSCRIPTION_CANCELLED = 'subscription_cancelled';
    const ACTION_PAYMENT_CREATED = 'payment_created';
    const ACTION_PAYMENT_COMPLETED = 'payment_completed';
    const ACTION_PAYMENT_RECEIVED = 'payment_received';
    const ACTION_PAYMENT_FAILED = 'payment_failed';
    const ACTION_PAYMENT_REFUNDED = 'payment_refunded';
    const ACTION_BRANCH_CREATED = 'branch_created';
    const ACTION_BRANCH_DELETED = 'branch_deleted';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_activity_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'action'], 'required'],
            [['organization_id', 'user_id'], 'integer'],
            [['description', 'old_value', 'new_value', 'user_agent'], 'string'],
            [['metadata'], 'safe'],
            [['action'], 'string', 'max' => 50],
            [['category'], 'string', 'max' => 30],
            [['user_type'], 'string', 'max' => 20],
            [['ip_address'], 'string', 'max' => 45],
            [['organization_id'], 'exist', 'targetClass' => Organizations::class, 'targetAttribute' => 'id'],
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
            'action' => Yii::t('main', 'Действие'),
            'category' => Yii::t('main', 'Категория'),
            'description' => Yii::t('main', 'Описание'),
            'old_value' => Yii::t('main', 'Старое значение'),
            'new_value' => Yii::t('main', 'Новое значение'),
            'user_id' => Yii::t('main', 'Пользователь'),
            'user_type' => Yii::t('main', 'Тип пользователя'),
            'ip_address' => Yii::t('main', 'IP адрес'),
            'created_at' => Yii::t('main', 'Дата'),
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
     * Связь с пользователем
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Список категорий
     */
    public static function getCategoryList(): array
    {
        return [
            self::CATEGORY_GENERAL => Yii::t('main', 'Общее'),
            self::CATEGORY_ORGANIZATION => Yii::t('main', 'Организация'),
            self::CATEGORY_SUBSCRIPTION => Yii::t('main', 'Подписка'),
            self::CATEGORY_PAYMENT => Yii::t('main', 'Платёж'),
            self::CATEGORY_STATUS => Yii::t('main', 'Статус'),
            self::CATEGORY_AUTH => Yii::t('main', 'Авторизация'),
        ];
    }

    /**
     * Название категории
     */
    public function getCategoryLabel(): string
    {
        return self::getCategoryList()[$this->category] ?? $this->category;
    }

    /**
     * Список действий
     */
    public static function getActionList(): array
    {
        return [
            self::ACTION_ORGANIZATION_CREATED => Yii::t('main', 'Организация создана'),
            self::ACTION_REGISTERED => Yii::t('main', 'Регистрация'),
            self::ACTION_EMAIL_VERIFIED => Yii::t('main', 'Email подтверждён'),
            self::ACTION_STATUS_CHANGED => Yii::t('main', 'Изменён статус'),
            self::ACTION_PLAN_CHANGED => Yii::t('main', 'Изменён план'),
            self::ACTION_SUBSCRIPTION_CREATED => Yii::t('main', 'Создана подписка'),
            self::ACTION_SUBSCRIPTION_ACTIVATED => Yii::t('main', 'Подписка активирована'),
            self::ACTION_SUBSCRIPTION_EXPIRED => Yii::t('main', 'Подписка истекла'),
            self::ACTION_SUBSCRIPTION_CANCELLED => Yii::t('main', 'Подписка отменена'),
            self::ACTION_PAYMENT_CREATED => Yii::t('main', 'Создан платёж'),
            self::ACTION_PAYMENT_COMPLETED => Yii::t('main', 'Платёж завершён'),
            self::ACTION_PAYMENT_RECEIVED => Yii::t('main', 'Платёж получен'),
            self::ACTION_PAYMENT_FAILED => Yii::t('main', 'Платёж не прошёл'),
            self::ACTION_PAYMENT_REFUNDED => Yii::t('main', 'Платёж возвращён'),
            self::ACTION_BRANCH_CREATED => Yii::t('main', 'Создан филиал'),
            self::ACTION_BRANCH_DELETED => Yii::t('main', 'Удалён филиал'),
            self::ACTION_LOGIN => Yii::t('main', 'Вход'),
            self::ACTION_LOGOUT => Yii::t('main', 'Выход'),
        ];
    }

    /**
     * Название действия
     */
    public function getActionLabel(): string
    {
        return self::getActionList()[$this->action] ?? $this->action;
    }

    /**
     * Записать лог
     */
    public static function log(
        int $organizationId,
        string $action,
        string $category = self::CATEGORY_GENERAL,
        ?string $description = null,
        $oldValue = null,
        $newValue = null,
        ?array $metadata = null
    ): bool {
        $log = new self();
        $log->organization_id = $organizationId;
        $log->action = $action;
        $log->category = $category;
        $log->description = $description;
        $log->old_value = $oldValue ? (is_array($oldValue) ? json_encode($oldValue) : $oldValue) : null;
        $log->new_value = $newValue ? (is_array($newValue) ? json_encode($newValue) : $newValue) : null;
        $log->metadata = $metadata;

        // Определяем пользователя
        if (!Yii::$app->request->isConsoleRequest && !Yii::$app->user->isGuest) {
            $log->user_id = Yii::$app->user->id;
            $log->user_type = Yii::$app->user->can('SUPER') ? self::USER_TYPE_SUPER_ADMIN : self::USER_TYPE_USER;
        } else {
            $log->user_type = self::USER_TYPE_SYSTEM;
        }

        // IP и User Agent
        if (!Yii::$app->request->isConsoleRequest) {
            $log->ip_address = Yii::$app->request->userIP;
            $log->user_agent = Yii::$app->request->userAgent;
        }

        return $log->save();
    }

    /**
     * Получить метаданные как массив
     */
    public function getMetadataArray(): array
    {
        if (empty($this->metadata)) {
            return [];
        }
        return is_array($this->metadata) ? $this->metadata : json_decode($this->metadata, true) ?? [];
    }

    /**
     * Логи организации за период
     */
    public static function findByOrganization(int $organizationId, ?string $category = null): \yii\db\ActiveQuery
    {
        $query = static::find()
            ->andWhere(['organization_id' => $organizationId])
            ->orderBy(['created_at' => SORT_DESC]);

        if ($category) {
            $query->andWhere(['category' => $category]);
        }

        return $query;
    }
}
