<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель шаблонов SMS и WhatsApp
 *
 * @property int $id
 * @property int $organization_id
 * @property string $code
 * @property string $type
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

    // Типы шаблонов
    const TYPE_SMS = 'sms';
    const TYPE_WHATSAPP = 'whatsapp';

    // Коды шаблонов SMS
    const CODE_LESSON_REMINDER = 'lesson_reminder';      // Напоминание о занятии
    const CODE_LESSON_CANCELLED = 'lesson_cancelled';    // Отмена занятия
    const CODE_PAYMENT_DUE = 'payment_due';              // Задолженность
    const CODE_PAYMENT_RECEIVED = 'payment_received';    // Оплата получена
    const CODE_BIRTHDAY = 'birthday';                    // День рождения
    const CODE_TRIAL_INVITE = 'trial_invite';            // Приглашение на пробное
    const CODE_CUSTOM = 'custom';                        // Произвольное

    // Коды шаблонов WhatsApp для лидов
    const CODE_WA_FIRST_CONTACT = 'wa_first_contact';    // Первый контакт
    const CODE_WA_TRIAL_INVITE = 'wa_trial_invite';      // Приглашение на пробное
    const CODE_WA_AFTER_TRIAL = 'wa_after_trial';        // После пробного
    const CODE_WA_REMINDER = 'wa_reminder';              // Напоминание
    const CODE_WA_FOLLOW_UP = 'wa_follow_up';            // Повторное касание

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
            [['type'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 255],
            ['code', 'in', 'range' => array_keys(self::getAllCodeList())],
            ['type', 'in', 'range' => [self::TYPE_SMS, self::TYPE_WHATSAPP]],
            ['type', 'default', 'value' => self::TYPE_SMS],
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
            'code' => 'Код шаблона',
            'type' => 'Тип',
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
    public static function getTypeList()
    {
        return [
            self::TYPE_SMS => 'SMS',
            self::TYPE_WHATSAPP => 'WhatsApp',
        ];
    }

    /**
     * Список кодов SMS шаблонов
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
     * Список кодов WhatsApp шаблонов для лидов
     */
    public static function getWhatsAppCodeList()
    {
        return [
            self::CODE_WA_FIRST_CONTACT => 'Первый контакт',
            self::CODE_WA_TRIAL_INVITE => 'Приглашение на пробное',
            self::CODE_WA_AFTER_TRIAL => 'После пробного занятия',
            self::CODE_WA_REMINDER => 'Напоминание',
            self::CODE_WA_FOLLOW_UP => 'Повторное касание',
        ];
    }

    /**
     * Все коды шаблонов
     */
    public static function getAllCodeList()
    {
        return array_merge(self::getCodeList(), self::getWhatsAppCodeList());
    }

    /**
     * Название типа/кода
     */
    public function getCodeLabel()
    {
        $list = self::getAllCodeList();
        return $list[$this->code] ?? $this->code;
    }

    /**
     * Название типа шаблона
     */
    public function getTypeLabel()
    {
        $list = self::getTypeList();
        return $list[$this->type] ?? $this->type;
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
     * Найти WhatsApp шаблоны для лидов
     */
    public static function findWhatsAppTemplates()
    {
        return self::find()
            ->byOrganization()
            ->andWhere(['type' => self::TYPE_WHATSAPP, 'is_active' => 1])
            ->notDeleted()
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * Проверить, является ли шаблон WhatsApp
     */
    public function isWhatsApp(): bool
    {
        return $this->type === self::TYPE_WHATSAPP;
    }

    /**
     * Создать стандартные шаблоны для организации
     */
    public static function createDefaults($organizationId)
    {
        // SMS шаблоны
        $smsDefaults = [
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

        foreach ($smsDefaults as $data) {
            $template = new self();
            $template->organization_id = $organizationId;
            $template->type = self::TYPE_SMS;
            $template->code = $data['code'];
            $template->name = $data['name'];
            $template->content = $data['content'];
            $template->is_active = true;
            $template->save();
        }

        // Создаём также WhatsApp шаблоны
        self::createWhatsAppDefaults($organizationId);
    }

    /**
     * Создать стандартные WhatsApp шаблоны для организации
     */
    public static function createWhatsAppDefaults($organizationId)
    {
        $waDefaults = [
            [
                'code' => self::CODE_WA_FIRST_CONTACT,
                'name' => 'Первый контакт',
                'content' => "Здравствуйте, {name}! 👋\n\nМеня зовут {manager}, я представляю учебный центр «{org_name}».\n\nВы оставляли заявку на обучение. Расскажите, пожалуйста, какой предмет вас интересует?\n\nБуду рад ответить на все вопросы!",
            ],
            [
                'code' => self::CODE_WA_TRIAL_INVITE,
                'name' => 'Приглашение на пробное',
                'content' => "Здравствуйте, {name}! 📚\n\nПриглашаем вас на БЕСПЛАТНОЕ пробное занятие!\n\n📅 Дата: {date}\n⏰ Время: {time}\n📍 Адрес: {address}\n\nНа занятии вы:\n✅ Познакомитесь с преподавателем\n✅ Оцените методику обучения\n✅ Получите рекомендации\n\nПодтвердите, пожалуйста, ваше участие 🙏",
            ],
            [
                'code' => self::CODE_WA_AFTER_TRIAL,
                'name' => 'После пробного занятия',
                'content' => "Здравствуйте, {name}! 🌟\n\nСпасибо, что посетили наше пробное занятие!\n\nКак вам понравилось? Какие впечатления у {pupil_name}?\n\nГотов ответить на ваши вопросы и рассказать о вариантах обучения.",
            ],
            [
                'code' => self::CODE_WA_REMINDER,
                'name' => 'Напоминание',
                'content' => "Здравствуйте, {name}! ⏰\n\nНапоминаю о нашей договорённости.\n\nКогда вам будет удобно продолжить разговор?",
            ],
            [
                'code' => self::CODE_WA_FOLLOW_UP,
                'name' => 'Повторное касание',
                'content' => "Здравствуйте, {name}! 👋\n\nМы общались ранее по поводу обучения.\n\nВопрос ещё актуален? Может быть, появились новые вопросы?\n\nБуду рад помочь!",
            ],
        ];

        foreach ($waDefaults as $data) {
            // Проверяем, не существует ли уже такой шаблон
            $exists = self::find()
                ->andWhere(['organization_id' => $organizationId, 'code' => $data['code']])
                ->exists();

            if (!$exists) {
                $template = new self();
                $template->organization_id = $organizationId;
                $template->type = self::TYPE_WHATSAPP;
                $template->code = $data['code'];
                $template->name = $data['name'];
                $template->content = $data['content'];
                $template->is_active = true;
                $template->save();
            }
        }
    }

    /**
     * Получить плейсхолдеры для WhatsApp шаблонов
     */
    public static function getWhatsAppPlaceholders()
    {
        return [
            '{name}' => 'Имя контакта',
            '{pupil_name}' => 'Имя ребёнка',
            '{manager}' => 'Имя менеджера',
            '{org_name}' => 'Название организации',
            '{date}' => 'Дата',
            '{time}' => 'Время',
            '{address}' => 'Адрес',
            '{subject}' => 'Предмет',
        ];
    }
}
