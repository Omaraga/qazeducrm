<?php

namespace app\models\services;

use app\components\ActiveRecord;
use app\helpers\OrganizationRoles;
use app\models\User;
use yii\helpers\ArrayHelper;

/**
 * TeacherService - сервис для работы с учителями
 *
 * Централизует запросы на получение списка учителей организации
 */
class TeacherService
{
    /**
     * Получить всех учителей текущей организации
     *
     * @return User[] Массив моделей User с ролью учителя
     */
    public static function getOrganizationTeachers(): array
    {
        return User::find()
            ->innerJoinWith(['currentUserOrganizations' => function ($q) {
                $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED])
                    ->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
            }])
            ->all();
    }

    /**
     * Получить список учителей для выпадающего списка
     *
     * @param string $valueField Поле для значения (по умолчанию 'id')
     * @param string $textField Поле для текста (по умолчанию 'fio')
     * @return array Ассоциативный массив [id => fio]
     */
    public static function getTeachersDropdown(string $valueField = 'id', string $textField = 'fio'): array
    {
        $teachers = self::getOrganizationTeachers();
        return ArrayHelper::map($teachers, $valueField, $textField);
    }

    /**
     * Получить количество учителей в организации
     *
     * @return int
     */
    public static function getTeachersCount(): int
    {
        return User::find()
            ->innerJoinWith(['currentUserOrganizations' => function ($q) {
                $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED])
                    ->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
            }])
            ->count();
    }

    /**
     * Найти учителя по ID с проверкой принадлежности к организации
     *
     * @param int $id ID пользователя
     * @return User|null
     */
    public static function findTeacher(int $id): ?User
    {
        return User::find()
            ->innerJoinWith(['currentUserOrganizations' => function ($q) {
                $q->andWhere(['<>', 'user_organization.is_deleted', ActiveRecord::DELETED])
                    ->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
            }])
            ->andWhere(['user.id' => $id])
            ->one();
    }
}
