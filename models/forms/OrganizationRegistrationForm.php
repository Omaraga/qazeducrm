<?php

namespace app\models\forms;

use app\models\Organizations;
use app\models\User;
use app\models\SaasPlan;
use app\models\OrganizationSubscription;
use app\models\OrganizationActivityLog;
use app\models\relations\UserOrganization;
use app\helpers\OrganizationRoles;
use Yii;
use yii\base\Model;

/**
 * Форма регистрации новой организации.
 *
 * Создаёт организацию, администратора и пробную подписку.
 */
class OrganizationRegistrationForm extends Model
{
    // Данные организации
    public $org_name;
    public $org_email;
    public $org_phone;
    public $org_bin;
    public $org_address;

    // Данные администратора
    public $admin_first_name;
    public $admin_last_name;
    public $admin_email;
    public $admin_phone;
    public $admin_password;
    public $admin_password_repeat;

    // Тарифный план
    public $plan_id;

    // Согласие
    public $agree_terms;

    /**
     * @var Organizations|null Созданная организация
     */
    private $_organization;

    /**
     * @var User|null Созданный администратор
     */
    private $_admin;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Обязательные поля
            [['org_name', 'org_email', 'org_phone'], 'required', 'message' => '{attribute} обязателен для заполнения'],
            [['admin_first_name', 'admin_last_name', 'admin_email', 'admin_password', 'admin_password_repeat'], 'required', 'message' => '{attribute} обязателен для заполнения'],
            [['plan_id'], 'required', 'message' => 'Выберите тарифный план'],
            [['agree_terms'], 'required', 'requiredValue' => 1, 'message' => 'Необходимо согласиться с условиями использования'],

            // Валидация организации
            [['org_name'], 'string', 'min' => 2, 'max' => 255],
            [['org_email'], 'email', 'message' => 'Введите корректный email'],
            [['org_email'], 'validateUniqueOrgEmail'],
            [['org_phone'], 'string', 'max' => 20],
            [['org_bin'], 'string', 'max' => 12],
            [['org_bin'], 'match', 'pattern' => '/^\d{12}$/', 'message' => 'БИН должен состоять из 12 цифр', 'skipOnEmpty' => true],
            [['org_address'], 'string', 'max' => 500],

            // Валидация администратора
            [['admin_first_name', 'admin_last_name'], 'string', 'min' => 2, 'max' => 100],
            [['admin_email'], 'email', 'message' => 'Введите корректный email'],
            [['admin_email'], 'validateUniqueAdminEmail'],
            [['admin_phone'], 'string', 'max' => 20],
            [['admin_password'], 'string', 'min' => 8, 'message' => 'Пароль должен содержать минимум 8 символов'],
            [['admin_password_repeat'], 'compare', 'compareAttribute' => 'admin_password', 'message' => 'Пароли не совпадают'],

            // Тарифный план
            [['plan_id'], 'integer'],
            [['plan_id'], 'exist', 'targetClass' => SaasPlan::class, 'targetAttribute' => 'id', 'filter' => ['is_active' => 1]],

            // Согласие
            [['agree_terms'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'org_name' => 'Название организации',
            'org_email' => 'Email организации',
            'org_phone' => 'Телефон организации',
            'org_bin' => 'БИН',
            'org_address' => 'Адрес',
            'admin_first_name' => 'Имя',
            'admin_last_name' => 'Фамилия',
            'admin_email' => 'Email администратора',
            'admin_phone' => 'Телефон администратора',
            'admin_password' => 'Пароль',
            'admin_password_repeat' => 'Повторите пароль',
            'plan_id' => 'Тарифный план',
            'agree_terms' => 'Согласен с условиями использования',
        ];
    }

    /**
     * Проверка уникальности email организации
     */
    public function validateUniqueOrgEmail($attribute)
    {
        if (!$this->hasErrors($attribute)) {
            $exists = Organizations::find()
                ->andWhere(['email' => $this->$attribute])
                ->andWhere(['is_deleted' => 0])
                ->exists();

            if ($exists) {
                $this->addError($attribute, 'Организация с таким email уже зарегистрирована');
            }
        }
    }

    /**
     * Проверка уникальности email администратора
     */
    public function validateUniqueAdminEmail($attribute)
    {
        if (!$this->hasErrors($attribute)) {
            $exists = User::find()
                ->andWhere(['email' => $this->$attribute])
                ->andWhere(['status' => User::STATUS_ACTIVE])
                ->exists();

            if ($exists) {
                $this->addError($attribute, 'Пользователь с таким email уже существует');
            }
        }
    }

