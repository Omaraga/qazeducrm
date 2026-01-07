<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель связи тарифного плана с функциями.
 *
 * @property int $id
 * @property int $saas_plan_id
 * @property int $feature_id
 * @property int $enabled
 * @property array|null $value
 * @property string $created_at
 * @property string $updated_at
 *
 * @property SaasPlan $plan
 * @property SaasFeature $feature
 */
class SaasPlanFeature extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_plan_feature}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['saas_plan_id', 'feature_id'], 'required'],
            [['saas_plan_id', 'feature_id', 'enabled'], 'integer'],
            [['value'], 'safe'],
            [
                ['saas_plan_id', 'feature_id'],
                'unique',
                'targetAttribute' => ['saas_plan_id', 'feature_id'],
                'message' => 'Эта функция уже добавлена к плану'
            ],
            [['saas_plan_id'], 'exist', 'targetClass' => SaasPlan::class, 'targetAttribute' => 'id'],
            [['feature_id'], 'exist', 'targetClass' => SaasFeature::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'saas_plan_id' => Yii::t('main', 'Тарифный план'),
            'feature_id' => Yii::t('main', 'Функция'),
            'enabled' => Yii::t('main', 'Включена'),
            'value' => Yii::t('main', 'Значение'),
        ];
    }

    /**
     * Связь с тарифным планом
     */
    public function getPlan()
    {
        return $this->hasOne(SaasPlan::class, ['id' => 'saas_plan_id']);
    }

    /**
     * Связь с функцией
     */
    public function getFeature()
    {
        return $this->hasOne(SaasFeature::class, ['id' => 'feature_id']);
    }

    /**
     * Включена ли функция
     */
    public function isEnabled(): bool
    {
        return (bool)$this->enabled;
    }

    /**
     * Получить значение функции как массив
     */
    public function getValueArray(): array
    {
        if (empty($this->value)) {
            return [];
        }
        return is_array($this->value)
            ? $this->value
            : json_decode($this->value, true) ?? [];
    }

    /**
     * Получить лимит из значения
     */
    public function getLimit(): ?int
    {
        $value = $this->getValueArray();
        return $value['limit'] ?? null;
    }

    /**
     * Получить конкретное значение из настроек
     */
    public function getValue(string $key = null)
    {
        $value = $this->getValueArray();

        if ($key === null) {
            return $value;
        }

        return $value[$key] ?? null;
    }

    /**
     * Найти связь по плану и коду функции
     */
    public static function findByPlanAndFeatureCode(int $planId, string $featureCode): ?self
    {
        return static::find()
            ->alias('pf')
            ->innerJoin('{{%saas_feature}} f', 'pf.feature_id = f.id')
            ->where(['pf.saas_plan_id' => $planId, 'f.code' => $featureCode])
            ->one();
    }

    /**
     * Получить все функции плана
     */
    public static function findByPlan(int $planId): array
    {
        return static::find()
            ->with('feature')
            ->where(['saas_plan_id' => $planId, 'enabled' => 1])
            ->all();
    }

    /**
     * Проверить, есть ли функция в плане
     */
    public static function hasFeature(int $planId, string $featureCode): bool
    {
        return static::find()
            ->alias('pf')
            ->innerJoin('{{%saas_feature}} f', 'pf.feature_id = f.id')
            ->where([
                'pf.saas_plan_id' => $planId,
                'pf.enabled' => 1,
                'f.code' => $featureCode,
            ])
            ->exists();
    }

    /**
     * Получить значение функции для плана
     */
    public static function getFeatureValue(int $planId, string $featureCode, string $key = null)
    {
        $planFeature = static::findByPlanAndFeatureCode($planId, $featureCode);

        if (!$planFeature || !$planFeature->enabled) {
            return null;
        }

        return $planFeature->getValue($key);
    }

    /**
     * Получить лимит функции для плана
     */
    public static function getFeatureLimit(int $planId, string $featureCode): ?int
    {
        $planFeature = static::findByPlanAndFeatureCode($planId, $featureCode);

        if (!$planFeature || !$planFeature->enabled) {
            return null;
        }

        return $planFeature->getLimit();
    }
}
