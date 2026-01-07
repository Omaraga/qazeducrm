<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель функций системы (Feature Flags).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $category
 * @property string $type
 * @property string|null $default_value
 * @property int $is_addon
 * @property float|null $addon_price_monthly
 * @property float|null $addon_price_yearly
 * @property int $trial_available
 * @property int $trial_days
 * @property array|null $dependencies
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property SaasPlanFeature[] $planFeatures
 */
class SaasFeature extends ActiveRecord
{
    // Категории функций
    const CATEGORY_CORE = 'core';
    const CATEGORY_CRM = 'crm';
    const CATEGORY_INTEGRATION = 'integration';
    const CATEGORY_ANALYTICS = 'analytics';
    const CATEGORY_PORTAL = 'portal';
    const CATEGORY_FEATURE = 'feature';

    // Типы функций
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_LIMIT = 'limit';
    const TYPE_CONFIG = 'config';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_feature}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'category'], 'required'],
            [['code'], 'unique'],
            [['description', 'default_value'], 'string'],
            [['is_addon', 'trial_available', 'trial_days', 'sort_order', 'is_active', 'is_deleted'], 'integer'],
            [['addon_price_monthly', 'addon_price_yearly'], 'number'],
            [['code'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 255],
            [['category'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => [self::TYPE_BOOLEAN, self::TYPE_LIMIT, self::TYPE_CONFIG]],
            [['dependencies'], 'safe'],
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
            'category' => Yii::t('main', 'Категория'),
            'type' => Yii::t('main', 'Тип'),
            'default_value' => Yii::t('main', 'Значение по умолчанию'),
            'is_addon' => Yii::t('main', 'Аддон'),
            'addon_price_monthly' => Yii::t('main', 'Цена аддона/мес'),
            'addon_price_yearly' => Yii::t('main', 'Цена аддона/год'),
            'trial_available' => Yii::t('main', 'Trial доступен'),
            'trial_days' => Yii::t('main', 'Дней trial'),
            'sort_order' => Yii::t('main', 'Порядок'),
            'is_active' => Yii::t('main', 'Активна'),
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
     * Все записи включая удалённые
     */
    public static function findWithDeleted()
    {
        return parent::find();
    }

    /**
     * Связь с привязками к планам
     */
    public function getPlanFeatures()
    {
        return $this->hasMany(SaasPlanFeature::class, ['feature_id' => 'id']);
    }

    /**
     * Найти функцию по коду
     */
    public static function findByCode(string $code): ?self
    {
        return static::find()
            ->andWhere(['code' => $code])
            ->andWhere(['is_active' => 1])
            ->one();
    }

    /**
     * Получить все активные функции
     */
    public static function findAllActive(): array
    {
        return static::find()
            ->andWhere(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Получить все доступные аддоны
     */
    public static function findAddons(): array
    {
        return static::find()
            ->andWhere(['is_addon' => 1, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Получить функции по категории
     */
    public static function findByCategory(string $category): array
    {
        return static::find()
            ->andWhere(['category' => $category, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Является ли функция аддоном
     */
    public function isAddon(): bool
    {
        return (bool)$this->is_addon;
    }

    /**
     * Доступен ли trial
     */
    public function isTrialAvailable(): bool
    {
        return (bool)$this->trial_available;
    }

    /**
     * Получить зависимости как массив
     */
    public function getDependenciesArray(): array
    {
        if (empty($this->dependencies)) {
            return [];
        }
        return is_array($this->dependencies)
            ? $this->dependencies
            : json_decode($this->dependencies, true) ?? [];
    }

    /**
     * Проверить, есть ли зависимости
     */
    public function hasDependencies(): bool
    {
        return !empty($this->getDependenciesArray());
    }

    /**
     * Форматированная цена аддона
     */
    public function getFormattedAddonPrice(): string
    {
        if (!$this->is_addon || !$this->addon_price_monthly) {
            return '';
        }
        return number_format($this->addon_price_monthly, 0, '.', ' ') . ' KZT/мес';
    }

    /**
     * Список категорий
     */
    public static function getCategoryList(): array
    {
        return [
            self::CATEGORY_CORE => Yii::t('main', 'Базовые'),
            self::CATEGORY_CRM => Yii::t('main', 'CRM'),
            self::CATEGORY_INTEGRATION => Yii::t('main', 'Интеграции'),
            self::CATEGORY_ANALYTICS => Yii::t('main', 'Аналитика'),
            self::CATEGORY_PORTAL => Yii::t('main', 'Порталы'),
            self::CATEGORY_FEATURE => Yii::t('main', 'Функции'),
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
     * Список типов
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_BOOLEAN => Yii::t('main', 'Вкл/Выкл'),
            self::TYPE_LIMIT => Yii::t('main', 'Лимит'),
            self::TYPE_CONFIG => Yii::t('main', 'Настройка'),
        ];
    }

    /**
     * Название типа
     */
    public function getTypeLabel(): string
    {
        return self::getTypeList()[$this->type] ?? $this->type;
    }
}
