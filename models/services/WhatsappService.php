<?php

namespace app\models\services;

use app\models\Lids;
use app\models\LidHistory;
use app\models\Organizations;
use app\models\WhatsappChat;
use app\models\WhatsappMessage;
use app\models\WhatsappSession;
use Yii;
use yii\base\Component;

/**
 * Сервис для работы с Evolution API
 * https://doc.evolution-api.com/
 */
class WhatsappService extends Component
{
    /**
     * URL Evolution API
     * @var string
     */
    public string $apiUrl;

    /**
     * API ключ для аутентификации
     * @var string
     */
    public string $apiKey;

    /**
     * Инициализация сервиса
     */
    public function init()
    {
        parent::init();

        // Загружаем конфигурацию из params
        $this->apiUrl = Yii::$app->params['whatsapp']['apiUrl'] ?? 'http://localhost:8085';
        $this->apiKey = Yii::$app->params['whatsapp']['apiKey'] ?? '';
    }

    /**
     * Выполнить API запрос через cURL
     * @param string $method HTTP метод
     * @param string $endpoint Endpoint
     * @param array $data Данные запроса
     * @return array|null
     */
    private function request(string $method, string $endpoint, array $data = []): ?array
    {
        try {
            $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'apikey: ' . $this->apiKey,
                ],
            ]);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            } elseif ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Yii::error("WhatsApp API cURL error: {$error}", 'whatsapp');
                return null;
            }

            $result = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                return $result;
            }

            Yii::error("WhatsApp API error: {$httpCode} - {$response}", 'whatsapp');
            return null;

        } catch (\Exception $e) {
            Yii::error("WhatsApp API exception: " . $e->getMessage(), 'whatsapp');
            return null;
        }
    }

    /**
     * Проверить доступность API
     * @return bool
     */
    public function checkConnection(): bool
    {
        $result = $this->request('GET', '/');
        return isset($result['status']) && $result['status'] === 200;
    }

    // ==================== Управление сессиями ====================

    /**
     * Создать новую сессию WhatsApp для организации
     * @param int $organizationId
     * @return WhatsappSession|null
     */
    public function createInstance(int $organizationId): ?WhatsappSession
    {
        // Проверяем нет ли уже сессии
        $existing = WhatsappSession::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if ($existing) {
            // Если сессия уже есть - пробуем получить новый QR
            $qrCode = $this->getQrCodeFromApi($existing->instance_name);
            if ($qrCode) {
                $existing->qr_code = $qrCode;
                $existing->qr_code_updated_at = date('Y-m-d H:i:s');
                $existing->status = WhatsappSession::STATUS_CONNECTING;
                $existing->save(false);
            }
            return $existing;
        }

        // Генерируем уникальное имя инстанса
        $instanceName = 'org_' . $organizationId;

        // Создаём инстанс в Evolution API
        $result = $this->request('POST', '/instance/create', [
            'instanceName' => $instanceName,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ]);

        if (!$result || !isset($result['instance'])) {
            Yii::error('Failed to create WhatsApp instance: ' . json_encode($result), 'whatsapp');
            return null;
        }

        // Создаём запись в БД
        $session = new WhatsappSession();
        $session->organization_id = $organizationId;
        $session->instance_name = $instanceName;
        $session->status = WhatsappSession::STATUS_CONNECTING;

        // QR код возвращается сразу в ответе
        if (isset($result['qrcode']['base64'])) {
            // Убираем префикс data:image/png;base64, если есть
            $qrBase64 = $result['qrcode']['base64'];
            if (str_starts_with($qrBase64, 'data:image')) {
                $qrBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $qrBase64);
            }
            $session->qr_code = $qrBase64;
            $session->qr_code_updated_at = date('Y-m-d H:i:s');
        }

        if ($session->save()) {
            // Настраиваем webhook для получения сообщений
            $this->setupWebhook($instanceName);

            return $session;
        }

        Yii::error('Failed to save WhatsApp session: ' . json_encode($session->errors), 'whatsapp');
        return null;
    }

    /**
     * Настроить webhook для инстанса
     * @param string $instanceName
     * @return bool
     */
    public function setupWebhook(string $instanceName): bool
    {
        // URL и Host для webhook
        // Для Docker: используем host.docker.internal с Host header
        $webhookUrl = Yii::$app->params['whatsapp']['webhookUrl'] ?? 'http://host.docker.internal/webhook/whatsapp';
        $webhookHost = Yii::$app->params['whatsapp']['webhookHost'] ?? 'educrm.loc';

        $webhookConfig = [
            'webhook' => [
                'enabled' => true,
                'url' => $webhookUrl,
                'webhookByEvents' => false,
                'webhookBase64' => false,
                'events' => [
                    'CONNECTION_UPDATE',
                    'MESSAGES_UPSERT',
                    'MESSAGES_UPDATE',
                    'QRCODE_UPDATED',
                ],
            ],
        ];

        // Добавляем Host header для правильной маршрутизации Apache
        if ($webhookHost) {
            $webhookConfig['webhook']['headers'] = [
                'Host' => $webhookHost,
            ];
        }

        $result = $this->request('POST', "/webhook/set/{$instanceName}", $webhookConfig);

        if ($result) {
            Yii::info("Webhook configured for {$instanceName}: {$webhookUrl} (Host: {$webhookHost})", 'whatsapp');
            return true;
        }

        Yii::error("Failed to setup webhook for {$instanceName}", 'whatsapp');
        return false;
    }

    /**
     * Получить QR-код из API
     * @param string $instanceName
     * @return string|null Base64 QR код
     */
    private function getQrCodeFromApi(string $instanceName): ?string
    {
        $result = $this->request('GET', "/instance/connect/{$instanceName}");

        if ($result && isset($result['base64'])) {
            $qrBase64 = $result['base64'];
            if (str_starts_with($qrBase64, 'data:image')) {
                $qrBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $qrBase64);
            }
            return $qrBase64;
        }

        return null;
    }

    /**
     * Получить QR-код для подключения
     * @param WhatsappSession $session
     * @return string|null Base64 QR код
     */
    public function getQrCode(WhatsappSession $session): ?string
    {
        $qrCode = $this->getQrCodeFromApi($session->instance_name);

        if ($qrCode) {
            $session->qr_code = $qrCode;
            $session->qr_code_updated_at = date('Y-m-d H:i:s');
            $session->save(false);

            return $qrCode;
        }

        return $session->qr_code;
    }

    /**
     * Получить статус подключения сессии
     * @param WhatsappSession $session
     * @return array
     */
    public function getConnectionState(WhatsappSession $session): array
    {
        $result = $this->request('GET', '/instance/fetchInstances?instanceName=' . $session->instance_name);

        if (!is_array($result) || empty($result)) {
            return [
                'status' => $session->status,
                'state' => 'unknown',
            ];
        }

        $instanceData = $result[0] ?? null;

        if (!$instanceData) {
            return [
                'status' => WhatsappSession::STATUS_DISCONNECTED,
                'state' => 'not_found',
            ];
        }

        // Маппинг статусов Evolution API -> наши статусы
        $connectionStatus = $instanceData['connectionStatus'] ?? 'close';
        $statusMap = [
            'open' => WhatsappSession::STATUS_CONNECTED,
            'connecting' => WhatsappSession::STATUS_CONNECTING,
            'close' => WhatsappSession::STATUS_DISCONNECTED,
        ];

        $newStatus = $statusMap[$connectionStatus] ?? WhatsappSession::STATUS_DISCONNECTED;

        if ($session->status !== $newStatus) {
            $additionalData = [];

            // Если подключились - сохраняем данные пользователя
            if ($newStatus === WhatsappSession::STATUS_CONNECTED) {
                if (isset($instanceData['ownerJid'])) {
                    $additionalData['phone_number'] = preg_replace('/@.*/', '', $instanceData['ownerJid']);
                }
                if (isset($instanceData['profileName'])) {
                    $additionalData['profile_name'] = $instanceData['profileName'];
                }
            }

            $session->updateConnectionStatus($newStatus, $additionalData);
        }

        return [
            'status' => $newStatus,
            'state' => $connectionStatus,
            'ownerJid' => $instanceData['ownerJid'] ?? null,
            'profileName' => $instanceData['profileName'] ?? null,
        ];
    }

    /**
     * Отключить сессию
     * @param WhatsappSession $session
     * @return bool
     */
    public function disconnect(WhatsappSession $session): bool
    {
        $result = $this->request('DELETE', "/instance/logout/{$session->instance_name}");

        if ($result !== null) {
            $session->updateConnectionStatus(WhatsappSession::STATUS_DISCONNECTED);
            return true;
        }

        return false;
    }

    /**
     * Удалить сессию
     * @param WhatsappSession $session
     * @return bool
     */
    public function deleteInstance(WhatsappSession $session): bool
    {
        $this->request('DELETE', "/instance/delete/{$session->instance_name}");

        // Помечаем как удалённый в любом случае
        $session->is_deleted = 1;
        return $session->save(false);
    }

    /**
     * Перезапустить сессию
     * @param WhatsappSession $session
     * @return bool
     */
    public function restartInstance(WhatsappSession $session): bool
    {
        $result = $this->request('PUT', "/instance/restart/{$session->instance_name}");

        if ($result) {
            $session->status = WhatsappSession::STATUS_CONNECTING;
            $session->save(false);
            return true;
        }

        return false;
    }

    // ==================== Отправка сообщений ====================

    /**
     * Отправить текстовое сообщение
     * @param WhatsappSession $session
     * @param string $phone Номер телефона
     * @param string $text Текст сообщения
     * @param int|null $lidId ID связанного лида
     * @return WhatsappMessage|null
     */
    public function sendText(WhatsappSession $session, string $phone, string $text, ?int $lidId = null): ?WhatsappMessage
    {
        // Нормализуем номер телефона
        $phone = $this->normalizePhone($phone);
        $remoteJid = "{$phone}@s.whatsapp.net";

        $result = $this->request('POST', "/message/sendText/{$session->instance_name}", [
            'number' => $phone,
            'text' => $text,
        ]);

        if (!$result || !isset($result['key'])) {
            Yii::error('Failed to send WhatsApp message: ' . json_encode($result), 'whatsapp');
            return null;
        }

        // Создаём запись о сообщении
        $message = new WhatsappMessage();
        $message->organization_id = $session->organization_id;
        $message->session_id = $session->id;
        $message->lid_id = $lidId;
        $message->remote_jid = $remoteJid;
        $message->remote_phone = $phone;
        $message->direction = WhatsappMessage::DIRECTION_OUTGOING;
        $message->message_type = WhatsappMessage::TYPE_TEXT;
        $message->content = $text;
        $message->status = WhatsappMessage::STATUS_SENT;
        $message->whatsapp_id = $result['key']['id'] ?? null;
        $message->is_from_me = true;
        $message->info = json_encode($result);

        if ($message->save()) {
            // Обновляем/создаём чат
            WhatsappChat::findOrCreateForMessage($message);

            // Записываем в историю лида если есть
            if ($lidId) {
                $this->logToLidHistory($lidId, $text, 'outgoing');
            }

            return $message;
        }

        return null;
    }

    /**
     * Отправить медиа сообщение
     * @param WhatsappSession $session
     * @param string $phone
     * @param string $mediaUrl URL медиа файла
     * @param string $mediaType Тип (image, video, audio, document)
     * @param string|null $caption Подпись
     * @param string|null $filename Имя файла
     * @param int|null $lidId
     * @return WhatsappMessage|null
     */
    public function sendMedia(
        WhatsappSession $session,
        string $phone,
        string $mediaUrl,
        string $mediaType,
        ?string $caption = null,
        ?string $filename = null,
        ?int $lidId = null
    ): ?WhatsappMessage {
        $phone = $this->normalizePhone($phone);
        $remoteJid = "{$phone}@s.whatsapp.net";

        $endpoint = match ($mediaType) {
            'image' => "/message/sendMedia/{$session->instance_name}",
            'document', 'file' => "/message/sendMedia/{$session->instance_name}",
            default => null,
        };

        if (!$endpoint) {
            return null;
        }

        $data = [
            'number' => $phone,
            'mediatype' => $mediaType,
            'media' => $mediaUrl,
        ];

        if ($caption) {
            $data['caption'] = $caption;
        }

        if ($filename) {
            $data['fileName'] = $filename;
        }

        $result = $this->request('POST', $endpoint, $data);

        if (!$result || !isset($result['key'])) {
            return null;
        }

        $message = new WhatsappMessage();
        $message->organization_id = $session->organization_id;
        $message->session_id = $session->id;
        $message->lid_id = $lidId;
        $message->remote_jid = $remoteJid;
        $message->remote_phone = $phone;
        $message->direction = WhatsappMessage::DIRECTION_OUTGOING;
        $message->message_type = $mediaType;
        $message->content = $caption;
        $message->media_url = $mediaUrl;
        $message->media_filename = $filename;
        $message->status = WhatsappMessage::STATUS_SENT;
        $message->whatsapp_id = $result['key']['id'] ?? null;
        $message->is_from_me = true;
        $message->info = json_encode($result);

        if ($message->save()) {
            WhatsappChat::findOrCreateForMessage($message);
            return $message;
        }

        return null;
    }

    // ==================== Обработка webhook ====================

    /**
     * Обработать входящий webhook от Evolution API
     * @param array $data
     * @return bool
     */
    public function handleWebhook(array $data): bool
    {
        $event = $data['event'] ?? '';
        $instanceName = $data['instance'] ?? '';

        Yii::info("WhatsApp webhook: {$event} for {$instanceName}", 'whatsapp');

        // Находим сессию по имени
        $session = WhatsappSession::find()
            ->where(['instance_name' => $instanceName])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$session) {
            Yii::warning("WhatsApp session not found: {$instanceName}", 'whatsapp');
            return false;
        }

        return match ($event) {
            'connection.update' => $this->handleConnectionUpdate($session, $data),
            'messages.upsert' => $this->handleMessagesUpsert($session, $data),
            'messages.update' => $this->handleMessagesUpdate($session, $data),
            default => true,
        };
    }

    /**
     * Обработка изменения статуса подключения
     */
    private function handleConnectionUpdate(WhatsappSession $session, array $data): bool
    {
        $payload = $data['data'] ?? [];
        $state = $payload['state'] ?? '';

        $statusMap = [
            'open' => WhatsappSession::STATUS_CONNECTED,
            'connecting' => WhatsappSession::STATUS_CONNECTING,
            'close' => WhatsappSession::STATUS_DISCONNECTED,
        ];

        $newStatus = $statusMap[$state] ?? $session->status;

        return $session->updateConnectionStatus($newStatus);
    }

    /**
     * Обработка сообщений (входящих и исходящих)
     */
    private function handleMessagesUpsert(WhatsappSession $session, array $data): bool
    {
        $messageData = $data['data'] ?? [];

        // Evolution API может отправлять как массив сообщений, так и одно сообщение
        // Если есть ключ 'key' напрямую в data - это одно сообщение
        if (isset($messageData['key'])) {
            $messages = [$messageData];
        } else {
            $messages = $messageData;
        }

        foreach ($messages as $msgData) {
            $key = $msgData['key'] ?? [];
            $isFromMe = $key['fromMe'] ?? false;

            // Пропускаем групповые сообщения
            $remoteJid = $key['remoteJid'] ?? '';
            if (str_contains($remoteJid, '@g.us')) {
                continue;
            }

            // Проверяем не существует ли уже это сообщение (чтобы избежать дубликатов)
            $whatsappId = $key['id'] ?? null;
            if ($whatsappId) {
                $exists = WhatsappMessage::find()
                    ->where(['whatsapp_id' => $whatsappId, 'session_id' => $session->id])
                    ->exists();
                if ($exists) {
                    continue; // Сообщение уже есть (например, отправлено через CRM)
                }
            }

            // Создаём сообщение
            $message = WhatsappMessage::createFromWebhook($session->id, $msgData);

            if ($message) {
                // Обновляем/создаём чат
                $chat = WhatsappChat::findOrCreateForMessage($message);

                // Если это входящее сообщение и у чата нет лида - создаём
                if (!$isFromMe && $chat && !$chat->lid_id && (Yii::$app->params['whatsapp']['autoCreateLids'] ?? false)) {
                    $lid = $chat->createLid();
                    if ($lid) {
                        // Обновляем lid_id в сообщении
                        $message->lid_id = $lid->id;
                        $message->save(false);
                        $this->logToLidHistory($lid->id, $message->content, 'incoming');
                    }
                }

                // Записываем в историю лида если есть связь
                if ($message->lid_id && $message->content) {
                    $direction = $isFromMe ? 'outgoing' : 'incoming';
                    $this->logToLidHistory($message->lid_id, $message->content, $direction);
                }
            }
        }

        return true;
    }

    /**
     * Обработка обновления статуса сообщений
     */
    private function handleMessagesUpdate(WhatsappSession $session, array $data): bool
    {
        $updates = $data['data'] ?? [];

        foreach ($updates as $update) {
            $messageId = $update['key']['id'] ?? null;
            $status = $update['update']['status'] ?? null;

            if (!$messageId || !$status) {
                continue;
            }

            // Статусы в Evolution API: PENDING, SERVER_ACK, DELIVERY_ACK, READ, PLAYED
            $statusMap = [
                'PENDING' => WhatsappMessage::STATUS_PENDING,
                'SERVER_ACK' => WhatsappMessage::STATUS_SENT,
                'DELIVERY_ACK' => WhatsappMessage::STATUS_DELIVERED,
                'READ' => WhatsappMessage::STATUS_READ,
                'PLAYED' => WhatsappMessage::STATUS_READ,
            ];

            $newStatus = $statusMap[$status] ?? null;

            if ($newStatus) {
                WhatsappMessage::updateAll(
                    ['status' => $newStatus],
                    ['whatsapp_id' => $messageId, 'session_id' => $session->id]
                );
            }
        }

        return true;
    }

    // ==================== Вспомогательные методы ====================

    /**
     * Нормализация номера телефона
     * @param string $phone
     * @return string
     */
    public function normalizePhone(string $phone): string
    {
        // Удаляем всё кроме цифр
        $phone = preg_replace('/\D/', '', $phone);

        // Если начинается с 8 - меняем на 7 (для казахстанских/российских номеров)
        if (strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }

        // Если 10 цифр - добавляем 7
        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }

        return $phone;
    }

    /**
     * Записать в историю лида
     * @param int $lidId
     * @param string $message
     * @param string $direction
     */
    private function logToLidHistory(int $lidId, string $message, string $direction): void
    {
        $lid = Lids::findOne($lidId);
        if (!$lid) {
            return;
        }

        $comment = $direction === 'incoming'
            ? "Входящее WhatsApp: {$message}"
            : "Исходящее WhatsApp: {$message}";

        $history = new LidHistory();
        $history->lid_id = $lidId;
        $history->organization_id = $lid->organization_id;
        $history->user_id = Yii::$app->user->id ?? null;
        $history->type = LidHistory::TYPE_WHATSAPP;
        $history->comment = mb_substr($comment, 0, 1000);
        $history->save(false);
    }

    /**
     * Получить сервис из приложения
     * @return WhatsappService
     */
    public static function getInstance(): WhatsappService
    {
        return new self();
    }
}
