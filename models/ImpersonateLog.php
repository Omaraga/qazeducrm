<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель логов impersonate (вход под другим пользователем)
 *
 * @property int $id
 * @property int $admin_user_id ID администратора
 * @property int $target_user_id ID целевого пользователя
 * @property int|null $organization_id ID организации
 * @property string $action Действие (start/end)
 * @property string|null $ip_address IP адрес
 * @property string|null $user_agent User Agent браузера
 * @property string $created_at Дата создания
 *
 * @property User $adminUser Администратор
 * @property User $targetUser Целевой пользователь
 * @property Organizations $organization Организация
 */
class ImpersonateLog extends ActiveRecord
{
    const ACTION_START = 'start';
    const ACTION_END = 'end';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%impersonate_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_user_id', 'target_user_id', 'action'], 'required'],
            [['admin_user_id', 'target_user_id', 'organization_id'], 'integer'],
            [['action'], 'string', 'max' => 20],
            [['action'], 'in', 'range' => [self::ACTION_START, self::ACTION_END]],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string'],
            [['admin_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['admin_user_id' => 'id']],
            [['target_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['target_user_id' => 'id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_user_id' => 'Администратор',
            'target_user_id' => 'Целевой пользователь',
            'organization_id' => 'Организация',
            'action' => 'Действие',
            'ip_address' => 'IP адрес',
            'user_agent' => 'User Agent',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * Записать лог входа/выхода impersonate
     *
     * @param int $adminId ID администратора
     * @param int $targetId ID целевого пользователя
     * @param string $action Действие (start/end)
     * @param int|null $orgId ID организации
     * @return bool
     */
    public static function log(int $adminId, int $targetId, string $action, ?int $orgId = null): bool
    {
        $log = new self();
        $log->admin_user_id = $adminId;
        $log->target_user_id = $targetId;
        $log->organization_id = $orgId;
        $log->action = $action;

        if (!Yii::$app->request->isConsoleRequest) {
            $log->ip_address = Yii::$app->request->userIP;
            $log->user_agent = Yii::$app->request->userAgent;
        }

        return $log->save();
    }

    /**
     * Получить метку действия
     *
     * @return string
     */
    public function getActionLabel(): string
    {
        $labels = [
            self::ACTION_START => 'Вход под пользователем',
            self::ACTION_END => 'Выход из сессии',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    /**
     * Связь с администратором
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(User::class, ['id' => 'admin_user_id']);
    }

    /**
     * Связь с целевым пользователем
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTargetUser()
    {
        return $this->hasOne(User::class, ['id' => 'target_user_id']);
    }

    /**
     * Связь с организацией
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }
}
