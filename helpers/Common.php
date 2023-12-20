<?php

namespace app\helpers;

use lajax\translatemanager\models\Language;

class Common
{

    public static function byLang($value, $language = null)
    {

        if (!$language) {
            $languageObj = Language::find()->where(['language_id' => \Yii::$app->language])->one();
            $language = $languageObj->language;
        }
        $found = false;
        $data = !is_array($value) ? json_decode($value, true) : $value;
        if (is_array($data)) {
            if (!key_exists($language, $data)){
                $data[$language] = '';
            }
            if (key_exists($language, $data) && !empty($data[$language]) OR $data[$language] == '0') {
                $found = true;
                $value = $data[$language];
            } else {
                foreach ($data as $d) {
                    if (!empty($d) OR $d == '0') {
                        $found = true;
                        $value = $d;
                        break;
                    }
                }
            }
        }

        if (!$found AND $data) {
            return null;
        }

        return $value;
    }

}