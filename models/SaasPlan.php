<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель тарифных планов SaaS.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $max_pupils
 * @property int $max_teachers
 * @property int $max_groups
 * @property int $max_admins
 * @property int $max_branches
 * @property float $price_monthly
 * @property float $price_yearly
 * @property int $trial_days
 * @property array|null $features
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property OrganizationSubscription[] $subscriptions
 */
class SaasPlan extends ActiveRecord
{
    // Коды тарифных планов
    const CODE_FREE = 'free';
    const CODE_BASIC = 'basic';
    const CODE_PRO = 'pro';
    const CODE_ENTERPRISE = 'enterprise';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_plan}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code'], 'unique'],
            [['description'], 'string'],
            [['max_pupils', 'max_teachers', 'max_groups', 'max_admins', 'max_branches', 'trial_days', 'sort_order', 'is_active', 'is_deleted'], 'integer'],
            [['price_monthly', 'price_yearly'], 'number'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [['features'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => Yii::t('main', 'Код'),
            'name' => Yii::t('main', 'Название'),
            'description' => Yii::t('main', 'Описание'),
            'max_pupils' => Yii::t('main', 'Макс. учеников'),
            'max_teachers' => Yii::t('main', 'Макс. учителей'),
            'max_groups' => Yii::t('main', 'Макс. групп'),
            'max_admins' => Yii::t('main', 'Макс. админов'),
            'max_branches' => Yii::t('main', 'Макс. филиалов'),
            'price_monthly' => Yii::t('main', 'Цена/месяц'),
            'price_yearly' => Yii::t('main', 'Цена/год'),
            'trial_days' => Yii::t('main', 'Пробный период (дней)'),
            'features' => Yii::t('main', 'Функции'),
            'sort_order' => Yii::t('main', 'Порядок'),
            'is_active' => Yii::t('main', 'Активен'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return parent::find()->andWhere(['is_deleted' => 0]);
    }

    /**
     * Все планы включая удалённые
     */
    public static function findWithDeleted()
    {
        return parent::find();
    }

    /**
     * Связь с подписками
     */
    public function getSubscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class, ['saas_plan_id' => 'id']);
    }

    /**
     * Получить план по коду
     */
    public static function findByCode(string $code): ?self
    {
        return static::find()->andWhere(['code' => $code])->one();
    }

    /**
     * Список планов для выпадающего списка
     */
    public static function getDropdownList(): array
    {
        return static::find()
            ->andWhere(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Проверить, является ли лимит безлимитным (0 = безлимит)
     */
    public function isUnlimited(string $limitField): bool
    {
        return $this->$limitField === 0;
    }

    /**
     * Получить массив features из JSON
     */
    public function getFeaturesArray(): array
    {
        if (empty($this->features)) {
            return [];
        }
        return is_array($this->features) ? $this->features : json_decode($this->features, true) ?? [];
    }

    /**
     * Проверить наличие функции
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->getFeaturesArray();
        return isset($features[$feature]) && $features[$feature];
    }

    /**
     * Форматированная цена
     */
    public function getFormattedPriceMonthly(): string
    {
        if ($this->price_monthly == 0) {
            return Yii::t('main', 'Бесплатно');
        }
        return number_format($this->price_monthly, 0, '.', ' ') . ' KZT';
    }

    /**
     * Форматированная годовая цена
     */
    public function getFormattedPriceYearly(): string
    {
        if ($this->price_yearly == 0) {
            return Yii::t('main', 'Бесплатно');
        }
        return number_format($this->price_yearly, 0, '.', ' ') . ' KZT';
    }
}
