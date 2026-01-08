<?php

namespace app\helpers;

use app\models\OrganizationAccessSettings;
use Yii;

/**
 * Централизованный класс для проверки ролей пользователя
 * Упрощает проверку доступа в контроллерах, views и сервисах
 * Учитывает настройки доступа организации (OrganizationAccessSettings)
 */
class RoleChecker
{
    /**
     * Получить текущую роль пользователя в организации
     * @return string|false
     */
    public static function getCurrentRole()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return Yii::$app->user->identity->getCurrentOrganizationRole();
    }

    /**
     * Проверка - суперадминистратор
     * @return bool
     */
    public static function isSuper(): bool
    {
        return Yii::$app->user->can(SystemRoles::SUPER);
    }

    /**
     * Проверка - директор или выше (SUPER, GENERAL_DIRECTOR, DIRECTOR)
     * Эти роли имеют полный доступ к финансам и управлению
     * @return bool
     */
    public static function isDirector(): bool
    {
        if (self::isSuper()) {
            return true;
        }

        $role = self::getCurrentRole();
        return in_array($role, [OrganizationRoles::GENERAL_DIRECTOR, OrganizationRoles::DIRECTOR]);
    }

    /**
     * Проверка - имеет доступ к финансовым данным
     * SUPER, GENERAL_DIRECTOR, DIRECTOR - видят финансы
     * ADMIN - по настройке организации
     * TEACHER - нет
     * @return bool
     */
    public static function hasFinanceAccess(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        // Admin может видеть финансы если настроено
        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_VIEW_FINANCE_DASHBOARD);
        }

        return false;
    }

    /**
     * Проверка - пользователь только учитель (не имеет более высокой роли)
     * @return bool
     */
    public static function isTeacherOnly(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $role = self::getCurrentRole();
        return $role === OrganizationRoles::TEACHER;
    }

    /**
     * Проверка - учитель (может иметь и более высокую роль)
     * @return bool
     */
    public static function isTeacher(): bool
    {
        $role = self::getCurrentRole();
        return $role === OrganizationRoles::TEACHER || self::isAdminOrHigher();
    }

    /**
     * Проверка - админ или выше (SUPER, GENERAL_DIRECTOR, DIRECTOR, ADMIN)
     * Эти роли имеют доступ к управлению учениками, группами, лидами
     * @return bool
     */
    public static function isAdminOrHigher(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        $role = self::getCurrentRole();
        return $role === OrganizationRoles::ADMIN;
    }

    /**
     * Проверка - только админ (не директор)
     * @return bool
     */
    public static function isAdminOnly(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $role = self::getCurrentRole();
        return $role === OrganizationRoles::ADMIN;
    }

    /**
     * Получить ID текущего пользователя как учителя
     * @return int|null
     */
    public static function getCurrentTeacherId(): ?int
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        return Yii::$app->user->id;
    }

    /**
     * Получить ID текущего пользователя
     * @return int|null
     */
    public static function getCurrentUserId(): ?int
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        return Yii::$app->user->id;
    }

    /**
     * Проверка - может ли пользователь сбрасывать пароли сотрудников
     * Только SUPER, GENERAL_DIRECTOR, DIRECTOR
     * @return bool
     */
    public static function canResetPasswords(): bool
    {
        return self::isDirector();
    }

    /**
     * Проверка - может ли пользователь одобрять запросы на изменение платежей
     * Только SUPER, GENERAL_DIRECTOR, DIRECTOR
     * @return bool
     */
    public static function canApprovePaymentRequests(): bool
    {
        return self::isDirector();
    }

    /**
     * Проверка - может ли пользователь редактировать платежи напрямую
     * SUPER, GENERAL_DIRECTOR, DIRECTOR - всегда
     * ADMIN - по настройке организации
     * @return bool
     */
    public static function canEditPayments(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_PAYMENT_DIRECT_EDIT);
        }

        return false;
    }

    /**
     * Проверка - может ли пользователь удалять платежи напрямую
     * SUPER, GENERAL_DIRECTOR, DIRECTOR - всегда
     * ADMIN - по настройке организации
     * @return bool
     */
    public static function canDeletePayments(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_PAYMENT_DIRECT_DELETE);
        }

        return false;
    }

    /**
     * Проверка - может ли пользователь удалять/редактировать платежи напрямую
     * Комбинированная проверка редактирования И удаления
     * @return bool
     */
    public static function canModifyPayments(): bool
    {
        return self::canEditPayments() && self::canDeletePayments();
    }

    /**
     * Проверка - может ли пользователь создавать платежи
     * SUPER, GENERAL_DIRECTOR, DIRECTOR, ADMIN
     * @return bool
     */
    public static function canCreatePayments(): bool
    {
        return self::isAdminOrHigher();
    }

    /**
     * Проверка - нужно ли пользователю создавать запрос на изменение платежа
     * Только для ADMIN без прав прямого редактирования
     * @return bool
     */
    public static function needsPaymentChangeRequest(): bool
    {
        if (!self::isAdminOnly()) {
            return false;
        }

        // Если Admin имеет права на прямое редактирование - запрос не нужен
        return !self::canEditPayments();
    }

    /**
     * Проверка - нужно ли пользователю создавать запрос на удаление платежа
     * @return bool
     */
    public static function needsPaymentDeleteRequest(): bool
    {
        if (!self::isAdminOnly()) {
            return false;
        }

        return !self::canDeletePayments();
    }

    /**
     * Получить список ролей для AccessControl behavior
     * @param string $level Уровень доступа: 'director', 'admin', 'teacher', 'all'
     * @return array
     */
    public static function getRolesForAccess(string $level = 'admin'): array
    {
        $roles = [SystemRoles::SUPER];

        if ($level === 'all' || $level === 'teacher' || $level === 'admin' || $level === 'director') {
            $roles[] = OrganizationRoles::GENERAL_DIRECTOR;
            $roles[] = OrganizationRoles::DIRECTOR;
        }

        if ($level === 'all' || $level === 'teacher' || $level === 'admin') {
            $roles[] = OrganizationRoles::ADMIN;
        }

        if ($level === 'all' || $level === 'teacher') {
            $roles[] = OrganizationRoles::TEACHER;
        }

        return $roles;
    }

    // ===== МЕТОДЫ ДЛЯ ADMIN =====

    /**
     * Может ли Admin удалять учеников
     * @return bool
     */
    public static function canAdminDeletePupils(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_PUPIL_DELETE);
        }

        return false;
    }

    /**
     * Может ли Admin видеть баланс учеников
     * @return bool
     */
    public static function canAdminViewPupilBalance(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_PUPIL_VIEW_BALANCE);
        }

        return false;
    }

    /**
     * Может ли Admin удалять лиды
     * @return bool
     */
    public static function canAdminDeleteLids(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_LIDS_DELETE);
        }

        return false;
    }

    /**
     * Может ли Admin удалять группы
     * @return bool
     */
    public static function canAdminDeleteGroups(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_GROUP_DELETE);
        }

        return false;
    }

    /**
     * Может ли Admin просматривать зарплаты
     * @return bool
     */
    public static function canAdminViewSalary(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::ADMIN_VIEW_SALARY);
        }

        return false;
    }

    // ===== МЕТОДЫ ДЛЯ TEACHER =====

    /**
     * Может ли Teacher создавать занятия
     * @return bool
     */
    public static function canTeacherCreateLessons(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_LESSON_CREATE);
        }

        return false;
    }

    /**
     * Может ли Teacher редактировать занятия
     * @return bool
     */
    public static function canTeacherEditLessons(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_LESSON_EDIT);
        }

        return false;
    }

    /**
     * Может ли Teacher удалять занятия
     * @return bool
     */
    public static function canTeacherDeleteLessons(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_LESSON_DELETE);
        }

        return false;
    }

    /**
     * Может ли Teacher видеть контакты учеников
     * @return bool
     */
    public static function canTeacherViewPupilContacts(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_PUPIL_VIEW_CONTACTS);
        }

        return false;
    }

    /**
     * Может ли Teacher видеть баланс учеников
     * @return bool
     */
    public static function canTeacherViewPupilBalance(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_PUPIL_VIEW_BALANCE);
        }

        return false;
    }

    /**
     * Может ли Teacher видеть свою зарплату
     * @return bool
     */
    public static function canTeacherViewOwnSalary(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_VIEW_OWN_SALARY);
        }

        return false;
    }

    /**
     * Может ли Teacher видеть все группы
     * @return bool
     */
    public static function canTeacherViewAllGroups(): bool
    {
        if (self::isAdminOrHigher()) {
            return true;
        }

        if (self::isTeacherOnly()) {
            return OrganizationAccessSettings::check(OrganizationAccessSettings::TEACHER_VIEW_ALL_GROUPS);
        }

        return false;
    }

    // ===== УНИВЕРСАЛЬНЫЕ МЕТОДЫ =====

    /**
     * Может ли текущий пользователь удалять учеников
     * @return bool
     */
    public static function canDeletePupils(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        return self::canAdminDeletePupils();
    }

    /**
     * Может ли текущий пользователь видеть баланс учеников
     * @return bool
     */
    public static function canViewPupilBalance(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        if (self::isAdminOnly()) {
            return self::canAdminViewPupilBalance();
        }

        if (self::isTeacherOnly()) {
            return self::canTeacherViewPupilBalance();
        }

        return false;
    }

    /**
     * Может ли текущий пользователь удалять лиды
     * @return bool
     */
    public static function canDeleteLids(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        return self::canAdminDeleteLids();
    }

    /**
     * Может ли текущий пользователь удалять группы
     * @return bool
     */
    public static function canDeleteGroups(): bool
    {
        if (self::isDirector()) {
            return true;
        }

        return self::canAdminDeleteGroups();
    }

    // ===== АЛИАСЫ ДЛЯ СОВМЕСТИМОСТИ =====

    /**
     * Alias для canEditPayments - Admin может редактировать напрямую
     * @return bool
     */
    public static function canAdminDirectEdit(): bool
    {
        return self::canEditPayments();
    }

    /**
     * Alias для canDeletePayments - Admin может удалять напрямую
     * @return bool
     */
    public static function canAdminDirectDelete(): bool
    {
        return self::canDeletePayments();
    }
}
