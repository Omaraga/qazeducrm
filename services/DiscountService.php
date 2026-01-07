<?php

namespace app\services;

use app\models\Organizations;
use app\models\SaasPlan;
use app\models\SaasFeature;
use app\models\SaasPromoCode;
use app\models\SaasVolumeDiscount;
use app\models\OrganizationDiscount;
use app\models\OrganizationPayment;

/**
 * Сервис расчёта скидок.
 *
 * Типы скидок (в порядке применения):
 * 1. Годовая подписка (2 месяца бесплатно) - встроенная
 * 2. Промокод - от маркетинга
 * 3. Накопительная скидка - за лояльность (месяцы подряд)
 * 4. Индивидуальная скидка - VIP клиентам
 */
class DiscountService
{
    /**
     * Рассчитать все применимые скидки для подписки
     */
    public function calculateSubscriptionDiscounts(
        Organizations $organization,
        SaasPlan $plan,
        string $billingPeriod,
        ?string $promoCode = null,
        bool $isRenewal = false
    ): DiscountResult {
        $originalAmount = $this->getBasePrice($plan, $billingPeriod);
        $discounts = [];

        // 1. Скидка за годовую оплату (встроенная в цену, но показываем)
        if ($billingPeriod === 'yearly') {
            $monthlyTotal = $plan->price_monthly * 12;
            $yearlyPrice = $plan->price_yearly ?: ($plan->price_monthly * 10);
            if ($monthlyTotal > $yearlyPrice) {
                $yearlyDiscount = $monthlyTotal - $yearlyPrice;
                $discounts[] = new Discount(
                    OrganizationPayment::DISCOUNT_YEARLY,
                    $yearlyDiscount,
                    'Годовая подписка: экономия 2 месяца'
                );
                // Пересчитываем original для корректного отображения
                $originalAmount = $monthlyTotal;
            }
        }

        // 2. Промокод
        if ($promoCode) {
            $promo = $this->validatePromoCode($promoCode, $organization, $plan);
            if ($promo) {
                $baseForPromo = $billingPeriod === 'yearly'
                    ? ($plan->price_yearly ?: $plan->price_monthly * 10)
                    : $plan->price_monthly;
                $promoDiscount = $promo->calculateDiscount($baseForPromo);
                if ($promoDiscount > 0) {
                    $discounts[] = new Discount(
                        OrganizationPayment::DISCOUNT_PROMO,
                        $promoDiscount,
                        $promo->name . ' (' . $promo->code . ')',
                        $promo->id
                    );
                }
            }
        }

        // 3. Накопительная скидка за лояльность
        $volumeDiscount = SaasVolumeDiscount::findForOrganization($organization, $isRenewal);
        if ($volumeDiscount) {
            $baseForVolume = $this->getAmountAfterDiscounts($originalAmount, $discounts);
            $volumeAmount = $volumeDiscount->calculateDiscount($baseForVolume);
            if ($volumeAmount > 0) {
                $discounts[] = new Discount(
                    OrganizationPayment::DISCOUNT_VOLUME,
                    $volumeAmount,
                    $volumeDiscount->name . ' (' . $volumeDiscount->discount_percent . '%)'
                );
            }
        }

        // 4. Индивидуальная скидка организации
        $individualDiscount = OrganizationDiscount::findBestForSubscription($organization->id);
        if ($individualDiscount && $individualDiscount->isValid()) {
            $baseForIndividual = $this->getAmountAfterDiscounts($originalAmount, $discounts);
            $individualAmount = $individualDiscount->calculateDiscount($baseForIndividual);
            if ($individualAmount > 0) {
                $discounts[] = new Discount(
                    OrganizationPayment::DISCOUNT_INDIVIDUAL,
                    $individualAmount,
                    'Индивидуальная скидка' . ($individualDiscount->reason ? ': ' . $individualDiscount->reason : '')
                );
            }
        }

        return new DiscountResult($originalAmount, $discounts);
    }

