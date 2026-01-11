<?php

namespace app\services\verification;

use app\models\Organizations;
use app\models\Pupil;
use app\models\TelegramUser;
use app\models\VerificationCode;
use Yii;

/**
 * Сервис верификации через Telegram
 * Отправляет коды и обрабатывает webhook
 */
class TelegramVerificationService
{
    private TelegramBotService $bot;

    // Rate limiting
    const SECONDS_BETWEEN_CODES = 60;
    const MAX_CODES_PER_HOUR = 5;

    public function __construct()
    {
        $this->bot = new TelegramBotService();
    }

    /**
     * Проверить привязан ли телефон к Telegram
     */
    public function isPhoneLinked(string $phone): bool
    {
        return TelegramUser::findByPhone($phone) !== null;
    }

    /**
     * Отправить код верификации
     * @return array ['success' => bool, 'error' => string|null, 'message' => string|null]
     */
    public function sendCode(string $phone, int $orgId): array
    {
        $normalizedPhone = TelegramUser::normalizePhone($phone);

        // Проверяем есть ли ученики с таким телефоном
        $pupils = $this->findPupils($normalizedPhone, $orgId);
        if (empty($pupils)) {
            return [
                'success' => false,
                'error' => 'not_found',
                'message' => Yii::t('app', 'Ученики с таким номером телефона не найдены'),
            ];
        }

        // Проверяем привязку к Telegram
        $user = TelegramUser::findByPhone($normalizedPhone);
        if (!$user) {
            return [
                'success' => false,
                'error' => 'not_linked',
                'message' => Yii::t('app', 'Телефон не привязан к Telegram'),
            ];
        }

        // Rate limiting
        $rateLimitCheck = $this->checkRateLimit($normalizedPhone);
        if (!$rateLimitCheck['allowed']) {
            return [
                'success' => false,
                'error' => 'rate_limit',
                'message' => $rateLimitCheck['message'],
                'wait_seconds' => $rateLimitCheck['wait_seconds'] ?? 0,
            ];
        }

        // Генерируем код
        $verification = VerificationCode::generate($orgId, $normalizedPhone);

        // Отправляем в Telegram
        $org = Organizations::findOne($orgId);
        $orgName = $org ? $org->name : 'Education CRM';

        $message = Yii::t('app', "Код для входа в {org_name}:\n\n*{code}*\n\nКод действителен 5 минут.", [
            'org_name' => $orgName,
            'code' => $verification->code,
        ]);

        $sent = $this->bot->sendMessage($user->chat_id, $message);

        if ($sent) {
            $verification->markAsSent();
            $this->recordRateLimitAttempt($normalizedPhone);

            return ['success' => true];
        }

        return [
            'success' => false,
            'error' => 'send_failed',
            'message' => Yii::t('app', 'Не удалось отправить код. Попробуйте позже.'),
        ];
    }

