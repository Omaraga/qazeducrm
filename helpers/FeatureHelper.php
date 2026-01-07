<?php

namespace app\helpers;

use app\services\FeatureService;
use app\services\SubscriptionLimitService;

/**
 * FeatureHelper - хелпер для проверки функций и лимитов во views.
 *
 * Использование:
 * ```php
 * // Проверка наличия функции
 * if (FeatureHelper::has('analytics')) { ... }
 *
 * // Получение значения функции
 * $limit = FeatureHelper::get('sms', 'monthly_limit');
 *
 * // Получение лимита
 * $pupilLimit = FeatureHelper::limit('max_pupils');
 *
 * // Можно ли добавить ученика
 * if (FeatureHelper::canAddPupil()) { ... }
 *
 * // Информация об использовании
 * $usage = FeatureHelper::getUsageInfo();
 * ```
 */
class FeatureHelper
{
    private static ?FeatureService $featureService = null;
    private static ?SubscriptionLimitService $limitService = null;

    /**
     * Получить FeatureService
     */
    private static function getFeatureService(): ?FeatureService
    {
        if (self::$featureService === null) {
            self::$featureService = FeatureService::forCurrentOrganization();
        }
        return self::$featureService;
    }

    /**
     * Получить SubscriptionLimitService
     */
    private static function getLimitService(): ?SubscriptionLimitService
    {
        if (self::$limitService === null) {
            self::$limitService = SubscriptionLimitService::forCurrentOrganization();
        }
        return self::$limitService;
    }

    /**
     * Сбросить кэш сервисов (для тестов или смены организации)
     */
    public static function reset(): void
    {
        self::$featureService = null;
        self::$limitService = null;
    }

    // ==================== FEATURES ====================

    /**
     * Проверить наличие функции
     */
    public static function has(string $code): bool
    {
        $service = self::getFeatureService();
        return $service ? $service->hasFeature($code) : false;
    }

    /**
     * Получить значение функции
     */
    public static function get(string $code, ?string $key = null)
    {
        $service = self::getFeatureService();
        return $service ? $service->getFeatureValue($code, $key) : null;
    }

    /**
     * Получить лимит функции
     */
    public static function featureLimit(string $code): ?int
    {
        $service = self::getFeatureService();
        return $service ? $service->getFeatureLimit($code) : null;
    }

    /**
     * Можно ли начать trial для функции
     */
    public static function canTrial(string $code): bool
    {
        $service = self::getFeatureService();
        return $service ? $service->canStartTrial($code) : false;
    }

    // ==================== LIMITS ====================

    /**
     * Получить лимит тарифа
     */
    public static function limit(string $field): int
    {
        $service = self::getLimitService();
        return $service ? $service->getLimit($field) : 0;
    }

    /**
     * Можно ли добавить ученика
     */
    public static function canAddPupil(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->canAddPupil() : false;
    }

    /**
     * Можно ли добавить группу
     */
    public static function canAddGroup(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->canAddGroup() : false;
    }

    /**
     * Можно ли добавить учителя
     */
    public static function canAddTeacher(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->canAddTeacher() : false;
    }

    /**
     * Можно ли добавить админа
     */
    public static function canAddAdmin(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->canAddAdmin() : false;
    }

    /**
     * Можно ли добавить филиал
     */
    public static function canAddBranch(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->canAddBranch() : false;
    }

    /**
     * Получить информацию об использовании лимитов
     */
    public static function getUsageInfo(): array
    {
        $service = self::getLimitService();
        return $service ? $service->getUsageInfo() : [];
    }

    /**
     * Получить процент использования
     */
    public static function usagePercent(string $field): int
    {
        $service = self::getLimitService();
        return $service ? $service->getUsagePercent($field) : 0;
    }

    /**
     * Есть ли активная подписка
     */
    public static function hasSubscription(): bool
    {
        $service = self::getLimitService();
        return $service ? $service->hasActiveSubscription() : false;
    }

    /**
     * Проверить наличие функции через план (SubscriptionLimitService)
     */
    public static function hasPlanFeature(string $feature): bool
    {
        $service = self::getLimitService();
        return $service ? $service->hasFeature($feature) : false;
    }

    // ==================== HELPERS ====================

    /**
     * Форматировать лимит (0 = безлимит)
     */
    public static function formatLimit(?int $limit): string
    {
        if ($limit === null || $limit === 0) {
            return '∞';
        }
        return (string)$limit;
    }

    /**
     * Цвет прогресс-бара по проценту использования
     */
    public static function getProgressColor(int $percent): string
    {
        if ($percent >= 100) {
            return 'danger';
        }
        if ($percent >= 90) {
            return 'warning';
        }
        if ($percent >= 70) {
            return 'primary';
        }
        return 'success';
    }

    /**
     * CSS класс для прогресс-бара по проценту
     */
    public static function getProgressClass(int $percent): string
    {
        $color = self::getProgressColor($percent);
        return match ($color) {
            'danger' => 'bg-danger-500',
            'warning' => 'bg-warning-500',
            'primary' => 'bg-primary-500',
            default => 'bg-success-500',
        };
    }
}
