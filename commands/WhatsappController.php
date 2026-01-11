<?php

namespace app\commands;

use app\models\services\WhatsappService;
use app\models\WhatsappChat;
use app\models\WhatsappMessage;
use app\models\WhatsappSession;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Консольные команды для WhatsApp интеграции
 */
class WhatsappController extends Controller
{
    /**
     * Синхронизация сообщений из Evolution API
     * Используется как fallback когда webhook не работает
     *
     * Запуск: php yii whatsapp/sync
     * Cron: * * * * * cd /var/www/qazeducrm && php yii whatsapp/sync >> runtime/logs/whatsapp-sync.log 2>&1
     *
     * @param int $limit Максимум сообщений для синхронизации
     * @return int
     */
    public function actionSync(int $limit = 50): int
    {
        $this->stdout("WhatsApp Sync started at " . date('Y-m-d H:i:s') . "\n");

        $service = WhatsappService::getInstance();
        $totalSynced = 0;

        // Получаем все активные сессии
        $sessions = WhatsappSession::find()
            ->where(['is_deleted' => 0])
            ->andWhere(['status' => WhatsappSession::STATUS_CONNECTED])
            ->all();

        if (empty($sessions)) {
            $this->stdout("No connected sessions found\n");
            return ExitCode::OK;
        }

        foreach ($sessions as $session) {
            $this->stdout("Syncing session: {$session->instance_name}\n");

            try {
                $synced = $this->syncSessionMessages($service, $session, $limit);
                $totalSynced += $synced;
                $this->stdout("  Synced {$synced} new messages\n");
            } catch (\Exception $e) {
                $this->stderr("  Error: " . $e->getMessage() . "\n");
                Yii::error("WhatsApp sync error for {$session->instance_name}: " . $e->getMessage(), 'whatsapp');
            }
        }

        $this->stdout("Total synced: {$totalSynced} messages\n");
        $this->stdout("WhatsApp Sync completed at " . date('Y-m-d H:i:s') . "\n\n");

        return ExitCode::OK;
    }

    /**
     * Синхронизировать сообщения для конкретной сессии
     */
    private function syncSessionMessages(WhatsappService $service, WhatsappSession $session, int $limit): int
    {
        // Получаем последнее синхронизированное сообщение
        $lastMessage = WhatsappMessage::find()
            ->where(['session_id' => $session->id])
            ->andWhere(['is_deleted' => 0])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $lastTimestamp = $lastMessage ? strtotime($lastMessage->created_at) : 0;

        // Запрашиваем сообщения из Evolution API
        $messages = $this->fetchMessagesFromApi($service, $session, $limit);

        if (empty($messages)) {
            return 0;
        }

        $synced = 0;

        foreach ($messages as $msgData) {
            $key = $msgData['key'] ?? [];
            $whatsappId = $key['id'] ?? null;
            $messageTimestamp = $msgData['messageTimestamp'] ?? 0;

            if (!$whatsappId) {
                continue;
            }

            // Пропускаем групповые и broadcast сообщения
            $remoteJid = $key['remoteJid'] ?? '';
            if ($this->shouldIgnoreJid($remoteJid)) {
                continue;
            }

            // Проверяем не существует ли уже это сообщение
            $exists = WhatsappMessage::find()
                ->where(['whatsapp_id' => $whatsappId, 'session_id' => $session->id])
                ->exists();

            if ($exists) {
                continue;
            }

            // Создаём сообщение
            $message = $this->createMessageFromApiData($session, $msgData);

            if ($message) {
                $synced++;

                // Обновляем/создаём чат
                $chat = WhatsappChat::findOrCreateForMessage($message);

                // Создаём лида если нужно
                $isFromMe = $key['fromMe'] ?? false;
                if (!$isFromMe && $chat && !$chat->lid_id && (Yii::$app->params['whatsapp']['autoCreateLids'] ?? false)) {
                    $lid = $chat->createLid();
                    if ($lid) {
                        $message->lid_id = $lid->id;
                        $message->save(false);
                    }
                }
            }
        }

        return $synced;
    }

    /**
     * Получить сообщения из Evolution API
     */
    private function fetchMessagesFromApi(WhatsappService $service, WhatsappSession $session, int $limit): array
    {
        $apiUrl = Yii::$app->params['whatsapp']['apiUrl'] ?? 'http://evolution-api:8080';
        $apiKey = Yii::$app->params['whatsapp']['apiKey'] ?? '';

        $url = rtrim($apiUrl, '/') . "/chat/findMessages/{$session->instance_name}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'limit' => $limit,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Yii::warning("Failed to fetch messages from API: HTTP {$httpCode}", 'whatsapp');
            return [];
        }

        $data = json_decode($response, true);

