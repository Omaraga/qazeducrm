<?php

namespace app\services\verification;

use Yii;

/**
 * Сервис для работы с Telegram Bot API
 */
class TelegramBotService
{
    private string $token;
    private string $apiUrl = 'https://api.telegram.org/bot';

    public function __construct(?string $token = null)
    {
        $this->token = $token ?? Yii::$app->params['telegramBot']['token'] ?? '';
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($replyMarkup !== null) {
            $data['reply_markup'] = json_encode($replyMarkup);
        }

        $result = $this->request('sendMessage', $data);

        return $result !== null && ($result['ok'] ?? false);
    }

    /**
     * Установить webhook
     */
    public function setWebhook(string $url): bool
    {
        $result = $this->request('setWebhook', [
            'url' => $url,
            'allowed_updates' => ['message'],
        ]);

        return $result !== null && ($result['ok'] ?? false);
    }

    /**
     * Удалить webhook
     */
    public function deleteWebhook(): bool
    {
        $result = $this->request('deleteWebhook');
        return $result !== null && ($result['ok'] ?? false);
    }

    /**
     * Получить информацию о боте
     */
    public function getMe(): ?array
    {
        $result = $this->request('getMe');

        if ($result !== null && ($result['ok'] ?? false)) {
            return $result['result'];
        }

        return null;
    }

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo(): ?array
    {
        $result = $this->request('getWebhookInfo');

        if ($result !== null && ($result['ok'] ?? false)) {
            return $result['result'];
        }

        return null;
    }

    /**
     * Создать клавиатуру с кнопкой "Поделиться контактом"
     */
    public function getContactKeyboard(): array
    {
        return [
            'keyboard' => [[
                ['text' => Yii::t('app', 'Поделиться контактом'), 'request_contact' => true]
            ]],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Клавиатура для удаления (скрытия)
     */
    public function getRemoveKeyboard(): array
    {
        return ['remove_keyboard' => true];
    }

    /**
     * Выполнить запрос к API Telegram
     */
    private function request(string $method, array $data = []): ?array
    {
        if (empty($this->token)) {
            Yii::error('Telegram bot token is not configured', 'telegram');
            return null;
        }

        $url = $this->apiUrl . $this->token . '/' . $method;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Yii::error("Telegram API curl error: {$error}", 'telegram');
            return null;
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200 || !($result['ok'] ?? false)) {
            Yii::error("Telegram API error: {$response}", 'telegram');
        }

        return $result;
    }
}
