<?php

namespace app\helpers;

use app\models\OrganizationActivityLog;
use app\models\Organizations;
use Yii;

/**
 * ActivityLogger - удобный helper для логирования активности
 *
 * Использование:
 * ```php
 * ActivityLogger::logPupilCreated($pupil);
 * ActivityLogger::logPaymentCreated($payment);
 * ActivityLogger::logLogin();
 * ```
 */
class ActivityLogger
{
    /**
     * Записать лог для текущей организации
     *
     * @param string $action Действие
     * @param string $category Категория
     * @param string|null $description Описание
     * @param mixed $oldValue Старое значение
     * @param mixed $newValue Новое значение
     * @param array|null $metadata Дополнительные данные
     * @return bool
     */
    public static function log(
        string $action,
        string $category = OrganizationActivityLog::CATEGORY_GENERAL,
        ?string $description = null,
        $oldValue = null,
        $newValue = null,
        ?array $metadata = null
    ): bool {
        $orgId = Organizations::getCurrentOrganizationId();
        if (!$orgId) {
            return false;
        }

        return OrganizationActivityLog::log(
            $orgId,
            $action,
            $category,
            $description,
            $oldValue,
            $newValue,
            null,      // $userId - автоопределение
            null,      // $userType - автоопределение
            $metadata
        );
    }

    // =========================================================================
    // Авторизация
    // =========================================================================

    /**
     * Лог входа в систему
     */
    public static function logLogin(?int $organizationId = null): bool
    {
        $orgId = $organizationId ?? Organizations::getCurrentOrganizationId();
        if (!$orgId) {
            return false;
        }

        $user = Yii::$app->user->identity;
        return OrganizationActivityLog::log(
            $orgId,
            OrganizationActivityLog::ACTION_LOGIN,
            OrganizationActivityLog::CATEGORY_AUTH,
            $user ? "Вход: {$user->fio}" : 'Вход в систему'
        );
    }

    /**
     * Лог выхода из системы
     */
    public static function logLogout(?int $organizationId = null): bool
    {
        $orgId = $organizationId ?? Organizations::getCurrentOrganizationId();
        if (!$orgId) {
            return false;
        }

        $user = Yii::$app->user->identity;
        return OrganizationActivityLog::log(
            $orgId,
            OrganizationActivityLog::ACTION_LOGOUT,
            OrganizationActivityLog::CATEGORY_AUTH,
            $user ? "Выход: {$user->fio}" : 'Выход из системы'
        );
    }

    // =========================================================================
    // Ученики
    // =========================================================================

    /**
     * Лог создания ученика
     */
    public static function logPupilCreated($pupil): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_PUPIL_CREATED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Создан ученик: {$pupil->fio}",
            null,
            null,
            ['pupil_id' => $pupil->id, 'fio' => $pupil->fio]
        );
    }

    /**
     * Лог обновления ученика
     */
    public static function logPupilUpdated($pupil, array $changedAttributes = []): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_PUPIL_UPDATED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Обновлён ученик: {$pupil->fio}",
            null,
            null,
            ['pupil_id' => $pupil->id, 'changed' => array_keys($changedAttributes)]
        );
    }

    /**
     * Лог удаления ученика
     */
    public static function logPupilDeleted($pupil): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_PUPIL_DELETED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Удалён ученик: {$pupil->fio}",
            null,
            null,
            ['pupil_id' => $pupil->id, 'fio' => $pupil->fio]
        );
    }

    // =========================================================================
    // Платежи
    // =========================================================================

    /**
     * Лог создания платежа
     */
    public static function logPaymentCreated($payment): bool
    {
        $pupilName = $payment->pupil ? $payment->pupil->fio : 'Неизвестно';
        return self::log(
            OrganizationActivityLog::ACTION_PAYMENT_CREATED,
            OrganizationActivityLog::CATEGORY_PAYMENT,
            "Платёж: {$payment->amount} ₸ от {$pupilName}",
            null,
            $payment->amount,
            ['payment_id' => $payment->id, 'pupil_id' => $payment->pupil_id, 'amount' => $payment->amount]
        );
    }

    /**
     * Лог удаления платежа
     */
    public static function logPaymentDeleted($payment): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_PAYMENT_DELETED,
            OrganizationActivityLog::CATEGORY_PAYMENT,
            "Удалён платёж #{$payment->id} на сумму {$payment->amount} ₸",
            $payment->amount,
            null,
            ['payment_id' => $payment->id, 'amount' => $payment->amount]
        );
    }

    // =========================================================================
    // Группы
    // =========================================================================

    /**
     * Лог создания группы
     */
    public static function logGroupCreated($group): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_GROUP_CREATED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Создана группа: {$group->name}",
            null,
            null,
            ['group_id' => $group->id, 'name' => $group->name]
        );
    }

    /**
     * Лог удаления группы
     */
    public static function logGroupDeleted($group): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_GROUP_DELETED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Удалена группа: {$group->name}",
            null,
            null,
            ['group_id' => $group->id, 'name' => $group->name]
        );
    }

    // =========================================================================
    // Лиды
    // =========================================================================

    /**
     * Лог создания лида
     */
    public static function logLidCreated($lid): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_LID_CREATED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Создан лид: {$lid->fio}",
            null,
            null,
            ['lid_id' => $lid->id, 'fio' => $lid->fio, 'source' => $lid->source]
        );
    }

    /**
     * Лог конверсии лида
     */
    public static function logLidConverted($lid, $pupil): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_LID_CONVERTED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Лид конвертирован в ученика: {$lid->fio}",
            null,
            null,
            ['lid_id' => $lid->id, 'pupil_id' => $pupil->id]
        );
    }

    /**
     * Лог потери лида
     */
    public static function logLidLost($lid, ?string $reason = null): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_LID_LOST,
            OrganizationActivityLog::CATEGORY_CRM,
            "Лид потерян: {$lid->fio}" . ($reason ? " ({$reason})" : ''),
            null,
            null,
            ['lid_id' => $lid->id, 'reason' => $reason]
        );
    }

    // =========================================================================
    // Занятия
    // =========================================================================

    /**
     * Лог проведения занятия
     */
    public static function logLessonCompleted($lesson): bool
    {
        $groupName = $lesson->group ? $lesson->group->name : 'Неизвестно';
        return self::log(
            OrganizationActivityLog::ACTION_LESSON_COMPLETED,
            OrganizationActivityLog::CATEGORY_CRM,
            "Занятие проведено: {$groupName}",
            null,
            null,
            ['lesson_id' => $lesson->id, 'group_id' => $lesson->group_id]
        );
    }

    // =========================================================================
    // Настройки
    // =========================================================================

    /**
     * Лог изменения настроек организации
     */
    public static function logSettingsChanged(string $settingName, $oldValue, $newValue): bool
    {
        return self::log(
            OrganizationActivityLog::ACTION_SETTINGS_CHANGED,
            OrganizationActivityLog::CATEGORY_ORGANIZATION,
            "Изменена настройка: {$settingName}",
            $oldValue,
            $newValue,
            ['setting' => $settingName]
        );
    }
}
