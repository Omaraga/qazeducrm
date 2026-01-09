<?php

namespace app\widgets\tailwind;

use app\helpers\RoleChecker;
use app\models\WhatsappChat;
use app\models\WhatsappSession;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * WhatsApp Widget - фиксированная кнопка и выдвижная панель чатов
 */
class WhatsappWidget extends Widget
{
    /**
     * @var bool Показывать виджет
     */
    public $visible = true;

    /**
     * @inheritdoc
     */
    public function run()
    {
        // Проверяем права доступа
        if (!$this->visible || !RoleChecker::isAdminOrHigher()) {
            return '';
        }

        // Проверяем есть ли активная сессия WhatsApp
        $session = WhatsappSession::find()
            ->byOrganization()
            ->andWhere(['status' => WhatsappSession::STATUS_CONNECTED])
            ->one();

        if (!$session) {
            return '';
        }

        // Получаем количество непрочитанных сообщений
        $unreadCount = WhatsappChat::find()
            ->byOrganization()
            ->andWhere(['>', 'unread_count', 0])
            ->sum('unread_count') ?: 0;

        // Получаем последние чаты для первоначальной загрузки
        $chats = WhatsappChat::find()
            ->byOrganization()
            ->with(['lastMessage', 'lid'])
            ->orderBy(['last_message_at' => SORT_DESC])
            ->limit(20)
            ->all();

        // URLs для AJAX
        $urls = [
            'getChats' => Url::to(['/crm/whatsapp/widget-chats']),
            'getChatContent' => Url::to(['/crm/whatsapp/get-chat-content']),
            'sendMessage' => Url::to(['/crm/whatsapp/send-message']),
            'getMessages' => Url::to(['/crm/whatsapp/get-messages']),
            'chatsPage' => Url::to(['/crm/whatsapp/chats']),
        ];

        return $this->render('whatsapp-widget', [
            'session' => $session,
            'chats' => $chats,
            'unreadCount' => $unreadCount,
            'urls' => $urls,
        ]);
    }
}
