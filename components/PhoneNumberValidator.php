<?php

namespace app\components;

class PhoneNumberValidator extends \yii\validators\Validator
{

    public $allowEmpty = true;

    public function validateAttribute($model,$attribute)
    {

        $value=$model->$attribute;
        $value = str_replace(["(",")","-"],"",trim($value, "+"));
        $value = str_replace('8', '7', substr($value, 0, 1)).substr($value,1,strlen($value));

        if ($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        if (!$this->validateValue($value))
        {
            $message=$this->message!==null?$this->message:\Yii::t('yii','Телефонный номер должен начинатся с 7');
            $model->addError($attribute,$message);
        }
        $model->$attribute = $value;
    }

    public function validateValue($value)
    {
        return substr($value, 0, 1) === '7';
    }
}