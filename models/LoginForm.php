<?php

namespace app\models;

use app\models\relations\UserOrganization;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            // check organization status
            ['username', 'validateOrganizationStatus'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль.');
            }
        }
    }

    /**
     * Validates organization status.
     * Prevents login if user's organization is not active (pending approval).
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateOrganizationStatus($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if ($user) {
                // Skip check for superadmins
                if (Yii::$app->authManager->checkAccess($user->id, 'SUPER')) {
                    return;
                }

                // Check user's organizations
                $userOrganizations = UserOrganization::find()
                    ->where(['related_id' => $user->id])
                    ->all();

                if (!empty($userOrganizations)) {
                    $hasActiveOrg = false;
                    $pendingOrgName = null;

                    foreach ($userOrganizations as $userOrg) {
                        $organization = $userOrg->organization;
                        if ($organization) {
                            if ($organization->status === Organizations::STATUS_ACTIVE) {
                                $hasActiveOrg = true;
                                break;
                            } elseif ($organization->status === Organizations::STATUS_PENDING) {
                                $pendingOrgName = $organization->name;
                            }
                        }
                    }

                    if (!$hasActiveOrg && $pendingOrgName) {
                        $this->addError($attribute, "Ваша организация \"{$pendingOrgName}\" ожидает одобрения администратором. Пожалуйста, дождитесь подтверждения.");
                    } elseif (!$hasActiveOrg) {
                        $this->addError($attribute, 'Ваша организация заблокирована или приостановлена. Свяжитесь с администратором.');
                    }
                }
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
