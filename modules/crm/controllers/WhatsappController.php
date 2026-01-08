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

        // Если есть сессия - обновляем статус
        if ($session && $apiAvailable) {
            $service->getConnectionState($session);
            $session->refresh();

            // Если уже подключен - редирект на чаты
            if ($session->isConnected()) {
                return $this->redirect(['chats']);
            }
        }

        return $this->render('index', [
            'session' => $session,
            'apiAvailable' => $apiAvailable,
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

        return [
            'success' => true,
            'connected' => $session->isConnected(),
            'status' => $session->status,
            'phone_number' => $session->phone_number,
            'qr_code' => $session->status === WhatsappSession::STATUS_CONNECTING ? $session->qr_code : null,
            'unread_count' => $session->getUnreadCount(),
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
     */
    public function actionGetChatContent($chat_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $chat = $this->findChat($chat_id);

        // Помечаем все сообщения как прочитанные
        $chat->markAllAsRead();

        $messages = WhatsappMessage::find()
            ->where(['session_id' => $chat->session_id, 'remote_jid' => $chat->remote_jid])
            ->andWhere(['is_deleted' => 0])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        // Формируем HTML сообщений
        $messagesHtml = $this->renderPartial('_messages-list', [
            'messages' => $messages,
        ]);

        return [
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'name' => $chat->remote_name ?: $chat->remote_phone,
                'phone' => $chat->remote_phone,
                'lid_id' => $chat->lid_id,
                'lid_name' => $chat->lid ? $chat->lid->fio : null,
            ],
            'messages_html' => $messagesHtml,
            'last_message_id' => $messages ? end($messages)->id : 0,
        ];
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
