<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationAddon;
use app\models\SaasFeature;

/**
 * Сервис для управления пробными периодами аддонов.
 *
 * Использование:
 * ```php
 * // Создать сервис для текущей организации
 * $trialService = AddonTrialService::forCurrentOrganization();
 *
 * // Проверить, можно ли начать trial
 * if ($trialService->canStartTrial('whatsapp')) {
 *     $trialService->startTrial('whatsapp');
 * }
 *
 * // Получить информацию о доступных trial
 * $availableTrials = $trialService->getAvailableTrials();
 * ```
 */
class AddonTrialService
{
    private Organizations $organization;

    public function __construct(Organizations $organization)
    {
        $this->organization = $organization->getHeadOrganization();
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
     * Создать сервис для организации по ID
     */
    public static function forOrganization(int $organizationId): ?self
    {
        $org = Organizations::findOne($organizationId);
        return $org ? new self($org) : null;
    }

    // ==================== TRIAL MANAGEMENT ====================

    /**
     * Проверить, можно ли начать trial для функции
     */
    public function canStartTrial(string $featureCode): bool
    {
        $feature = SaasFeature::findByCode($featureCode);
        if (!$feature) {
            return false;
        }

        // Проверяем, доступен ли trial для этой функции
        if (!$feature->isTrialAvailable()) {
            return false;
        }

        // Проверяем, не использовал ли пользователь уже trial для этой функции
        if ($this->hasUsedTrial($featureCode)) {
            return false;
        }

        // Проверяем, нет ли уже активного аддона
        if (OrganizationAddon::hasActiveAddon($this->organization->id, $featureCode)) {
            return false;
        }

        // Проверяем зависимости
        if (!$this->checkDependencies($feature)) {
            return false;
        }

        return true;
    }

    /**
     * Начать пробный период для функции
     */
    public function startTrial(string $featureCode): ?OrganizationAddon
    {
        if (!$this->canStartTrial($featureCode)) {
            return null;
        }

        $feature = SaasFeature::findByCode($featureCode);
        if (!$feature) {
            return null;
        }

        $addon = new OrganizationAddon();
        $addon->organization_id = $this->organization->id;
        $addon->feature_id = $feature->id;
        $addon->quantity = 1;
        $addon->price = 0; // Trial бесплатен
        $addon->created_by = Yii::$app->user->id ?? null;

        // Устанавливаем конфиг по умолчанию для лимитных аддонов
        if ($feature->type === SaasFeature::TYPE_LIMIT) {
            $this->setTrialLimitConfig($addon, $feature);
        }

        if (!$addon->startTrial($feature->trial_days)) {
            return null;
        }

        // Логируем событие
        $this->logTrialStart($addon, $feature);

        return $addon;
    }

    /**
     * Проверить, использовал ли пользователь trial для функции
     */
    public function hasUsedTrial(string $featureCode): bool
    {
        $feature = SaasFeature::findByCode($featureCode);
        if (!$feature) {
            return false;
        }

        // Ищем любой аддон (включая истёкший/отменённый) с этой функцией
        return OrganizationAddon::find()
            ->alias('oa')
            ->innerJoin(['f' => SaasFeature::tableName()], 'oa.feature_id = f.id')
            ->where(['oa.organization_id' => $this->organization->id])
            ->andWhere(['f.code' => $featureCode])
            ->andWhere(['oa.status' => OrganizationAddon::STATUS_TRIAL]) // Был trial
            ->exists();
    }

    /**
     * Получить активный trial для функции
     */
    public function getActiveTrial(string $featureCode): ?OrganizationAddon
    {
        $addon = OrganizationAddon::findByFeatureCode($this->organization->id, $featureCode);

        if ($addon && $addon->isTrial() && $addon->isActive()) {
            return $addon;
        }

        return null;
    }

    /**
     * Получить все активные trial организации
     */
    public function getActiveTrials(): array
    {
        return OrganizationAddon::find()
            ->with('feature')
            ->where(['organization_id' => $this->organization->id])
            ->andWhere(['status' => OrganizationAddon::STATUS_TRIAL])
            ->all();
    }

    /**
     * Отменить trial
     */
    public function cancelTrial(string $featureCode): bool
    {
        $addon = $this->getActiveTrial($featureCode);
        if (!$addon) {
            return false;
        }

        return $addon->cancel();
    }

    /**
     * Конвертировать trial в платную подписку
     */
    public function convertTrialToPaid(
        string $featureCode,
        string $period = OrganizationAddon::PERIOD_MONTHLY
    ): bool {
        $addon = $this->getActiveTrial($featureCode);
        if (!$addon) {
            return false;
        }

        return $addon->activate($period);
    }

    // ==================== AVAILABLE TRIALS ====================

    /**
     * Получить список доступных для trial функций
     */
    public function getAvailableTrials(): array
    {
        $features = SaasFeature::find()
            ->where(['is_addon' => 1, 'trial_available' => 1, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        $available = [];
        foreach ($features as $feature) {
            if ($this->canStartTrial($feature->code)) {
                $available[] = [
                    'feature' => $feature,
                    'trial_days' => $feature->trial_days,
                    'price_after' => $feature->addon_price_monthly,
                ];
            }
        }

        return $available;
    }

    /**
     * Получить информацию о trial для функции
     */
    public function getTrialInfo(string $featureCode): array
    {
        $feature = SaasFeature::findByCode($featureCode);
        if (!$feature) {
            return [
                'available' => false,
                'reason' => 'feature_not_found',
            ];
        }

        // Проверяем активный trial
        $activeTrial = $this->getActiveTrial($featureCode);
        if ($activeTrial) {
            return [
                'available' => false,
                'reason' => 'trial_active',
                'addon' => $activeTrial,
                'days_remaining' => $activeTrial->getDaysRemaining(),
                'ends_at' => $activeTrial->trial_ends_at,
            ];
        }

        // Проверяем активный платный аддон
        $activeAddon = OrganizationAddon::findByFeatureCode($this->organization->id, $featureCode);
        if ($activeAddon && $activeAddon->isActive() && !$activeAddon->isTrial()) {
            return [
                'available' => false,
                'reason' => 'already_active',
                'addon' => $activeAddon,
            ];
        }

        // Проверяем использованный trial
        if ($this->hasUsedTrial($featureCode)) {
            return [
                'available' => false,
                'reason' => 'trial_used',
                'feature' => $feature,
            ];
        }

        // Trial доступен?
        if (!$feature->isTrialAvailable()) {
            return [
                'available' => false,
                'reason' => 'trial_not_available',
                'feature' => $feature,
            ];
        }

        // Проверяем зависимости
        if (!$this->checkDependencies($feature)) {
            return [
                'available' => false,
                'reason' => 'dependencies_not_met',
                'feature' => $feature,
                'dependencies' => $feature->getDependenciesArray(),
            ];
        }

        return [
            'available' => true,
            'feature' => $feature,
            'trial_days' => $feature->trial_days,
            'price_monthly' => $feature->addon_price_monthly,
            'price_yearly' => $feature->addon_price_yearly,
        ];
    }

    // ==================== EXPIRATION HANDLING ====================

    /**
     * Обработать истёкшие trial (для cron)
     */
    public static function processExpiredTrials(): array
    {
        $result = [
            'processed' => 0,
            'errors' => [],
        ];

        $expiredTrials = OrganizationAddon::find()
            ->where(['status' => OrganizationAddon::STATUS_TRIAL])
            ->andWhere(['<', 'trial_ends_at', date('Y-m-d H:i:s')])
            ->all();

        foreach ($expiredTrials as $addon) {
            try {
                if ($addon->markExpired()) {
                    $result['processed']++;

                    // Отправляем уведомление
                    self::notifyTrialExpired($addon);
                }
            } catch (\Exception $e) {
                $result['errors'][] = [
                    'addon_id' => $addon->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    /**
     * Получить истекающие trial (для уведомлений)
     */
    public static function getExpiringTrials(int $days = 3): array
    {
        $deadline = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        return OrganizationAddon::find()
            ->with(['organization', 'feature'])
            ->where(['status' => OrganizationAddon::STATUS_TRIAL])
            ->andWhere(['>', 'trial_ends_at', date('Y-m-d H:i:s')])
            ->andWhere(['<=', 'trial_ends_at', $deadline])
            ->all();
    }

    /**
     * Отправить напоминания об истекающих trial
     */
    public static function sendExpirationReminders(int $days = 3): array
    {
        $result = [
            'sent' => 0,
            'errors' => [],
        ];

        $expiringTrials = self::getExpiringTrials($days);

        foreach ($expiringTrials as $addon) {
            try {
                self::notifyTrialExpiring($addon);
                $result['sent']++;
            } catch (\Exception $e) {
                $result['errors'][] = [
                    'addon_id' => $addon->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    // ==================== HELPERS ====================

    /**
     * Проверить зависимости функции
     */
    private function checkDependencies(SaasFeature $feature): bool
    {
        $dependencies = $feature->getDependenciesArray();
        if (empty($dependencies)) {
            return true;
        }

        $limitService = SubscriptionLimitService::forCurrentOrganization();
        if (!$limitService) {
            return false;
        }

        foreach ($dependencies as $depCode) {
            if (!$limitService->hasFeature($depCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Установить конфиг лимита для trial
     */
    private function setTrialLimitConfig(OrganizationAddon $addon, SaasFeature $feature): void
    {
        // Получаем конфиг из default_value функции
        if ($feature->default_value) {
            $config = json_decode($feature->default_value, true);
            if (is_array($config)) {
                // Для trial даём минимальное значение
                if (isset($config['limit_value'])) {
                    $config['limit_value'] = min($config['limit_value'], 10); // Максимум 10 для trial
                }
                $addon->value = $config;
            }
        }
    }

    /**
     * Логировать начало trial
     */
    private function logTrialStart(OrganizationAddon $addon, SaasFeature $feature): void
    {
        Yii::info(sprintf(
            'Trial started: org=%d, feature=%s, ends=%s',
            $this->organization->id,
            $feature->code,
            $addon->trial_ends_at
        ), 'subscription');

        // Можно добавить запись в activity log
        // OrganizationActivityLog::log(...)
    }

    /**
     * Уведомить об истечении trial
     */
    private static function notifyTrialExpired(OrganizationAddon $addon): void
    {
        // TODO: Отправить email/notification
        Yii::info(sprintf(
            'Trial expired: org=%d, addon=%d',
            $addon->organization_id,
            $addon->id
        ), 'subscription');
    }

    /**
     * Уведомить о скором истечении trial
     */
    private static function notifyTrialExpiring(OrganizationAddon $addon): void
    {
        // TODO: Отправить email/notification
        Yii::info(sprintf(
            'Trial expiring soon: org=%d, addon=%d, ends=%s',
            $addon->organization_id,
            $addon->id,
            $addon->trial_ends_at
        ), 'subscription');
    }

    /**
     * Получить организацию
     */
    public function getOrganization(): Organizations
    {
        return $this->organization;
    }

    /**
     * Статистика trial для организации
     */
    public function getTrialStatistics(): array
    {
        $activeTrials = $this->getActiveTrials();
        $expiringSoon = array_filter($activeTrials, fn($a) => $a->isExpiringSoon());

        return [
            'active_count' => count($activeTrials),
            'expiring_soon' => count($expiringSoon),
            'active_trials' => $activeTrials,
            'available_trials' => $this->getAvailableTrials(),
        ];
    }
}
