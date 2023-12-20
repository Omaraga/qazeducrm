<?php

namespace common\components;

use Yii;
use yii\helpers\Json;
use yii\validators\Validator;

class LanguageJsonValidator extends Validator
{
    public $allowEmpty = true;
    public $requiredParams = ['ru'];
    public $required = false;

    // условие при котором поле объязательное
    public $when;

    // чтобы ошибку расскидывал не по языкам, а общую
    public $isSplit = false;

    public function validateAttribute($model,$attribute)
    {
        $arr = $model->$attribute;
        if (!is_array($arr)) {
            $arr = json_decode($arr, true);
        }

        if (($this->required && $this->when) || ($this->required && !$this->when)) {
            $this->required($model, $attribute, $arr);
        }

        if (is_array($model->$attribute)) {
            $model->$attribute = Json::encode($model->$attribute);
        }
        return true;
    }

    protected function required($model, $attribute, $arr)
    {
        $validator = Validator::createValidator('required', $model, $attribute);
        foreach ($this->requiredParams as $languageId) {
            if (!isset($arr[$languageId])) {
                continue;
            }
            $result = $validator->validateValue($arr[$languageId]);
            if (!empty($result)) {
                $message = $result[0];
                $attributeByLang = !$this->isSplit ? $attribute . '[' . $languageId . ']' : $attribute;
                $model->addError($attributeByLang, Yii::t("app", $message, [
                    'attribute' => $model->getAttributeLabel($attribute)
                ]));
            }
        }
    }

}