<?php


namespace app\models\enum;


class StatusEnum
{
    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVED = 0;

    /**
     * @return array
     */
    public static function getStatusList(){
        return [
            self::STATUS_ACTIVE => \Yii::t('main', 'Активный'),
            self::STATUS_ARCHIVED => \Yii::t('main', 'Архивный'),
        ];
    }


}