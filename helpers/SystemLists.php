<?php

namespace app\helpers;

use Yii;

/**
 * SystemLists - справочники системных данных (роли, должности, типы учета)
 */
class SystemLists
{
    /**
     * Список ролей пользователей
     * @return array
     */
    public static function getRoles(): array
    {
        return [
            SystemRoles::SUPER => Yii::t('main', 'Системный администратор'),
            OrganizationRoles::GENERAL_DIRECTOR => Yii::t('main', 'Директор'),
            OrganizationRoles::ADMIN => Yii::t('main', 'Администратор'),
            OrganizationRoles::DIRECTOR => Yii::t('main', 'Директор филиала'),
            SystemRoles::PARENT => Yii::t('main', 'Родитель'),
            OrganizationRoles::TEACHER => Yii::t('main', 'Преподаватель'),
            OrganizationRoles::NO_ROLE => Yii::t("main", "Без роли"),
        ];
    }

    /**
     * Должности из школьной системы
     * @return array
     */
    public static function getRanks(): array
    {
        return [
            1 => Yii::t('main', 'Директор'),
            3 => Yii::t('main', 'Заместитель директора по научной работе'),
            4 => Yii::t('main', 'Заместитель директора по учебно-воспитательной работе'),
            5 => Yii::t('main', 'Заместитель директора по учебно-производственной работе'),
            42 => Yii::t('main', 'Заместитель директора по инновациям'),
            43 => Yii::t('main', 'Заместитель директора по информатизации'),
            44 => Yii::t('main', 'Психолог'),
            100 => Yii::t('main', 'Зам. директора по воспитательной работе'),
            153 => Yii::t('main', 'Заместитель директора по учебно-методической работе'),
            161 => Yii::t('main', 'Помощник заместителя директора по АХР'),
            163 => Yii::t('main', 'Заместитель директора по профильной работе'),
            164 => Yii::t('main', 'Заместитель директора по научно-методической работе'),
            213 => Yii::t('main', 'Заместитель директора по учебной работе'),
            40 => Yii::t('main', 'Администратор системы'),
            67 => Yii::t('main', 'Секретарь'),
            22 => Yii::t('main', 'Секретарь по учебной части'),
            23 => Yii::t('main', 'Секретарь (делопроизводитель)'),
            52 => Yii::t('main', 'Социальный педагог'),
            68 => Yii::t('main', 'Делопроизводитель'),
        ];
    }

    /**
     * Статус резидентства
     * @return array
     */
    public static function getResident(): array
    {
        return [
            1 => Yii::t('main', 'Гражданин РК (Резидент)'),
            2 => Yii::t('main', 'Не гражданин РК (Не резидент)')
        ];
    }

    /**
     * Типы учета организации
     * @return array
     */
    public static function getAccountingTypes(): array
    {
        return [
            0 => Yii::t('main', 'Не указано'),
            1 => Yii::t('main', 'Организация образования (Заявления подписываются директором школы)'),
            2 => Yii::t('main', 'Канцелярия услугодателя (Заявления подписываются руководителем отдела образования)'),
        ];
    }
}