    /**
     * Регистрация организации
     *
     * @return bool
     */
    public function register(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // 1. Создаём организацию
            $organization = new Organizations();
            $organization->name = $this->org_name;
            $organization->email = $this->org_email;
            $organization->phone = $this->org_phone;
            $organization->bin = $this->org_bin;
            $organization->address = $this->org_address;
            $organization->type = Organizations::TYPE_HEAD;
            $organization->status = Organizations::STATUS_PENDING;
            $organization->timezone = 'Asia/Almaty';
            $organization->locale = 'ru';
            $organization->verification_token = Yii::$app->security->generateRandomString(32);

            if (!$organization->save()) {
                throw new \Exception('Не удалось создать организацию: ' . implode(', ', $organization->getFirstErrors()));
            }

            // 2. Создаём администратора
            $admin = new User();
            $admin->first_name = $this->admin_first_name;
            $admin->last_name = $this->admin_last_name;
            $admin->fio = $this->admin_last_name . ' ' . $this->admin_first_name;
            $admin->email = $this->admin_email;
            $admin->username = $this->admin_email;
            $admin->phone = $this->admin_phone;
            $admin->status = User::STATUS_ACTIVE;
            $admin->setPassword($this->admin_password);
            $admin->generateAuthKey();
            $admin->generateEmailVerificationToken();

            if (!$admin->save()) {
                throw new \Exception('Не удалось создать администратора: ' . implode(', ', $admin->getFirstErrors()));
            }

            // 3. Связываем администратора с организацией
            $userOrg = new UserOrganization();
            $userOrg->related_id = $admin->id;
            $userOrg->target_id = $organization->id;
            $userOrg->role = OrganizationRoles::GENERAL_DIRECTOR;

            if (!$userOrg->save()) {
                throw new \Exception('Не удалось связать пользователя с организацией');
            }

            // 4. Создаём пробную подписку
            $plan = SaasPlan::findOne($this->plan_id);
            $subscription = OrganizationSubscription::createTrial($organization->id, $plan->id);

            if (!$subscription->save()) {
                throw new \Exception('Не удалось создать подписку: ' . implode(', ', $subscription->getFirstErrors()));
            }

            // 5. Логируем активность
            OrganizationActivityLog::log(
                $organization->id,
                OrganizationActivityLog::ACTION_ORGANIZATION_CREATED,
                OrganizationActivityLog::CATEGORY_ORGANIZATION,
                "Организация зарегистрирована. План: {$plan->name}"
            );

            $transaction->commit();

            $this->_organization = $organization;
            $this->_admin = $admin;

            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Registration error: ' . $e->getMessage(), 'registration');
            $this->addError('org_name', 'Произошла ошибка при регистрации. Попробуйте позже.');
            return false;
        }
    }

    /**
     * Отправка email с подтверждением
     *
     * @return bool
     */
    public function sendVerificationEmail(): bool
    {
        if (!$this->_organization || !$this->_admin) {
            return false;
        }

        try {
            return Yii::$app->mailer->compose('organization-verification', [
                'organization' => $this->_organization,
                'admin' => $this->_admin,
                'verificationLink' => Yii::$app->urlManager->createAbsoluteUrl([
                    'registration/verify-email',
                    'token' => $this->_organization->verification_token,
                ]),
            ])
                ->setTo($this->_organization->email)
                ->setSubject('Подтверждение регистрации — ' . Yii::$app->name)
                ->send();
        } catch (\Exception $e) {
            Yii::error('Failed to send verification email: ' . $e->getMessage(), 'registration');
            return false;
        }
    }

    /**
     * Получить созданную организацию
     *
     * @return Organizations|null
     */
    public function getOrganization(): ?Organizations
    {
        return $this->_organization;
    }

    /**
     * Получить созданного администратора
     *
     * @return User|null
     */
    public function getAdmin(): ?User
    {
        return $this->_admin;
    }

    /**
     * Получить список доступных тарифов для формы
     *
     * @return array
     */
    public static function getPlanOptions(): array
    {
        $plans = SaasPlan::find()
            ->andWhere(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        $options = [];
        foreach ($plans as $plan) {
            $options[$plan->id] = [
                'label' => $plan->name,
                'price' => $plan->getFormattedPriceMonthly(),
                'trial_days' => $plan->trial_days,
                'description' => $plan->description,
                'features' => $plan->getFeaturesArray(),
                'limits' => [
                    'pupils' => $plan->max_pupils ?: '∞',
                    'teachers' => $plan->max_teachers ?: '∞',
                    'groups' => $plan->max_groups ?: '∞',
                ],
            ];
        }

        return $options;
    }
}