    /**
     * Рассчитать скидки для аддона
     */
    public function calculateAddonDiscounts(
        Organizations $organization,
        SaasFeature $addon,
        string $billingPeriod,
        ?string $promoCode = null
    ): DiscountResult {
        $originalAmount = $billingPeriod === 'yearly'
            ? ($addon->addon_price_yearly ?: $addon->addon_price_monthly * 10)
            : $addon->addon_price_monthly;

        $discounts = [];

        // 1. Скидка за годовую оплату
        if ($billingPeriod === 'yearly' && $addon->addon_price_monthly) {
            $monthlyTotal = $addon->addon_price_monthly * 12;
            $yearlyPrice = $addon->addon_price_yearly ?: ($addon->addon_price_monthly * 10);
            if ($monthlyTotal > $yearlyPrice) {
                $yearlyDiscount = $monthlyTotal - $yearlyPrice;
                $discounts[] = new Discount(
                    OrganizationPayment::DISCOUNT_YEARLY,
                    $yearlyDiscount,
                    'Годовая оплата аддона'
                );
                $originalAmount = $monthlyTotal;
            }
        }

        // 2. Промокод
        if ($promoCode) {
            $promo = $this->validatePromoCodeForAddon($promoCode, $organization, $addon);
            if ($promo) {
                $baseForPromo = $billingPeriod === 'yearly'
                    ? ($addon->addon_price_yearly ?: $addon->addon_price_monthly * 10)
                    : $addon->addon_price_monthly;
                $promoDiscount = $promo->calculateDiscount($baseForPromo);
                if ($promoDiscount > 0) {
                    $discounts[] = new Discount(
                        OrganizationPayment::DISCOUNT_PROMO,
                        $promoDiscount,
                        $promo->name . ' (' . $promo->code . ')',
                        $promo->id
                    );
                }
            }
        }

        // 3. Индивидуальная скидка организации для аддонов
        $individualDiscount = OrganizationDiscount::findBestForAddon($organization->id);
        if ($individualDiscount && $individualDiscount->isValid()) {
            $baseForIndividual = $this->getAmountAfterDiscounts($originalAmount, $discounts);
            $individualAmount = $individualDiscount->calculateDiscount($baseForIndividual);
            if ($individualAmount > 0) {
                $discounts[] = new Discount(
                    OrganizationPayment::DISCOUNT_INDIVIDUAL,
                    $individualAmount,
                    'Индивидуальная скидка' . ($individualDiscount->reason ? ': ' . $individualDiscount->reason : '')
                );
            }
        }

        return new DiscountResult($originalAmount, $discounts);
    }

    /**
     * Валидация промокода для подписки
     */
    public function validatePromoCode(string $code, Organizations $organization, ?SaasPlan $plan = null): ?SaasPromoCode
    {
        $promo = SaasPromoCode::findByCode($code);
        if (!$promo) {
            return null;
        }

        if (!$promo->isApplicableToOrganization($organization)) {
            return null;
        }

        if (!$promo->isApplicableToPlan($plan)) {
            return null;
        }

        return $promo;
    }

    /**
     * Валидация промокода для аддона
     */
    public function validatePromoCodeForAddon(string $code, Organizations $organization, ?SaasFeature $addon = null): ?SaasPromoCode
    {
        $promo = SaasPromoCode::findByCode($code);
        if (!$promo) {
            return null;
        }

        if (!$promo->isApplicableToOrganization($organization)) {
            return null;
        }

        if (!$promo->isApplicableToAddon($addon)) {
            return null;
        }

        return $promo;
    }

    /**
     * Получить базовую цену плана
     */
    private function getBasePrice(SaasPlan $plan, string $billingPeriod): float
    {
        if ($billingPeriod === 'yearly') {
            return $plan->price_yearly ?: ($plan->price_monthly * 10);
        }
        return $plan->price_monthly;
    }

    /**
     * Получить сумму после применения скидок
     */
    private function getAmountAfterDiscounts(float $originalAmount, array $discounts): float
    {
        $totalDiscount = array_sum(array_map(fn($d) => $d->amount, $discounts));
        return max(0, $originalAmount - $totalDiscount);
    }

