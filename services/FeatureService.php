<?php

namespace app\services;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationAddon;
use app\models\SaasFeature;
use app\models\SaasPlan;
use app\models\SaasPlanFeature;
use Yii;
use yii\caching\TagDependency;

/**
 * Сервис проверки функций (Feature Flags).
 *
 * Иерархия проверки:
 * 1. Кастомные настройки организации (organization.features JSON)
 * 2. Докупленные аддоны организации
 * 3. Функции из тарифного плана
 * 4. Значения по умолчанию
 */
class FeatureService
{
    private Organizations $organization;
    private ?OrganizationSubscription $subscription;
    private ?SaasPlan $plan;
    private array $featureCache = [];
    private ?array $activeAddons = null;

    /**
     * Время кэширования в секундах
     */
    const CACHE_DURATION = 300; // 5 минут

    /**
     * Тег для инвалидации кэша
     */
    const CACHE_TAG = 'feature_service';

    public function __construct(Organizations $organization)
    {
        $this->organization = $organization->getHeadOrganization();
        $this->subscription = $this->organization->getActiveSubscription();
        $this->plan = $this->subscription?->saasPlan;
    }

    /**
     * Создать сервис для текущей организации
     */
    public static function forCurrentOrganization(): ?self
    {
        $org = Organizations::getCurrentOrganization();
        return $org ? new self($org) : null;
    }

    /**
     * Проверить наличие функции
     *
     * @param string $code Код функции
     * @return bool
     */
    public function hasFeature(string $code): bool
    {
        // Кэш в рамках запроса
        if (isset($this->featureCache[$code]['has'])) {
            return $this->featureCache[$code]['has'];
        }

        // 1. Проверяем кастомные настройки организации
        $orgFeatures = $this->getOrganizationCustomFeatures();
        if (isset($orgFeatures[$code])) {
            $result = (bool)$orgFeatures[$code];
            $this->featureCache[$code]['has'] = $result;
            return $result;
        }

        // 2. Проверяем докупленные аддоны
        if ($this->hasAddon($code)) {
            $this->featureCache[$code]['has'] = true;
            return true;
        }

        // 3. Проверяем тарифный план
        if ($this->plan) {
            // Сначала проверяем в JSON поле features плана
            if ($this->plan->hasFeature($code)) {
                $this->featureCache[$code]['has'] = true;
                return true;
            }

            // Затем проверяем в таблице saas_plan_feature
            $result = SaasPlanFeature::hasFeature($this->plan->id, $code);
            $this->featureCache[$code]['has'] = $result;
            return $result;
        }

        $this->featureCache[$code]['has'] = false;
        return false;
    }

    /**
     * Получить значение функции
     *
     * @param string $code Код функции
     * @param string|null $key Ключ в значении (для config типа)
     * @return mixed
     */
    public function getFeatureValue(string $code, ?string $key = null)
    {
        $cacheKey = $code . ($key ? ":{$key}" : '');

        if (isset($this->featureCache[$cacheKey]['value'])) {
            return $this->featureCache[$cacheKey]['value'];
        }

        // 1. Кастомные настройки организации
        $orgFeatures = $this->getOrganizationCustomFeatures();
        if (isset($orgFeatures[$code])) {
            $value = $key && is_array($orgFeatures[$code])
                ? ($orgFeatures[$code][$key] ?? null)
                : $orgFeatures[$code];
            $this->featureCache[$cacheKey]['value'] = $value;
            return $value;
        }

        // 2. Докупленные аддоны
        $addonValue = $this->getAddonValue($code, $key);
        if ($addonValue !== null) {
            $this->featureCache[$cacheKey]['value'] = $addonValue;
            return $addonValue;
        }

        // 3. Тарифный план
        if ($this->plan) {
            // Сначала JSON features
            $planFeatures = $this->plan->getFeaturesArray();
            if (isset($planFeatures[$code])) {
                $value = $key && is_array($planFeatures[$code])
                    ? ($planFeatures[$code][$key] ?? $planFeatures[$code])
                    : $planFeatures[$code];
                $this->featureCache[$cacheKey]['value'] = $value;
                return $value;
            }

            // Затем таблица saas_plan_feature
            $value = SaasPlanFeature::getFeatureValue($this->plan->id, $code, $key);
            $this->featureCache[$cacheKey]['value'] = $value;
            return $value;
        }

        // 4. Значение по умолчанию
        $feature = SaasFeature::findByCode($code);
        if ($feature && $feature->default_value) {
            $default = json_decode($feature->default_value, true);
            $value = $key && is_array($default) ? ($default[$key] ?? null) : $default;
            $this->featureCache[$cacheKey]['value'] = $value;
            return $value;
        }

        $this->featureCache[$cacheKey]['value'] = null;
        return null;
    }

