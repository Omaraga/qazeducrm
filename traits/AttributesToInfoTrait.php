<?php
/**
 * Created by PhpStorm.
 * User: aloud
 * Date: 30.05.2017
 * Time: 20:00
 */

namespace app\traits;

use app\helpers\Common;
use app\helpers\StringHelper;

trait AttributesToInfoTrait
{

    public function attributesToInfo()
    {
        return [];
    }

    private $_properties = -1;

    public function __get($name)
    {

        if (substr($name, strlen($name) - 6, 6) == 'ByLang') {
            $key = substr($name, 0, strlen($name) - 6);
            if (in_array($key, $this->attributesToInfo())) {
                $info = $this->getInfo();
                return key_exists($key, $info) && isset($info[$key]) ? Common::byLang($info[$key]) : null;
            }
        }
        if (in_array($name, $this->attributesToInfo())) {
            $info = $this->getInfo();
            return isset($info[$name]) ? $info[$name] : null;
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->attributesToInfo())) {
            $info = $this->getInfo();
            $info[$name] = $value;
            $this->info = $info;
        } else {
            parent::__set($name, $value);
        }
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), $this->attributesToInfo());
    }

    public function safeAttributes()
    {
        return array_merge(parent::attributes(), $this->attributesToInfo());
    }

    public function getInfo()
    {
        if (StringHelper::isJson($this->info)) {
            return json_decode($this->info, true);
        }
        return is_array($this->info) ? $this->info : [];
    }

}