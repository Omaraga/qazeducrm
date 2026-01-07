<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель использования промокода.
 *
 * @property int $id
 * @property int $promo_code_id
 * @property int $organization_id
 * @property int|null $payment_id
 * @property float $discount_amount
 * @property string $used_at
 *
 * @property SaasPromoCode $promoCode
 * @property Organizations $organization
 * @property OrganizationPayment $payment
 */
class SaasPromoCodeUsage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%saas_promo_code_usage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promo_code_id', 'organization_id', 'discount_amount'], 'required'],
            [['promo_code_id', 'organization_id', 'payment_id'], 'integer'],
            [['discount_amount'], 'number', 'min' => 0],
            [['used_at'], 'safe'],
            [['promo_code_id'], 'exist', 'skipOnError' => true, 'targetClass' => SaasPromoCode::class, 'targetAttribute' => ['promo_code_id' => 'id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['organization_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationPayment::class, 'targetAttribute' => ['payment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'promo_code_id' => 'Промокод',
            'organization_id' => 'Организация',
            'payment_id' => 'Платёж',
            'discount_amount' => 'Сумма скидки',
            'used_at' => 'Дата использования',
        ];
    }

    /**
     * Связь с промокодом
     */
    public function getPromoCode()
    {
        return $this->hasOne(SaasPromoCode::class, ['id' => 'promo_code_id']);
    }

    /**
     * Связь с организацией
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * Связь с платежом
     */
    public function getPayment()
    {
        return $this->hasOne(OrganizationPayment::class, ['id' => 'payment_id']);
    }

    /**
     * Найти использования по промокоду
     */
    public static function findByPromoCode(int $promoCodeId)
    {
        return self::find()->where(['promo_code_id' => $promoCodeId]);
    }

    /**
     * Найти использования по организации
     */
    public static function findByOrganization(int $organizationId)
    {
        return self::find()->where(['organization_id' => $organizationId]);
    }

    /**
     * Статистика по промокоду
     */
    public static function getPromoCodeStats(int $promoCodeId): array
    {
        $query = self::find()->where(['promo_code_id' => $promoCodeId]);

        return [
            'total_usages' => (int)$query->count(),
            'total_discount' => (float)$query->sum('discount_amount'),
            'unique_organizations' => (int)$query->select('organization_id')->distinct()->count(),
        ];
    }
}
