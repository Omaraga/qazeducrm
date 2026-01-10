<?php

use app\models\WhatsappChat;
use app\models\WhatsappSession;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var WhatsappSession $session */
/** @var WhatsappChat[] $chats */
/** @var string|null $search */

$this->title = 'WhatsApp';
$this->params['breadcrumbs'][] = ['label' => 'CRM', 'url' => ['/crm']];
$this->params['breadcrumbs'][] = 'WhatsApp';

// URLs для AJAX
$getChatContentUrl = Url::to(['/crm/whatsapp/get-chat-content']);
$getMessagesUrl = Url::to(['/crm/whatsapp/get-messages']);
$loadMoreMessagesUrl = Url::to(['/crm/whatsapp/load-more-messages']);
$sendMessageUrl = Url::to(['/crm/whatsapp/send-message']);
$createLidUrl = Url::to(['/crm/whatsapp/create-lid-from-chat']);
$csrfToken = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
?>

<?php
// Register WhatsApp CSS
$this->registerCssFile('@web/css/whatsapp.css', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="space-y-4" x-data="whatsappChat()" x-init="init()">

    <!-- Flash сообщения -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-green-800"><?= Yii::$app->session->getFlash('success') ?></span>
                <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4" x-data="{ show: true }" x-show="show">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-red-800"><?= Yii::$app->session->getFlash('error') ?></span>
                <button @click="show = false" class="ml-auto text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">WhatsApp</h1>
            <p class="text-gray-500 mt-1">
                <?php if ($session->phone_number): ?>
                    <span class="inline-flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        +<?= Html::encode($session->phone_number) ?>
                    </span>
                <?php else: ?>
                    Чатов: <?= count($chats) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Настройки
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="card overflow-hidden" style="height: calc(100vh - 220px); min-height: 500px;">
        <div class="flex h-full">

            <!-- Левая панель: Список чатов -->
            <div class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r border-gray-200 flex flex-col bg-gray-50"
                 :class="{ 'hidden md:flex': selectedChatId }">

                <!-- Фильтры и Поиск -->
                <div class="p-3 border-b border-gray-200 space-y-2">
                    <!-- Табы фильтрации -->
                    <div class="wa-filter-tabs">
                        <button type="button" class="wa-filter-tab" :class="{ 'active': chatFilter === 'all' }" @click="chatFilter = 'all'">Все</button>
                        <button type="button" class="wa-filter-tab" :class="{ 'active': chatFilter === 'unread' }" @click="chatFilter = 'unread'">Непрочитанные</button>
                        <button type="button" class="wa-filter-tab" :class="{ 'active': chatFilter === 'leads' }" @click="chatFilter = 'leads'">С лидами</button>
                    </div>
                    <!-- Поиск -->
                    <form method="get" action="<?= Url::to(['chats']) ?>">
                        <div class="relative">
                            <input type="text" name="search" value="<?= Html::encode($search) ?>"
                                   placeholder="Поиск по имени или телефону..."
                                   class="form-input pl-10 pr-4 py-2 text-sm">
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>

                <!-- Список чатов -->
                <div class="flex-1 overflow-y-auto wa-scrollbar">
                    <?php if (empty($chats)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="font-medium">Нет чатов</p>
                            <p class="text-sm mt-1">Чаты появятся когда клиенты напишут вам</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($chats as $chat): ?>
                            <div @click="selectChat(<?= $chat->id ?>)"
                                 :class="selectedChatId === <?= $chat->id ?> ? 'bg-blue-50 border-l-2 border-blue-500' : 'hover:bg-gray-100 border-l-2 border-transparent'"
                                 x-show="chatFilter === 'all' || (chatFilter === 'unread' && <?= $chat->unread_count > 0 ? 'true' : 'false' ?>) || (chatFilter === 'leads' && <?= $chat->lid_id ? 'true' : 'false' ?>)"
                                 class="flex items-center gap-3 px-3 py-3 cursor-pointer transition-colors border-b border-gray-100 chat-item"
                                 data-unread="<?= $chat->unread_count > 0 ? '1' : '0' ?>"
                                 data-has-lid="<?= $chat->lid_id ? '1' : '0' ?>">

                                <!-- Аватар -->
                                <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden <?= !$chat->profile_picture_url ? ($chat->lid_id ? 'bg-blue-100' : 'bg-gray-200') : '' ?>">
                                    <?php if ($chat->profile_picture_url): ?>
                                        <img src="<?= Html::encode($chat->profile_picture_url) ?>"
                                             alt="<?= Html::encode($chat->getDisplayName()) ?>"
                                             class="w-full h-full object-cover"
                                             onerror="this.parentElement.innerHTML='<span class=\'<?= $chat->lid_id ? 'text-blue-600' : 'text-gray-500' ?> font-semibold\'><?= mb_strtoupper(mb_substr($chat->getDisplayName(), 0, 1)) ?></span>'">
                                    <?php elseif ($chat->lid): ?>
                                        <span class="text-blue-600 font-semibold">
                                            <?= mb_strtoupper(mb_substr($chat->getDisplayName(), 0, 1)) ?>
                                        </span>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>

                                <!-- Информация -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="font-medium text-gray-900 truncate text-sm">
                                            <?= Html::encode($chat->getDisplayName()) ?>
                                        </span>
                                        <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                            <?= $chat->getLastMessageTime() ?>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-500 truncate">
                                            <?php if ($chat->lastMessage): ?>
                                                <?php if ($chat->lastMessage->is_from_me): ?>
                                                    <svg class="w-4 h-4 inline <?= $chat->lastMessage->status === 'read' ? 'text-blue-500' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                <?php endif; ?>
                                                <?= Html::encode($chat->lastMessage->getPreview()) ?>
                                            <?php else: ?>
                                                <span class="italic text-gray-400">Нет сообщений</span>
                                            <?php endif; ?>
                                        </p>

                                        <?php if ($chat->unread_count > 0): ?>
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-blue-500 rounded-full flex-shrink-0 ml-2">
                                                <?= $chat->unread_count > 99 ? '99+' : $chat->unread_count ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($chat->lid): ?>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                                <?= Html::encode($chat->lid->fio ?: $chat->lid->parent_fio ?: 'Лид') ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Правая панель: Содержимое чата -->
            <div class="flex-1 flex flex-col bg-white"
                 :class="{ 'hidden md:flex': !selectedChatId }">

                <!-- Пустое состояние -->
                <div x-show="!selectedChatId" class="hidden md:flex flex-1 items-center justify-center bg-gray-50">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Выберите чат</h3>
                        <p class="text-gray-500 mt-1">для начала общения</p>
                    </div>
                </div>

                <!-- Контент чата -->
                <div x-show="selectedChatId" x-cloak class="flex-1 flex flex-col h-full">

                    <!-- Заголовок чата -->
                    <div class="px-4 py-3 flex items-center justify-between border-b border-gray-200 bg-white">
                        <div class="flex items-center gap-3">
                            <!-- Кнопка назад (мобильные) -->
                            <button @click="goBack()"
                                    class="md:hidden p-1.5 -ml-1 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <!-- Аватар с онлайн-индикатором -->
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden"
                                     :class="!chatInfo.profile_picture_url ? (chatInfo.lid_id ? 'bg-blue-100' : 'bg-gray-200') : ''">
                                    <template x-if="chatInfo.profile_picture_url">
                                        <img :src="chatInfo.profile_picture_url"
                                             :alt="chatInfo.name"
                                             class="w-full h-full object-cover"
                                             @error="chatInfo.profile_picture_url = null">
                                    </template>
                                    <template x-if="!chatInfo.profile_picture_url">
                                        <span class="font-semibold text-lg" :class="chatInfo.lid_id ? 'text-blue-600' : 'text-gray-500'"
                                              x-text="chatInfo.name ? chatInfo.name.charAt(0).toUpperCase() : '?'"></span>
                                    </template>
                                </div>
                                <!-- Online indicator -->
                                <div class="wa-online-indicator" title="Онлайн"></div>
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 truncate text-base" x-text="chatInfo.name"></div>
                                <div class="text-sm text-gray-500 truncate" x-text="chatInfo.phone"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <!-- Ссылка на лид -->
                            <a x-show="chatInfo.lid_id"
                               :href="'/<?= Yii::$app->params['organizationId'] ?? 2 ?>/lids/view?id=' + chatInfo.lid_id"
                               class="btn btn-secondary btn-sm">
                                <svg class="w-4 h-4 md:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="hidden md:inline" x-text="chatInfo.lid_name || 'Открыть лид'"></span>
                            </a>

                            <!-- Кнопка создания лида -->
                            <button x-show="!chatInfo.lid_id"
                                    @click="createLid()"
                                    :disabled="creatingLid"
                                    class="btn btn-primary btn-sm">
                                <svg class="w-4 h-4 md:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="hidden md:inline" x-text="creatingLid ? 'Создание...' : 'Создать лид'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Область сообщений -->
                    <div class="flex-1 overflow-y-auto wa-scrollbar p-4 bg-gray-50"
                         x-ref="messagesContainer"
                         id="messages-container">
                        <!-- Кнопка загрузки старых сообщений -->
                        <div x-show="hasMoreMessages" class="text-center py-3 mb-4">
                            <button type="button"
                                    @click="loadMoreMessages()"
                                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors shadow-sm"
                                    :disabled="loadingMore">
                                <span x-show="!loadingMore">Загрузить ранние сообщения</span>
                                <span x-show="loadingMore" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Загрузка...
                                </span>
                            </button>
                        </div>
                        <div x-html="messagesHtml"></div>
                    </div>

                    <!-- Область ввода сообщения -->
                    <div class="px-4 py-3 border-t border-gray-200 bg-white relative"
                         @reply-message.window="replyTo = $event.detail; $nextTick(() => $refs.messageInput?.focus())">

                        <!-- Reply preview -->
                        <div x-show="replyTo" x-transition class="wa-reply-preview mb-2">
                            <div class="wa-reply-preview-content">
                                <div class="wa-reply-preview-name" x-text="replyTo?.name"></div>
                                <div class="wa-reply-preview-text" x-text="replyTo?.text"></div>
                            </div>
                            <button type="button" class="wa-reply-preview-close" @click="replyTo = null">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Emoji Picker -->
                        <div x-show="showEmojiPicker"
                             x-transition
                             @click.outside="showEmojiPicker = false"
                             class="absolute bottom-full left-4 mb-2 z-50 bg-white rounded-lg shadow-xl border border-gray-200">
                            <emoji-picker @emoji-click="insertEmoji($event)"></emoji-picker>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Кнопка Emoji -->
                            <button @click="showEmojiPicker = !showEmojiPicker"
                                    type="button"
                                    class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <!-- Поле ввода -->
                            <div class="flex-1">
                                <textarea x-model="messageText"
                                          x-ref="messageInput"
                                          @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                                          @input="adjustTextarea()"
                                          placeholder="Введите сообщение..."
                                          rows="1"
                                          class="form-input py-2 text-sm resize-none max-h-32 overflow-y-auto"
                                          style="min-height: 38px; line-height: 1.4;"></textarea>
                            </div>

                            <!-- Кнопка отправки -->
                            <button @click="sendMessage()"
                                    :disabled="!messageText.trim() || sending"
                                    class="btn btn-primary flex-shrink-0 px-3"
                                    :class="{ 'opacity-50 cursor-not-allowed': !messageText.trim() || sending }">
                                <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                                <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Emoji Picker Script -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('whatsappChat', () => ({
        selectedChatId: null,
        chatInfo: {},
        messagesHtml: '',
        lastMessageId: 0,
        oldestMessageId: null,
        hasMoreMessages: false,
        loadingMore: false,
        messageText: '',
        showEmojiPicker: false,
        sending: false,
        creatingLid: false,
        pollInterval: null,
        chatFilter: 'all',
        replyTo: null,

        init() {
            this.pollInterval = setInterval(() => {
                if (this.selectedChatId) {
                    this.checkNewMessages();
                }
            }, 3000);
        },

        goBack() {
            this.selectedChatId = null;
            this.chatInfo = {};
            this.messagesHtml = '';
            this.oldestMessageId = null;
            this.hasMoreMessages = false;
        },

        async selectChat(chatId, forceReload = false) {
            if (this.selectedChatId === chatId && !forceReload) return;

            this.selectedChatId = chatId;
            this.messagesHtml = '<div class="text-center py-8 text-gray-400">Загрузка...</div>';
            this.oldestMessageId = null;
            this.hasMoreMessages = false;

            try {
                const response = await fetch(`<?= $getChatContentUrl ?>?chat_id=${chatId}`);
                const data = await response.json();

                if (data.success) {
                    this.chatInfo = {
                        ...data.chat,
                        profile_picture_url: data.chat.profile_picture_url || null
                    };
                    this.messagesHtml = data.messages_html;
                    this.lastMessageId = data.last_message_id;
                    this.oldestMessageId = data.oldest_id;
                    this.hasMoreMessages = data.has_more;

                    this.$nextTick(() => this.scrollToBottom());
                }
            } catch (error) {
                console.error('Error loading chat:', error);
                this.messagesHtml = '<div class="text-center py-8 text-red-500">Ошибка загрузки</div>';
            }
        },

        async loadMoreMessages() {
            if (!this.oldestMessageId || this.loadingMore || !this.hasMoreMessages) return;

            this.loadingMore = true;
            const container = this.$refs.messagesContainer;
            const scrollHeightBefore = container ? container.scrollHeight : 0;

            try {
                const response = await fetch(`<?= $loadMoreMessagesUrl ?>?chat_id=${this.selectedChatId}&before_id=${this.oldestMessageId}`);
                const data = await response.json();

                if (data.success && data.messages_html) {
                    // Вставляем новые сообщения в начало
                    const messagesWrapper = container.querySelector('.space-y-4');
                    if (messagesWrapper) {
                        // Создаём временный контейнер для новых сообщений
                        const temp = document.createElement('div');
                        temp.innerHTML = data.messages_html;

                        // Ищем контейнер сообщений в новом HTML
                        const newMessages = temp.querySelector('.space-y-4');
                        if (newMessages) {
                            // Вставляем содержимое в начало существующего контейнера
                            messagesWrapper.insertAdjacentHTML('afterbegin', newMessages.innerHTML);
                        }
                    }

                    this.oldestMessageId = data.oldest_id;
                    this.hasMoreMessages = data.has_more;

                    // Сохраняем позицию скролла
                    this.$nextTick(() => {
                        if (container) {
                            const scrollHeightAfter = container.scrollHeight;
                            container.scrollTop = scrollHeightAfter - scrollHeightBefore;
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading more messages:', error);
            } finally {
                this.loadingMore = false;
            }
        },

        async sendMessage() {
            if (!this.messageText.trim() || this.sending) return;

            this.sending = true;
            const text = this.messageText;
            const replyToId = this.replyTo?.id || null;
            this.messageText = '';
            this.showEmojiPicker = false;
            this.replyTo = null;

            if (this.$refs.messageInput) {
                this.$refs.messageInput.style.height = '38px';
            }

            try {
                const formData = new FormData();
                formData.append('chat_id', this.selectedChatId);
                formData.append('text', text);
                if (replyToId) formData.append('reply_to', replyToId);
                formData.append('<?= $csrfParam ?>', '<?= $csrfToken ?>');

                const response = await fetch('<?= $sendMessageUrl ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await this.selectChat(this.selectedChatId, true);
                } else {
                    alert(data.message || 'Ошибка отправки');
                    this.messageText = text;
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Ошибка отправки сообщения');
                this.messageText = text;
            } finally {
                this.sending = false;
            }
        },

        pollCounter: 0,

        async checkNewMessages() {
            if (!this.selectedChatId) return;

            this.pollCounter++;

            try {
                // Каждые 5 проверок (15 сек) делаем полную перезагрузку для обновления статусов
                if (this.pollCounter % 5 === 0) {
                    await this.reloadMessages();
                    return;
                }

                // Остальные проверки - только новые сообщения
                if (!this.lastMessageId) return;

                const response = await fetch(`<?= $getMessagesUrl ?>?chat_id=${this.selectedChatId}&after_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages && data.messages.length > 0) {
                    await this.reloadMessages();
                }
            } catch (error) {
                console.error('Error checking messages:', error);
            }
        },

        async reloadMessages() {
            try {
                const response = await fetch(`<?= $getChatContentUrl ?>?chat_id=${this.selectedChatId}`);
                const data = await response.json();

                if (data.success) {
                    const wasAtBottom = this.isScrolledToBottom();

                    this.messagesHtml = data.messages_html;
                    this.lastMessageId = data.last_message_id;
                    this.oldestMessageId = data.oldest_id;
                    this.hasMoreMessages = data.has_more;

                    if (wasAtBottom) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                }
            } catch (error) {
                console.error('Error reloading messages:', error);
            }
        },

        isScrolledToBottom() {
            const container = this.$refs.messagesContainer;
            if (!container) return true;
            return container.scrollHeight - container.scrollTop - container.clientHeight < 50;
        },

        async createLid() {
            if (this.creatingLid) return;
            this.creatingLid = true;

            try {
                const response = await fetch(`<?= $createLidUrl ?>?chat_id=${this.selectedChatId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': '<?= $csrfToken ?>' }
                });

                const data = await response.json();

                if (data.success) {
                    this.chatInfo.lid_id = data.lid_id;
                    this.chatInfo.lid_name = 'Лид';
                } else {
                    alert(data.message || 'Ошибка создания лида');
                }
            } catch (error) {
                console.error('Error creating lid:', error);
                alert('Ошибка создания лида');
            } finally {
                this.creatingLid = false;
            }
        },

        insertEmoji(event) {
            const emoji = event.detail.unicode;
            const input = this.$refs.messageInput;
            if (!input) return;

            const start = input.selectionStart;
            const end = input.selectionEnd;

            this.messageText = this.messageText.substring(0, start) + emoji + this.messageText.substring(end);

            this.$nextTick(() => {
                input.focus();
                input.setSelectionRange(start + emoji.length, start + emoji.length);
            });
        },

        adjustTextarea() {
            const textarea = this.$refs.messageInput;
            if (!textarea) return;
            textarea.style.height = '38px';
            textarea.style.height = Math.min(textarea.scrollHeight, 128) + 'px';
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
    }));
});

// Audio player functions
function toggleAudio(audioId) {
    const audio = document.getElementById(audioId);
    const playBtn = document.getElementById(audioId + '-play');
    const pauseBtn = document.getElementById(audioId + '-pause');

    if (!audio) return;

    // Pause all other audio
    document.querySelectorAll('.wa-audio-player audio').forEach(a => {
        if (a.id !== audioId && !a.paused) {
            a.pause();
            const otherId = a.id;
            document.getElementById(otherId + '-play')?.classList.remove('hidden');
            document.getElementById(otherId + '-pause')?.classList.add('hidden');
        }
    });

    if (audio.paused) {
        audio.play();
        playBtn?.classList.add('hidden');
        pauseBtn?.classList.remove('hidden');
    } else {
        audio.pause();
        playBtn?.classList.remove('hidden');
        pauseBtn?.classList.add('hidden');
    }

    // Setup event listeners once
    if (!audio.dataset.initialized) {
        audio.dataset.initialized = 'true';

        audio.addEventListener('timeupdate', () => {
            const fill = document.getElementById(audioId + '-fill');
            const timeEl = document.getElementById(audioId + '-time');
            if (fill && audio.duration) {
                fill.style.width = (audio.currentTime / audio.duration * 100) + '%';
            }
            if (timeEl) {
                timeEl.textContent = formatAudioTime(audio.currentTime);
            }
        });

        audio.addEventListener('ended', () => {
            playBtn?.classList.remove('hidden');
            pauseBtn?.classList.add('hidden');
            const fill = document.getElementById(audioId + '-fill');
            if (fill) fill.style.width = '0%';
        });

        audio.addEventListener('loadedmetadata', () => {
            const timeEl = document.getElementById(audioId + '-time');
            if (timeEl && audio.duration) {
                timeEl.textContent = formatAudioTime(audio.duration);
            }
        });
    }
}

function seekAudio(event, audioId) {
    const audio = document.getElementById(audioId);
    const track = event.currentTarget;
    if (!audio || !track) return;

    const rect = track.getBoundingClientRect();
    const percent = (event.clientX - rect.left) / rect.width;
    audio.currentTime = percent * audio.duration;
}

function formatAudioTime(seconds) {
    if (!seconds || isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return mins + ':' + (secs < 10 ? '0' : '') + secs;
}

// Copy message text
function copyMessageText(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Could show a toast notification here
    });
}
</script>
