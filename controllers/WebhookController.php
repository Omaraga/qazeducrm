<?php

namespace app\controllers;

use app\models\services\WhatsappService;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Контроллер для приёма webhook от внешних сервисов
 *
 * Не требует авторизации - проверка по API ключу или подписи
 */
class WebhookController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'whatsapp' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        // Отключаем CSRF для webhook
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Webhook для Evolution API (WhatsApp)
     *
     * @return array
     */
    public function actionWhatsapp(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Дебаг: записываем в файл что webhook пришёл
        $rawBody = Yii::$app->request->rawBody;
        file_put_contents(
            Yii::getAlias('@runtime/logs/webhook_debug.log'),
            date('Y-m-d H:i:s') . " - Webhook received:\n" . $rawBody . "\n\n",
            FILE_APPEND
        );

        try {
            $data = json_decode($rawBody, true);

            if (!$data) {
                Yii::warning('WhatsApp webhook: empty or invalid JSON', 'whatsapp');
                return ['success' => false, 'error' => 'Invalid JSON'];
            }

            // Обрабатываем через сервис
            $service = WhatsappService::getInstance();
            $result = $service->handleWebhook($data);

            return [
                'success' => $result,
                'message' => $result ? 'Processed' : 'Processing failed',
            ];

        } catch (\Exception $e) {
            Yii::error('WhatsApp webhook error: ' . $e->getMessage(), 'whatsapp');

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