        return $data['messages']['records'] ?? [];
    }

    /**
     * Создать сообщение из данных API
     */
    private function createMessageFromApiData(WhatsappSession $session, array $msgData): ?WhatsappMessage
    {
        $key = $msgData['key'] ?? [];
        $messageContent = $msgData['message'] ?? [];

        $message = new WhatsappMessage();
        $message->organization_id = $session->organization_id;
        $message->session_id = $session->id;
        $message->whatsapp_id = $key['id'] ?? null;
        $message->remote_jid = $key['remoteJid'] ?? '';
        $message->remote_phone = preg_replace('/@.*/', '', $message->remote_jid);
        $message->is_from_me = $key['fromMe'] ?? false;
        $message->direction = $message->is_from_me
            ? WhatsappMessage::DIRECTION_OUTGOING
            : WhatsappMessage::DIRECTION_INCOMING;

        // Определяем тип и контент сообщения
        if (isset($messageContent['conversation'])) {
            $message->message_type = WhatsappMessage::TYPE_TEXT;
            $message->content = $messageContent['conversation'];
        } elseif (isset($messageContent['extendedTextMessage'])) {
            $message->message_type = WhatsappMessage::TYPE_TEXT;
            $message->content = $messageContent['extendedTextMessage']['text'] ?? '';
        } elseif (isset($messageContent['imageMessage'])) {
            $message->message_type = WhatsappMessage::TYPE_IMAGE;
            $message->content = $messageContent['imageMessage']['caption'] ?? '';
            $message->media_mimetype = $messageContent['imageMessage']['mimetype'] ?? 'image/jpeg';
        } elseif (isset($messageContent['videoMessage'])) {
            $message->message_type = WhatsappMessage::TYPE_VIDEO;
            $message->content = $messageContent['videoMessage']['caption'] ?? '';
            $message->media_mimetype = $messageContent['videoMessage']['mimetype'] ?? 'video/mp4';
        } elseif (isset($messageContent['audioMessage'])) {
            $message->message_type = WhatsappMessage::TYPE_AUDIO;
            $message->media_mimetype = $messageContent['audioMessage']['mimetype'] ?? 'audio/ogg';
        } elseif (isset($messageContent['documentMessage'])) {
            $message->message_type = WhatsappMessage::TYPE_DOCUMENT;
            $message->content = $messageContent['documentMessage']['title'] ?? '';
            $message->media_filename = $messageContent['documentMessage']['fileName'] ?? '';
            $message->media_mimetype = $messageContent['documentMessage']['mimetype'] ?? '';
        } elseif (isset($messageContent['stickerMessage'])) {
            $message->message_type = 'sticker';
        } elseif (isset($messageContent['contactMessage'])) {
            $message->message_type = 'contact';
            $message->content = $messageContent['contactMessage']['displayName'] ?? '';
        } elseif (isset($messageContent['locationMessage'])) {
            $message->message_type = 'location';
        } else {
            $message->message_type = 'unknown';
        }

        $message->status = WhatsappMessage::STATUS_DELIVERED;
        $message->remote_name = $msgData['pushName'] ?? null;
        $message->info = json_encode($msgData);

        // Устанавливаем время создания из timestamp сообщения
        if (isset($msgData['messageTimestamp'])) {
            $message->created_at = date('Y-m-d H:i:s', $msgData['messageTimestamp']);
        }

        if ($message->save()) {
            return $message;
        }

        Yii::error("Failed to save synced message: " . json_encode($message->errors), 'whatsapp');
        return null;
    }

    /**
     * Проверить, нужно ли игнорировать JID
     */
    private function shouldIgnoreJid(string $jid): bool
    {
        return str_contains($jid, '@g.us')
            || str_contains($jid, '@broadcast')
            || str_contains($jid, '@status')
            || str_contains($jid, '@lid')
            || str_starts_with($jid, 'status@');
    }

    /**
     * Настроить webhook для всех сессий
     *
     * Запуск: php yii whatsapp/setup-webhooks
     */
    public function actionSetupWebhooks(): int
    {
        $this->stdout("Setting up webhooks for all sessions...\n");

        $service = WhatsappService::getInstance();

        $sessions = WhatsappSession::find()
            ->where(['is_deleted' => 0])
            ->all();

        foreach ($sessions as $session) {
            $this->stdout("Setting webhook for: {$session->instance_name}\n");

            if ($service->setupWebhook($session->instance_name)) {
                $this->stdout("  OK\n");
            } else {
                $this->stderr("  FAILED\n");
            }
        }

        return ExitCode::OK;
    }

    /**
     * Проверить статус всех сессий
     *
     * Запуск: php yii whatsapp/status
     */
    public function actionStatus(): int
    {
        $sessions = WhatsappSession::find()
            ->where(['is_deleted' => 0])
            ->all();

        if (empty($sessions)) {
            $this->stdout("No sessions found\n");
            return ExitCode::OK;
        }

        $service = WhatsappService::getInstance();

        foreach ($sessions as $session) {
            $state = $service->getConnectionState($session);

            $this->stdout(sprintf(
                "Session: %s | Status: %s | State: %s | Phone: %s\n",
                $session->instance_name,
                $session->status,
                $state['state'] ?? 'unknown',
                $session->phone_number ?? 'N/A'
            ));
        }

        return ExitCode::OK;
    }
}
