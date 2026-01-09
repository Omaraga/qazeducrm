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

            // Увеличенный таймаут для больших файлов (base64)
            $timeout = 60;
            if (!empty($data['media']) && strlen($data['media']) > 100000) {
                $timeout = 120; // 2 минуты для больших файлов
            }

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
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

            // Логируем детали ошибки
            $errorDetail = is_array($result) ? json_encode($result) : $response;
            Yii::error("WhatsApp API error [{$httpCode}]: {$errorDetail}", 'whatsapp');

            // Возвращаем результат с меткой ошибки для диагностики
            if (is_array($result)) {
                $result['_api_error'] = true;
                $result['_http_code'] = $httpCode;
                return $result;
            }

            return ['_api_error' => true, '_http_code' => $httpCode, '_raw_response' => substr($response, 0, 500)];

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
                    'APPLICATION_STARTUP',
                    'CONNECTION_UPDATE',
                    'QRCODE_UPDATED',
                    'MESSAGES_SET',
                    'MESSAGES_UPSERT',
                    'MESSAGES_UPDATE',
                    'MESSAGES_DELETE',
                    'SEND_MESSAGE',
                    'MESSAGE_ACK',
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

        Yii::info('SendText API response: ' . json_encode($result), 'whatsapp');

        if (!$result || !isset($result['key'])) {
            Yii::error('Failed to send WhatsApp message: ' . json_encode($result), 'whatsapp');
            return null;
        }

        Yii::info('Message sent, whatsapp_id: ' . ($result['key']['id'] ?? 'null'), 'whatsapp');

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

        // Маппинг типов для Evolution API
        $apiMediaType = match ($mediaType) {
            'image' => 'image',
            'video' => 'video',
            'audio' => 'audio',
            'document', 'file' => 'document',
            default => 'document',
        };

        // Evolution API v2 - выбираем endpoint в зависимости от типа
        $endpoint = match ($apiMediaType) {
            'image' => "/message/sendMedia/{$session->instance_name}",
            'video' => "/message/sendMedia/{$session->instance_name}",
            'audio' => "/message/sendWhatsAppAudio/{$session->instance_name}",
            'document' => "/message/sendMedia/{$session->instance_name}",
            default => "/message/sendMedia/{$session->instance_name}",
        };

        // Формат данных для Evolution API v2
        $data = [
            'number' => $phone,
            'mediatype' => $apiMediaType,
            'media' => $mediaUrl,
        ];

        // Для документов добавляем имя файла и mimetype
        if ($apiMediaType === 'document' && $filename) {
            $data['fileName'] = $filename;
            // Определяем mimetype по расширению
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'txt' => 'text/plain',
            ];
            if (isset($mimeTypes[$ext])) {
                $data['mimetype'] = $mimeTypes[$ext];
            }
        }

        if ($caption) {
            $data['caption'] = $caption;
        }

        Yii::info("Sending media to {$phone}: type={$apiMediaType}, mediaLength=" . strlen($mediaUrl), 'whatsapp');

        $result = $this->request('POST', $endpoint, $data);

        Yii::info("SendMedia result: " . json_encode($result), 'whatsapp');

        if (!$result) {
            Yii::error("SendMedia failed: no result returned", 'whatsapp');
            return null;
        }

        // Проверяем на ошибку API
        if (!empty($result['_api_error'])) {
            $errorMsg = $result['error'] ?? $result['message'] ?? $result['response']['message'] ?? $result['_raw_response'] ?? 'API Error';
            $httpCode = $result['_http_code'] ?? 'unknown';
            Yii::error("SendMedia API error [{$httpCode}]: {$errorMsg}", 'whatsapp');
            return null;
        }

        // Evolution API может возвращать key в разных форматах
        $messageKey = $result['key'] ?? $result['id'] ?? $result['messageId'] ?? null;

        if (!$messageKey) {
            // Проверим, может ответ содержит ошибку
            $errorMsg = $result['error'] ?? $result['message'] ?? $result['response']['message'] ?? json_encode($result);
            Yii::error("SendMedia failed: no key in response. Response: {$errorMsg}", 'whatsapp');
            return null;
        }

        // Извлекаем ID сообщения
        $whatsappId = is_array($messageKey) ? ($messageKey['id'] ?? null) : $messageKey;

        $message = new WhatsappMessage();
        $message->organization_id = $session->organization_id;
        $message->session_id = $session->id;
        $message->lid_id = $lidId;
        $message->remote_jid = $remoteJid;
        $message->remote_phone = $phone;
        $message->direction = WhatsappMessage::DIRECTION_OUTGOING;
        $message->message_type = $mediaType;
        $message->content = $caption;
        $message->media_url = ''; // Will be updated after save
        $message->media_filename = $filename;
        $message->status = WhatsappMessage::STATUS_SENT;
        $message->whatsapp_id = $whatsappId;
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

        // Быстрая проверка на групповые/broadcast сообщения до обработки
        $messageData = $data['data'] ?? [];
        $key = $messageData['key'] ?? [];
        $remoteJid = $key['remoteJid'] ?? $messageData['remoteJid'] ?? '';
        if ($remoteJid && $this->shouldIgnoreJid($remoteJid)) {
            // Тихо игнорируем групповые сообщения без лишнего логирования
            return true;
        }

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

        // Evolution API v2 может использовать разные названия событий
        return match ($event) {
            'connection.update' => $this->handleConnectionUpdate($session, $data),
            'messages.upsert' => $this->handleMessagesUpsert($session, $data),
            'messages.update', 'message.ack', 'messages.ack' => $this->handleMessagesUpdate($session, $data),
            'send.message' => $this->handleSendMessage($session, $data),
            default => true,
        };
    }

    /**
     * Обработка события отправки сообщения (содержит статус)
     */
    private function handleSendMessage(WhatsappSession $session, array $data): bool
    {
        // send.message может содержать обновление статуса отправленного сообщения
        $messageData = $data['data'] ?? [];
        $key = $messageData['key'] ?? [];

        // Пропускаем групповые и broadcast сообщения
        $remoteJid = $key['remoteJid'] ?? '';
        if ($this->shouldIgnoreJid($remoteJid)) {
            return true;
        }

        $messageId = $key['id'] ?? null;
        $status = $messageData['status'] ?? $messageData['ack'] ?? null;

        if ($messageId && $status !== null) {
            return $this->updateMessageStatus($session, $messageId, $status);
        }

        return true;
    }

    /**
     * Обновить статус сообщения
     */
    private function updateMessageStatus(WhatsappSession $session, string $messageId, $status): bool
    {
        $newStatus = null;

        // Числовой статус (0=ERROR, 1=PENDING, 2=SERVER_ACK, 3=DELIVERY_ACK, 4=READ, 5=PLAYED)
        if (is_numeric($status)) {
            $numericMap = [
                0 => WhatsappMessage::STATUS_FAILED,
                1 => WhatsappMessage::STATUS_PENDING,
                2 => WhatsappMessage::STATUS_SENT,
                3 => WhatsappMessage::STATUS_DELIVERED,
                4 => WhatsappMessage::STATUS_READ,
                5 => WhatsappMessage::STATUS_READ,
            ];
            $newStatus = $numericMap[(int)$status] ?? null;
        } else {
            // Строковые статусы
            $statusMap = [
                'PENDING' => WhatsappMessage::STATUS_PENDING,
                'SERVER_ACK' => WhatsappMessage::STATUS_SENT,
                'DELIVERY_ACK' => WhatsappMessage::STATUS_DELIVERED,
                'READ' => WhatsappMessage::STATUS_READ,
                'PLAYED' => WhatsappMessage::STATUS_READ,
                'sent' => WhatsappMessage::STATUS_SENT,
                'delivered' => WhatsappMessage::STATUS_DELIVERED,
                'read' => WhatsappMessage::STATUS_READ,
            ];
            $newStatus = $statusMap[$status] ?? null;
        }

        if ($newStatus) {
            $updated = WhatsappMessage::updateAll(
                ['status' => $newStatus],
                ['whatsapp_id' => $messageId, 'session_id' => $session->id]
            );
            Yii::info("Updated message {$messageId} status to {$newStatus}, affected: {$updated}", 'whatsapp');
            return $updated > 0;
        }

        return false;
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
     * Проверить, нужно ли игнорировать JID (группы, broadcast, статусы)
     * @param string $jid
     * @return bool true если нужно игнорировать
     */
    private function shouldIgnoreJid(string $jid): bool
    {
        // Игнорируем:
        // - группы: @g.us
        // - broadcast/рассылки: @broadcast
        // - статусы: @status или status@broadcast
        // - lid (labels): @lid
        return str_contains($jid, '@g.us')
            || str_contains($jid, '@broadcast')
            || str_contains($jid, '@status')
            || str_contains($jid, '@lid')
            || str_starts_with($jid, 'status@');
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

            // Пропускаем групповые, broadcast и статусные сообщения
            $remoteJid = $key['remoteJid'] ?? '';
            if ($this->shouldIgnoreJid($remoteJid)) {
                Yii::info("Ignoring message from JID: {$remoteJid}", 'whatsapp');
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
     * Evolution API v2 отправляет данные в формате:
     * {"event":"messages.update","data":{"keyId":"3A306016E4342E2264BC","status":"DELIVERY_ACK",...}}
     */
    private function handleMessagesUpdate(WhatsappSession $session, array $data): bool
    {
        $updateData = $data['data'] ?? [];

        // Пропускаем групповые и broadcast сообщения
        $remoteJid = $updateData['remoteJid'] ?? $updateData['key']['remoteJid'] ?? '';
        if ($this->shouldIgnoreJid($remoteJid)) {
            return true;
        }

        Yii::info("Messages update received: " . json_encode($data), 'whatsapp');

        // Evolution API v2 формат: data.keyId и data.status напрямую
        if (isset($updateData['keyId'])) {
            $messageId = $updateData['keyId'];
            $status = $updateData['status'] ?? null;

            if ($messageId && $status) {
                Yii::info("Processing status update: messageId={$messageId}, status={$status}", 'whatsapp');
                $this->updateMessageStatus($session, $messageId, $status);
            }
            return true;
        }

        // Альтернативный формат: массив updates
        $updates = $updateData;
        if (isset($updates['key'])) {
            $updates = [$updates];
        }

        foreach ($updates as $update) {
            // Попробуем найти messageId в разных местах
            $messageId = $update['keyId'] ?? $update['key']['id'] ?? $update['id'] ?? null;

            // Попробуем найти status в разных местах
            $status = $update['status']
                ?? $update['update']['status']
                ?? $update['ack']
                ?? null;

            if (!$messageId) {
                Yii::warning("Messages update: no messageId found in: " . json_encode($update), 'whatsapp');
                continue;
            }

            if ($status !== null) {
                $this->updateMessageStatus($session, $messageId, $status);
            } else {
                Yii::warning("Messages update: no status found for {$messageId} in: " . json_encode($update), 'whatsapp');
            }
        }

        return true;
    }

    // ==================== Получение профиля контакта ====================

    /**
     * Получить информацию о профиле контакта WhatsApp
     * @param WhatsappSession $session
     * @param string $phone Номер телефона
     * @return array|null Данные профиля (profilePictureUrl, isBusiness, name, description)
     */
    public function fetchProfile(WhatsappSession $session, string $phone): ?array
    {
        $phone = $this->normalizePhone($phone);

        Yii::info("Fetching profile for phone: {$phone}", 'whatsapp');

        // Evolution API v2 использует GET запрос с query параметром
        $result = $this->request('GET', "/chat/fetchProfile/{$session->instance_name}?number={$phone}");

        Yii::info("FetchProfile result: " . json_encode($result), 'whatsapp');

        if (!$result) {
            return null;
        }

        // Evolution API v2 может возвращать данные в разных форматах
        $pictureUrl = $result['profilePictureUrl']
            ?? $result['picture']
            ?? $result['imgUrl']
            ?? $result['profilePicThumbObj']['eurl']
            ?? null;

        if ($pictureUrl) {
            return [
                'profilePictureUrl' => $pictureUrl,
                'isBusiness' => $result['isBusiness'] ?? false,
                'name' => $result['name'] ?? $result['pushName'] ?? null,
                'description' => $result['description'] ?? $result['status'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Получить URL аватарки контакта
     * @param WhatsappSession $session
     * @param string $phone Номер телефона
     * @return string|null URL аватарки или null
     */
    public function fetchProfilePicture(WhatsappSession $session, string $phone): ?string
    {
        $phone = $this->normalizePhone($phone);

        // Попытка 1: Прямой endpoint для аватарки
        $result = $this->request('POST', "/chat/fetchProfilePictureUrl/{$session->instance_name}", [
            'number' => $phone,
        ]);

        Yii::info("FetchProfilePictureUrl result: " . json_encode($result), 'whatsapp');

        if ($result) {
            $pictureUrl = $result['profilePictureUrl']
                ?? $result['picture']
                ?? $result['imgUrl']
                ?? $result['url']
                ?? null;

            if ($pictureUrl) {
                return $pictureUrl;
            }
        }

        // Попытка 2: Через общий профиль
        $profile = $this->fetchProfile($session, $phone);
        return $profile['profilePictureUrl'] ?? null;
    }

    /**
     * Обновить аватарку в чате
     * @param WhatsappChat $chat
     * @return bool
     */
    public function updateChatProfilePicture(WhatsappChat $chat): bool
    {
        if (!$chat->remote_phone) {
            return false;
        }

        $session = $chat->session;
        if (!$session || !$session->isConnected()) {
            return false;
        }

        $pictureUrl = $this->fetchProfilePicture($session, $chat->remote_phone);

        if ($pictureUrl) {
            $chat->profile_picture_url = $pictureUrl;
            return $chat->save(false);
        }

        return false;
    }

    // ==================== Скачивание медиа ====================

    /**
     * Получить медиа файл в base64 по ID сообщения
     * @param WhatsappSession $session
     * @param string $messageId WhatsApp ID сообщения
     * @return array|null ['base64' => string, 'mimetype' => string] или null
     */
    public function getMediaBase64(WhatsappSession $session, string $messageId): ?array
    {
        if (!$messageId) {
            return null;
        }

        // Evolution API v2 endpoint для получения base64 медиа
        // POST /chat/getBase64FromMediaMessage/{instanceName}
        $result = $this->request('POST', "/chat/getBase64FromMediaMessage/{$session->instance_name}", [
            'message' => [
                'key' => [
                    'id' => $messageId,
                ],
            ],
            'convertToMp4' => false,
        ]);

        Yii::info("GetMediaBase64 result for {$messageId}: " . (isset($result['base64']) ? 'has base64' : 'no base64'), 'whatsapp');

        if ($result && !empty($result['base64'])) {
            return [
                'base64' => $result['base64'],
                'mimetype' => $result['mimetype'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Альтернативный метод получения медиа - используя сохранённые данные сообщения
     * @param WhatsappSession $session
     * @param WhatsappMessage $message
     * @return array|null
     */
    public function getMediaBase64Alternative(WhatsappSession $session, WhatsappMessage $message): ?array
    {
        // Пробуем получить из info - там может быть сохранён полный объект сообщения
        $info = is_string($message->info) ? json_decode($message->info, true) : $message->info;

        if (!$info) {
            return null;
        }

        // Пробуем найти messageId в разных местах
        $messageId = $info['key']['id']
            ?? $info['id']['id']
            ?? $info['id']
            ?? $message->whatsapp_id;

        if ($messageId && $messageId !== $message->whatsapp_id) {
            // Пробуем с другим ID
            return $this->getMediaBase64($session, $messageId);
        }

        // Пробуем использовать полный key объект если он есть
        $key = $info['key'] ?? null;
        if ($key && isset($key['remoteJid'])) {
            $result = $this->request('POST', "/chat/getBase64FromMediaMessage/{$session->instance_name}", [
                'message' => [
                    'key' => $key,
                ],
                'convertToMp4' => false,
            ]);

            if ($result && !empty($result['base64'])) {
                return [
                    'base64' => $result['base64'],
                    'mimetype' => $result['mimetype'] ?? $message->media_mimetype,
                ];
            }
        }

        return null;
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
