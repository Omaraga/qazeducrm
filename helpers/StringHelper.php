<?php

namespace app\helpers;

class StringHelper
{

    /**
     * Является ли строка json
     *
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        return is_string($string) AND is_array(json_decode($string, true)) AND (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

}