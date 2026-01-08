<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Настройки доступа организации.
 * Позволяет гибко настраивать права доступа для разных ролей.
 *
 * @property int $id
 * @property int $organization_id
 * @property array $settings
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property User $updatedByUser
 */
class OrganizationAccessSettings extends ActiveRecord
{
    // ===== НАСТРОЙКИ ДЛЯ ADMIN =====

    // Платежи
    const ADMIN_PAYMENT_DIRECT_EDIT = 'admin_payment_direct_edit';       // Прямое редактирование (без запросов)
    const ADMIN_PAYMENT_DIRECT_DELETE = 'admin_payment_direct_delete';   // Прямое удаление (без запросов)

    // Ученики
    const ADMIN_PUPIL_DELETE = 'admin_pupil_delete';                     // Удаление учеников
    const ADMIN_PUPIL_VIEW_BALANCE = 'admin_pupil_view_balance';         // Просмотр баланса

    // Лиды
    const ADMIN_LIDS_DELETE = 'admin_lids_delete';                       // Удаление лидов

    // Группы
    const ADMIN_GROUP_DELETE = 'admin_group_delete';                     // Удаление групп

    // Финансы
    const ADMIN_VIEW_FINANCE_DASHBOARD = 'admin_view_finance_dashboard'; // Финансы на дашборде
    const ADMIN_VIEW_SALARY = 'admin_view_salary';                       // Просмотр зарплат

    // ===== НАСТРОЙКИ ДЛЯ TEACHER =====

    // Занятия
    const TEACHER_LESSON_CREATE = 'teacher_lesson_create';               // Создание занятий
    const TEACHER_LESSON_EDIT = 'teacher_lesson_edit';                   // Редактирование занятий
    const TEACHER_LESSON_DELETE = 'teacher_lesson_delete';               // Удаление занятий

    // Ученики
    const TEACHER_PUPIL_VIEW_CONTACTS = 'teacher_pupil_view_contacts';   // Просмотр контактов (телефоны)
    const TEACHER_PUPIL_VIEW_BALANCE = 'teacher_pupil_view_balance';     // Просмотр баланса учеников

    // Зарплата
    const TEACHER_VIEW_OWN_SALARY = 'teacher_view_own_salary';           // Просмотр своей зарплаты

    // Группы
    const TEACHER_VIEW_ALL_GROUPS = 'teacher_view_all_groups';           // Просмотр всех групп (не только своих)

    /**
     * Значения по умолчанию для всех настроек
     */
    const DEFAULTS = [
        // Admin - по умолчанию ограниченный доступ
        self::ADMIN_PAYMENT_DIRECT_EDIT => false,
        self::ADMIN_PAYMENT_DIRECT_DELETE => false,
        self::ADMIN_PUPIL_DELETE => false,
        self::ADMIN_PUPIL_VIEW_BALANCE => true,
        self::ADMIN_LIDS_DELETE => false,
        self::ADMIN_GROUP_DELETE => false,
        self::ADMIN_VIEW_FINANCE_DASHBOARD => false,
        self::ADMIN_VIEW_SALARY => false,

        // Teacher - базовые права
        self::TEACHER_LESSON_CREATE => true,
        self::TEACHER_LESSON_EDIT => true,
        self::TEACHER_LESSON_DELETE => false,
        self::TEACHER_PUPIL_VIEW_CONTACTS => true,
        self::TEACHER_PUPIL_VIEW_BALANCE => false,
        self::TEACHER_VIEW_OWN_SALARY => true,
        self::TEACHER_VIEW_ALL_GROUPS => false,
    ];

    /**
     * Описания настроек для UI
     */
    const LABELS = [
        self::ADMIN_PAYMENT_DIRECT_EDIT => 'Редактирование платежей без запроса',
        self::ADMIN_PAYMENT_DIRECT_DELETE => 'Удаление платежей без запроса',
        self::ADMIN_PUPIL_DELETE => 'Удаление учеников',
        self::ADMIN_PUPIL_VIEW_BALANCE => 'Просмотр баланса учеников',
        self::ADMIN_LIDS_DELETE => 'Удаление лидов',
        self::ADMIN_GROUP_DELETE => 'Удаление групп',
        self::ADMIN_VIEW_FINANCE_DASHBOARD => 'Финансовые данные на дашборде',
        self::ADMIN_VIEW_SALARY => 'Просмотр зарплат сотрудников',

        self::TEACHER_LESSON_CREATE => 'Создание занятий',
        self::TEACHER_LESSON_EDIT => 'Редактирование своих занятий',
        self::TEACHER_LESSON_DELETE => 'Удаление своих занятий',
        self::TEACHER_PUPIL_VIEW_CONTACTS => 'Просмотр контактов учеников',
        self::TEACHER_PUPIL_VIEW_BALANCE => 'Просмотр баланса учеников',
        self::TEACHER_VIEW_OWN_SALARY => 'Просмотр своей зарплаты',
        self::TEACHER_VIEW_ALL_GROUPS => 'Просмотр всех групп организации',
    ];