    /**
     * Проверить код верификации
     * @return array ['success' => bool, 'error' => string|null, 'pupil_ids' => array|null]
     */
    public function verifyCode(string $phone, string $code, int $orgId): array
    {
        $normalizedPhone = TelegramUser::normalizePhone($phone);

        $verification = VerificationCode::find()
            ->where(['phone' => $normalizedPhone, 'organization_id' => $orgId])
            ->andWhere(['in', 'status', [VerificationCode::STATUS_PENDING, VerificationCode::STATUS_SENT]])
            ->andWhere(['>', 'expires_at', date('Y-m-d H:i:s')])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if (!$verification) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Код истёк или не найден. Запросите новый код.'),
            ];
        }

        if ($verification->verify($code)) {
            $pupils = $this->findPupils($normalizedPhone, $orgId);
            $pupilIds = array_map(fn($p) => $p->id, $pupils);

            return [
                'success' => true,
                'pupil_ids' => $pupilIds,
            ];
        }

        $attemptsLeft = VerificationCode::MAX_ATTEMPTS - $verification->attempts;

        if ($attemptsLeft <= 0) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Превышено количество попыток. Запросите новый код.'),
            ];
        }

        return [
            'success' => false,
            'error' => Yii::t('app', 'Неверный код. Осталось попыток: {count}', ['count' => $attemptsLeft]),
        ];
    }

    /**
     * Обработка webhook от Telegram
     */
    public function handleWebhook(array $update): void
    {
        if (isset($update['message']['contact'])) {
            $this->handleContact($update['message']);
        } elseif (isset($update['message']['text'])) {
            $this->handleCommand($update['message']);
        }
    }

    /**
     * Обработка команды (например /start)
     */
    private function handleCommand(array $message): void
    {
        $chatId = (string)$message['chat']['id'];
        $text = $message['text'] ?? '';

        if ($text === '/start' || mb_strtolower($text) === 'войти') {
            $user = TelegramUser::findByChatId($chatId);

            if ($user) {
                $this->bot->sendMessage(
                    $chatId,
                    Yii::t('app', "Вы уже зарегистрированы!\n\nВернитесь на сайт и нажмите 'Получить код'."),
                    $this->bot->getRemoveKeyboard()
                );
            } else {
                $this->bot->sendMessage(
                    $chatId,
                    Yii::t('app', "Добро пожаловать в Education CRM!\n\nДля получения кодов входа нажмите кнопку ниже:"),
                    $this->bot->getContactKeyboard()
                );
            }
        } elseif ($text === '/help') {
            $this->bot->sendMessage(
                $chatId,
                Yii::t('app', "Этот бот отправляет коды для входа в личный кабинет Education CRM.\n\n" .
                    "Команды:\n" .
                    "/start - Начать работу\n" .
                    "/help - Помощь")
            );
        }
    }

    /**
     * Обработка полученного контакта
     */
    private function handleContact(array $message): void
    {
        $contact = $message['contact'];
        $chatId = (string)$message['chat']['id'];
        $fromId = $message['from']['id'] ?? null;

        // Проверяем что контакт принадлежит отправителю
        if (($contact['user_id'] ?? null) != $fromId) {
            $this->bot->sendMessage(
                $chatId,
                Yii::t('app', 'Пожалуйста, отправьте свой контакт, а не чужой.'),
                $this->bot->getContactKeyboard()
            );
            return;
        }

        $phone = TelegramUser::normalizePhone($contact['phone_number']);

        // Сохраняем пользователя
        TelegramUser::createOrUpdate($chatId, $phone, $message['from'] ?? []);

        // Форматируем телефон для отображения
        $formattedPhone = '+7' . $phone;

        // Убираем клавиатуру и отправляем подтверждение
        $this->bot->sendMessage(
            $chatId,
            Yii::t('app', "Отлично! Ваш номер {phone} привязан.\n\nТеперь вернитесь на сайт и нажмите 'Получить код'.", [
                'phone' => $formattedPhone
            ]),
            $this->bot->getRemoveKeyboard()
        );

        Yii::info("Telegram user linked: chat_id={$chatId}, phone={$phone}", 'telegram');
    }

    /**
     * Найти учеников по номеру телефона в организации
     */
    private function findPupils(string $phone, int $orgId): array
    {
        return Pupil::find()
            ->where(['organization_id' => $orgId])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['status' => Pupil::STATUS_ACTIVE])
            ->andWhere([
                'or',
                ['like', 'parent_phone', $phone],
                ['like', 'phone', $phone],
            ])
            ->all();
    }

    /**
     * Проверка rate limiting
     */
    private function checkRateLimit(string $phone): array
    {
        $cache = Yii::$app->cache;

        // Проверяем время последней отправки
        $lastSendKey = "verify_last_{$phone}";
        $lastSend = $cache->get($lastSendKey);

        if ($lastSend) {
            $elapsed = time() - $lastSend;
            $waitSeconds = self::SECONDS_BETWEEN_CODES - $elapsed;

            if ($waitSeconds > 0) {
                return [
                    'allowed' => false,
                    'message' => Yii::t('app', 'Подождите {seconds} секунд перед повторным запросом', ['seconds' => $waitSeconds]),
                    'wait_seconds' => $waitSeconds,
                ];
            }
        }

        // Проверяем часовой лимит
        $hourlyKey = "verify_hour_{$phone}_" . date('YmdH');
        $hourlyCount = (int)$cache->get($hourlyKey);

        if ($hourlyCount >= self::MAX_CODES_PER_HOUR) {
            return [
                'allowed' => false,
                'message' => Yii::t('app', 'Превышен лимит запросов. Попробуйте через час.'),
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Записать попытку для rate limiting
     */
    private function recordRateLimitAttempt(string $phone): void
    {
        $cache = Yii::$app->cache;

        // Записываем время последней отправки
        $lastSendKey = "verify_last_{$phone}";
        $cache->set($lastSendKey, time(), self::SECONDS_BETWEEN_CODES);

        // Увеличиваем часовой счётчик
        $hourlyKey = "verify_hour_{$phone}_" . date('YmdH');
        $hourlyCount = (int)$cache->get($hourlyKey);
        $cache->set($hourlyKey, $hourlyCount + 1, 3600);
    }
}
