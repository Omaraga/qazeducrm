<?php

namespace app\components;

use app\helpers\Common;
use app\models\Organizations;
use yii;


class ActiveRecord extends yii\db\ActiveRecord
{
    protected static $ORGANIZATION_ID_DEFAULT_VALUE = null;
    const DELETED = 1;
    // Сохраняет предыдущие значения аттрибутов, перед записью в базу
    public function beforeSave($insert)
    {
        if (in_array("info", $this->attributes()) && is_array($this->info)) {
            $this->info = json_encode($this->info, true);
        }

        if (in_array("organization_id", $this->attributes()) && $this->isNewRecord && empty($this->organization_id) AND Organizations::getCurrentOrganizationId()) {
            $this->organization_id = Organizations::getCurrentOrganizationId();
        } else if (isset(\Yii::$app->user) && in_array("organization_id", $this->attributes()) && $this->isNewRecord && empty($this->organization_id) && \Yii::$app->user->can("SUPER")) {
            $this->organization_id = static::$ORGANIZATION_ID_DEFAULT_VALUE;
        }


        if (in_array("user_id", $this->attributes()) && $this->isNewRecord && empty($this->user_id)) {
            $this->user_id = isset(\Yii::$app->user) ? \Yii::$app->user->id : -1;
        }

        return parent::beforeSave($insert);
    }

    public function setInfo($name, $value)
    {
        $jInfo = $this->infoJson;
        if (!$jInfo) {
            $jInfo = array();
        }
        $jInfo[$name] = $value;
        $this->info = $jInfo;
    }

    public function updateInfo()
    {
        $this->save();
    }

    public function __get($name)
    {
        if (substr($name, strlen($name) - 4, 4) == 'Json') {
            $name = substr($name, 0, strlen($name) - 4);
            $attr = parent::__get($name);
            return is_array($attr) ? $attr : (json_decode($attr, true) ? json_decode($attr, true) : []);
        }

        if (substr($name, strlen($name) - 6, 6) == 'ByLang') {
            $name = substr($name, 0, strlen($name) - 6);
            $attr = parent::__get($name);
            return Common::byLang($attr);
        }

        return parent::__get($name);
    }

    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return (new ActiveQuery(get_called_class()))->notDeleted(static::tableName());
    }

    public static function findWithDeleted()
    {
        return (new ActiveQuery(get_called_class()));
    }


    public function getIsInOrganization()
    {
        return $this->organization_id == \app\models\Organizations::getCurrentOrganizationId() OR \Yii::$app->user->can("SUPER");
    }


}

