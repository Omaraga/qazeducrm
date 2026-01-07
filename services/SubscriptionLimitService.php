<?php

namespace app\services;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationAddon;
use app\models\Pupil;
use app\models\User;
use app\models\Group;
use app\helpers\OrganizationRoles;
use app\models\relations\UserOrganization;
use Yii;

/**
 * Сервис для проверки лимитов подписки.
 *
 * Проверяет, не превышены ли лимиты тарифного плана организации
 * при создании учеников, учителей, групп, админов и филиалов.
 */
class SubscriptionLimitService
{
    private Organizations $organization;
    private ?OrganizationSubscription $subscription;
    private ?array $activeAddons = null;

    public function __construct(Organizations $organization)
    {
        $this->organization = $organization->getHeadOrganization();
        $this->subscription = $this->organization->getActiveSubscription();
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
     * Создать сервис для указанной организации
     */
    public static function forOrganization(Organizations $organization): self
    {
        return new self($organization);
    }

    /**
     * Есть ли активная подписка
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription !== null && $this->subscription->isActive();
    }

    /**
     * Получить лимит (базовый + бонусы от аддонов)
     */
    public function getLimit(string $field): int
    {
        if (!$this->subscription) {
            return 0;
        }

        // Базовый лимит из плана
        $baseLimit = $this->subscription->getLimit($field);

        // Если безлимит - возвращаем сразу
        if ($baseLimit === 0) {
            return 0;
        }

        // Добавляем бонусы от аддонов
        $addonBonus = $this->getAddonLimitBonus($field);

        return $baseLimit + $addonBonus;
    }

    /**
     * Получить базовый лимит (без аддонов)
     */
    public function getBaseLimit(string $field): int
    {
        if (!$this->subscription) {
            return 0;
        }
        return $this->subscription->getLimit($field);
    }

    /**
     * Получить бонус лимита от аддонов
     */
    public function getAddonLimitBonus(string $field): int
    {
        return OrganizationAddon::getTotalLimitBonus($this->organization->id, $field);
    }

    /**
     * Получить все активные аддоны организации
     */
    public function getActiveAddons(): array
    {
        if ($this->activeAddons === null) {
            $this->activeAddons = OrganizationAddon::findActiveByOrganization($this->organization->id);
        }
        return $this->activeAddons;
    }

    /**
     * Проверить, есть ли у организации активный аддон
     */
    public function hasAddon(string $featureCode): bool
    {
        return OrganizationAddon::hasActiveAddon($this->organization->id, $featureCode);
    }

    /**
     * Проверить лимит (0 = безлимит)
     */
    public function isLimitReached(string $field, int $currentCount): bool
    {
        $limit = $this->getLimit($field);
        if ($limit === 0) {
            return false; // Безлимит
        }
        return $currentCount >= $limit;
    }

    // ==================== PUPILS ====================

    /**
     * Текущее количество учеников в организации (включая филиалы)
     */
    public function getPupilCount(): int
    {
        $orgIds = $this->getAllOrganizationIds();
        return Pupil::find()
            ->andWhere(['in', 'organization_id', $orgIds])
            ->andWhere(['is_deleted' => 0])
            ->count();
    }

    /**
     * Можно ли добавить ученика
     */
    public function canAddPupil(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }
        return !$this->isLimitReached('max_pupils', $this->getPupilCount());
    }

    /**
     * Сколько учеников ещё можно добавить
     */
    public function getRemainingPupils(): ?int
    {
        $limit = $this->getLimit('max_pupils');
        if ($limit === 0) {
            return null; // Безлимит
        }
        return max(0, $limit - $this->getPupilCount());
    }

    // ==================== TEACHERS ====================

    /**
     * Текущее количество учителей
     */
    public function getTeacherCount(): int
    {
        $orgIds = $this->getAllOrganizationIds();
        return UserOrganization::find()
            ->andWhere(['in', 'target_id', $orgIds])
            ->andWhere(['role' => OrganizationRoles::TEACHER])
            ->andWhere(['<>', 'is_deleted', 1])
            ->andWhere(['state' => UserOrganization::STATE_ACTIVE])
            ->count();
    }

