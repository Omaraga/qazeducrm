<?php

namespace app\models\relations;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use yii\helpers\ArrayHelper;
use app\models\Organizations;
use app\models\User;
use app\traits\AttributesToInfoTrait;
use yii\helpers\Html;

/**
 * This is the model class for table "relations.user_organization".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $related_id
 * @property int $target_id
 * @property string $info
 * @property string $ts
 * @property int $state
 * @property string $role
 */
class UserOrganization extends ActiveRecord
{

    protected static $_multiple = true;

    const STATE_ACTIVE = 1;
    const STATE_RESERVE = 2;
    const STATE_FIRED = 0;

    use AttributesToInfoTrait, UpdateInsteadOfDeleteTrait;

    public function attributesToInfo()
    {
        return [
            'iin',
            'roleOld',
            'reason',
            'date_leaving',
            'number_order_leaving'
        ];
    }

    public function getCustomAttributes()
    {
        return $this->attributesToInfo();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['role'], 'string', 'max' => 200],
            [$this->attributesToInfo(), 'safe']
        ]);
    }

    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'related_id']);
    }

    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'target_id']);
    }

    public function getFio()
    {
        return $this->user->fio;
    }

    public function getSexName()
    {
        return $this->user->sexName;
    }

    public function getBirthday()
    {
        return $this->user->birthday;
    }

    public function getLogin()
    {
        return $this->user->login;
    }

    public function getEmailPhone()
    {
        return $this->user->email."<br />".$this->user->phone;
    }

    public function getFormattedTs()
    {
        return $this->getByFormat('ts', 'd.m.Y');
    }

    public function getRoleName()
    {
        return Users::getRoles()[$this->role];
    }

    public function getDisplayField($name)
    {
        $custom = ArrayHelper::map(DicValues::findByDic("pupil_custom_fields"), 'value', 'input');
        if (isset($custom[$name]) AND !empty($this->$name)) {
            if ($custom[$name] AND $custom[$name]['type'] == 'select') {
                return $custom[$name]['data'] ? $custom[$name]['data'][$this->$name] : DicValues::fromDic($this->$name);
            }
        }
        if ($name == "fio") {
            return Html::tag('span', $this->user->getImportLabelByType(), [
                    'class' => "mr-2 badge badge-primary"
                ]) . $this->$name;
        }
        return $this->$name;
    }

    public function getImportLabelByType()
    {

    }

    public function afterSave($insert, $changedAttributes)
    {
        if (!(\Yii::$app instanceof \Yii\console\Application) && $this->related_id == \Yii::$app->user->getId() && $changedAttributes['role'] == \Yii::$app->user->identity->active_role) {
            \Yii::$app->user->identity->active_role = $this->role;
            \Yii::$app->user->identity->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

}
