<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationSubscription;

/**
 * Сервис управления доступом на основе подписки
 * Реализует graceful degradation при истечении подписки
 */
class SubscriptionAccessService
{
    // Режимы доступа
    const MODE_FULL = 'full';           // Полный доступ
    const MODE_LIMITED = 'limited';     // Ограниченный (нельзя создавать новые записи)
    const MODE_READ_ONLY = 'read_only'; // Только просмотр
    const MODE_BLOCKED = 'blocked';     // Полная блокировка

    // Grace period настройки (дни)
    const GRACE_PERIOD_DAYS = 3;        // Дней после истечения до read_only
    const READ_ONLY_PERIOD_DAYS = 7;    // Дней в read_only до блокировки

    private ?Organizations $organization;
    private ?OrganizationSubscription $subscription;

    public function __construct(?Organizations $organization = null)
    {
        $this->organization = $organization ?? Organizations::getCurrentOrganization();
        $this->subscription = $this->organization?->getActiveSubscription();
    }

    /**
     * Создать экземпляр для текущей организации
     */
    public static function forCurrentOrganization(): self
    {
        return new self();
    }

    /**
     * Создать экземпляр для указанной организации
     */
    public static function forOrganization(Organizations $organization): self
    {
        return new self($organization);
    }

    /**
     * Получить текущий режим доступа
     */
    public function getAccessMode(): string
    {
        if (!$this->organization) {
            return self::MODE_BLOCKED;
        }

        if (!$this->subscription) {
            // Нет подписки - проверяем, есть ли триал или бесплатный план
            return $this->getAccessModeWithoutSubscription();
        }

        // Проверяем статус подписки
        switch ($this->subscription->status) {
            case OrganizationSubscription::STATUS_ACTIVE:
                return self::MODE_FULL;

            case OrganizationSubscription::STATUS_TRIAL:
                return $this->getTrialAccessMode();

            case OrganizationSubscription::STATUS_EXPIRED:
                return $this->getExpiredAccessMode();

            case OrganizationSubscription::STATUS_CANCELLED:
                return self::MODE_READ_ONLY;

            default:
                return self::MODE_LIMITED;
        }
    }

