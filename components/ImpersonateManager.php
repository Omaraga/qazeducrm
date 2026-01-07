<?php

namespace app\components;

use app\helpers\SystemRoles;
use app\models\ImpersonateLog;
use app\models\OrganizationActivityLog;
use app\models\User;
use Yii;
use yii\base\Component;

/**
 * Компонент для управления impersonate сессиями (вход под другим пользователем)
 *
 * Используется техподдержкой для входа под пользователем без знания его пароля.
 * Сохраняет данные оригинальной сессии для возможности возврата.
 *
 * Использование:
 * - Yii::$app->impersonate->start($userId, $orgId) - начать impersonate
 * - Yii::$app->impersonate->stop() - завершить и вернуться
 * - Yii::$app->impersonate->isImpersonating() - проверить активность
 */
class ImpersonateManager extends Component
{
    /** @var string Ключ сессии для ID оригинального пользователя */
    const SESSION_KEY_ORIGINAL_USER = 'impersonate_original_user_id';

    /** @var string Ключ сессии для auth key оригинального пользователя */
    const SESSION_KEY_ORIGINAL_AUTH = 'impersonate_original_auth_key';

    /** @var string Ключ сессии для ID целевого пользователя */
    const SESSION_KEY_TARGET_USER = 'impersonate_target_user_id';

    /** @var string Ключ сессии для ID организации */
    const SESSION_KEY_ORGANIZATION = 'impersonate_organization_id';

    /**
     * Проверить, активна ли impersonate сессия
     *
     * @return bool
     */
    public function isImpersonating(): bool
    {
        return Yii::$app->session->has(self::SESSION_KEY_ORIGINAL_USER);
    }

    /**
     * Получить ID оригинального администратора
     *
     * @return int|null
     */
    public function getOriginalUserId(): ?int
    {
        return Yii::$app->session->get(self::SESSION_KEY_ORIGINAL_USER);
    }

    /**
     * Получить ID целевого пользователя
     *
     * @return int|null
     */
    public function getTargetUserId(): ?int
    {
        return Yii::$app->session->get(self::SESSION_KEY_TARGET_USER);
    }

    /**
     * Получить ID организации impersonate сессии
     *
     * @return int|null
     */
    public function getOrganizationId(): ?int
    {
        return Yii::$app->session->get(self::SESSION_KEY_ORGANIZATION);
    }

    /**
     * Получить данные целевого пользователя
     *
     * @return User|null
     */
    public function getTargetUser(): ?User
    {
        $userId = $this->getTargetUserId();
        return $userId ? User::findOne($userId) : null;
    }

    /**
     * Получить данные оригинального администратора
     *
     * @return User|null
     */
    public function getOriginalUser(): ?User
    {
        $userId = $this->getOriginalUserId();
        return $userId ? User::findOne($userId) : null;
    }

    /**
     * Начать impersonate сессию
     *
     * @param int $targetUserId ID целевого пользователя
     * @param int|null $organizationId ID организации
     * @return bool Успех операции
     */
    public function start(int $targetUserId, ?int $organizationId = null): bool
    {
        $currentUser = Yii::$app->user->identity;

        // Проверяем, что текущий пользователь - SUPER админ
        if (!$currentUser || $currentUser->system_role !== SystemRoles::SUPER) {
            Yii::warning('Impersonate attempt without SUPER role', 'impersonate');
            return false;
        }

        // Проверяем, что не пытаемся войти сам под собой
        if ($currentUser->id == $targetUserId) {
            Yii::warning('Impersonate attempt to self', 'impersonate');
            return false;
        }

        // Проверяем, что целевой пользователь существует и активен
        $targetUser = User::findOne($targetUserId);
        if (!$targetUser || $targetUser->status != User::STATUS_ACTIVE) {
            Yii::warning("Impersonate target user not found or inactive: $targetUserId", 'impersonate');
            return false;
        }

        // Если уже в режиме impersonate - сначала выходим
        if ($this->isImpersonating()) {
            $this->stop();
        }

        // Сохраняем данные оригинальной сессии
        Yii::$app->session->set(self::SESSION_KEY_ORIGINAL_USER, $currentUser->id);
        Yii::$app->session->set(self::SESSION_KEY_ORIGINAL_AUTH, $currentUser->auth_key);
        Yii::$app->session->set(self::SESSION_KEY_TARGET_USER, $targetUserId);
        Yii::$app->session->set(self::SESSION_KEY_ORGANIZATION, $organizationId);

        // Логируем в таблицу impersonate_log
        ImpersonateLog::log($currentUser->id, $targetUserId, ImpersonateLog::ACTION_START, $organizationId);

        // Логируем в organization_activity_log если есть организация
        if ($organizationId) {
            OrganizationActivityLog::log(
                $organizationId,
                OrganizationActivityLog::ACTION_IMPERSONATE_START,
                OrganizationActivityLog::CATEGORY_AUTH,
                "Супер-админ {$currentUser->fio} вошёл под пользователем {$targetUser->fio}",
                null,
                null,
                $currentUser->id,
                OrganizationActivityLog::USER_TYPE_SUPER_ADMIN
            );
        }

        // Переключаемся на целевого пользователя
        Yii::$app->user->switchIdentity($targetUser);

        // Устанавливаем активную организацию для целевого пользователя
        if ($organizationId) {
            $targetUser->active_organization_id = $organizationId;
            $targetUser->save(false, ['info']);
        }

        Yii::info("Impersonate started: admin={$currentUser->id} -> target={$targetUserId}, org={$organizationId}", 'impersonate');

        return true;
    }

    /**
     * Завершить impersonate сессию и вернуться к оригинальному пользователю
     *
     * @return bool Успех операции
     */
    public function stop(): bool
    {
        if (!$this->isImpersonating()) {
            return false;
        }

        $originalUserId = $this->getOriginalUserId();
        $targetUserId = $this->getTargetUserId();
        $organizationId = $this->getOrganizationId();

        $originalUser = User::findOne($originalUserId);
        if (!$originalUser) {
            Yii::error("Original user not found: $originalUserId", 'impersonate');
            // Очищаем сессию в любом случае
            $this->clearSession();
            return false;
        }

        // Логируем завершение
        ImpersonateLog::log($originalUserId, $targetUserId, ImpersonateLog::ACTION_END, $organizationId);

        // Логируем в organization_activity_log если есть организация
        if ($organizationId) {
            $targetUser = User::findOne($targetUserId);
            OrganizationActivityLog::log(
                $organizationId,
                OrganizationActivityLog::ACTION_IMPERSONATE_END,
                OrganizationActivityLog::CATEGORY_AUTH,
                "Супер-админ {$originalUser->fio} вышел из сессии пользователя " . ($targetUser ? $targetUser->fio : $targetUserId),
                null,
                null,
                $originalUserId,
                OrganizationActivityLog::USER_TYPE_SUPER_ADMIN
            );
        }

        // Очищаем сессию impersonate
        $this->clearSession();

        // Возвращаемся к оригинальному пользователю
        Yii::$app->user->switchIdentity($originalUser);

        Yii::info("Impersonate stopped: admin={$originalUserId} <- target={$targetUserId}", 'impersonate');

        return true;
    }

    /**
     * Очистить данные сессии impersonate
     */
    protected function clearSession(): void
    {
        Yii::$app->session->remove(self::SESSION_KEY_ORIGINAL_USER);
        Yii::$app->session->remove(self::SESSION_KEY_ORIGINAL_AUTH);
        Yii::$app->session->remove(self::SESSION_KEY_TARGET_USER);
        Yii::$app->session->remove(self::SESSION_KEY_ORGANIZATION);
    }
}