    /**
     * Подсказки для настроек
     */
    const HINTS = [
        self::ADMIN_PAYMENT_DIRECT_EDIT => 'Если выключено, админ отправляет запрос директору',
        self::ADMIN_PAYMENT_DIRECT_DELETE => 'Если выключено, админ отправляет запрос директору',
        self::ADMIN_PUPIL_DELETE => 'Разрешить администратору удалять учеников из системы',
        self::ADMIN_PUPIL_VIEW_BALANCE => 'Видеть баланс и историю платежей учеников',
        self::ADMIN_LIDS_DELETE => 'Разрешить администратору удалять лиды',
        self::ADMIN_GROUP_DELETE => 'Разрешить администратору удалять группы',
        self::ADMIN_VIEW_FINANCE_DASHBOARD => 'Показывать выручку и финансовые графики на главной',
        self::ADMIN_VIEW_SALARY => 'Просматривать начисленные зарплаты сотрудников',

        self::TEACHER_LESSON_CREATE => 'Создавать новые занятия для своих групп',
        self::TEACHER_LESSON_EDIT => 'Изменять время и параметры своих занятий',
        self::TEACHER_LESSON_DELETE => 'Удалять свои занятия из расписания',
        self::TEACHER_PUPIL_VIEW_CONTACTS => 'Видеть телефоны учеников и родителей',
        self::TEACHER_PUPIL_VIEW_BALANCE => 'Видеть баланс учеников своих групп',
        self::TEACHER_VIEW_OWN_SALARY => 'Просматривать свою начисленную зарплату',
        self::TEACHER_VIEW_ALL_GROUPS => 'Видеть все группы, не только назначенные',
    ];

    /**
     * Группировка настроек для UI
     */
    const GROUPS = [
        'admin' => [
            'label' => 'Права администратора',
            'description' => 'Настройки прав для роли "Администратор"',
            'settings' => [
                'Платежи' => [
                    self::ADMIN_PAYMENT_DIRECT_EDIT,
                    self::ADMIN_PAYMENT_DIRECT_DELETE,
                ],
                'Ученики' => [
                    self::ADMIN_PUPIL_VIEW_BALANCE,
                    self::ADMIN_PUPIL_DELETE,
                ],
                'Лиды и группы' => [
                    self::ADMIN_LIDS_DELETE,
                    self::ADMIN_GROUP_DELETE,
                ],
                'Финансы' => [
                    self::ADMIN_VIEW_FINANCE_DASHBOARD,
                    self::ADMIN_VIEW_SALARY,
                ],
            ],
        ],
        'teacher' => [
            'label' => 'Права учителя',
            'description' => 'Настройки прав для роли "Преподаватель"',
            'settings' => [
                'Занятия' => [
                    self::TEACHER_LESSON_CREATE,
                    self::TEACHER_LESSON_EDIT,
                    self::TEACHER_LESSON_DELETE,
                ],
                'Ученики' => [
                    self::TEACHER_PUPIL_VIEW_CONTACTS,
                    self::TEACHER_PUPIL_VIEW_BALANCE,
                ],
                'Прочее' => [
                    self::TEACHER_VIEW_OWN_SALARY,
                    self::TEACHER_VIEW_ALL_GROUPS,
                ],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'organization_access_settings';
    }

    /**
     * @return array[]
     */
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id'], 'required'],
            [['organization_id', 'updated_by'], 'integer'],
            [['settings'], 'safe'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'unique'],
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
            'organization_id' => 'Организация',
            'settings' => 'Настройки',
            'updated_by' => 'Обновил',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Получить настройки как массив
     * @return array
     */
    public function getSettingsArray(): array
    {
        if (empty($this->settings)) {
            return self::DEFAULTS;
        }

        $settings = is_string($this->settings) ? json_decode($this->settings, true) : $this->settings;

        // Объединяем с дефолтами для новых настроек
        return array_merge(self::DEFAULTS, $settings ?: []);
    }

    /**
     * Установить настройки из массива
     * @param array $settings
     */
    public function setSettingsArray(array $settings): void
    {
        // Фильтруем только известные ключи
        $filtered = [];
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                $filtered[$key] = (bool)$value;
            }
        }
        $this->settings = json_encode($filtered);
    }

    /**
     * Получить значение конкретной настройки
     * @param string $key
     * @return bool
     */
    public function getSetting(string $key): bool
    {
        $settings = $this->getSettingsArray();
        return $settings[$key] ?? (self::DEFAULTS[$key] ?? false);
    }

    /**
     * Получить или создать настройки для организации
     * @param int|null $organizationId
     * @return self
     */
    public static function getForOrganization(?int $organizationId = null): self
    {
        if ($organizationId === null) {
            $organizationId = Organizations::getCurrentOrganizationId();
        }

        $model = self::findOne(['organization_id' => $organizationId]);

        if ($model === null) {
            $model = new self();
            $model->organization_id = $organizationId;
            $model->settings = json_encode(self::DEFAULTS);
        }

        return $model;
    }

    /**
     * Быстрая проверка настройки для текущей организации
     * @param string $key
     * @return bool
     */
    public static function check(string $key): bool
    {
        static $cache = [];

        $orgId = Organizations::getCurrentOrganizationId();
        $cacheKey = $orgId . '_' . $key;

        if (!isset($cache[$cacheKey])) {
            $model = self::getForOrganization($orgId);
            $cache[$cacheKey] = $model->getSetting($key);
        }

        return $cache[$cacheKey];
    }

    /**
     * Сбросить кэш настроек
     */
    public static function clearCache(): void
    {
        // Статический кэш сбрасывается автоматически при следующем запросе
    }

    /**
     * Сохранить с логированием
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->updated_by = Yii::$app->user->id;
        return parent::save($runValidation, $attributeNames);
    }
}
