<?php

namespace app\models;

use app\components\PhoneNumberValidator;
use app\traits\AttributesToInfoTrait;
use app\models\Organizations;
use app\models\relations\UserOrganization;
use app\helpers\Lists;
use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\base\NotSupportedException;
use app\components\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $sex
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $fio
 * @property string $info
 * @property string $system_role [varchar(255)]
 * @property string $first_name [varchar(255)]
 * @property string $last_name [varchar(255)]
 * @property string $middle_name [varchar(255)]
 * @property string $iin [varchar(255)]
 * @property string $phone [varchar(255)]
 * @property string $home_phone [varchar(255)]
 * @property string $birth_date [varchar(255)]
 * @property string $address [varchar(255)]
 */
class User extends ActiveRecord implements IdentityInterface
{
    use AttributesToInfoTrait;
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const ROLE_SUPER = 'SUPER';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function attributesToInfo(){
        return [
            'active_organization_id',
            'active_role',
            'sex',
            'address'
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        return parent::save($runValidation, $attributeNames); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            [['phone', 'home_phone'], PhoneNumberValidator::class],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    private $_role = null;

    public function getOrganization()
    {
        return $this->hasOne(UserOrganization::class, ['related_id' => 'id']);
    }

    public function getCurrentOrganizationRole()
    {
        if ($this->system_role === self::ROLE_SUPER) {
            return self::ROLE_SUPER;
        }

        if ($this->_role === null) {
            if ($this->active_role) {
                return $this->active_role;
            }

            $organization = Organizations::getCurrentOrganization();
            if ($organization) {
                $role = $organization->users[$this->id];
                if ($role) {
                    $this->_role = $role->role;
                }
            }
            if ($this->_role === null) {
                $this->_role = false;
            }
        }

        return $this->_role;
    }private $_list = null;

    public function getOrganizationsList($query = false)
    {
        if ($this->_list === null) {
            if (Yii::$app->user->can("SUPER")) {
                if ($this->_list === null) {
                    $organizations = Organizations::find()->indexBy('id')->orderBy("name");
                    $moderation = $this->infoJson['moderation'];
                    if ($moderation and $moderation['organization_types']) {
                        $organizations->andWhere([
                            'in',
                            'type',
                            $moderation['organization_types']
                        ]);
                    }
                    $this->_list = $query ? $organizations : $organizations->all();
                }
            } else {
                $orgs = $query ? $this->getOrganizations() : $this->organizations;
                if ($orgs and !$query) {
                    foreach ($orgs as $org) {
                        $this->_list[$org->role][$org->target_id] = $org->organization;
                    }
                } elseif ($query) {
                    return $orgs;
                } else {
                    $this->_list = [];
                }
            }
        }
        return $this->_list;
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     *
     * @param \app\models\Organizations $organization
     *
     * @return mixed
     */
    public static function getRoles($organization = null)
    {
        $roles = [
            SystemRoles::PARENT,
            OrganizationRoles::ADMIN,
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
            OrganizationRoles::TEACHER,
            OrganizationRoles::NO_ROLE
        ];

        $data = [];
        $roleList = Lists::getRoles();
        foreach ($roles as $role) {
            if (!isset($roleList[$role])) {
                $data[$role] = Yii::t('main', 'Роль `{role}` не определена', [
                    'role' => $role
                ]);
                continue;
            }
            $data[$role] = $roleList[$role];
        }
        return $data;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserOrganizations()
    {
        return $this->hasMany(UserOrganization::class, ['related_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentUserOrganizations(){
        return $this->hasMany(UserOrganization::class, ['related_id' => 'id'])->onCondition(['user_organization.target_id' => Organizations::getCurrentOrganizationId()]);
    }

    public function getRolesMap(){
        $userOrgs = $this->userOrganizations;
        $result = [];
        $roleList = Lists::getRoles();
        foreach ($userOrgs as $userOrg){
            $result[$userOrg->id] = $userOrg->organization->name.' ('.$roleList[$userOrg->role].')';
        }
        return $result;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iin' => 'ИИН',
            'username' => 'Логин',
            'email' => 'Email',
            'phone' => 'Мобильный телефон',
            'fio' => 'ФИО',
            'contacts' => 'Контакты',
            'parent_contacts' => 'Контакты родителя',
            'home_phone' => 'Телефон',
            'address' => 'Адрес',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'sex' => 'Пол',
            'genderLabel' => 'Пол',
            'birth_date' => 'Дата рождения',
            'info' => 'Info',
            'status' => 'Статус',
            'balance' => 'На счету ученика',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'statusLabel' => 'Статус',
        ]; // TODO: Change the autogenerated stub
    }

    /**
     * @return array
     */
    public static function getStatusList(){
        return [
            self::STATUS_DELETED => Yii::t('main', 'Удален'),
            self::STATUS_ACTIVE => Yii::t('main', 'Активный'),
            self::STATUS_INACTIVE => Yii::t('main', 'Не активный'),
        ];
    }

    public function getGenderLabel(){
        return Lists::getGenders()[$this->sex];
    }

    public function getStatusLabel(){
        return self::getStatusList()[$this->status];
    }
}