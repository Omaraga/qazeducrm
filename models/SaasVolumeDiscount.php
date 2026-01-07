<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель накопительной скидки за лояльность.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $min_months
 * @property float $discount_percent
 * @property string $applies_to renewal|all
 * @property bool $is_active
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class SaasVolumeDiscount extends ActiveRecord
{
    const APPLIES_RENEWAL = 'renewal';
    const APPLIES_ALL = 'all';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_volume_discount}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'min_months', 'discount_percent'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['min_months', 'sort_order'], 'integer'],
            [['min_months'], 'integer', 'min' => 1],
            [['discount_percent'], 'number', 'min' => 0, 'max' => 100],
            [['applies_to'], 'in', 'range' => [self::APPLIES_RENEWAL, self::APPLIES_ALL]],
            [['is_active'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'description' => 'Описание',
            'min_months' => 'Мин. месяцев',
            'discount_percent' => 'Процент скидки',
            'applies_to' => 'Применяется к',
            'is_active' => 'Активна',
            'sort_order' => 'Порядок',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    /**
     * Список "применяется к"
     */
    public static function getAppliesToList(): array
    {
        return [
            self::APPLIES_RENEWAL => 'Только продление',
            self::APPLIES_ALL => 'Все платежи',
        ];
    }

    /**
     * Метка "применяется к"
     */
    public function getAppliesToLabel(): string
    {
        return self::getAppliesToList()[$this->applies_to] ?? $this->applies_to;
    }

    /**
     * Найти активные скидки
     */
    public static function findActive()
    {
        return self::find()
            ->where(['is_active' => true])
            ->orderBy(['min_months' => SORT_ASC]);
    }

    /**
     * Найти подходящую скидку для организации
     */
    public static function findForOrganization(Organizations $organization, bool $isRenewal = true): ?self
    {
        $consecutiveMonths = $organization->getConsecutivePaymentMonths();

        $query = self::findActive()
            ->andWhere(['<=', 'min_months', $consecutiveMonths])
            ->orderBy(['min_months' => SORT_DESC]); // Берём максимальную подходящую

        if ($isRenewal) {
            // Для продления подходят обе категории
        } else {
            // Для не-продления только 'all'
            $query->andWhere(['applies_to' => self::APPLIES_ALL]);
        }

        return $query->one();
    }

    /**
     * Рассчитать скидку
     */
    public function calculateDiscount(float $amount): float
    {
        return round($amount * ($this->discount_percent / 100), 2);
    }

    /**
     * Форматированное описание скидки
     */
    public function getFormattedDiscount(): string
    {
        return $this->discount_percent . '%';
    }
}