    /**
     * Получить лимит функции
     *
     * @param string $code Код функции
     * @return int|null null если безлимит или функция недоступна
     */
    public function getFeatureLimit(string $code): ?int
    {
        $value = $this->getFeatureValue($code, 'limit');

        if ($value === null) {
            // Проверяем числовое значение напрямую
            $directValue = $this->getFeatureValue($code);
            if (is_numeric($directValue)) {
                return (int)$directValue === 0 ? null : (int)$directValue; // 0 = безлимит
            }
            return null;
        }

        return (int)$value === 0 ? null : (int)$value;
    }

    /**
     * Проверить, безлимитна ли функция
     */
    public function isFeatureUnlimited(string $code): bool
    {
        return $this->getFeatureLimit($code) === null && $this->hasFeature($code);
    }

    /**
     * Можно ли начать trial для функции
     */
    public function canStartTrial(string $code): bool
    {
        // Уже есть функция
        if ($this->hasFeature($code)) {
            return false;
        }

        $feature = SaasFeature::findByCode($code);
        if (!$feature || !$feature->isTrialAvailable()) {
            return false;
        }

        // Проверяем, не был ли уже использован trial
        if ($this->hasUsedTrial($code)) {
            return false;
        }

        return true;
    }

    /**
     * Проверить, был ли использован trial для функции
     */
    public function hasUsedTrial(string $code): bool
    {
        $feature = SaasFeature::findByCode($code);
        if (!$feature) {
            return false;
        }

        // Проверяем все аддоны (включая истекшие)
        return OrganizationAddon::find()
            ->where(['organization_id' => $this->organization->id])
            ->andWhere(['feature_id' => $feature->id])
            ->andWhere(['IS NOT', 'trial_ends_at', null])
            ->exists();
    }

    /**
     * Начать trial для функции
     */
    public function startTrial(string $code): ?OrganizationAddon
    {
        if (!$this->canStartTrial($code)) {
            return null;
        }

        $feature = SaasFeature::findByCode($code);
        if (!$feature) {
            return null;
        }

        $addon = OrganizationAddon::create(
            $this->organization->id,
            $feature->id,
            1,
            OrganizationAddon::PERIOD_MONTHLY,
            0 // Бесплатно для trial
        );

        if ($addon->startTrial($feature->trial_days)) {
            // Сбрасываем кэш
            $this->activeAddons = null;
            $this->featureCache = [];
            return $addon;
        }

        return null;
    }

    /**
     * Получить все доступные аддоны для организации
     */
    public function getAvailableAddons(): array
    {
        $addons = SaasFeature::findAddons();
        $available = [];

        foreach ($addons as $addon) {
            // Пропускаем уже имеющиеся функции
            if ($this->hasFeature($addon->code)) {
                continue;
            }

            // Проверяем зависимости
            if ($addon->hasDependencies()) {
                $deps = $addon->getDependenciesArray();
                $allDepsOk = true;
                foreach ($deps as $depCode) {
                    if (!$this->hasFeature($depCode)) {
                        $allDepsOk = false;
                        break;
                    }
                }
                if (!$allDepsOk) {
                    continue;
                }
            }

            $available[] = $addon;
        }

        return $available;
    }

