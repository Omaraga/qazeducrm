<?php

namespace app\models;

use app\traits\AttributesToInfoTrait;
use app\traits\UpdateInsteadOfDeleteTrait;
use app\components\ActiveRecord;
use app\helpers\OrganizationHelper;
use app\helpers\OrganizationRoles;
use app\models\relations\UserOrganization;
use Yii;
use yii\console\Application;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "organization".
 *
 * @property int              $id
 * @property int|null         $parent_id
 * @property string           $type
 * @property string           $name
 * @property string           $status
 * @property string|null      $email
 * @property string|null      $email_verified_at
 * @property string|null      $verification_token
 * @property string|null      $bin
 * @property string|null      $legal_name
 * @property string|null      $logo
 * @property string           $timezone
 * @property string           $locale
 * @property int              $is_deleted
 * @property string           $info
 * @property string           $address
 * @property string           $phone
 * @property string           $created_at
 * @property string           $updated_at
 *
 * @property Organizations|null $parentOrganization
 * @property Organizations[] $branches
 * @property OrganizationSubscription[] $subscriptions
 * @property OrganizationSubscription|null $activeSubscription
 * @property OrganizationPayment[] $payments
 * @property OrganizationActivityLog[] $activityLogs
 */
class Organizations extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait, AttributesToInfoTrait;

    // Типы организации
    const TYPE_HEAD = 'head';
    const TYPE_BRANCH = 'branch';

    // Статусы организации
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BLOCKED = 'blocked';

    public function preload()
    {
        // ORGANIZATION PRELOAD DATA
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organization';
    }

    public function attributesToInfo()
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'phone', 'address', 'email', 'bin', 'legal_name', 'logo', 'verification_token'], 'string'],
            [['status', 'type', 'timezone', 'locale'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['bin'], 'string', 'max' => 12],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_BLOCKED]],
            [['type'], 'in', 'range' => [self::TYPE_HEAD, self::TYPE_BRANCH]],
            [['email_verified_at'], 'safe'],
            [['parent_id', 'is_deleted'], 'integer'],
            [['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(UserOrganization::class, ['target_id' => 'id'])->indexBy("related_id");
    }

    public function getTeachers(){
        return $this->hasMany(User::class, ['id', 'related_id'])->via('users')->onCondition(['user_organization.role' => OrganizationRoles::TEACHER]);
    }

    public static function getOrganizationTeachersMap(){
        $users = User::find()->innerJoinWith(['currentUserOrganizations' => function($q){
            $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
        }])->all();
        return ArrayHelper::map($users, 'id', 'fio');
    }

    /**
     * Получить всех учителей текущей организации
     * @return User[]
     */
    public static function getOrganizationTeachers(): array
    {
        return User::find()->innerJoinWith(['currentUserOrganizations' => function($q){
            $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])
              ->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
        }])->all();
    }


    public static function setCurrentOrganization($organization = null, $id = null)
    {
        if ($organization) {
            static::$_current_organization = $organization;
        } else {
            static::$_current_organization = Organizations::getList()[$id];
        }
        static::$_id = $id ?: ($organization ? $organization->id : null);
    }

    public static $_current_organization = -1;

    /**
     * @return array|int|null|Organizations
     */
    public static function getCurrentOrganization()
    {
        if (Yii::$app instanceof Application) {
            return null;
        }

        if (static::$_current_organization === -1) {
            if (static::getCurrentOrganizationId()) {
                static::$_current_organization = static::find()->where(['id' =>static::getCurrentOrganizationId()])->one();
            } else {
                $organizations = Yii::$app->user->identity->organizations ?? [];
                if (!Yii::$app->user->isGuest and $organizations) {
                    $currentOrgId = OrganizationHelper::getUnblockedOrganizationId($organizations);
                    static::$_current_organization = $currentOrgId ? static::find()->where(['id' => $currentOrgId])->one() : null;
                } else {
                    static::$_current_organization = null;
                }
            }
        }
        return static::$_current_organization;
    }

    public static $_id = -1;

    public static function getCurrentOrganizationId()
    {
        if (static::$_id === -1) {
            $id = null;
            if (!Yii::$app->request->isConsoleRequest) {
                $id = Yii::$app->request->getHeaders()->get('X-SERVER-ID') ?: Yii::$app->request->get('oid');
                if (!$id and Yii::$app->user->id and !Yii::$app->user->can("SUPER") and Yii::$app->user->identity->active_organization_id) {
                    $id = Yii::$app->user->identity->active_organization_id;
                }
            } else {
                if (isset(Yii::$app->controller->organization_id)) {
                    $id = Yii::$app->controller->organization_id;
                }
            }
            if (isset(Yii::$app->user)) {
                if (!$id and Yii::$app->user->can("SUPER")) {
                    $id = 0;
                }
            }
            static::$_id = $id;
        }
        return static::$_id;
    }

    public function applyUser($user, $role = OrganizationRoles::TEACHER)
    {
        $user_org = UserOrganization::find()->andWhere([
            'target_id' => $this->id,
            'related_id' => $user->id
        ])->one();
        if (!$user_org) {
            $user_org = new UserOrganization();
            $user_org->organization_id = $this->id;
            $user_org->target_id = $this->id;
            $user_org->related_id = $user->id;
            $user_org->role = $role;
            $user_org->save();
        }
        return true;
    }

    protected static $_organization = null;


    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function getList()
    {
        if (!Yii::$app->has("organizations_list")) {
            Yii::$app->set("organizations_list", function () {
                return Organizations::find()->indexBy('id')->all();
            });
        }
        return Yii::$app->get("organizations_list");
    }



    public function getDirector()
    {
        return User::find()->joinWith([
            'organizations'
        ])->andWhere([
            'user_organization.role' => [OrganizationRoles::DIRECTOR],
            'user_organization.target_id' => $this->id,
            'users.is_deleted' => 0,
            'user_organization.state' => UserOrganization::STATE_ACTIVE
        ])->one();
    }


    public function getAdmins()
    {
        return User::find()->joinWith([
            'organizations'
        ])->andWhere([
            'user_organization.role' => OrganizationRoles::ADMIN,
            'user_organization.target_id' => $this->id,
            'users.is_deleted' => 0
        ])->all();
    }

    /**
     * Возвращает идентификатор организации
     *
     * @param Organizations $organization
     *
     * @return int
     * @author Alexander Mityukhin  <almittt@mail.ru>
     */
    public static function getId(Organizations $organization): int
    {
        return $organization->id;
    }

    // ==================== BRANCH RELATIONS ====================

    /**
     * Связь с головной организацией
     */
    public function getParentOrganization()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * Связь с филиалами
     */
    public function getBranches()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * Это головная организация?
     */
    public function isHead(): bool
    {
        return $this->type === self::TYPE_HEAD || $this->parent_id === null;
    }

    /**
     * Это филиал?
     */
    public function isBranch(): bool
    {
        return $this->type === self::TYPE_BRANCH && $this->parent_id !== null;
    }

    /**
     * Получить головную организацию (для филиала - родитель, для головной - сама)
     */
    public function getHeadOrganization(): self
    {
        return $this->isHead() ? $this : $this->parentOrganization;
    }

    /**
     * Количество филиалов
     */
    public function getBranchCount(): int
    {
        return $this->getBranches()->count();
    }

    // ==================== SUBSCRIPTION RELATIONS ====================

    /**
     * Связь со всеми подписками
     */
    public function getSubscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class, ['organization_id' => 'id']);
    }

    /**
     * Активная подписка (для филиала берём подписку головной)
     */
    public function getActiveSubscription(): ?OrganizationSubscription
    {
        $headOrg = $this->getHeadOrganization();
        return OrganizationSubscription::findActiveByOrganization($headOrg->id);
    }

    /**
     * Связь с платежами
     */
    public function getPayments()
    {
        return $this->hasMany(OrganizationPayment::class, ['organization_id' => 'id']);
    }

    /**
     * Связь с логами активности
     */
    public function getActivityLogs()
    {
        return $this->hasMany(OrganizationActivityLog::class, ['organization_id' => 'id']);
    }

    // ==================== STATUS METHODS ====================

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => Yii::t('main', 'Ожидает'),
            self::STATUS_ACTIVE => Yii::t('main', 'Активна'),
            self::STATUS_SUSPENDED => Yii::t('main', 'Приостановлена'),
            self::STATUS_BLOCKED => Yii::t('main', 'Заблокирована'),
        ];
    }

    /**
     * Название статуса
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    /**
     * Список типов
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_HEAD => Yii::t('main', 'Головная'),
            self::TYPE_BRANCH => Yii::t('main', 'Филиал'),
        ];
    }

    /**
     * Название типа
     */
    public function getTypeLabel(): string
    {
        return self::getTypeList()[$this->type] ?? $this->type;
    }

    /**
     * Активна ли организация
     */
    public function isActiveStatus(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // ==================== SUBSCRIPTION LIMITS ====================

    /**
     * Проверить, есть ли активная подписка
     */
    public function hasActiveSubscription(): bool
    {
        return $this->getActiveSubscription() !== null;
    }

    /**
     * Получить текущий тарифный план
     */
    public function getCurrentPlan(): ?SaasPlan
    {
        $subscription = $this->getActiveSubscription();
        return $subscription ? $subscription->saasPlan : null;
    }

    /**
     * Получить лимит из подписки
     */
    public function getLimit(string $field): int
    {
        $subscription = $this->getActiveSubscription();
        return $subscription ? $subscription->getLimit($field) : 0;
    }

    /**
     * Проверить, превышен ли лимит
     * @param string $field Поле лимита (max_pupils, max_teachers, etc.)
     * @param int $currentCount Текущее количество
     */
    public function isLimitExceeded(string $field, int $currentCount): bool
    {
        $limit = $this->getLimit($field);
        // 0 означает безлимит
        if ($limit === 0) {
            return false;
        }
        return $currentCount >= $limit;
    }

    /**
     * Можно ли добавить ещё одну единицу (ученика, учителя и т.д.)
     */
    public function canAdd(string $field, int $currentCount): bool
    {
        return !$this->isLimitExceeded($field, $currentCount);
    }

    // ==================== EMAIL VERIFICATION ====================

    /**
     * Сгенерировать токен верификации
     */
    public function generateVerificationToken(): string
    {
        $this->verification_token = Yii::$app->security->generateRandomString(64);
        return $this->verification_token;
    }

    /**
     * Подтвердить email
     */
    public function verifyEmail(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->verification_token = null;
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * Email подтверждён?
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
}
