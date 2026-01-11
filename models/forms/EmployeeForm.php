<?php

namespace app\models\forms;

use app\components\PhoneNumberValidator;
use app\helpers\OrganizationRoles;
use app\models\Organizations;
use app\models\relations\UserOrganization;
use app\models\User;
use Yii;

/**
 * Форма добавления/редактирования сотрудника организации
 * Поддерживает роли: teacher, admin, director
 */
class EmployeeForm extends \yii\base\Model
{
    public $id;
    public $fio;
    public $first_name;
    public $last_name;
    public $middle_name;
    public $birth_date;
    public $sex;
    public $username;
    public $iin;
    public $phone;
    public $home_phone;
    public $address;
    public $status = 10;
    public $email;
    public $role;

    /**
     * Получить список доступных ролей для выбора
     * @return array
     */
    public static function getRolesList(): array
    {
        return [
            OrganizationRoles::TEACHER => Yii::t('main', 'Преподаватель'),
            OrganizationRoles::ADMIN => Yii::t('main', 'Администратор'),
            OrganizationRoles::DIRECTOR => Yii::t('main', 'Директор филиала'),
        ];
    }

    /**
     * Получить название роли
     * @param string $role
     * @return string
     */
    public static function getRoleName(string $role): string
    {
        $roles = self::getRolesList();
        return $roles[$role] ?? $role;
    }

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'username', 'phone', 'email', 'role'], 'required'],
            [['first_name', 'last_name', 'middle_name', 'username', 'phone', 'email', 'address', 'iin'], 'string'],
            [['first_name', 'last_name', 'username', 'phone', 'email', 'address', 'sex', 'birth_date', 'iin', 'home_phone', 'middle_name', 'role'], 'safe'],
            [['sex'], 'integer'],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d'],
            [['phone', 'home_phone'], PhoneNumberValidator::class],
            [['role'], 'in', 'range' => array_keys(self::getRolesList())],
        ];
    }

    public function init()
    {
        $this->loadDefaultValues();
        parent::init();
    }

    public function loadDefaultValues()
    {
        // Роль по умолчанию - преподаватель
        if (empty($this->role)) {
            $this->role = OrganizationRoles::TEACHER;
        }

        if ($id = Yii::$app->request->get('id')) {
            $user = User::findOne($id);
            if ($user) {
                $this->fio = $user->fio;
                $this->first_name = $user->first_name;
                $this->middle_name = $user->middle_name;
                $this->last_name = $user->last_name;
                $this->sex = $user->sex;
                $this->birth_date = $user->birth_date;
                $this->phone = $user->phone;
                $this->home_phone = $user->home_phone;
                $this->address = $user->address;
                $this->username = $user->username;
                $this->email = $user->email;
                $this->iin = $user->iin;
                $this->status = $user->status;
                $this->id = $user->id;

                // Загрузить текущую роль пользователя в организации
                $userOrganization = UserOrganization::find()
                    ->where([
                        'related_id' => $user->id,
                        'target_id' => Organizations::getCurrentOrganizationId()
                    ])
                    ->one();
                if ($userOrganization) {
                    $this->role = $userOrganization->role;
                }
            }
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $model = User::findOne($this->id);
                if (!$model) {
                    $this->addError('id', 'Пользователь не найден');
                    $transaction->rollBack();
                    return false;
                }
            } else {
                $model = new User();
                $model->setPassword('Aa123456@');
                $model->generateAuthKey();
                $model->generateEmailVerificationToken();
            }

            $model->first_name = $this->first_name;
            $model->last_name = $this->last_name;
            $model->middle_name = $this->middle_name;
            $model->username = $this->username;
            $model->iin = $this->iin;
            $model->sex = $this->sex;
            $model->birth_date = $this->birth_date;
            $model->phone = $this->phone;
            $model->home_phone = $this->home_phone;
            $model->address = $this->address;
            $model->email = $this->email;
            $model->status = $this->status;
            $model->fio = trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);

            if (!$model->save()) {
                $this->addErrors($model->getErrors());
                $transaction->rollBack();
                return false;
            }

            $this->id = $model->id;

            // Найти или создать связь пользователя с организацией
            $organizationId = Organizations::getCurrentOrganizationId();
            $userOrganization = UserOrganization::find()
                ->where([
                    'related_id' => $model->id,
                    'target_id' => $organizationId
                ])
                ->one();

            if (!$userOrganization) {
                $userOrganization = new UserOrganization();
                $userOrganization->target_id = $organizationId;
                $userOrganization->organization_id = $organizationId;
                $userOrganization->related_id = $model->id;
            }

            // Обновить роль
            $userOrganization->role = $this->role;

            if (!$userOrganization->save()) {
                $this->addErrors($userOrganization->getErrors());
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error saving employee: ' . $e->getMessage());
            $this->addError('id', 'Ошибка сохранения: ' . $e->getMessage());
            return false;
        }
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
            'birth_date' => 'Дата рождения',
            'info' => 'Info',
            'status' => 'Статус',
            'balance' => 'На счету ученика',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'role' => 'Должность',
        ];
    }
}