    /**
     * Получить все функции текущего плана
     */
    public function getPlanFeatures(): array
    {
        if (!$this->plan) {
            return [];
        }

        $cacheKey = "plan_features_{$this->plan->id}";

        return Yii::$app->cache->getOrSet($cacheKey, function () {
            return SaasPlanFeature::findByPlan($this->plan->id);
        }, self::CACHE_DURATION, new TagDependency(['tags' => self::CACHE_TAG]));
    }

    /**
     * Получить сводку по функциям для UI
     */
    public function getFeaturesOverview(): array
    {
        $categories = SaasFeature::getCategoryList();
        $overview = [];

        foreach ($categories as $categoryCode => $categoryName) {
            $features = SaasFeature::findByCategory($categoryCode);
            $overview[$categoryCode] = [
                'name' => $categoryName,
                'features' => [],
            ];

            foreach ($features as $feature) {
                $overview[$categoryCode]['features'][] = [
                    'code' => $feature->code,
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'enabled' => $this->hasFeature($feature->code),
                    'is_addon' => $feature->isAddon(),
                    'addon_price' => $feature->addon_price_monthly,
                    'can_trial' => $this->canStartTrial($feature->code),
                    'value' => $this->getFeatureValue($feature->code),
                    'limit' => $this->getFeatureLimit($feature->code),
                ];
            }
        }

        return $overview;
    }

    /**
     * Получить кастомные настройки функций организации
     */
    private function getOrganizationCustomFeatures(): array
    {
        $info = $this->organization->getInfoJson();
        return $info['custom_features'] ?? [];
    }

    /**
     * Инвалидировать кэш
     */
    public static function invalidateCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
    }

    // ==================== ADDON METHODS ====================

    /**
     * Получить активные аддоны организации
     */
    public function getActiveAddons(): array
    {
        if ($this->activeAddons === null) {
            $this->activeAddons = OrganizationAddon::findActiveByOrganization($this->organization->id);
        }
        return $this->activeAddons;
    }

    /**
     * Проверить, есть ли активный аддон для функции
     */
    public function hasAddon(string $code): bool
    {
        return OrganizationAddon::hasActiveAddon($this->organization->id, $code);
    }

    /**
     * Получить значение из аддона
     */
    private function getAddonValue(string $code, ?string $key = null)
    {
        $addon = OrganizationAddon::findByFeatureCode($this->organization->id, $code);

        if (!$addon || !$addon->isActive()) {
            return null;
        }

        $values = $addon->getValueArray();

        if ($key) {
            return $values[$key] ?? null;
        }

        // Возвращаем либо все значения, либо true для boolean функций
        return !empty($values) ? $values : true;
    }

    /**
     * Получить аддон по коду функции
     */
    public function getAddon(string $code): ?OrganizationAddon
    {
        return OrganizationAddon::findByFeatureCode($this->organization->id, $code);
    }

    /**
     * Получить все докупленные аддоны (включая неактивные)
     */
    public function getAllAddons(): array
    {
        return OrganizationAddon::find()
            ->with('feature')
            ->where(['organization_id' => $this->organization->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    /**
     * Получить информацию об аддонах для UI
     */
    public function getAddonsOverview(): array
    {
        $activeAddons = $this->getActiveAddons();
        $allAddons = $this->getAllAddons();

        return [
            'active_count' => count($activeAddons),
            'total_count' => count($allAddons),
            'active' => array_map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->getFullName(),
                    'feature_code' => $addon->feature->code ?? null,
                    'status' => $addon->status,
                    'status_label' => $addon->getStatusLabel(),
                    'expires_at' => $addon->expires_at,
                    'days_remaining' => $addon->getDaysRemaining(),
                    'is_trial' => $addon->isTrial(),
                    'price' => $addon->getFormattedPrice(),
                ];
            }, $activeAddons),
        ];
    }

    /**
     * Получить организацию
     */
    public function getOrganization(): Organizations
    {
        return $this->organization;
    }

    /**
     * Получить подписку
     */
    public function getSubscription(): ?OrganizationSubscription
    {
        return $this->subscription;
    }

    /**
     * Получить тарифный план
     */
    public function getPlan(): ?SaasPlan
    {
        return $this->plan;
    }
}