    /**
     * Получить режим доступа когда нет подписки
     */
    private function getAccessModeWithoutSubscription(): string
    {
        // Проверяем, была ли когда-то подписка
        $lastSubscription = OrganizationSubscription::find()
            ->where(['organization_id' => $this->organization->id])
            ->orderBy(['expires_at' => SORT_DESC])
            ->one();

        if (!$lastSubscription) {
            // Никогда не было подписки - возможно новая организация
            // Даём ограниченный доступ для ознакомления
            return self::MODE_LIMITED;
        }

        // Была подписка, но истекла
        $daysSinceExpired = $this->getDaysSinceExpired($lastSubscription);

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS) {
            return self::MODE_LIMITED;
        }

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS + self::READ_ONLY_PERIOD_DAYS) {
            return self::MODE_READ_ONLY;
        }

        return self::MODE_BLOCKED;
    }

    /**
     * Получить режим доступа для триала
     */
    private function getTrialAccessMode(): string
    {
        if ($this->subscription->isExpired()) {
            // Триал истёк
            $daysSinceExpired = $this->getDaysSinceExpired($this->subscription);

            if ($daysSinceExpired <= 1) {
                return self::MODE_LIMITED; // 1 день после триала
            }

            return self::MODE_READ_ONLY;
        }

        return self::MODE_FULL;
    }

    /**
     * Получить режим доступа для истёкшей подписки
     */
    private function getExpiredAccessMode(): string
    {
        // Проверяем grace period
        if ($this->subscription->grace_period_ends_at) {
            $graceEndsAt = strtotime($this->subscription->grace_period_ends_at);

            if (time() < $graceEndsAt) {
                return self::MODE_LIMITED;
            }
        }

        $daysSinceExpired = $this->getDaysSinceExpired($this->subscription);

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS) {
            return self::MODE_LIMITED;
        }

        if ($daysSinceExpired <= self::GRACE_PERIOD_DAYS + self::READ_ONLY_PERIOD_DAYS) {
            return self::MODE_READ_ONLY;
        }

        return self::MODE_BLOCKED;
    }

    /**
     * Рассчитать дни с момента истечения
     */
    private function getDaysSinceExpired(OrganizationSubscription $subscription): int
    {
        if (!$subscription->expires_at) {
            return 0;
        }

        $expiresAt = strtotime($subscription->expires_at);
        $now = time();

        if ($now <= $expiresAt) {
            return 0;
        }

        return (int) floor(($now - $expiresAt) / 86400);
    }

    /**
     * Можно ли создавать новые записи
     */
    public function canCreate(): bool
    {
        return $this->getAccessMode() === self::MODE_FULL;
    }

    /**
     * Можно ли редактировать записи
     */
    public function canUpdate(): bool
    {
        $mode = $this->getAccessMode();
        return in_array($mode, [self::MODE_FULL, self::MODE_LIMITED]);
    }

    /**
     * Можно ли просматривать записи
     */
    public function canView(): bool
    {
        $mode = $this->getAccessMode();
        return $mode !== self::MODE_BLOCKED;
    }

    /**
     * Можно ли удалять записи
     */
    public function canDelete(): bool
    {
        return $this->getAccessMode() === self::MODE_FULL;
    }

    /**
     * Можно ли экспортировать данные
     */
    public function canExport(): bool
    {
        $mode = $this->getAccessMode();
        return in_array($mode, [self::MODE_FULL, self::MODE_LIMITED, self::MODE_READ_ONLY]);
    }

    /**
     * Находится ли в grace периоде
     */
    public function isInGracePeriod(): bool
    {
        if (!$this->subscription) {
            return false;
        }

        if ($this->subscription->status !== OrganizationSubscription::STATUS_EXPIRED) {
            return false;
        }

        $daysSinceExpired = $this->getDaysSinceExpired($this->subscription);
        return $daysSinceExpired <= self::GRACE_PERIOD_DAYS;
    }

    /**
     * Дней до блокировки
     */
    public function getDaysUntilBlock(): ?int
    {
        $mode = $this->getAccessMode();

        if ($mode === self::MODE_FULL) {
            // Если подписка активна, считаем дни до истечения
            if ($this->subscription && $this->subscription->expires_at) {
                $daysRemaining = $this->subscription->getDaysRemaining();
                return $daysRemaining + self::GRACE_PERIOD_DAYS + self::READ_ONLY_PERIOD_DAYS;
            }
            return null;
        }

        if ($mode === self::MODE_LIMITED) {
            $daysSinceExpired = $this->subscription
                ? $this->getDaysSinceExpired($this->subscription)
                : 0;
            return self::GRACE_PERIOD_DAYS + self::READ_ONLY_PERIOD_DAYS - $daysSinceExpired;
        }

        if ($mode === self::MODE_READ_ONLY) {
            $daysSinceExpired = $this->subscription
                ? $this->getDaysSinceExpired($this->subscription)
                : self::GRACE_PERIOD_DAYS;
            $daysInReadOnly = $daysSinceExpired - self::GRACE_PERIOD_DAYS;
            return max(0, self::READ_ONLY_PERIOD_DAYS - $daysInReadOnly);
        }

        return 0;
    }

    /**
     * Дней до перехода в read_only
     */
    public function getDaysUntilReadOnly(): ?int
    {
        $mode = $this->getAccessMode();

        if ($mode === self::MODE_FULL && $this->subscription) {
            $daysRemaining = $this->subscription->getDaysRemaining();
            return $daysRemaining + self::GRACE_PERIOD_DAYS;
        }

        if ($mode === self::MODE_LIMITED) {
            $daysSinceExpired = $this->subscription
                ? $this->getDaysSinceExpired($this->subscription)
                : 0;
            return max(0, self::GRACE_PERIOD_DAYS - $daysSinceExpired);
        }

        return null;
    }

    /**
     * Получить сообщение о текущем режиме доступа
     */
    public function getAccessMessage(): ?string
    {
        $mode = $this->getAccessMode();

        switch ($mode) {
            case self::MODE_FULL:
                return null;

            case self::MODE_LIMITED:
                $daysUntilReadOnly = $this->getDaysUntilReadOnly();
                if ($daysUntilReadOnly !== null && $daysUntilReadOnly <= 3) {
                    return "Подписка истекла. Создание новых записей ограничено. " .
                           "Через {$daysUntilReadOnly} дн. доступ будет только для чтения.";
                }
                return "Подписка истекла. Создание новых записей ограничено. Продлите подписку.";

            case self::MODE_READ_ONLY:
                $daysUntilBlock = $this->getDaysUntilBlock();
                return "Режим только для чтения. Через {$daysUntilBlock} дн. доступ будет заблокирован. " .
                       "Вы можете экспортировать данные.";

            case self::MODE_BLOCKED:
                return "Доступ заблокирован. Для продолжения работы необходимо оплатить подписку.";

            default:
                return null;
        }
    }

    /**
     * Получить название режима
     */
    public function getAccessModeLabel(): string
    {
        return match ($this->getAccessMode()) {
            self::MODE_FULL => 'Полный доступ',
            self::MODE_LIMITED => 'Ограниченный доступ',
            self::MODE_READ_ONLY => 'Только чтение',
            self::MODE_BLOCKED => 'Заблокирован',
            default => 'Неизвестно',
        };
    }

    /**
     * Получить CSS класс для режима
     */
    public function getAccessModeBadgeClass(): string
    {
        return match ($this->getAccessMode()) {
            self::MODE_FULL => 'badge-success',
            self::MODE_LIMITED => 'badge-warning',
            self::MODE_READ_ONLY => 'badge-secondary',
            self::MODE_BLOCKED => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Получить иконку для режима
     */
    public function getAccessModeIcon(): string
    {
        return match ($this->getAccessMode()) {
            self::MODE_FULL => 'check-circle',
            self::MODE_LIMITED => 'exclamation-triangle',
            self::MODE_READ_ONLY => 'eye',
            self::MODE_BLOCKED => 'lock',
            default => 'question-circle',
        };
    }

    /**
     * Проверить, нужно ли показывать баннер с предупреждением
     */
    public function shouldShowWarningBanner(): bool
    {
        $mode = $this->getAccessMode();

        if ($mode !== self::MODE_FULL) {
            return true;
        }

        // Показываем баннер если подписка скоро истечёт
        if ($this->subscription && $this->subscription->isExpiringSoon(7)) {
            return true;
        }

        return false;
    }

    /**
     * Получить данные для баннера
     */
    public function getWarningBannerData(): ?array
    {
        $mode = $this->getAccessMode();

        if ($mode === self::MODE_BLOCKED) {
            return [
                'type' => 'danger',
                'icon' => 'lock',
                'title' => 'Доступ заблокирован',
                'message' => 'Ваша подписка истекла. Для продолжения работы необходимо оплатить подписку.',
                'action' => [
                    'label' => 'Оплатить подписку',
                    'url' => '/subscription/renew',
                ],
            ];
        }

        if ($mode === self::MODE_READ_ONLY) {
            $daysUntilBlock = $this->getDaysUntilBlock();
            return [
                'type' => 'warning',
                'icon' => 'eye',
                'title' => 'Режим только для чтения',
                'message' => "Вы можете только просматривать и экспортировать данные. " .
                             "Через {$daysUntilBlock} дн. доступ будет заблокирован.",
                'action' => [
                    'label' => 'Продлить подписку',
                    'url' => '/subscription/renew',
                ],
            ];
        }

        if ($mode === self::MODE_LIMITED) {
            $daysUntilReadOnly = $this->getDaysUntilReadOnly();
            return [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Ограниченный доступ',
                'message' => "Создание новых записей ограничено. " .
                             "Через {$daysUntilReadOnly} дн. режим сменится на 'только чтение'.",
                'action' => [
                    'label' => 'Продлить подписку',
                    'url' => '/subscription/renew',
                ],
            ];
        }

        // Подписка скоро истекает
        if ($this->subscription && $this->subscription->isExpiringSoon(7)) {
            $daysRemaining = $this->subscription->getDaysRemaining();
            return [
                'type' => 'info',
                'icon' => 'clock',
                'title' => 'Подписка истекает',
                'message' => "Ваша подписка истекает через {$daysRemaining} дн. " .
                             "Продлите сейчас, чтобы избежать ограничений.",
                'action' => [
                    'label' => 'Продлить',
                    'url' => '/subscription/renew',
                ],
                'dismissible' => true,
            ];
        }

        return null;
    }

    /**
     * Запустить grace период
     */
    public function startGracePeriod(): bool
    {
        if (!$this->subscription) {
            return false;
        }

        $gracePeriodEndsAt = date('Y-m-d H:i:s', strtotime("+{self::GRACE_PERIOD_DAYS} days"));

        $this->subscription->grace_period_ends_at = $gracePeriodEndsAt;
        $this->subscription->access_mode = self::MODE_LIMITED;

        return $this->subscription->save(false, ['grace_period_ends_at', 'access_mode']);
    }

    /**
     * Обновить режим доступа в БД
     */
    public function updateAccessModeInDb(): bool
    {
        if (!$this->subscription) {
            return false;
        }

        $currentMode = $this->getAccessMode();

        if ($this->subscription->access_mode !== $currentMode) {
            $this->subscription->access_mode = $currentMode;
            return $this->subscription->save(false, ['access_mode']);
        }

        return true;
    }

    /**
     * Получить статистику по режимам доступа (для SuperAdmin)
     */
    public static function getAccessModeStatistics(): array
    {
        $stats = [
            self::MODE_FULL => 0,
            self::MODE_LIMITED => 0,
            self::MODE_READ_ONLY => 0,
            self::MODE_BLOCKED => 0,
        ];

        $organizations = Organizations::find()
            ->where(['is_deleted' => 0])
            ->all();

        foreach ($organizations as $org) {
            $service = new self($org);
            $mode = $service->getAccessMode();
            $stats[$mode]++;
        }

        return $stats;
    }

    /**
     * Получить организации с определённым режимом доступа
     */
    public static function getOrganizationsByAccessMode(string $mode): array
    {
        $result = [];

        $organizations = Organizations::find()
            ->where(['is_deleted' => 0])
            ->all();

        foreach ($organizations as $org) {
            $service = new self($org);
            if ($service->getAccessMode() === $mode) {
                $result[] = [
                    'organization' => $org,
                    'subscription' => $service->subscription,
                    'daysUntilBlock' => $service->getDaysUntilBlock(),
                ];
            }
        }

        return $result;
    }
}
