<?php

namespace app\controllers;

use app\services\verification\TelegramVerificationService;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Контроллер для обработки webhook от Telegram
 * Один endpoint на всю платформу
 */
class TelegramWebhookController extends Controller
{
    /**
     * Отключаем CSRF для webhook
     */
    public $enableCsrfValidation = false;

    /**
     * Обработка входящих обновлений от Telegram
     */
    public function actionIndex()
    {
        // Получаем данные от Telegram
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Логируем для отладки
        Yii::info("Telegram webhook received: " . $input, 'telegram');

        if (empty($data)) {
            Yii::warning("Telegram webhook: empty data received", 'telegram');
            return '';
        }

        try {
            $service = new TelegramVerificationService();
            $service->handleWebhook($data);
        } catch (\Throwable $e) {
            Yii::error("Telegram webhook error: " . $e->getMessage(), 'telegram');
        }

        // Telegram ожидает пустой ответ или 200 OK
        Yii::$app->response->format = Response::FORMAT_RAW;
        return '';
    }

    /**
     * Проверка статуса webhook (для отладки)
     */
    public function actionStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $token = Yii::$app->params['telegramBot']['token'] ?? '';

        if (empty($token)) {
            return ['status' => 'error', 'message' => 'Bot token not configured'];
        }

        $service = new \app\services\verification\TelegramBotService($token);

        $botInfo = $service->getMe();
        $webhookInfo = $service->getWebhookInfo();

        return [
            'status' => 'ok',
            'bot' => $botInfo,
            'webhook' => $webhookInfo,
        ];
    }

    /**
     * Установка webhook (вызывать один раз при настройке)
     */
    public function actionSetWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $token = Yii::$app->params['telegramBot']['token'] ?? '';
        $webhookUrl = Yii::$app->params['telegramBot']['webhookUrl'] ?? '';

        if (empty($token)) {
            return ['status' => 'error', 'message' => 'Bot token not configured'];
        }

        if (empty($webhookUrl)) {
            // Генерируем URL автоматически
            $webhookUrl = Yii::$app->request->hostInfo . '/telegram-webhook';
        }

        $service = new \app\services\verification\TelegramBotService($token);
        $result = $service->setWebhook($webhookUrl);

        if ($result) {
            return ['status' => 'ok', 'webhook_url' => $webhookUrl];
        }

        return ['status' => 'error', 'message' => 'Failed to set webhook'];
    }
}
