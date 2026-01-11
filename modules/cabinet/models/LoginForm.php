<?php

namespace app\modules\cabinet\models;

use app\models\Pupil;
use app\modules\cabinet\Module;
use app\services\verification\TelegramVerificationService;
use Yii;
use yii\base\Model;

/**
 * Форма авторизации родителя в личном кабинете
 * Авторизация по номеру телефона родителя + код из Telegram
 */
class LoginForm extends Model
{
    const SCENARIO_PHONE = 'phone';
    const SCENARIO_CODE = 'code';

    /**
     * @var string Номер телефона родителя
     */
    public $phone;

    /**
     * @var string Код подтверждения из Telegram
     */
    public $code;

    /**
     * @var int ID организации
     */
    public $organization_id;

    /**
     * @var Pupil[] Найденные ученики
     */
    private $_pupils;

    /**
     * @var TelegramVerificationService
     */
    private $_verificationService;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->_verificationService = new TelegramVerificationService();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Шаг 1: Ввод телефона
            [['phone', 'organization_id'], 'required', 'on' => self::SCENARIO_PHONE],
            ['phone', 'string', 'min' => 10, 'max' => 20, 'on' => self::SCENARIO_PHONE],
            ['phone', 'validatePhone', 'on' => self::SCENARIO_PHONE],

            // Шаг 2: Ввод кода из Telegram
            [['code'], 'required', 'on' => self::SCENARIO_CODE],
            ['code', 'string', 'length' => 6, 'on' => self::SCENARIO_CODE],
            ['code', 'validateCode', 'on' => self::SCENARIO_CODE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => Yii::t('app', 'Номер телефона'),
            'code' => Yii::t('app', 'Код подтверждения'),
            'organization_id' => Yii::t('app', 'Организация'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_PHONE => ['phone', 'organization_id'],
            self::SCENARIO_CODE => ['code'],
        ];
    }

    /**
     * Валидация телефона - проверяем что есть ученики с таким телефоном родителя
     */
    public function validatePhone($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $phone = $this->normalizePhone($this->phone);

            // Ищем учеников по номеру родителя в указанной организации
            $this->_pupils = Pupil::find()
                ->where(['organization_id' => $this->organization_id])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['status' => Pupil::STATUS_ACTIVE])
                ->andWhere([
                    'or',
                    ['like', 'parent_phone', $phone],
                    ['like', 'phone', $phone],
                ])
                ->all();

            if (empty($this->_pupils)) {
                $this->addError($attribute, Yii::t('app', 'Ученики с таким номером телефона не найдены'));
            }
        }
    }

    /**
     * Валидация кода через TelegramVerificationService
     */
    public function validateCode($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $session = Yii::$app->session;
            $phone = $session->get('cabinet_temp_phone');
            $orgId = $session->get('cabinet_temp_organization_id');

            if (!$phone || !$orgId) {
                $this->addError($attribute, Yii::t('app', 'Сессия истекла. Начните авторизацию заново.'));
                return;
            }

            $result = $this->_verificationService->verifyCode($phone, $this->code, $orgId);

            if (!$result['success']) {
                $this->addError($attribute, $result['error']);
            }
        }
    }

    /**
     * Отправка кода через Telegram
     * @return bool|string true если успешно, 'not_linked' если телефон не привязан, false при ошибке
     */
    public function sendCode()
    {
        if (!$this->validate()) {
            return false;
        }

        $result = $this->_verificationService->sendCode($this->phone, $this->organization_id);

        if (!$result['success']) {
            if ($result['error'] === 'not_linked') {
                // Сохраняем телефон в сессию для проверки после привязки
                $session = Yii::$app->session;
                $session->set('cabinet_pending_phone', $this->normalizePhone($this->phone));
                $session->set('cabinet_pending_org', $this->organization_id);
                return 'not_linked';
            }

            $this->addError('phone', $result['message']);
            return false;
        }

        // Сохраняем для проверки кода
        $session = Yii::$app->session;
        $session->set('cabinet_temp_phone', $this->normalizePhone($this->phone));
        $session->set('cabinet_temp_organization_id', $this->organization_id);

        return true;
    }

    /**
     * Авторизация по коду
     * @return bool
     */
    public function login()
    {
        $this->scenario = self::SCENARIO_CODE;

        if (!$this->validate()) {
            return false;
        }

        $session = Yii::$app->session;
        $phone = $session->get('cabinet_temp_phone');
        $organizationId = $session->get('cabinet_temp_organization_id');

        // Находим учеников
        $pupils = Pupil::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['status' => Pupil::STATUS_ACTIVE])
            ->andWhere([
                'or',
                ['like', 'parent_phone', $phone],
                ['like', 'phone', $phone],
            ])
            ->all();

        if (empty($pupils)) {
            $this->addError('code', Yii::t('app', 'Ученики не найдены'));
            return false;
        }

        // Собираем ID учеников
        $pupilIds = array_map(function($p) { return $p->id; }, $pupils);

        // Генерируем криптографически безопасный уникальный ID сессии родителя
        $parentId = Yii::$app->security->generateRandomString(32);

        // Сохраняем авторизацию в сессию
        Module::setAuthData($parentId, $pupilIds, $organizationId, $phone);

        // Очищаем временные данные
        $session->remove('cabinet_temp_phone');
        $session->remove('cabinet_temp_organization_id');
        $session->remove('cabinet_pending_phone');
        $session->remove('cabinet_pending_org');

        return true;
    }

    /**
     * Проверить привязан ли телефон к Telegram
     */
    public function isPhoneLinked(): bool
    {
        return $this->_verificationService->isPhoneLinked($this->phone);
    }

    /**
     * Нормализация номера телефона
     */
    public function normalizePhone($phone)
    {
        // Убираем всё кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Убираем 8 или 7 в начале если есть
        if (strlen($phone) === 11 && ($phone[0] === '8' || $phone[0] === '7')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Получить найденных учеников
     */
    public function getPupils()
    {
        return $this->_pupils;
    }
}
