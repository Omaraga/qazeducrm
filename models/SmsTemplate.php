<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель шаблонов SMS
 *
 * @property int $id
 * @property int $organization_id
 * @property string $code
 * @property string $name
 * @property string $content
 * @property bool $is_active
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 */
class SmsTemplate extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Коды шаблонов
    const CODE_LESSON_REMINDER = 'lesson_reminder';      // Напоминание о занятии
    const CODE_LESSON_CANCELLED = 'lesson_cancelled';    // Отмена занятия
    const CODE_PAYMENT_DUE = 'payment_due';              // Задолженность
    const CODE_PAYMENT_RECEIVED = 'payment_received';    // Оплата получена
    const CODE_BIRTHDAY = 'birthday';                    // День рождения
    const CODE_TRIAL_INVITE = 'trial_invite';            // Приглашение на пробное
    const CODE_CUSTOM = 'custom';                        // Произвольное

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_template}}';
    }

    /**
     * {@inheritdoc}
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
            [['code', 'name', 'content'], 'required'],
            [['organization_id', 'is_deleted'], 'integer'],
            [['content'], 'string'],
            [['is_active'], 'boolean'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            ['code', 'in', 'range' => array_keys(self::getCodeList())],
            ['is_active', 'default', 'value' => true],
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
            'code' => 'Тип шаблона',
            'name' => 'Название',
            'content' => 'Текст сообщения',
            'is_active' => 'Активен',
            'is_deleted' => 'Удалён',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * Список типов шаблонов
     */
    public static function getCodeList()
    {
        return [
            self::CODE_LESSON_REMINDER => 'Напоминание о занятии',
            self::CODE_LESSON_CANCELLED => 'Отмена занятия',
            self::CODE_PAYMENT_DUE => 'Задолженность',
            self::CODE_PAYMENT_RECEIVED => 'Оплата получена',
            self::CODE_BIRTHDAY => 'День рождения',
            self::CODE_TRIAL_INVITE => 'Приглашение на пробное',
            self::CODE_CUSTOM => 'Произвольное',
        ];
    }

    /**
     * Название типа
     */
    public function getCodeLabel()
    {
        $list = self::getCodeList();
        return $list[$this->code] ?? $this->code;
    }

    /**
     * Доступные плейсхолдеры для шаблона
     */
    public static function getPlaceholders()
    {
        return [
            '{name}' => 'Имя получателя',
            '{pupil_name}' => 'Имя ученика',
            '{date}' => 'Дата',
            '{time}' => 'Время',
            '{group}' => 'Название группы',
            '{subject}' => 'Предмет',
            '{amount}' => 'Сумма',
            '{balance}' => 'Баланс',
            '{org_name}' => 'Название организации',
            '{teacher}' => 'Имя преподавателя',
        ];
    }

    /**
     * Заменить плейсхолдеры на значения
     */
    public function render(array $data)
    {
        $message = $this->content;
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $message = str_replace($placeholder, $value, $message);
        }
        return $message;
    }

    /**
     * Найти шаблон по коду
     */
    public static function findByCode($code)
    {
        return self::find()
            ->byOrganization()
            ->andWhere(['code' => $code, 'is_active' => 1])
            ->notDeleted()
            ->one();
    }

    /**
     * Создать стандартные шаблоны для организации
     */
    public static function createDefaults($organizationId)
    {
        $defaults = [
            [
                'code' => self::CODE_LESSON_REMINDER,
                'name' => 'Напоминание о занятии',
                'content' => 'Здравствуйте, {name}! Напоминаем о занятии {date} в {time}. Группа: {group}. {org_name}',
            ],
            [
                'code' => self::CODE_LESSON_CANCELLED,
                'name' => 'Отмена занятия',
                'content' => 'Здравствуйте, {name}! Занятие {date} в {time} отменено. Приносим извинения. {org_name}',
            ],
            [
                'code' => self::CODE_PAYMENT_DUE,
                'name' => 'Задолженность',
                'content' => 'Здравствуйте, {name}! Напоминаем о задолженности {amount} тг за обучение {pupil_name}. {org_name}',
            ],
            [
                'code' => self::CODE_PAYMENT_RECEIVED,
                'name' => 'Оплата получена',
                'content' => 'Здравствуйте, {name}! Оплата {amount} тг получена. Баланс: {balance} тг. Спасибо! {org_name}',
            ],
            [
                'code' => self::CODE_BIRTHDAY,
                'name' => 'День рождения',
                'content' => 'Дорогой {name}! Поздравляем с Днём рождения! Желаем успехов в учёбе! {org_name}',
            ],
            [
                'code' => self::CODE_TRIAL_INVITE,
                'name' => 'Приглашение на пробное',
                'content' => 'Здравствуйте, {name}! Приглашаем на пробное занятие {date} в {time}. Ждём вас! {org_name}',
            ],
        ];

        foreach ($defaults as $data) {
            $template = new self();
            $template->organization_id = $organizationId;
            $template->code = $data['code'];
            $template->name = $data['name'];
            $template->content = $data['content'];
            $template->is_active = true;
            $template->save();
        }
    }
}
