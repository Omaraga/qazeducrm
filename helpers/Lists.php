<?php

namespace app\helpers;

use ReflectionMethod;
use Yii;

/**
 * Lists - фасад для справочников
 *
 * Делегирует вызовы специализированным классам:
 * - SystemLists - роли, должности, типы учета
 * - GeographyLists - национальности, гражданство
 * - EducationLists - образовательные справочники
 * - CommonLists - общие справочники (пол, дни недели, языки)
 *
 * @see SystemLists
 * @see GeographyLists
 * @see EducationLists
 * @see CommonLists
 */
class Lists
{
    /**
     * No init
     */
    public function __construct()
    {
    }

    /**
     * Возвращаем данные из словаря (используется в фильтре twig)
     *
     * @example {{ row.role | dict('roles') }}
     *
     * @param string $dictName
     * @param mixed $value
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getValueFromDict($dictName, $value)
    {
        $_class = new self();
        $method = new ReflectionMethod($_class, 'get' . $dictName);
        $data = $method->invoke($_class);
        return isset($data[$value]) ? $data[$value] : '';
    }

    // =========================================================================
    // CommonLists - общие справочники
    // =========================================================================

    public static function getGenders(): array
    {
        return CommonLists::getGenders();
    }

    public static function getWeekDays(): array
    {
        return CommonLists::getWeekDays();
    }

    public static function getWeekDaysShort(): array
    {
        return CommonLists::getWeekDaysShort();
    }

    public static function getStudyLang(): array
    {
        return CommonLists::getStudyLang();
    }

    public static function getLanguageList(): array
    {
        return CommonLists::getLanguageList();
    }

    public static function getOrderLang(): array
    {
        return CommonLists::getOrderLang();
    }

    // =========================================================================
    // GeographyLists - география
    // =========================================================================

    public static function getNationality(): array
    {
        return GeographyLists::getNationality();
    }

    public static function getCitizenshipStatus(): array
    {
        return GeographyLists::getCitizenshipStatus();
    }

    // =========================================================================
    // SystemLists - системные справочники
    // =========================================================================

    public static function getRoles(): array
    {
        return SystemLists::getRoles();
    }

    public static function getRanks(): array
    {
        return SystemLists::getRanks();
    }

    public static function getResident(): array
    {
        return SystemLists::getResident();
    }

    public static function getAccountingTypes(): array
    {
        return SystemLists::getAccountingTypes();
    }

    // =========================================================================
    // EducationLists - образование
    // =========================================================================

    public static function getChildrenSocialCategory(): array
    {
        return EducationLists::getChildrenSocialCategory();
    }

    public static function getStudentSocialCategory(): array
    {
        return EducationLists::getStudentSocialCategory();
    }

    public static function getCertificateSubjects(): array
    {
        return EducationLists::getCertificateSubjects();
    }

    public static function getCertificateReason(): array
    {
        return EducationLists::getCertificateReason();
    }

    public static function getCertificateType(): array
    {
        return EducationLists::getCertificateType();
    }

    public static function getCampList(): array
    {
        return EducationLists::getCampList();
    }

    public static function getCategories(): array
    {
        return EducationLists::getCategories();
    }

    public static function getTariffDurations(): array
    {
        return EducationLists::getTariffDurations();
    }

    public static function getTariffTypes(): array
    {
        return EducationLists::getTariffTypes();
    }

    public static function getGrades(): array
    {
        return EducationLists::getGrades();
    }

    public static function getGroupCategories(): array
    {
        return EducationLists::getGroupCategories();
    }
}
