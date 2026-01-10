<?php

namespace app\modules\crm\controllers;

use app\helpers\RoleChecker;
use app\models\Organizations;
use app\models\services\WhatsappService;
use app\models\WhatsappChat;
use app\models\WhatsappMessage;
use app\models\WhatsappSession;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * WhatsappController - управление WhatsApp интеграцией
 *
 * - Подключение/отключение WhatsApp
 * - Список чатов
 * - Отправка сообщений
 */
class WhatsappController extends CrmBaseController
{
    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        // Отключаем CSRF для webhook - он вызывается извне
        if ($action->id === 'webhook') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'connect' => ['POST'],
                        'disconnect' => ['POST'],
                        'send-message' => ['POST'],
                        'send-media' => ['POST'],
                        'mark-read' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['webhook'], // webhook доступен без авторизации
                            'allow' => true,
                        ],
                        [
                            'allow' => true,
                            'roles' => RoleChecker::getRolesForAccess('admin'),
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Главная страница - статус подключения и QR-код
     */
    public function actionIndex()
    {
        $session = WhatsappSession::getCurrentSession();
        $service = WhatsappService::getInstance();

        // Проверяем доступность API
        $apiAvailable = $service->checkConnection();
        $webhookDiagnostic = null;
        $disconnectReason = null;

        // Если есть сессия - обновляем статус
        if ($session && $apiAvailable) {
            $state = $service->getConnectionState($session);
            $session->refresh();

            // Если уже подключен - запускаем автодиагностику webhook
            if ($session->isConnected()) {
                $webhookDiagnostic = $service->diagnoseAndFixWebhook($session);

                // Если были исправлены проблемы - показываем flash сообщение
                if (!empty($webhookDiagnostic['fixed'])) {
                    Yii::$app->session->setFlash('success', 'Проблемы с подключением автоматически исправлены');
                }

                return $this->redirect(['chats']);
            }

            // Проверяем причину отключения
            if (($state['disconnectionReasonCode'] ?? null) == 401) {
                $disconnectReason = 'device_removed';
            }

            // Если сессия отключена и требует переподключения (device_removed)
            // автоматически инициируем переподключение
            if (($state['needsReconnect'] ?? false) || $session->status === WhatsappSession::STATUS_DISCONNECTED) {
                // Пробуем получить новый QR код
                $qrCode = $service->getQrCode($session);
                if ($qrCode) {
                    $session->status = WhatsappSession::STATUS_CONNECTING;
                    $session->save(false);
                    $session->refresh();
                }
            }
        }

        return $this->render('index', [
            'session' => $session,
            'apiAvailable' => $apiAvailable,
            'webhookDiagnostic' => $webhookDiagnostic,
            'disconnectReason' => $disconnectReason,
        ]);
    }

    /**
     * Подключить WhatsApp (создать инстанс)
     */
    public function actionConnect()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = WhatsappService::getInstance();

        if (!$service->checkConnection()) {
            return [
                'success' => false,
                'message' => 'Evolution API недоступен. Проверьте что Docker контейнер запущен.',
            ];
        }

        $organizationId = Organizations::getCurrentOrganizationId();
        $session = $service->createInstance($organizationId);

        if (!$session) {
            return [
                'success' => false,
                'message' => 'Не удалось создать подключение. Попробуйте позже.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Подключение создано. Отсканируйте QR-код.',
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'qr_code' => $session->qr_code,
            ],
        ];
    }

    /**
     * Отключить WhatsApp
     */
    public function actionDisconnect()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = WhatsappSession::getCurrentSession();

        if (!$session) {
            return [
                'success' => false,
                'message' => 'Активная сессия не найдена.',
            ];
        }

        $service = WhatsappService::getInstance();
        $service->disconnect($session);

        return [
            'success' => true,
            'message' => 'WhatsApp отключен.',
        ];
    }

    /**
     * Получить актуальный QR-код
     */
    public function actionGetQrCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = WhatsappSession::getCurrentSession();

        if (!$session) {
            return [
                'success' => false,
                'message' => 'Сессия не найдена. Сначала создайте подключение.',
            ];
        }

        $service = WhatsappService::getInstance();
        $qrCode = $service->getQrCode($session);

        return [
            'success' => (bool)$qrCode,
            'qr_code' => $qrCode,
            'status' => $session->status,
        ];
    }

    /**
     * Получить статус подключения
     */
    public function actionGetStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = WhatsappSession::getCurrentSession();

        if (!$session) {
            return [
                'success' => true,
                'connected' => false,
                'status' => 'not_created',
            ];
        }

        $service = WhatsappService::getInstance();
        $state = $service->getConnectionState($session);
        $session->refresh();

        // Проверяем здоровье webhook если подключен
        $webhookHealthy = null;
        if ($session->isConnected()) {
            $diagnostic = $service->diagnoseAndFixWebhook($session);
            $webhookHealthy = $diagnostic['healthy'] ?? true;
        }

        return [
            'success' => true,
            'connected' => $session->isConnected(),
            'status' => $session->status,
            'phone_number' => $session->phone_number,
            'qr_code' => $session->status === WhatsappSession::STATUS_CONNECTING ? $session->qr_code : null,
            'unread_count' => $session->getUnreadCount(),
            'last_message_at' => $session->getLastMessageTime(),
            'webhook_healthy' => $webhookHealthy,
            'disconnect_reason' => ($state['disconnectionReasonCode'] ?? null) == 401 ? 'device_removed' : null,
        ];
    }

    /**
     * Список чатов
     */
    public function actionChats()
    {
        $session = WhatsappSession::getCurrentSession();

        if (!$session || !$session->isConnected()) {
            return $this->render('not-connected');
        }

        $query = WhatsappChat::find()
            ->where(['session_id' => $session->id])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['is_archived' => false])
            ->with(['lid', 'lastMessage'])
            ->orderBy(['last_message_at' => SORT_DESC]);

        // Поиск
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'remote_name', $search],
                ['like', 'remote_phone', $search],
            ]);
        }

        $chats = $query->all();

        return $this->render('chats', [
            'session' => $session,
            'chats' => $chats,
            'search' => $search,
        ]);
    }

    /**
     * Просмотр чата (сообщения)
     */
    public function actionChat($id)
    {
        $chat = $this->findChat($id);

        // Помечаем все сообщения как прочитанные
        $chat->markAllAsRead();

        $messages = WhatsappMessage::find()
            ->where(['session_id' => $chat->session_id, 'remote_jid' => $chat->remote_jid])
            ->andWhere(['is_deleted' => 0])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_chat-messages', [
                'chat' => $chat,
                'messages' => $messages,
            ]);
        }

        return $this->render('chat', [
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }

    /**
     * AJAX: Получить содержимое чата для split-view
     * @param int $chat_id ID чата
     * @param int|null $before_id Загрузить сообщения до этого ID (для пагинации)
     * @param int $limit Количество сообщений
     */
    public function actionGetChatContent($chat_id, $before_id = null, $limit = 50)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $chat = $this->findChat($chat_id);

            // Помечаем все сообщения как прочитанные
            $chat->markAllAsRead();

            // Получаем аватарку если её ещё нет (в try-catch чтобы не ломать загрузку)
            if (!$chat->profile_picture_url && $chat->remote_phone) {
                try {
                    $service = WhatsappService::getInstance();
                    $service->updateChatProfilePicture($chat);
                    $chat->refresh();
                } catch (\Exception $e) {
                    Yii::warning("Failed to fetch profile picture: " . $e->getMessage(), 'whatsapp');
                }
            }

            // Базовый запрос
            $query = WhatsappMessage::find()
                ->where(['session_id' => $chat->session_id, 'remote_jid' => $chat->remote_jid])
                ->andWhere(['is_deleted' => 0]);

            // Если запрашиваем старые сообщения
            if ($before_id) {
                $query->andWhere(['<', 'id', $before_id]);
            }

            // Считаем общее количество для определения hasMore
            $totalCount = (clone $query)->count();

            // Получаем сообщения с лимитом (берём последние N)
            $messages = $query
                ->orderBy(['id' => SORT_DESC])
                ->limit($limit)
                ->all();

            // Переворачиваем для правильного порядка отображения
            $messages = array_reverse($messages);

            // Определяем есть ли ещё сообщения
            $hasMore = $totalCount > $limit;
            $oldestId = $messages ? reset($messages)->id : null;

            // Формируем HTML сообщений
            $messagesHtml = $this->renderPartial('_messages-list', [
                'messages' => $messages,
                'hasMore' => $hasMore,
                'oldestId' => $oldestId,
            ]);

            return [
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'name' => $chat->remote_name ?: $chat->remote_phone,
                    'phone' => $chat->remote_phone,
                    'lid_id' => $chat->lid_id,
                    'lid_name' => $chat->lid ? $chat->lid->fio : null,
                    'profile_picture_url' => $chat->profile_picture_url,
                ],
                'messages_html' => $messagesHtml,
                'last_message_id' => $messages ? end($messages)->id : 0,
                'oldest_id' => $oldestId,
                'has_more' => $hasMore,
            ];
        } catch (\Exception $e) {
            Yii::error("GetChatContent error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'whatsapp');
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'Ошибка загрузки чата',
            ];
        }
    }

    /**
     * AJAX: Загрузить более старые сообщения
     */
    public function actionLoadMoreMessages($chat_id, $before_id, $limit = 30)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $chat = $this->findChat($chat_id);

            $query = WhatsappMessage::find()
                ->where(['session_id' => $chat->session_id, 'remote_jid' => $chat->remote_jid])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['<', 'id', $before_id]);

            $totalCount = (clone $query)->count();

            $messages = $query
                ->orderBy(['id' => SORT_DESC])
                ->limit($limit)
                ->all();

            $messages = array_reverse($messages);

            $hasMore = $totalCount > $limit;
            $oldestId = $messages ? reset($messages)->id : null;

            $messagesHtml = $this->renderPartial('_messages-list', [
                'messages' => $messages,
                'hasMore' => false, // Не показываем кнопку в этом блоке
                'oldestId' => $oldestId,
            ]);

            return [
                'success' => true,
                'messages_html' => $messagesHtml,
                'oldest_id' => $oldestId,
                'has_more' => $hasMore,
                'count' => count($messages),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка загрузки сообщений',
            ];
        }
    }

    /**
     * Отправить сообщение
     */
    public function actionSendMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chatId = Yii::$app->request->post('chat_id');
        $text = Yii::$app->request->post('text');
        $phone = Yii::$app->request->post('phone');
        // Параметр для явного разрешения первого сообщения (только для ручной отправки)
        $allowFirst = (bool)Yii::$app->request->post('allow_first', false);

        if (!$text) {
            return ['success' => false, 'message' => 'Введите текст сообщения'];
        }

        $session = WhatsappSession::getCurrentSession();

        if (!$session || !$session->isConnected()) {
            return ['success' => false, 'message' => 'WhatsApp не подключен'];
        }

        // Получаем номер телефона
        if ($chatId) {
            $chat = $this->findChat($chatId);
            $phone = $chat->remote_phone;
            $lidId = $chat->lid_id;
        } else {
            $lidId = Yii::$app->request->post('lid_id');
        }

        if (!$phone) {
            return ['success' => false, 'message' => 'Не указан номер телефона'];
        }

        // Проверяем наличие входящих сообщений от контакта
        // Это обязательное условие для предотвращения блокировки номера WhatsApp
        if (!$allowFirst && !WhatsappChat::hasIncomingFrom($phone, $session->id)) {
            return [
                'success' => false,
                'message' => 'Нельзя отправить первое сообщение. Дождитесь когда клиент напишет вам первым.',
                'require_confirmation' => true,
            ];
        }

        $service = WhatsappService::getInstance();
        $message = $service->sendText($session, $phone, $text, $lidId);

        if ($message) {
            return [
                'success' => true,
                'message' => 'Сообщение отправлено',
                'data' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->getFormattedDate(),
                ],
            ];
        }

        return ['success' => false, 'message' => 'Не удалось отправить сообщение'];
    }

    /**
     * Отправить медиа файл (изображение, документ)
     */
    public function actionSendMedia()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chatId = Yii::$app->request->post('chat_id');
        $caption = Yii::$app->request->post('caption', '');

        $session = WhatsappSession::getCurrentSession();

        if (!$session || !$session->isConnected()) {
            return ['success' => false, 'message' => 'WhatsApp не подключен'];
        }

        // Получаем загруженный файл
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('file');

        if (!$uploadedFile) {
            return ['success' => false, 'message' => 'Файл не загружен'];
        }

        // Проверка размера (макс 16MB для WhatsApp)
        if ($uploadedFile->size > 16 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Файл слишком большой (макс. 16MB)'];
        }

        // Определяем тип медиа
        $mimeType = $uploadedFile->type;
        $mediaType = 'document';

        if (str_starts_with($mimeType, 'image/')) {
            $mediaType = 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $mediaType = 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $mediaType = 'audio';
        }

        // Получаем чат и телефон
        $chat = $this->findChat($chatId);
        $phone = $chat->remote_phone;
        $lidId = $chat->lid_id;

        // Проверяем наличие входящих сообщений от контакта
        if (!WhatsappChat::hasIncomingFrom($phone, $session->id)) {
            return [
                'success' => false,
                'message' => 'Нельзя отправить первое сообщение. Дождитесь когда клиент напишет вам первым.',
            ];
        }

        // Читаем файл и конвертируем в base64
        $fileContent = file_get_contents($uploadedFile->tempName);
        if (!$fileContent) {
            return ['success' => false, 'message' => 'Ошибка чтения файла'];
        }

        // Формируем Data URL для Evolution API
        $base64 = base64_encode($fileContent);
        $mediaUrl = "data:{$mimeType};base64,{$base64}";

        $service = WhatsappService::getInstance();
        $message = $service->sendMedia(
            $session,
            $phone,
            $mediaUrl,
            $mediaType,
            $caption ?: null,
            $uploadedFile->baseName . '.' . $uploadedFile->extension,
            $lidId
        );

        if ($message) {
            // Сохраняем файл для отображения в чате
            $uploadDir = Yii::getAlias('@webroot/uploads/whatsapp/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = uniqid('wa_') . '_' . $uploadedFile->baseName . '.' . $uploadedFile->extension;
            $uploadedFile->saveAs($uploadDir . $filename);

            // Обновляем URL в сообщении на локальный
            $message->media_url = '/uploads/whatsapp/' . $filename;
            $message->save(false);

            return [
                'success' => true,
                'message' => 'Файл отправлен',
                'data' => [
                    'id' => $message->id,
                    'media_type' => $mediaType,
                    'created_at' => $message->getFormattedDate(),
                ],
            ];
        }

        // Получаем последние ошибки из логов для диагностики
        $errorDetails = [];
        $messages = Yii::getLogger()->messages;
        foreach (array_reverse($messages) as $log) {
            if (isset($log[2]) && $log[2] === 'whatsapp') {
                $errorDetails[] = $log[0];
                if (count($errorDetails) >= 3) break;
            }
        }

        $debugInfo = implode(' | ', $errorDetails);

        return [
            'success' => false,
            'message' => 'Не удалось отправить файл. ' . (YII_DEBUG && $debugInfo ? $debugInfo : 'Проверьте подключение WhatsApp.'),
        ];
    }

    /**
     * Получить новые сообщения чата (для polling)
     */
    public function actionGetMessages($chat_id, $after_id = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chat = $this->findChat($chat_id);

        $messages = WhatsappMessage::find()
            ->where(['session_id' => $chat->session_id, 'remote_jid' => $chat->remote_jid])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['>', 'id', $after_id])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        // Помечаем входящие как прочитанные
        foreach ($messages as $message) {
            if ($message->direction === WhatsappMessage::DIRECTION_INCOMING) {
                $message->markAsRead();
            }
        }

        // Обновляем счётчик непрочитанных
        $chat->unread_count = 0;
        $chat->save(false);

        $result = [];
        foreach ($messages as $message) {
            $result[] = [
                'id' => $message->id,
                'direction' => $message->direction,
                'message_type' => $message->message_type,
                'content' => $message->content,
                'status' => $message->status,
                'created_at' => $message->getFormattedDate(),
                'is_from_me' => $message->is_from_me,
            ];
        }

        return [
            'success' => true,
            'messages' => $result,
        ];
    }

    /**
     * Пометить чат как прочитанный
     */
    public function actionMarkRead($chat_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chat = $this->findChat($chat_id);
        $count = $chat->markAllAsRead();

        return [
            'success' => true,
            'marked_count' => $count,
        ];
    }

    /**
     * Привязать чат к лиду
     */
    public function actionLinkToLid()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chatId = Yii::$app->request->post('chat_id');
        $lidId = Yii::$app->request->post('lid_id');

        $chat = $this->findChat($chatId);

        if ($chat->linkToLid($lidId)) {
            return [
                'success' => true,
                'message' => 'Чат привязан к лиду',
            ];
        }

        return ['success' => false, 'message' => 'Ошибка привязки'];
    }

    /**
     * Создать лид из чата
     */
    public function actionCreateLidFromChat($chat_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chat = $this->findChat($chat_id);

        if ($chat->lid_id) {
            return ['success' => false, 'message' => 'Чат уже привязан к лиду'];
        }

        $lid = $chat->createLid();

        if ($lid) {
            return [
                'success' => true,
                'message' => 'Лид создан',
                'lid_id' => $lid->id,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка создания лида'];
    }

    /**
     * Webhook endpoint для Evolution API
     * Принимает входящие события (сообщения, статусы и т.д.)
     */
    public function actionWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Получаем данные из тела запроса
        $rawBody = Yii::$app->request->rawBody;
        $data = json_decode($rawBody, true);

        if (!$data) {
            Yii::warning('WhatsApp webhook: empty or invalid JSON', 'whatsapp');
            return ['status' => 'error', 'message' => 'Invalid JSON'];
        }

        // Логируем входящий webhook для отладки
        Yii::info('WhatsApp webhook received: ' . $rawBody, 'whatsapp');

        // Обрабатываем через сервис
        $service = WhatsappService::getInstance();
        $result = $service->handleWebhook($data);

        return [
            'status' => $result ? 'ok' : 'error',
        ];
    }

    /**
     * Скачать медиа файл из сообщения
     * Получает файл через Evolution API и отдаёт пользователю
     * @param int $message_id ID сообщения
     * @param bool $inline Если true - отдать для воспроизведения в браузере (inline), иначе для скачивания (attachment)
     */
    public function actionDownloadMedia($message_id, $inline = false)
    {
        $message = WhatsappMessage::find()
            ->byOrganization()
            ->andWhere(['id' => $message_id])
            ->notDeleted()
            ->one();

        if (!$message) {
            throw new NotFoundHttpException('Сообщение не найдено');
        }

        // Проверяем что это медиа сообщение
        if (!in_array($message->message_type, [
            WhatsappMessage::TYPE_IMAGE,
            WhatsappMessage::TYPE_VIDEO,
            WhatsappMessage::TYPE_AUDIO,
            WhatsappMessage::TYPE_DOCUMENT,
            WhatsappMessage::TYPE_STICKER,
        ])) {
            throw new NotFoundHttpException('Это не медиа сообщение');
        }

        $session = WhatsappSession::findOne($message->session_id);
        if (!$session) {
            throw new NotFoundHttpException('Сессия не найдена');
        }

        try {
            $service = WhatsappService::getInstance();

            // Получаем base64 медиа через Evolution API
            $result = $service->getMediaBase64($session, $message->whatsapp_id);

            if (!$result || empty($result['base64'])) {
                // Пробуем альтернативный метод
                $result = $service->getMediaBase64Alternative($session, $message);
            }

            if (!$result || empty($result['base64'])) {
                Yii::warning("Failed to download media for message {$message->id}", 'whatsapp');
                throw new \yii\web\HttpException(404, 'Не удалось загрузить медиа файл. Возможно файл устарел.');
            }

            // Декодируем base64
            $base64 = $result['base64'];
            // Убираем data URI prefix если есть
            if (str_contains($base64, ',')) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }

            $fileData = base64_decode($base64);
            if (!$fileData) {
                throw new \yii\web\HttpException(500, 'Ошибка декодирования файла');
            }

            // Определяем MIME тип и расширение
            $mimeType = $result['mimetype'] ?? $message->media_mimetype ?? 'application/octet-stream';
            $filename = $message->media_filename;

            if (!$filename) {
                // Генерируем имя файла
                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                    'video/mp4' => 'mp4',
                    'audio/ogg' => 'ogg',
                    'audio/mpeg' => 'mp3',
                    'application/pdf' => 'pdf',
                ];
                $ext = $extensions[$mimeType] ?? 'bin';
                $typeNames = [
                    WhatsappMessage::TYPE_IMAGE => 'image',
                    WhatsappMessage::TYPE_VIDEO => 'video',
                    WhatsappMessage::TYPE_AUDIO => 'audio',
                    WhatsappMessage::TYPE_DOCUMENT => 'document',
                    WhatsappMessage::TYPE_STICKER => 'sticker',
                ];
                $typeName = $typeNames[$message->message_type] ?? 'file';
                $filename = "whatsapp_{$typeName}_" . date('Y-m-d_His', strtotime($message->created_at)) . ".{$ext}";
            }

            // Отправляем файл
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->headers->set('Content-Type', $mimeType);

            // inline - для воспроизведения в браузере (аудио/видео), attachment - для скачивания
            $disposition = $inline ? 'inline' : 'attachment';
            Yii::$app->response->headers->set('Content-Disposition', "{$disposition}; filename=\"{$filename}\"");
            Yii::$app->response->headers->set('Content-Length', strlen($fileData));

            // Для inline добавляем заголовки для стриминга
            if ($inline) {
                Yii::$app->response->headers->set('Accept-Ranges', 'bytes');
                Yii::$app->response->headers->set('Cache-Control', 'public, max-age=3600');
            }

            return $fileData;

        } catch (\yii\web\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Yii::error("Download media error: " . $e->getMessage(), 'whatsapp');
            throw new \yii\web\HttpException(500, 'Ошибка загрузки файла: ' . $e->getMessage());
        }
    }

    /**
     * Переконфигурировать webhook для сессии
     * Применяет новые настройки событий
     */
    public function actionReconfigureWebhook()
    {
        $session = WhatsappSession::getCurrentSession();

        if (!$session) {
            Yii::$app->session->setFlash('error', 'Сессия WhatsApp не найдена');
            return $this->redirect(['index']);
        }

        $service = WhatsappService::getInstance();
        $result = $service->setupWebhook($session->instance_name);

        if ($result) {
            Yii::$app->session->setFlash('success', 'Webhook успешно переконфигурирован. Статусы доставки теперь будут обновляться.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось переконфигурировать webhook');
        }

        return $this->redirect(['index']);
    }

    /**
     * AJAX: Получить список чатов для виджета
     * Возвращает чаты в формате JSON для WhatsApp Widget
     */
    public function actionWidgetChats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = WhatsappSession::getCurrentSession();

        if (!$session || !$session->isConnected()) {
            return [
                'success' => false,
                'message' => 'WhatsApp не подключен',
            ];
        }

        $chats = WhatsappChat::find()
            ->where(['session_id' => $session->id])
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['is_archived' => false])
            ->with(['lastMessage', 'lid'])
            ->orderBy(['last_message_at' => SORT_DESC])
            ->limit(30)
            ->all();

        $result = [];
        foreach ($chats as $chat) {
            $result[] = [
                'id' => $chat->id,
                'name' => $chat->getDisplayName(),
                'phone' => $chat->remote_phone,
                'preview' => $chat->lastMessage ? $chat->lastMessage->getPreview() : '',
                'time' => $chat->getLastMessageTime(),
                'unread' => $chat->unread_count,
                'lid_id' => $chat->lid_id,
                'profile_picture_url' => $chat->profile_picture_url,
            ];
        }

        return [
            'success' => true,
            'chats' => $result,
        ];
    }

    /**
     * Найти чат по ID
     * @param int $id
     * @return WhatsappChat
     * @throws NotFoundHttpException
     */
    protected function findChat($id): WhatsappChat
    {
        $chat = WhatsappChat::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if (!$chat) {
            throw new NotFoundHttpException('Чат не найден');
        }

        return $chat;
    }
}