    /**
     * Проверить промокод и вернуть информацию
     */
    public function checkPromoCode(string $code, ?int $organizationId = null): array
    {
        $promo = SaasPromoCode::findByCode($code);

        if (!$promo) {
            return [
                'valid' => false,
                'error' => 'Промокод не найден',
            ];
        }

        if (!$promo->is_active) {
            return [
                'valid' => false,
                'error' => 'Промокод деактивирован',
            ];
        }

        if ($promo->isExpired()) {
            return [
                'valid' => false,
                'error' => 'Срок действия промокода истёк',
            ];
        }

        if ($promo->isNotStarted()) {
            return [
                'valid' => false,
                'error' => 'Промокод ещё не активен',
            ];
        }

        if ($promo->usage_limit && $promo->getUsageCount() >= $promo->usage_limit) {
            return [
                'valid' => false,
                'error' => 'Лимит использований промокода исчерпан',
            ];
        }

        if ($organizationId && $promo->getUsageCountByOrg($organizationId) >= $promo->usage_per_org) {
            return [
                'valid' => false,
                'error' => 'Вы уже использовали этот промокод',
            ];
        }

        return [
            'valid' => true,
            'promo' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'formatted_discount' => $promo->getFormattedDiscount(),
                'applies_to' => $promo->applies_to,
                'min_amount' => $promo->min_amount,
                'max_discount' => $promo->max_discount,
            ],
        ];
    }

    /**
     * Получить все доступные скидки для организации
     */
    public function getAvailableDiscounts(Organizations $organization): array
    {
        $discounts = [];

        // Накопительная скидка
        $volumeDiscount = SaasVolumeDiscount::findForOrganization($organization, true);
        if ($volumeDiscount) {
            $discounts['volume'] = [
                'type' => 'volume',
                'name' => $volumeDiscount->name,
                'description' => $volumeDiscount->description,
                'discount_percent' => $volumeDiscount->discount_percent,
                'consecutive_months' => $organization->getConsecutivePaymentMonths(),
            ];
        }

        // Индивидуальные скидки
        $individualDiscounts = OrganizationDiscount::findActiveForOrganization($organization->id)->all();
        if (!empty($individualDiscounts)) {
            $discounts['individual'] = array_map(function ($d) {
                return [
                    'type' => 'individual',
                    'discount_type' => $d->discount_type,
                    'discount_value' => $d->discount_value,
                    'formatted' => $d->getFormattedDiscount(),
                    'reason' => $d->reason,
                    'applies_to' => $d->applies_to,
                    'valid_until' => $d->valid_until,
                ];
            }, $individualDiscounts);
        }

        return $discounts;
    }

    /**
     * Применить скидки к платежу
     */
    public function applyDiscountsToPayment(OrganizationPayment $payment, DiscountResult $discountResult, ?int $promoCodeId = null): void
    {
        $payment->original_amount = $discountResult->originalAmount;
        $payment->discount_amount = $discountResult->totalDiscount;
        $payment->amount = $discountResult->finalAmount;

        // Определяем основной тип скидки
        if (!empty($discountResult->discounts)) {
            // Берём первый (приоритетный) тип скидки
            $payment->discount_type = $discountResult->discounts[0]->type;
        }

        // Сохраняем детали всех скидок
        $payment->discount_details = json_encode([
            'discounts' => array_map(fn($d) => $d->toArray(), $discountResult->discounts),
            'calculated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($promoCodeId) {
            $payment->promo_code_id = $promoCodeId;
        }
    }

    /**
     * Зарегистрировать использование промокода после оплаты
     */
    public function registerPromoCodeUsage(OrganizationPayment $payment): void
    {
        if (!$payment->promo_code_id) {
            return;
        }

        $promo = SaasPromoCode::findOne($payment->promo_code_id);
        if ($promo) {
            // Находим сумму скидки по промокоду из деталей
            $promoDiscount = 0;
            if ($payment->discount_details) {
                $details = json_decode($payment->discount_details, true);
                foreach ($details['discounts'] ?? [] as $discount) {
                    if ($discount['type'] === OrganizationPayment::DISCOUNT_PROMO) {
                        $promoDiscount = $discount['amount'];
                        break;
                    }
                }
            }

            $promo->registerUsage($payment->organization_id, $promoDiscount, $payment->id);
        }
    }
}

/**
 * DTO для одной скидки
 */
class Discount
{
    public string $type;
    public float $amount;
    public string $description;
    public ?int $referenceId;

    public function __construct(string $type, float $amount, string $description, ?int $referenceId = null)
    {
        $this->type = $type;
        $this->amount = round($amount, 2);
        $this->description = $description;
        $this->referenceId = $referenceId;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'reference_id' => $this->referenceId,
        ];
    }
}

/**
 * DTO для результата расчёта скидок
 */
class DiscountResult
{
    public float $originalAmount;
    /** @var Discount[] */
    public array $discounts;
    public float $totalDiscount;
    public float $finalAmount;

    public function __construct(float $originalAmount, array $discounts)
    {
        $this->originalAmount = round($originalAmount, 2);
        $this->discounts = $discounts;
        $this->totalDiscount = round(array_sum(array_map(fn($d) => $d->amount, $discounts)), 2);
        $this->finalAmount = round(max(0, $originalAmount - $this->totalDiscount), 2);
    }

    public function hasDiscounts(): bool
    {
        return !empty($this->discounts);
    }

    public function getDiscountPercent(): float
    {
        if ($this->originalAmount <= 0) {
            return 0;
        }
        return round(($this->totalDiscount / $this->originalAmount) * 100, 1);
    }

    public function toArray(): array
    {
        return [
            'original_amount' => $this->originalAmount,
            'discounts' => array_map(fn($d) => $d->toArray(), $this->discounts),
            'total_discount' => $this->totalDiscount,
            'final_amount' => $this->finalAmount,
            'discount_percent' => $this->getDiscountPercent(),
        ];
    }
}
