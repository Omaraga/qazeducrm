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

/**
 * This is the model class for table "organization".
 *
 * @property int              $id
 * @property string           $name
 * @property int              $is_deleted
 * @property string           $info
 * @property string           $address
 * @property string           $phone
 *
 */
class Organizations extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait, AttributesToInfoTrait;

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
            [
                [
                    'name',
                    'phone',
                    'address',
                ],
                'string'
            ],

            [
                [
                    'region_id',
                    'locality_id',
                    'province_id',
                    'type',
                    'server_id',
                    'edu_type',
                    'age_from',
                    'age_to',
                    'is_active',
                    'ownership_type',
                    'server_id',
                    'parent_id',
                ],
                'integer'
            ],
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(UserOrganization::class, ['target_id' => 'id'])->indexBy("related_id");
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
                static::$_current_organization = static::find()->byPk(static::getCurrentOrganizationId())->one();
            } else {
                $organizations = Yii::$app->user->identity->organizations ?? [];
                if (!Yii::$app->user->isGuest and $organizations) {
                    $currentOrgId = OrganizationHelper::getUnblockedOrganizationId($organizations);
                    static::$_current_organization = $currentOrgId ? static::find()->byPk($currentOrgId)->one() : null;
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
}