    /**
     * Можно ли добавить учителя
     */
    public function canAddTeacher(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }
        return !$this->isLimitReached('max_teachers', $this->getTeacherCount());
    }

    /**
     * Сколько учителей ещё можно добавить
     */
    public function getRemainingTeachers(): ?int
    {
        $limit = $this->getLimit('max_teachers');
        if ($limit === 0) {
            return null;
        }
        return max(0, $limit - $this->getTeacherCount());
    }

    // ==================== GROUPS ====================

    /**
     * Текущее количество групп
     */
    public function getGroupCount(): int
    {
        $orgIds = $this->getAllOrganizationIds();
        return Group::find()
            ->andWhere(['in', 'organization_id', $orgIds])
            ->andWhere(['is_deleted' => 0])
            ->count();
    }

    /**
     * Можно ли добавить группу
     */
    public function canAddGroup(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }
        return !$this->isLimitReached('max_groups', $this->getGroupCount());
    }

    /**
     * Сколько групп ещё можно добавить
     */
    public function getRemainingGroups(): ?int
    {
        $limit = $this->getLimit('max_groups');
        if ($limit === 0) {
            return null;
        }
        return max(0, $limit - $this->getGroupCount());
    }

    // ==================== ADMINS ====================

    /**
     * Текущее количество админов
     */
    public function getAdminCount(): int
    {
        $orgIds = $this->getAllOrganizationIds();
        return UserOrganization::find()
            ->andWhere(['in', 'target_id', $orgIds])
            ->andWhere(['role' => OrganizationRoles::ADMIN])
            ->andWhere(['<>', 'is_deleted', 1])
            ->andWhere(['state' => UserOrganization::STATE_ACTIVE])
            ->count();
    }

    /**
     * Можно ли добавить админа
     */
    public function canAddAdmin(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }
        return !$this->isLimitReached('max_admins', $this->getAdminCount());
    }

    // ==================== BRANCHES ====================

    /**
     * Текущее количество филиалов
     */
    public function getBranchCount(): int
    {
        return Organizations::find()
            ->andWhere(['parent_id' => $this->organization->id])
            ->andWhere(['is_deleted' => 0])
            ->count();
    }

    /**
     * Можно ли добавить филиал
     */
    public function canAddBranch(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }
        return !$this->isLimitReached('max_branches', $this->getBranchCount());
    }

    /**
     * Сколько филиалов ещё можно добавить
     */
    public function getRemainingBranches(): ?int
    {
        $limit = $this->getLimit('max_branches');
        if ($limit === 0) {
            return null;
        }
        return max(0, $limit - $this->getBranchCount());
    }

    // ==================== FEATURES ====================

    /**
     * Проверить наличие функции (в плане или аддонах)
     */
    public function hasFeature(string $feature): bool
    {
        // 1. Проверяем в плане
        if ($this->subscription && $this->subscription->saasPlan) {
            if ($this->subscription->saasPlan->hasFeature($feature)) {
                return true;
            }
        }

        // 2. Проверяем в активных аддонах
        if ($this->hasAddon($feature)) {
            return true;
        }

        return false;
    }

    /**
     * Проверить наличие функции только в базовом плане (без аддонов)
     */
    public function hasFeatureInPlan(string $feature): bool
    {
        if (!$this->subscription || !$this->subscription->saasPlan) {
            return false;
        }
        return $this->subscription->saasPlan->hasFeature($feature);
    }

    // ==================== USAGE INFO ====================

    /**
     * Получить информацию об использовании лимитов
     */
    public function getUsageInfo(): array
    {
        return [
            'pupils' => [
                'current' => $this->getPupilCount(),
                'limit' => $this->getLimit('max_pupils'),
                'base_limit' => $this->getBaseLimit('max_pupils'),
                'addon_bonus' => $this->getAddonLimitBonus('max_pupils'),
                'remaining' => $this->getRemainingPupils(),
                'can_add' => $this->canAddPupil(),
            ],
            'teachers' => [
                'current' => $this->getTeacherCount(),
                'limit' => $this->getLimit('max_teachers'),
                'base_limit' => $this->getBaseLimit('max_teachers'),
                'addon_bonus' => $this->getAddonLimitBonus('max_teachers'),
                'remaining' => $this->getRemainingTeachers(),
                'can_add' => $this->canAddTeacher(),
            ],
            'groups' => [
                'current' => $this->getGroupCount(),
                'limit' => $this->getLimit('max_groups'),
                'base_limit' => $this->getBaseLimit('max_groups'),
                'addon_bonus' => $this->getAddonLimitBonus('max_groups'),
                'remaining' => $this->getRemainingGroups(),
                'can_add' => $this->canAddGroup(),
            ],
            'admins' => [
                'current' => $this->getAdminCount(),
                'limit' => $this->getLimit('max_admins'),
                'base_limit' => $this->getBaseLimit('max_admins'),
                'addon_bonus' => $this->getAddonLimitBonus('max_admins'),
                'can_add' => $this->canAddAdmin(),
            ],
            'branches' => [
                'current' => $this->getBranchCount(),
                'limit' => $this->getLimit('max_branches'),
                'base_limit' => $this->getBaseLimit('max_branches'),
                'addon_bonus' => $this->getAddonLimitBonus('max_branches'),
                'remaining' => $this->getRemainingBranches(),
                'can_add' => $this->canAddBranch(),
            ],
            'active_addons' => count($this->getActiveAddons()),
        ];
    }

    /**
     * Получить текущее количество для поля лимита
     */
    public function getCurrentCount(string $field): int
    {
        return match ($field) {
            'max_pupils' => $this->getPupilCount(),
            'max_teachers' => $this->getTeacherCount(),
            'max_groups' => $this->getGroupCount(),
            'max_admins' => $this->getAdminCount(),
            'max_branches' => $this->getBranchCount(),
            default => 0,
        };
    }

    /**
     * Получить процент использования лимита
     */
    public function getUsagePercent(string $field): int
    {
        $limit = $this->getLimit($field);
        if ($limit === 0) {
            return 0; // Безлимит
        }

        $current = $this->getCurrentCount($field);

        return min(100, (int)round(($current / $limit) * 100));
    }

    // ==================== HELPERS ====================

    /**
     * Получить все ID организаций (головная + филиалы)
     */
    private function getAllOrganizationIds(): array
    {
        $ids = [$this->organization->id];
        $branches = Organizations::find()
            ->select('id')
            ->andWhere(['parent_id' => $this->organization->id])
            ->andWhere(['is_deleted' => 0])
            ->column();
        return array_merge($ids, $branches);
    }

    /**
     * Сообщение об ошибке при превышении лимита
     */
    public static function getLimitErrorMessage(string $entity): string
    {
        $messages = [
            'pupil' => Yii::t('main', 'Достигнут лимит учеников. Обновите тарифный план или докупите пакет учеников.'),
            'teacher' => Yii::t('main', 'Достигнут лимит учителей. Обновите тарифный план или докупите пакет.'),
            'group' => Yii::t('main', 'Достигнут лимит групп. Обновите тарифный план или докупите пакет групп.'),
            'admin' => Yii::t('main', 'Достигнут лимит администраторов. Обновите тарифный план.'),
            'branch' => Yii::t('main', 'Достигнут лимит филиалов. Обновите тарифный план или докупите филиал.'),
        ];

        return $messages[$entity] ?? Yii::t('main', 'Достигнут лимит. Обновите тарифный план.');
    }

    /**
     * Получить информацию о доступных аддонах для увеличения лимита
     */
    public function getAvailableAddonInfo(string $limitField): ?array
    {
        $feature = \app\models\SaasFeature::find()
            ->where(['is_addon' => 1, 'is_active' => 1])
            ->andWhere(['type' => \app\models\SaasFeature::TYPE_LIMIT])
            ->one();

        // TODO: Реализовать поиск подходящего аддона по полю лимита
        return null;
    }

    /**
     * Получить головную организацию
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
}
