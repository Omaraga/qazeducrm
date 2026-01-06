<?php

namespace app\helpers;

use Yii;

/**
 * CommonLists - общие справочники (пол, дни недели, языки)
 */
class CommonLists
{
    /**
     * Список полов
     * @return array
     */
    public static function getGenders(): array
    {
        return [
            '1' => Yii::t('main', 'Мужской'),
            '2' => Yii::t('main', 'Женский')
        ];
    }

    /**
     * Дни недели (полные)
     * @return array
     */
    public static function getWeekDays(): array
    {
        return [
            1 => Yii::t('main', 'Понедельник'),
            2 => Yii::t('main', 'Вторник'),
            3 => Yii::t('main', 'Среда'),
            4 => Yii::t('main', 'Четверг'),
            5 => Yii::t('main', 'Пятница'),
            6 => Yii::t('main', 'Суббота'),
            7 => Yii::t('main', 'Воскресенье')
        ];
    }

    /**
     * Дни недели (сокращённые)
     * @return array
     */
    public static function getWeekDaysShort(): array
    {
        return [
            1 => Yii::t('main', 'Пн'),
            2 => Yii::t('main', 'Вт'),
            3 => Yii::t('main', 'Ср'),
            4 => Yii::t('main', 'Чт'),
            5 => Yii::t('main', 'Пт'),
            6 => Yii::t('main', 'Сб'),
            7 => Yii::t('main', 'Вс')
        ];
    }

    /**
     * Язык обучения
     * @return array
     */
    public static function getStudyLang(): array
    {
        return [
            1 => Yii::t('main', 'Русский язык'),
            2 => Yii::t('main', 'Казахский язык'),
        ];
    }

    /**
     * Языки интерфейса
     * @return array
     */
    public static function getLanguageList(): array
    {
        return [
            'ru-RU' => Yii::t('main', 'Русский язык'),
            'kk-KZ' => Yii::t('main', 'Казахский язык'),
        ];
    }

    /**
     * Язык документа/приказа
     * @return array
     */
    public static function getOrderLang(): array
    {
        return [
            1 => Yii::t('main', 'На русском языке'),
            2 => Yii::t('main', 'На казахском языке')
        ];
    }
}
